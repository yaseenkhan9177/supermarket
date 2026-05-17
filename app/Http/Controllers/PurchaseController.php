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
            'items' => 'required|array|min:1'
        ]);

        try {
            DB::transaction(function () use ($request) {

                // 1. Create Purchase Header
                $paymentType = $request->payment_type ?? 'Cash';
                $status = ($paymentType === 'Credit') ? 'Pending' : 'Paid';

                // For Credit, paid_from_account should be null
                $paidFrom = ($paymentType === 'Credit') ? null : $request->paid_from_account;

                $purchase = Purchase::create([
                    'purchase_no' => $request->purchase_no,
                    'vendor_bill_no' => $request->vendor_bill_no,
                    'purchase_date' => $request->purchase_date,
                    'supplier_id' => $request->supplier_id,
                    'paid_from_account' => $paidFrom,
                    'payment_type' => $paymentType,
                    'due_date' => $request->due_date,
                    'payment_status' => $status,
                    'subtotal' => 0,
                    'tax_amount' => $request->tax_amount ?? 0,
                    'discount' => $request->discount ?? 0,
                    'net_total' => 0,
                    'user_id' => auth()->id() ?? 1,
                    'memo' => $request->memo,
                ]);

                $subtotal = 0;

                // 2. Process Items
                foreach ($request->items as $row) {
                    if (!empty($row['item_id'])) {
                        // Validate Item Exists
                        $item = Item::find($row['item_id']);
                        if (!$item) continue;

                        $line_total = $row['qty'] * $row['rate'];
                        $subtotal += $line_total;

                        PurchaseItem::create([
                            'purchase_id' => $purchase->id,
                            'item_id' => $row['item_id'],
                            'batch_no' => $row['batch_no'] ?? null,
                            'expiry_date' => $row['expiry_date'] ?? null,
                            'qty' => $row['qty'],
                            'cost_rate' => $row['rate'],
                            'total' => $line_total
                        ]);

                        // 3. INCREMENT Stock & Update Cost
                        $item->increment('on_hand', $row['qty']);
                        
                        // Update Master Item Cost Price
                        if ($row['rate'] > 0) {
                            $item->update(['cost_rate' => $row['rate']]);
                        }

                        // 4. CREATE FIFO Batch
                        Batch::create([
                            'item_id' => $row['item_id'],
                            'batch_no' => !empty($row['batch_no']) ? $row['batch_no'] : 'B-' . date('Ymd') . '-' . mt_rand(1000, 9999),
                            'quantity_available' => $row['qty'],
                            'sale_price' => $item->sale_rate ?? 0,
                            'cost_price' => $row['rate'],
                            'received_at' => $request->purchase_date ?? now(),
                            'expires_at' => !empty($row['expiry_date']) ? $row['expiry_date'] : null,
                        ]);
                    }
                }

                // 5. Update Header Totals
                $net_total = $subtotal + ($request->tax_amount ?? 0) - ($request->discount ?? 0);
                $purchase->update([
                    'subtotal' => $subtotal,
                    'net_total' => $net_total
                ]);

                // 6. Update Accounting & Ledger
                if ($paymentType === 'Credit') {
                    $supplier = Supplier::find($request->supplier_id);
                    if ($supplier) {
                        $supplier->increment('balance', $net_total);
                        
                        // Create Supplier Ledger Entry
                        SupplierLedger::create([
                            'supplier_id' => $supplier->id,
                            'date' => $request->purchase_date ?? now(),
                            'reference_type' => 'Purchase',
                            'reference_id' => $purchase->id,
                            'description' => 'Purchase Inv: ' . $purchase->purchase_no,
                            'debit' => 0,
                            'credit' => $net_total,
                            'balance' => $supplier->fresh()->balance
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
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        $purchase = Purchase::with(['supplier', 'items.item', 'user'])->findOrFail($id);
        return view('purchases.print', compact('purchase'));
    }
}
