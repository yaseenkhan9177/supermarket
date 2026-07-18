<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Item;
use App\Models\Customer;
use App\Models\User;
use App\Services\FifoStockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CashSalesController extends Controller
{
    // 1. Show the Form
    public function create()
    {
        $customers = Customer::all();
        $salesmen = User::all();

        // Generate Invoice No (e.g., CS-2026-0001)
        $nextInvoice = 'CS-' . date('Y') . '-' . str_pad(Sale::count() + 1, 4, '0', STR_PAD_LEFT);

        $activeWallet = \App\Models\Wallet::where('is_active', true)->first();
        $activeWalletName = $activeWallet ? $activeWallet->name : 'Shop Counter';

        return view('cash-sales.create', compact('customers', 'salesmen', 'nextInvoice', 'activeWalletName'));
    }

    // 2. Search Items API
    public function searchItems(Request $request)
    {
        $query = $request->get('q');

        // 🔥 UPDATED: Select 'on_hand' instead of stock_qty to show correct stock in search
        // Schema mapping: description -> name, sale_rate -> sale_price
        $items = Item::where('description', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->select('id', 'description as name', 'code', 'sale_rate as price', 'on_hand as stock_qty', 'item_type')
            ->limit(50) // ✅ CHANGED: Increased limit to show more results
            ->get();
        return response()->json($items);
    }

    public function show($id)
    {
        $sale = Sale::with(['items', 'user'])->findOrFail($id);
        return view('sales.receipt', compact('sale'));
    }

    // 3. Store Sale (The Main Engine)
    public function store(Request $request)
    {
        $request->validate([
            'rows'        => 'required|array|min:1',
            'grand_total' => 'required|numeric',
        ]);

        try {
            $fifo = new FifoStockService();

            $sale = DB::transaction(function () use ($request, $fifo) {
                $returnAdj = $request->input('return_adjustment', 0);

                // A. Create Invoice Header with placeholder totals (recalculated below)
                $sale = Sale::create([
                    'invoice_no'        => $request->invoice_no,
                    'customer_id'       => $request->customer_id,
                    'user_id'           => $request->salesman_id ?? Auth::id(),
                    'sale_date'         => $request->date ?? now(),
                    'subtotal'          => 0,
                    'return_adjustment' => $returnAdj,
                    'grand_total'       => 0,
                    'paid_amount'       => $request->received_amount ?? 0,
                    'change_amount'     => 0,
                    'payment_mode'      => 'Cash',
                    'status'            => 'completed',
                ]);

                $calculatedSubtotal = 0;

                // B. Save Items & Deduct Stock via FIFO
                foreach ($request->rows as $row) {
                    $item = Item::where('id', $row['id'])->lockForUpdate()->first();
                    if (!$item) {
                        continue;
                    }

                    $qty = (float) $row['qty'];

                    if ($item->item_type === 'Service') {
                        // Service items: no stock, use the rate the cashier entered
                        $lineTotal = $qty * $row['price'];
                        SaleItem::create([
                            'sale_id'   => $sale->id,
                            'item_id'   => $item->id,
                            'item_name' => $item->description,
                            'batch_id'  => null,
                            'qty'       => $qty,
                            'rate'      => $row['price'],
                            'total'     => $lineTotal,
                        ]);
                        $calculatedSubtotal += $lineTotal;
                    } else {
                        // Stock item: FIFO deduction — may span multiple batches
                        $result = $fifo->deductStock($item->id, $qty, $sale->id, Auth::id());

                        foreach ($result['batches_used'] as $batchUsed) {
                            $lineTotal = $batchUsed['quantity_deducted'] * $batchUsed['sale_price'];
                            SaleItem::create([
                                'sale_id'   => $sale->id,
                                'item_id'   => $item->id,
                                'item_name' => $item->description,
                                'batch_id'  => $batchUsed['batch_id'],
                                'qty'       => $batchUsed['quantity_deducted'],
                                'rate'      => $batchUsed['sale_price'],
                                'total'     => $lineTotal,
                            ]);
                            $calculatedSubtotal += $lineTotal;
                        }
                    }
                }

                // C. Update Sale header with FIFO-accurate totals
                $grandTotal = max(0, $calculatedSubtotal - $returnAdj);
                $sale->update([
                    'subtotal'      => $calculatedSubtotal,
                    'grand_total'   => $grandTotal,
                    'change_amount' => ($request->received_amount ?? 0) - $grandTotal,
                ]);

                // D. Adjust Active Wallet Balance
                $activeWallet = \App\Models\Wallet::where('is_active', true)->first();
                if ($activeWallet) {
                    $activeWallet->adjustBalance($grandTotal);
                }

                return $sale;
            });

            // E. Generate Receipt View
            $receiptHtml = view('sales.receipt', compact('sale'))->render();
            $receiptHtml = str_replace(['window.print()', 'window.close()'], '', $receiptHtml);

            // F. Send Response
            return response()->json([
                'success'     => true,
                'invoice_no'  => $sale->invoice_no,
                'receipt_html'=> $receiptHtml,
                'sale_id'     => $sale->id,
                'print_url'   => route('cash-sales.show', $sale->id),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
