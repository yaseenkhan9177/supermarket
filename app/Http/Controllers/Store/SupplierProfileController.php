<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\SupplierLedgerEntry;
use App\Models\SupplierPaymentVoucher;
use App\Models\CompanySetting;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplierProfileController extends Controller
{
    /**
     * Helper to verify admin/owner access for financial operations.
     */
    private function checkAdminPermission()
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $role = strtolower($user->role ?? '');
        $isAdmin = in_array($role, ['owner', 'admin', 'store admin', 'super_owner', 'manager'])
            || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['owner', 'admin', 'Store Admin', 'Owner', 'manager']));

        if (!$isAdmin) {
            abort(403, 'Unauthorized action. Only admins can modify supplier financial records.');
        }
    }

    /**
     * Supplier profile page — KPIs + tabbed transaction histories.
     */
    public function show($id)
    {
        $supplier = Supplier::findOrFail($id);

        // ── Purchases ─────────────────────────────────────────────────────────
        $purchases = $supplier->purchases()
            ->withCount('items')
            ->orderBy('invoice_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'purchases_page')
            ->withQueryString();

        $totalPurchasesCount  = $supplier->purchases()->count();
        $totalPurchasesAmount = $supplier->purchases()->sum('net_total');

        // ── Payments Made ────────────────────────────────────────────────────
        $payments = $supplier->ledgerEntries()
            ->where('type', 'payment_made')
            ->with(['creator', 'voucher', 'reversal'])
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'payments_page')
            ->withQueryString();

        $totalPaidAmount = abs($supplier->ledgerEntries()
            ->where('type', 'payment_made')
            ->sum('amount'));

        // ── Outstanding Payable ──────────────────────────────────────────────
        $payableBalance = (float) $supplier->current_balance;

        // ── Unified Ledger Entries ───────────────────────────────────────────
        $ledgerEntries = $supplier->ledgerEntries()
            ->with(['creator', 'reversal', 'voucher'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15, ['*'], 'ledger_page')
            ->withQueryString();

        $user = auth()->user();
        $role = strtolower($user->role ?? '');
        $isAdmin = $user && (in_array($role, ['owner', 'admin', 'store admin', 'super_owner', 'manager'])
            || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['owner', 'admin', 'Store Admin', 'Owner', 'manager'])));

        return view('store.suppliers.show', compact(
            'supplier',
            'purchases',
            'payments',
            'ledgerEntries',
            'payableBalance',
            'totalPurchasesCount',
            'totalPurchasesAmount',
            'totalPaidAmount',
            'isAdmin'
        ));
    }

    /**
     * Pay Supplier (Reduces store's payable balance to supplier).
     * Creates matching immutable Supplier Payment Voucher.
     */
    public function paySupplier(Request $request, $id)
    {
        $this->checkAdminPermission();

        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'method' => 'required|string|in:cash,bank,easypaisa,jazzcash,cheque,other',
            'date'   => 'nullable|date',
            'note'   => 'nullable|string|max:1000',
        ]);

        $amount = (float) $request->amount;
        $supplier = Supplier::findOrFail($id);

        DB::transaction(function () use ($supplier, $amount, $request) {
            // Deduct payable balance (Store owes supplier less)
            $supplier->current_balance -= $amount;
            $supplier->save();

            $ledgerEntry = SupplierLedgerEntry::create([
                'supplier_id'   => $supplier->id,
                'type'          => 'payment_made',
                'amount'        => -$amount, // Negative = Store owes less
                'balance_after' => $supplier->current_balance,
                'method'        => $request->method,
                'note'          => $request->note ?: 'Payment Made (' . ucfirst($request->method) . ')',
                'created_by'    => auth()->id(),
                'created_at'    => $request->filled('date') ? $request->date . ' ' . now()->format('H:i:s') : now(),
            ]);

            $setting = CompanySetting::first();
            $storeName = $setting->business_name ?? config('app.name', 'Supermarket');

            $voucherNumber = SupplierPaymentVoucher::generateNextVoucherNumber();

            SupplierPaymentVoucher::create([
                'voucher_number'    => $voucherNumber,
                'supplier_id'       => $supplier->id,
                'ledger_entry_id'   => $ledgerEntry->id,
                'amount'            => $amount,
                'remaining_balance' => $supplier->current_balance,
                'payment_method'    => $request->method,
                'paid_by'           => auth()->id(),
                'store_name'        => $storeName,
            ]);
        });

        $supplier->refresh();

        return response()->json([
            'success'           => true,
            'message'           => 'Payment of Rs. ' . number_format($amount, 2) . ' paid to supplier successfully.',
            'current_balance'   => (float) $supplier->current_balance,
            'formatted_balance' => 'Rs. ' . number_format($supplier->current_balance, 2),
        ]);
    }

    /**
     * Manual adjustment of supplier balance.
     * Note is strictly required (min 10 characters).
     */
    public function adjustBalance(Request $request, $id)
    {
        $this->checkAdminPermission();

        $request->validate([
            'action' => 'required|in:add_payable,reduce_payable,set_balance',
            'amount' => 'required|numeric|min:0',
            'note'   => 'required|string|min:10|max:1000',
        ]);

        $supplier = Supplier::findOrFail($id);
        $amount   = (float) $request->amount;
        $action   = $request->action;

        DB::transaction(function () use ($supplier, $amount, $action, $request) {
            $oldBalance = (float) $supplier->current_balance;
            $delta      = 0;
            $newBalance = $oldBalance;

            if ($action === 'add_payable') {
                $delta      = $amount;
                $newBalance = $oldBalance + $amount;
            } elseif ($action === 'reduce_payable') {
                $delta      = -$amount;
                $newBalance = $oldBalance - $amount;
            } elseif ($action === 'set_balance') {
                $delta      = $amount - $oldBalance;
                $newBalance = $amount;
            }

            $supplier->current_balance = $newBalance;
            $supplier->save();

            SupplierLedgerEntry::create([
                'supplier_id'   => $supplier->id,
                'type'          => 'manual_adjustment',
                'amount'        => $delta,
                'balance_after' => $newBalance,
                'note'          => $request->note,
                'created_by'    => auth()->id(),
            ]);

            AuditLog::record(
                'supplier_adjustment',
                "Adjusted balance for supplier {$supplier->name} ({$action}, delta: Rs. " . number_format($delta, 2) . ") — Note: {$request->note}",
                'Supplier',
                $supplier->id,
                ['action' => $action, 'delta' => $delta, 'new_balance' => $newBalance]
            );
        });

        $supplier->refresh();

        return response()->json([
            'success'           => true,
            'message'           => 'Supplier balance adjusted successfully.',
            'current_balance'   => (float) $supplier->current_balance,
            'formatted_balance' => 'Rs. ' . number_format($supplier->current_balance, 2),
        ]);
    }

    /**
     * Reverse a specific payment ledger entry (payment_made).
     * Creates an offsetting payment_reversal entry and restores supplier payable.
     */
    public function reverseLedgerEntry(Request $request, $id, $entryId)
    {
        $this->checkAdminPermission();

        $request->validate([
            'note' => 'required|string|min:3|max:1000',
        ]);

        $supplier = Supplier::findOrFail($id);
        $entry    = SupplierLedgerEntry::where('supplier_id', $id)->findOrFail($entryId);

        if ($entry->type !== 'payment_made') {
            return response()->json(['success' => false, 'message' => 'Only payment entries can be reversed.'], 422);
        }

        $alreadyReversed = SupplierLedgerEntry::where('reversed_entry_id', $entry->id)->exists();
        if ($alreadyReversed) {
            return response()->json(['success' => false, 'message' => 'This entry has already been reversed.'], 422);
        }

        DB::transaction(function () use ($supplier, $entry, $request) {
            $reversalAmount = -$entry->amount; // Reverses the negative amount back to positive (payable restored)

            $supplier->current_balance += $reversalAmount;
            $supplier->save();

            SupplierLedgerEntry::create([
                'supplier_id'       => $supplier->id,
                'type'              => 'payment_reversal',
                'amount'            => $reversalAmount,
                'balance_after'     => $supplier->current_balance,
                'note'              => $request->note,
                'reversed_entry_id' => $entry->id,
                'method'            => $entry->method,
                'created_by'        => auth()->id(),
            ]);

            AuditLog::record(
                'supplier_reversal',
                "Reversed payment entry #{$entry->id} for supplier {$supplier->name} (amount: Rs. " . number_format(abs($reversalAmount), 2) . ") — Note: {$request->note}",
                'Supplier',
                $supplier->id,
                ['entry_id' => $entry->id, 'amount' => $reversalAmount]
            );
        });

        $supplier->refresh();

        return response()->json([
            'success'           => true,
            'message'           => 'Supplier payment entry reversed successfully.',
            'current_balance'   => (float) $supplier->current_balance,
            'formatted_balance' => 'Rs. ' . number_format($supplier->current_balance, 2),
        ]);
    }
}
