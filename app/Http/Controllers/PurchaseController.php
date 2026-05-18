<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Batch;
use App\Models\SupplierLedger;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    public function create()
    {
        $suppliers = Supplier::all();
        return view('purchases.create', compact('suppliers'));
    }

    public function createCredit()
    {
        $suppliers = Supplier::all();
        return view('purchases.create_credit', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'items'       => 'nullable|array'
        ]);

        // Filter out blank rows (has either item_id or code) — rows without both are skipped
        $items = array_filter($request->items ?? [], fn($row) => !empty($row['item_id']) || !empty($row['code']));

        if (empty($items)) {
            return back()->with('error', 'Please add at least one valid item.')->withInput();
        }

        try {
            DB::transaction(function () use ($request, $items) {

                // 1. Create Purchase Header
                $paymentType = $request->payment_type ?? 'Cash';
                $paymentStatus = ($paymentType === 'Credit') ? 'Pending' : 'Paid';

                // For Credit, paid_from_account should be null
                $paidFrom = ($paymentType === 'Credit') ? null : $request->paid_from_account;

                // invoice_date is NOT NULL — use today as fallback
                $invoiceDate = !empty($request->purchase_date) ? $request->purchase_date : now()->toDateString();

                $purchase = Purchase::create([
                    'purchase_no'      => $request->purchase_no,
                    'vendor_bill_no'   => $request->vendor_bill_no,
                    'invoice_date'     => $invoiceDate,
                    'supplier_id'      => $request->supplier_id,
                    'paid_from_account'=> $paidFrom,
                    'payment_type'     => $paymentType,
                    'due_date'         => $request->due_date ?? null,
                    'payment_status'   => $paymentStatus,
                    'status'           => 'received',
                    'gross_total'      => 0,
                    'tax_amount'       => $request->tax_amount ?? 0,
                    'discount'         => $request->discount ?? 0,
                    'net_total'        => 0,
                    'user_id'          => auth()->id() ?? 1,
                    'notes'            => $request->memo,
                ]);

                $subtotal = 0;

                // 2. Process Items
                foreach ($items as $row) {
                    $itemId = $row['item_id'];
                    if (empty($itemId) || $itemId === 'new') {
                        $item = Item::firstOrCreate(
                            ['code' => trim($row['code'])],
                            [
                                'item_type' => 'Stock',
                                'description' => $row['name'] ?: 'New Item ' . $row['code'],
                                'cost_rate' => $row['rate'],
                                'sale_rate' => $row['rate'] * 1.25,
                                'on_hand' => 0
                            ]
                        );
                        $itemId = $item->id;
                    } else {
                        $item = Item::find($itemId);
                    }
                    if (!$item) continue;

                    $line_total = ($row['qty'] ?? 0) * ($row['rate'] ?? 0);
                    $subtotal += $line_total;

                    $batchNo = !empty($row['batch_no']) ? $row['batch_no'] : 'B-' . date('Ymd') . '-' . mt_rand(1000, 9999);

                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'item_id'     => $itemId,
                        'batch_no'    => $batchNo,
                        'expiry_date' => !empty($row['expiry_date']) ? $row['expiry_date'] : null,
                        'qty'         => $row['qty'],
                        'cost_rate'   => $row['rate'],
                        'total'       => $line_total
                    ]);

                    // 3. Increment Stock & Update Master Cost Price
                    $item->increment('on_hand', $row['qty']);
                    if ($row['rate'] > 0) {
                        $item->update(['cost_rate' => $row['rate']]);
                    }

                    // 4. CREATE FIFO Batch
                    // expires_at is a timestamp column — parse carefully
                    $expiresAt = null;
                    if (!empty($row['expiry_date'])) {
                        try { $expiresAt = Carbon::parse($row['expiry_date']); } catch (\Exception $e) { $expiresAt = null; }
                    }

                    Batch::create([
                        'item_id'            => $itemId,
                        'batch_no'           => $batchNo,
                        'quantity_available' => $row['qty'],
                        'sale_price'         => $item->sale_rate ?? 0,
                        'cost_price'         => $row['rate'],
                        'received_at'        => Carbon::parse($invoiceDate),
                        'expires_at'         => $expiresAt,
                    ]);
                }

                // 5. Update Header Totals
                $net_total = $subtotal + ($request->tax_amount ?? 0) - ($request->discount ?? 0);
                $purchase->update([
                    'gross_total' => $subtotal,
                    'net_total' => $net_total
                ]);

                // 6. Update Accounting & Ledger
                if ($paymentType === 'Credit') {
                    $supplier = Supplier::find($request->supplier_id);
                    if ($supplier) {
                        $supplier->increment('current_balance', $net_total);
                        
                        // Create Supplier Ledger Entry
                        SupplierLedger::create([
                            'supplier_id' => $supplier->id,
                            'date' => $request->purchase_date ?? now(),
                            'reference_type' => 'Purchase',
                            'reference_id' => $purchase->id,
                            'description' => 'Purchase Inv: ' . $purchase->purchase_no,
                            'debit' => 0,
                            'credit' => $net_total,
                            'balance' => $supplier->fresh()->current_balance
                        ]);
                    }
                } else {
                    // Paid immediately via Account
                    if ($paidFrom) {
                        $account = Account::find($paidFrom);
                        if ($account) {
                            $account->decrement('current_balance', $net_total);
                            // NOTE: If General Ledger tracking is fully implemented, a GLEntry could also be recorded here.
                        }
                    }
                }
            });

            return back()->with('success', 'Purchase Recorded! Stock Updated.');
        } catch (\Exception $e) {
            \Log::error('Purchase Error: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        $purchase = Purchase::with(['supplier', 'items.item', 'user'])->findOrFail($id);
        return view('purchases.print', compact('purchase'));
    }
}
