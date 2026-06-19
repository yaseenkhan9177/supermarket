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
            return DB::transaction(function () use ($request) {

                // 1. Calculate Totals Backend-Side (For Security)
                $calculatedTotal = 0;
                foreach ($request->cart as $item) {
                    // Fetch latest price from DB to avoid tampering
                    $product = Item::find($item['id']);
                    if ($product) {
                        $calculatedTotal += ($product->sale_rate * $item['qty']);
                    }
                }

                $returnAdj = $request->input('return_adjustment', 0);
                $grandTotal = max(0, $calculatedTotal - $returnAdj);

                // 2. Create the Invoice Record
                $sale = Sale::create([
                    'invoice_no' => 'INV-' . time(),
                    'user_id' => auth()->id(),
                    'subtotal' => $calculatedTotal,
                    'return_adjustment' => $returnAdj,
                    'grand_total' => $grandTotal,
                    'paid_amount' => $request->amount_received,
                    'change_amount' => $request->amount_received - $grandTotal,
                    'payment_mode' => 'Cash',
                    'status' => 'completed',
                    'sale_date' => now(), // Ensure date is recorded
                ]);

                // Adjust Active Wallet Balance
                $activeWallet = \App\Models\Wallet::where('is_active', true)->first();
                if ($activeWallet) {
                    $activeWallet->adjustBalance($grandTotal);
                }

                // 3. Save Items & Deduct Stock
                foreach ($request->cart as $cartItem) {
                    $product = Item::lockForUpdate()->find($cartItem['id']);

                    if ($product) {
                        // Check Stock
                        if ($product->item_type !== 'Service' && $product->on_hand < $cartItem['qty']) {
                            throw new \Exception("Insufficient stock for {$product->description}");
                        }

                        // Save Line Item
                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'item_id' => $product->id,
                            'qty' => $cartItem['qty'],
                            'rate' => $product->sale_rate,
                            'total' => $cartItem['qty'] * $product->sale_rate,
                            'item_name' => $product->description,
                        ]);

                        // 🔥 CRITICAL: Deduct Stock Here
                        // Using 'on_hand' as per your DB schema
                        if ($product->item_type !== 'Service') {
                            $product->decrement('on_hand', $cartItem['qty']);
                        }
                    }
                }

                // 4. Post Accounting (Optional, keeping if you want it)
                // $this->postAccountingEntries($sale);

                return response()->json([
                    'success' => true,
                    'message' => 'Sale Recorded!',
                    'invoice_no' => $sale->invoice_no,
                    'print_url' => route('sales.print', $sale->id) // For Receipt Popup
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
        // 1. Calculate Stats for Cards (Global Totals)
        $stats = [
            'all_count' => Sale::count(),
            'all_total' => Sale::sum('grand_total'),

            'cash_count' => Sale::where('payment_mode', 'Cash')->count(),
            'cash_total' => Sale::where('payment_mode', 'Cash')->sum('grand_total'),

            'debit_count' => Sale::where('payment_mode', 'Debit')->count(),
            'debit_total' => Sale::where('payment_mode', 'Debit')->sum('grand_total'),
        ];

        // 2. Filter Logic (Based on which card is clicked)
        $query = Sale::with(['user', 'customer'])->latest();

        if ($request->has('type') && $request->type != 'all') {
            $query->where('payment_mode', $request->type);
        }

        $sales = $query->paginate(15);

        return view('sales.history', compact('sales', 'stats'));
    }
}
