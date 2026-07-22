<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Refund;
use App\Models\RefundItem;
use App\Models\Item;
use App\Models\Customer;
use App\Models\CashSale;
use App\Models\DebitSale;
use App\Models\Sale;
use App\Models\WasteLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RefundController extends Controller
{
    // =========================================================================
    // INDEX — list of processed returns
    // =========================================================================

    public function index()
    {
        $refunds = Refund::with(['customer', 'processedBy'])
            ->latest()
            ->paginate(20);

        return view('refunds.index', compact('refunds'));
    }

    // =========================================================================
    // CREATE — the new bill-search returns page
    // =========================================================================

    public function create()
    {
        return view('refunds.create');
    }

    // =========================================================================
    // SEARCH BILLS — AJAX endpoint
    // Searches cash_sales, debit_sales, and POS sales by:
    //   - customer name
    //   - customer phone
    //   - invoice_no
    // Returns a JSON array of matching bill summaries.
    // =========================================================================

    public function searchBills(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $like = '%' . $q . '%';
        $results = [];

        // --- Cash Sales ---
        $cashSales = CashSale::with('customer')
            ->where(function ($query) use ($like) {
                $query->where('invoice_no', 'LIKE', $like)
                      ->orWhere('customer_name', 'LIKE', $like)
                      ->orWhereHas('customer', function ($cq) use ($like) {
                          $cq->where('name', 'LIKE', $like)
                             ->orWhere('phone', 'LIKE', $like);
                      });
            })
            ->orderByDesc('sale_date')
            ->limit(15)
            ->get();

        foreach ($cashSales as $sale) {
            $results[] = [
                'id'           => $sale->id,
                'source'       => 'cash_sale',
                'invoice_no'   => $sale->invoice_no,
                'date'         => optional($sale->sale_date)->format('d M Y'),
                'customer_name'=> $sale->customer_name ?? 'Walk-in',
                'customer_id'  => $sale->customer_id,
                'grand_total'  => number_format($sale->grand_total, 2),
                'type_label'   => 'Cash Sale',
                'type_color'   => 'green',
            ];
        }

        // --- Debit Sales ---
        $debitSales = DebitSale::with('customer')
            ->where(function ($query) use ($like) {
                $query->where('invoice_no', 'LIKE', $like)
                      ->orWhereHas('customer', function ($cq) use ($like) {
                          $cq->where('name', 'LIKE', $like)
                             ->orWhere('phone', 'LIKE', $like);
                      });
            })
            ->orderByDesc('invoice_date')
            ->limit(15)
            ->get();

        foreach ($debitSales as $sale) {
            $results[] = [
                'id'           => $sale->id,
                'source'       => 'debit_sale',
                'invoice_no'   => $sale->invoice_no,
                'date'         => optional($sale->invoice_date)->format('d M Y'),
                'customer_name'=> optional($sale->customer)->name ?? 'Unknown',
                'customer_id'  => $sale->customer_id,
                'grand_total'  => number_format($sale->net_total, 2),
                'type_label'   => 'Debit Sale',
                'type_color'   => 'orange',
            ];
        }

        // --- POS Sales ---
        $posSales = Sale::with('customer')
            ->where(function ($query) use ($like) {
                $query->where('invoice_no', 'LIKE', $like)
                      ->orWhere('customer_name', 'LIKE', $like)
                      ->orWhereHas('customer', function ($cq) use ($like) {
                          $cq->where('name', 'LIKE', $like)
                             ->orWhere('phone', 'LIKE', $like);
                      });
            })
            ->orderByDesc('sale_date')
            ->limit(15)
            ->get();

        foreach ($posSales as $sale) {
            $results[] = [
                'id'           => $sale->id,
                'source'       => 'pos_sale',
                'invoice_no'   => $sale->invoice_no,
                'date'         => optional($sale->sale_date)->format('d M Y'),
                'customer_name'=> $sale->customer_name ?? 'Walk-in',
                'customer_id'  => $sale->customer_id,
                'grand_total'  => number_format($sale->grand_total, 2),
                'type_label'   => 'POS Sale',
                'type_color'   => 'blue',
            ];
        }

        return response()->json($results);
    }

    // =========================================================================
    // GET BILL ITEMS — AJAX endpoint
    // Returns line items for one specific bill, normalised into a common shape.
    // =========================================================================

    public function getBillItems(Request $request)
    {
        $source = $request->input('source');
        $id     = $request->input('id');

        $items = [];

        if ($source === 'cash_sale') {
            $sale = CashSale::with('items')->findOrFail($id);
            foreach ($sale->items as $row) {
                $items[] = [
                    'line_item_id' => $row->id,
                    'product_id'   => $row->product_id,
                    'item_name'    => $row->item_name,
                    'quantity'     => $row->quantity,
                    'rate'         => $row->rate,
                    'total'        => $row->total,
                ];
            }
        } elseif ($source === 'debit_sale') {
            $sale = DebitSale::with('items')->findOrFail($id);
            foreach ($sale->items as $row) {
                $items[] = [
                    'line_item_id' => $row->id,
                    'product_id'   => $row->product_id,
                    'item_name'    => $row->item_name,
                    'quantity'     => $row->quantity,
                    'rate'         => $row->rate,
                    'total'        => $row->net_amount,
                ];
            }
        } elseif ($source === 'pos_sale') {
            $sale = Sale::with('items')->findOrFail($id);
            foreach ($sale->items as $row) {
                $items[] = [
                    'line_item_id' => $row->id,
                    'product_id'   => $row->item_id,
                    'item_name'    => $row->item_name,
                    'quantity'     => $row->qty,
                    'rate'         => $row->rate,
                    'total'        => $row->total,
                ];
            }
        } else {
            return response()->json(['error' => 'Unknown source'], 422);
        }

        return response()->json($items);
    }

    // =========================================================================
    // STORE — process the return transaction
    // =========================================================================

    public function store(Request $request)
    {
        $request->validate([
            'items'         => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:items,id',
            'items.*.item_name'    => 'required|string',
            'items.*.return_qty'   => 'required|numeric|min:0.01',
            'items.*.rate'         => 'required|numeric|min:0',
            'items.*.condition'    => 'required|in:restock,damaged',
            'items.*.sale_source'  => 'required|in:cash_sale,debit_sale,pos_sale',
            'items.*.bill_id'      => 'required|integer',
            'refund_method'        => 'required|in:CASH,STORE_CREDIT,REDUCE_DEBIT',
        ], [
            'items.required'       => 'Please add at least one item to the return cart.',
            'items.min'            => 'Please add at least one item to the return cart.',
            'refund_method.required' => 'Please select a refund method.',
        ]);

        DB::beginTransaction();
        try {
            // -----------------------------------------------------------------
            // 1. Calculate total refund amount
            // -----------------------------------------------------------------
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['return_qty'] * $item['rate'];
            }

            // -----------------------------------------------------------------
            // 2. Determine customer_id (may come from any of the selected bills)
            // -----------------------------------------------------------------
            $customerId = $request->input('customer_id') ?: null;

            // -----------------------------------------------------------------
            // 3. Determine refund_mode for the DB (map incoming value)
            // -----------------------------------------------------------------
            $refundMode = $request->refund_method; // CASH | STORE_CREDIT | REDUCE_DEBIT

            // -----------------------------------------------------------------
            // 4. Create the Refund header record
            // -----------------------------------------------------------------
            $creditNo = 'CR-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

            $refund = Refund::create([
                'credit_no'       => $creditNo,
                'refund_date'     => now()->toDateString(),
                'customer_id'     => $customerId,
                'salesman_id'     => null,
                'processed_by'    => Auth::id(),
                'paid_from_account' => 'Cash Drawer',
                'memo'            => $request->input('memo'),
                'total_amount'    => $totalAmount,
                'status'          => 'completed',
                'refund_mode'     => $refundMode,
            ]);

            // -----------------------------------------------------------------
            // 5. Create RefundItem rows + handle stock / waste logic
            // -----------------------------------------------------------------
            foreach ($request->items as $item) {
                $netAmount = $item['return_qty'] * $item['rate'];

                // Map 'restock' → 'sellable', 'damaged' → 'damaged' (existing ENUM values)
                $condition = $item['condition'] === 'restock' ? 'sellable' : 'damaged';

                RefundItem::create([
                    'refund_id'       => $refund->id,
                    'product_id'      => $item['product_id'],
                    'sale_item_id'    => $item['line_item_id'] ?? null,
                    'item_name'       => $item['item_name'],
                    'quantity'        => $item['return_qty'],
                    'rate'            => $item['rate'],
                    'net_amount'      => $netAmount,
                    'condition'       => $condition,
                    'sale_source'     => $item['sale_source'],
                    'original_bill_id'=> $item['bill_id'],
                ]);

                if ($item['condition'] === 'restock') {
                    // Add back to sellable stock (on_hand cache)
                    // NOTE: Not touching batch/FIFO logic (separate purchase bug fix)
                    Item::where('id', $item['product_id'])
                        ->increment('on_hand', $item['return_qty']);
                } else {
                    // Damaged / Expired — log to waste_logs, do NOT restock
                    WasteLog::create([
                        'item_id'   => $item['product_id'],
                        'quantity'  => $item['return_qty'],
                        'reason'    => 'Customer return — damaged/expired',
                        'refund_id' => $refund->id,
                        'user_id'   => Auth::id(),
                        'logged_at' => now(),
                    ]);
                }
            }

            // -----------------------------------------------------------------
            // 6. Apply the chosen refund method's side effect
            // -----------------------------------------------------------------
            if ($customerId) {
                $customer = Customer::find($customerId);

                if ($refundMode === 'STORE_CREDIT' && $customer) {
                    // Add to customer's store credit balance
                    $customer->increment('store_credit', $totalAmount);
                    \App\Models\CustomerLedgerEntry::create([
                        'customer_id'   => $customer->id,
                        'type'          => 'return',
                        'amount'        => -$totalAmount,
                        'balance_after' => $customer->fresh()->balance,
                        'note'          => 'Customer Return (Store Credit) #' . $refundNumber,
                        'created_by'    => auth()->id(),
                    ]);

                } elseif ($refundMode === 'REDUCE_DEBIT' && $customer) {
                    // Reduce what the customer owes (balance is positive = they owe us)
                    $newBalance = max(0, $customer->balance - $totalAmount);
                    $customer->update(['balance' => $newBalance]);
                    \App\Models\CustomerLedgerEntry::create([
                        'customer_id'   => $customer->id,
                        'type'          => 'return',
                        'amount'        => -$totalAmount,
                        'balance_after' => $newBalance,
                        'note'          => 'Customer Return (Reduce Debit) #' . $refundNumber,
                        'created_by'    => auth()->id(),
                    ]);

                } elseif ($refundMode === 'CASH') {
                    // Cash out from active wallet
                    $activeWallet = \App\Models\Wallet::where('is_active', true)->first();
                    if ($activeWallet) {
                        $activeWallet->adjustBalance(-$totalAmount);
                    }
                    if ($customer) {
                        \App\Models\CustomerLedgerEntry::create([
                            'customer_id'   => $customer->id,
                            'type'          => 'return',
                            'amount'        => -$totalAmount,
                            'balance_after' => $customer->balance,
                            'note'          => 'Customer Return (Cash Refund) #' . $refundNumber,
                            'created_by'    => auth()->id(),
                        ]);
                    }
                }
            } elseif ($refundMode === 'CASH') {
                // Walk-in customer cash refund — still deduct from wallet
                $activeWallet = \App\Models\Wallet::where('is_active', true)->first();
                if ($activeWallet) {
                    $activeWallet->adjustBalance(-$totalAmount);
                }
            }

            DB::commit();

            return redirect()
                ->route('refunds.print', $refund->id)
                ->with('success', 'Return processed successfully. Credit Note: ' . $creditNo);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error processing return: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // PRINT — credit note receipt
    // =========================================================================

    public function print($id)
    {
        $refund = Refund::with(['items.item', 'customer', 'processedBy'])->findOrFail($id);
        return view('refunds.print', compact('refund'));
    }
}
