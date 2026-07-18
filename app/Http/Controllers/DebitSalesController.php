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

class DebitSalesController extends Controller
{
    // 0. Index (Redirects to Create)
    public function index()
    {
        return redirect()->route('debit-sales.create');
    }

    // 1. Show Page
    public function create()
    {
        // Fetch only registered customers (Debit requires a known person)
        $customers = Customer::all();
        $salesmen = User::all();

        // Generate Invoice No (DS = Debit Sale)
        $nextInvoice = 'DS-' . date('Y') . '-' . str_pad(Sale::count() + 1, 4, '0', STR_PAD_LEFT);

        return view('debit-sales.create', compact('customers', 'salesmen', 'nextInvoice'));
    }

    // 2. Search API (Same as Cash Sales)
    public function searchItems(Request $request)
    {
        $query = $request->get('q');
        // Schema Mapping: description -> name, sale_rate -> price
        $items = Item::where('description', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->select('id', 'description as name', 'code', 'sale_rate as price', 'on_hand as stock_qty', 'item_type')
            ->limit(50)
            ->get();
        return response()->json($items);
    }

    public function show($id)
    {
        $sale = Sale::with(['items', 'user'])->findOrFail($id);
        return view('sales.receipt', compact('sale'));
    }

    // 3. Store Debit Sale
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id', // MANDATORY FOR DEBIT
            'rows'        => 'required|array|min:1',
            'grand_total' => 'required|numeric',
        ]);

        try {
            $fifo = new FifoStockService();

            $sale = DB::transaction(function () use ($request, $fifo) {

                $paid = (float) ($request->received_amount ?? 0);

                // A. Create Invoice Header with placeholder totals (recalculated below)
                $sale = Sale::create([
                    'invoice_no'   => $request->invoice_no,
                    'customer_id'  => $request->customer_id,
                    'user_id'      => $request->salesman_id ?? Auth::id(),
                    'sale_date'    => $request->date ?? now(),
                    'subtotal'     => 0,
                    'grand_total'  => 0,
                    'paid_amount'  => $paid,
                    'change_amount'=> 0,
                    'payment_mode' => 'Debit',
                    'status'       => 'completed',
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
                        // Service items: no stock deduction
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
                $grandTotal = $calculatedSubtotal;
                $sale->update([
                    'subtotal'    => $calculatedSubtotal,
                    'grand_total' => $grandTotal,
                ]);

                // D. Update customer balance (amount still owed)
                $customer = Customer::lockForUpdate()->find($request->customer_id);
                if ($customer) {
                    $due = max(0, $grandTotal - $paid);
                    $customer->increment('balance', $due);
                }

                return $sale;
            });

            // E. Receipt
            $receiptHtml = view('sales.receipt', compact('sale'))->render();
            $receiptHtml = str_replace(['window.print()', 'window.close()'], '', $receiptHtml);

            return response()->json([
                'success'      => true,
                'invoice_no'   => $sale->invoice_no,
                'receipt_html' => $receiptHtml,
                'sale_id'      => $sale->id,
                'print_url'    => route('debit-sales.show', $sale->id),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
