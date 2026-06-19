<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseCharge;
use App\Models\PurchasePaymentSplit;
use App\Models\TaxChargeType;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Batch;
use App\Models\SupplierLedger;
use App\Models\Account;
use App\Models\Wallet;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    public function create()
    {
        $suppliers   = Supplier::orderBy('name')->get();
        $accounts    = Account::where('type', 'Asset')->orderBy('name')->get();
        $chargeTypes = TaxChargeType::orderBy('name')->get();
        
        $prefilledItem = null;
        if (request('item_id')) {
            $prefilledItem = Item::find(request('item_id'));
        }
        
        return view('purchases.create', compact('suppliers', 'accounts', 'chargeTypes', 'prefilledItem'));
    }

    public function createCredit()
    {
        $suppliers = Supplier::all();
        return view('purchases.create_credit', compact('suppliers'));
    }

    /**
     * Store a new cash purchase bill.
     *
     * Accepts a multi-split payments array and automatically applies any
     * existing supplier credit (return credit) before debiting accounts.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items'       => 'nullable|array',
            'payments'    => 'nullable|array',
        ]);

        // Filter out blank item rows
        $items = array_filter($request->items ?? [], fn($row) => !empty($row['item_id']) || !empty($row['code']));

        if (empty($items)) {
            return back()->with('error', 'Please add at least one valid item.')->withInput();
        }

        // Validate payment splits
        $payments = array_filter($request->payments ?? [], fn($p) => !empty($p['amount']) && $p['amount'] > 0);

        try {
            DB::transaction(function () use ($request, $items, $payments) {

                // ── 1. Compute item subtotal ──────────────────────────────────────
                $subtotal  = 0;
                $itemsData = []; // will hold enriched row data

                foreach ($items as $row) {
                    $itemId = $row['item_id'] ?? null;

                    if (empty($itemId) || $itemId === 'new') {
                        $item = Item::firstOrCreate(
                            ['code' => trim($row['code'])],
                            [
                                'item_type'   => 'Stock',
                                'description' => $row['name'] ?: 'New Item ' . $row['code'],
                                'cost_rate'   => $row['rate'],
                                'sale_rate'   => $row['rate'] * 1.25,
                                'on_hand'     => 0
                            ]
                        );
                        $itemId = $item->id;
                    } else {
                        $item = Item::find($itemId);
                    }

                    if (!$item) continue;

                    $lineTotal  = ($row['qty'] ?? 0) * ($row['rate'] ?? 0);
                    $subtotal  += $lineTotal;
                    $itemsData[] = compact('item', 'row', 'itemId', 'lineTotal');
                }

                $taxAmount   = $request->tax_amount  ?? 0;
                $discount    = $request->discount    ?? 0;
                $grossNet    = $subtotal + $taxAmount - $discount;

                // ── 2. Apply supplier return credit ───────────────────────────────
                $supplier      = Supplier::findOrFail($request->supplier_id);
                $creditApplied = 0;
                $netTotal      = $grossNet;

                if ($supplier->has_credit) {
                    $availableCredit = $supplier->return_credit; // abs(negative balance)
                    $creditApplied   = min($availableCredit, $grossNet);
                    $netTotal        = $grossNet - $creditApplied;
                }

                // ── 3. Validate payment splits sum == netTotal ────────────────────
                $splitTotal = collect($payments)->sum('amount');

                // Allow small floating-point rounding (within 1 PKR)
                if (abs($splitTotal - $netTotal) > 1.00) {
                    throw new \Exception(
                        "Payment split total (Rs. " . number_format($splitTotal, 2) .
                        ") does not match the net payable (Rs. " . number_format($netTotal, 2) . "). " .
                        "Please adjust your payments."
                    );
                }

                // ── 4. Create Purchase Header ─────────────────────────────────────
                $invoiceDate = !empty($request->purchase_date)
                    ? $request->purchase_date
                    : now()->toDateString();

                $purchase = Purchase::create([
                    'purchase_no'       => $request->purchase_no,
                    'vendor_bill_no'    => $request->vendor_bill_no,
                    'invoice_date'      => $invoiceDate,
                    'supplier_id'       => $request->supplier_id,
                    'paid_from_account' => null, // replaced by payment splits
                    'payment_type'      => 'Cash',
                    'payment_status'    => 'Paid',
                    'status'            => 'received',
                    'gross_total'       => $subtotal,
                    'tax_amount'        => $taxAmount,
                    'discount'          => $discount,
                    'net_total'         => $netTotal,
                    'user_id'           => auth()->id() ?? 1,
                    'notes'             => $request->memo,
                ]);

                // ── 5. Save Item Lines, Stock, Batches ────────────────────────────
                foreach ($itemsData as $data) {
                    ['item' => $item, 'row' => $row, 'itemId' => $itemId, 'lineTotal' => $lineTotal] = $data;

                    $batchNo = !empty($row['batch_no'])
                        ? $row['batch_no']
                        : 'B-' . date('Ymd') . '-' . mt_rand(1000, 9999);

                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'item_id'     => $itemId,
                        'batch_no'    => $batchNo,
                        'expiry_date' => !empty($row['expiry_date']) ? $row['expiry_date'] : null,
                        'qty'         => $row['qty'],
                        'cost_rate'   => $row['rate'],
                        'total'       => $lineTotal,
                    ]);

                    $item->increment('on_hand', $row['qty']);
if ($item->on_hand < ($item->min_stock_level ?? 0)) {
    session()->flash('warning', "Note: {$item->name} is below minimum stock level after this purchase (Current: {$item->on_hand}, Minimum: {$item->min_stock_level}).");
}
                    if ($row['rate'] > 0) {
                        $item->update(['cost_rate' => $row['rate']]);
                    }

                    // FIFO Batch record
                    $expiresAt = null;
                    if (!empty($row['expiry_date'])) {
                        try { $expiresAt = Carbon::parse($row['expiry_date']); } catch (\Exception $e) {}
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

                // ── 6. Save Payment Splits & Debit Accounts ───────────────────────
                foreach ($payments as $payment) {
                    if (empty($payment['amount']) || $payment['amount'] <= 0) continue;

                    $accountId = !empty($payment['account_id']) ? $payment['account_id'] : null;

                    PurchasePaymentSplit::create([
                        'purchase_id'    => $purchase->id,
                        'payment_method' => $payment['method'] ?? 'Cash Drawer',
                        'account_id'     => $accountId,
                        'amount'         => $payment['amount'],
                        'reference_no'   => $payment['reference_no'] ?? null,
                    ]);

                    // Debit the source account if one was specified
                    if ($accountId) {
                        $account = Account::find($accountId);
                        if ($account) {
                            $account->decrement('current_balance', $payment['amount']);
                        }
                    }
                }

                // ── 7. Save Import / Clearing Charges ───────────────────────────
                $charges = array_filter($request->charges ?? [], fn($c) => !empty($c['tax_charge_type_id']) && !empty($c['amount']) && $c['amount'] > 0);
                foreach ($charges as $charge) {
                    PurchaseCharge::create([
                        'purchase_id'        => $purchase->id,
                        'tax_charge_type_id' => $charge['tax_charge_type_id'],
                        'amount'             => $charge['amount'],
                    ]);
                }

                // ── 8. Settle the supplier credit & ledger entries ────────────────
                if ($creditApplied > 0) {
                    // Reduce the supplier's negative balance (credit is consumed)
                    $supplier->increment('current_balance', $creditApplied);

                    SupplierLedger::create([
                        'supplier_id'    => $supplier->id,
                        'date'           => $invoiceDate,
                        'reference_type' => 'CreditApplied',
                        'reference_id'   => $purchase->id,
                        'description'    => 'Return Credit Applied on Bill #' . $purchase->purchase_no,
                        'debit'          => $creditApplied, // reduces what they owe us
                        'credit'         => 0,
                        'balance'        => $supplier->fresh()->current_balance,
                    ]);
                }

                // Log the purchase payment in supplier ledger
                SupplierLedger::create([
                    'supplier_id'    => $supplier->id,
                    'date'           => $invoiceDate,
                    'reference_type' => 'Purchase',
                    'reference_id'   => $purchase->id,
                    'description'    => 'Cash Purchase Inv: ' . $purchase->purchase_no .
                                       ($creditApplied > 0 ? ' (Credit Rs.' . number_format($creditApplied, 2) . ' Applied)' : ''),
                    'debit'          => 0,
                    'credit'         => $netTotal,
                    'balance'        => $supplier->fresh()->current_balance,
                ]);

                // ── 9. Deduct from active wallet ──────────────────────────────────
                $activeWallet = Wallet::where('is_active', true)->first();
                if ($activeWallet && $netTotal > 0) {
                    $activeWallet->adjustBalance(-$netTotal);
                }
            });

            return back()->with('success', 'Purchase Recorded! Stock Updated.');

        } catch (\Exception $e) {
            \Log::error('Purchase Error: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function print($id)
    {
        $purchase = Purchase::with(['supplier', 'items.item', 'paymentSplits', 'charges.taxChargeType', 'user'])->findOrFail($id);
        return view('purchases.print', compact('purchase'));
    }
    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'items.item', 'paymentSplits', 'charges.taxChargeType', 'user'])->findOrFail($id);
        return view('purchases.show', compact('purchase'));
    }

}
