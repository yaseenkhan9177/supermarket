<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\GLEntry;
use App\Services\FifoStockService;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function pos()
    {
        // Get all items for POS display (using correct column names)
        // Get all items for POS display (using correct column names)
        $items = Item::select('id', 'description as name', 'sale_rate as price', 'on_hand as stock', 'code', 'item_type as category', 'image_path')
            ->get();
        return view('sales.pos', compact('items'));
    }

    // 1️⃣ Step 1 & 2: Product Fetch & Form Logic
    // This API endpoint feeds your Alpine.js frontend search
    public function searchProducts(Request $request)
    {
        $query = $request->get('query');

        $products = Item::where('description', 'LIKE', "%{$query}%")
            ->orWhere('code', 'LIKE', "%{$query}%")
            ->select('id', 'description as name', 'code', 'sale_rate as sale_price', 'on_hand as stock_qty', 'image_path')
            // ->selectRaw('10 as tax_percent') // Fallback if column missing, or remove if you added migration
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $item->tax_percent = 0; // Default tax if column missing
                return $item;
            });

        return response()->json($products);
    }

    // 5️⃣ Step 5: Save Sale Transaction (The Heavy Lifting)
    public function store(Request $request)
    {
        $request->validate([
            'cart' => 'required|array|min:1',
            'amount_received' => 'required|numeric|min:0'
        ]);

        try {
            $fifo = new FifoStockService();

            return DB::transaction(function () use ($request, $fifo) {

                $returnAdj = $request->input('return_adjustment', 0);

                // Placeholder totals — will be recalculated below from actual batch prices
                $sale = Sale::create([
                    'invoice_no'        => 'INV-' . time(),
                    'user_id'           => auth()->id(),
                    'subtotal'          => 0,
                    'return_adjustment' => $returnAdj,
                    'grand_total'       => 0,
                    'paid_amount'       => $request->amount_received,
                    'change_amount'     => 0,
                    'payment_mode'      => 'Cash',
                    'status'            => 'completed',
                    'sale_date'         => now(),
                ]);

                $calculatedSubtotal = 0;

                // Save Items & Deduct Stock via FIFO
                foreach ($request->cart as $cartItem) {
                    $product = Item::lockForUpdate()->find($cartItem['id']);
                    if (!$product) {
                        continue;
                    }

                    $qty = (float) $cartItem['qty'];

                    if ($product->item_type === 'Service') {
                        // Service items: no stock deduction, use item sale_rate
                        $lineTotal = $qty * $product->sale_rate;
                        SaleItem::create([
                            'sale_id'   => $sale->id,
                            'item_id'   => $product->id,
                            'item_name' => $product->description,
                            'batch_id'  => null,
                            'qty'       => $qty,
                            'rate'      => $product->sale_rate,
                            'total'     => $lineTotal,
                        ]);
                        $calculatedSubtotal += $lineTotal;
                    } else {
                        // Stock item: FIFO deduction — may span multiple batches
                        $result = $fifo->deductStock($product->id, $qty, $sale->id, auth()->id());

                        foreach ($result['batches_used'] as $batchUsed) {
                            $lineTotal = $batchUsed['quantity_deducted'] * $batchUsed['sale_price'];
                            SaleItem::create([
                                'sale_id'   => $sale->id,
                                'item_id'   => $product->id,
                                'item_name' => $product->description,
                                'batch_id'  => $batchUsed['batch_id'],
                                'qty'       => $batchUsed['quantity_deducted'],
                                'rate'      => $batchUsed['sale_price'],
                                'total'     => $lineTotal,
                            ]);
                            $calculatedSubtotal += $lineTotal;
                        }
                    }
                }

                // Update Sale header with accurate FIFO-based totals
                $grandTotal = max(0, $calculatedSubtotal - $returnAdj);
                $sale->update([
                    'subtotal'     => $calculatedSubtotal,
                    'grand_total'  => $grandTotal,
                    'change_amount'=> $request->amount_received - $grandTotal,
                ]);

                // Adjust Active Wallet Balance
                $activeWallet = \App\Models\Wallet::where('is_active', true)->first();
                if ($activeWallet) {
                    $activeWallet->adjustBalance($grandTotal);
                }

                return response()->json([
                    'success'    => true,
                    'message'    => 'Sale Recorded!',
                    'invoice_no' => $sale->invoice_no,
                    'print_url'  => route('sales.print', $sale->id),
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // New Method: Print Receipt
    public function print($id)
    {
        $sale = Sale::with(['items', 'user'])->findOrFail($id);
        // Use a dedicated thermal-friendly view
        return view('sales.receipt', compact('sale'));
    }

    // Helper for Step 7
    private function postAccountingEntries($sale)
    {
        // Debit Cash
        GLEntry::create([
            'account_id' => 101, // Cash on Hand
            'debit' => $sale->grand_total,
            'credit' => 0,
            'description' => "Sale #{$sale->invoice_no}",
            'date' => now(),
        ]);

        // Credit Sales
        GLEntry::create([
            'account_id' => 401, // Sales Revenue
            'debit' => 0,
            'credit' => $sale->grand_total,
            'description' => "Revenue Sale #{$sale->invoice_no}",
            'date' => now(),
        ]);
    }

    public function apiCustomer($id)
    {
        $customer = Customer::findOrFail($id);
        return response()->json([
            'id' => $customer->id,
            'address' => $customer->address,
            'phone' => $customer->phone,
            'credit_limit' => number_format($customer->credit_limit, 2, '.', ''),
            'balance' => number_format($customer->balance, 2, '.', ''),
        ]);
    }

    public function apiProduct(Request $request)
    {
        $barcode = $request->query('barcode');
        $item = Item::where('code', $barcode)->first();

        if ($item) {
            return response()->json([
                'id' => $item->id,
                'description' => $item->description,
                'sale_rate' => $item->sale_rate,
                'on_hand' => $item->on_hand,
            ]);
        }

        return response()->json(['error' => 'Product not found'], 404);
    }
    public function history(Request $request)
    {
        $statsQuery = Sale::query();
        if ($request->filled('from_date')) {
            $statsQuery->whereDate('sale_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $statsQuery->whereDate('sale_date', '<=', $request->to_date);
        }

        // 1. Calculate Stats for Cards (Filtered or Global Totals)
        $stats = [
            'all_count' => (clone $statsQuery)->count(),
            'all_total' => (clone $statsQuery)->sum('grand_total'),

            'cash_count' => (clone $statsQuery)->where('payment_mode', 'Cash')->count(),
            'cash_total' => (clone $statsQuery)->where('payment_mode', 'Cash')->sum('grand_total'),

            'debit_count' => (clone $statsQuery)->where('payment_mode', 'Debit')->count(),
            'debit_total' => (clone $statsQuery)->where('payment_mode', 'Debit')->sum('grand_total'),
        ];

        // 2. Filter Logic
        $query = Sale::with(['user', 'customer'])->latest();

        if ($request->filled('from_date')) {
            $query->whereDate('sale_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('sale_date', '<=', $request->to_date);
        }
        if ($request->has('type') && $request->type != 'all') {
            $query->where('payment_mode', $request->type);
        }

        $sales = $query->paginate(15)->withQueryString();

        return view('sales.history', compact('sales', 'stats'));
    }

    public function todaysSales()
    {
        $todaySales = Sale::whereDate('sale_date', today())
            ->with(['customer:id,name', 'user:id,name'])
            ->withCount('items')
            ->latest('sale_date')
            ->get();

        $totalRevenue = $todaySales->sum('grand_total');

        return view('sales.today', compact('todaySales', 'totalRevenue'));
    }
}
