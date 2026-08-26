<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Wallet;
use App\Models\CustomerLedgerEntry;
use App\Models\SupplierLedgerEntry;
use App\Models\Receipt;
use App\Models\SupplierPaymentVoucher;
use App\Models\Payment;
use App\Models\GLEntry;
use App\Models\GeneralLedgerAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AccountingService
{
    /**
     * Record accounting entries for a Sale (Cash, Online, Card, Bank, Credit, or Partial).
     */
    public function recordSale(Sale $sale, ?int $walletId = null, ?int $userId = null): void
    {
        DB::transaction(function () use ($sale, $walletId, $userId) {
            $effectiveUserId = $userId ?: (Auth::id() ?: $sale->user_id);
            $effectiveWalletId = $walletId ?: $sale->wallet_id;

            // Idempotency Check: Prevent duplicate ledger entries for the same sale
            $existingEntry = CustomerLedgerEntry::where('note', 'LIKE', "%Invoice #{$sale->invoice_no}%")->first();
            if ($existingEntry && $sale->customer_id) {
                return; // Already processed
            }

            $grandTotal = (float) $sale->grand_total;
            $paidAmount = (float) $sale->paid_amount;
            $unpaidAmount = max(0.00, round($grandTotal - $paidAmount, 2));

            // 1. Process Paid Portion via Wallet / Cash Account
            if ($paidAmount > 0) {
                $walletName = 'Cash Drawer';
                if ($effectiveWalletId) {
                    $wallet = Wallet::find($effectiveWalletId);
                    if ($wallet) {
                        $wallet->adjustBalance($paidAmount);
                        $walletName = $wallet->name;
                    }
                } else {
                    $defaultWallet = Wallet::where('type', 'cash')->first() ?: Wallet::first();
                    if ($defaultWallet) {
                        $defaultWallet->adjustBalance($paidAmount);
                        $sale->wallet_id = $defaultWallet->id;
                        $sale->save();
                        $walletName = $defaultWallet->name;
                    }
                }

                $methodEnum = in_array($sale->payment_mode, ['Cash', 'Bank', 'Cheque']) ? $sale->payment_mode : 'Bank';

                // Record Payment Audit Row
                Payment::create([
                    'payment_no'      => 'PAY-SALE-' . $sale->id,
                    'payment_date'    => $sale->sale_date ?: now(),
                    'payment_method'  => $methodEnum,
                    'paid_to_account' => $walletName,
                    'wallet_id'       => $sale->wallet_id,
                    'amount_paid'     => $paidAmount,
                    'memo'            => "Sale Payment Invoice #{$sale->invoice_no}",
                    'user_id'         => $effectiveUserId,
                ]);
            }

            // 2. Process Customer Credit / Receivable Portion
            if ($sale->customer_id) {
                $customer = Customer::find($sale->customer_id);
                if ($customer && $unpaidAmount > 0) {
                    $customer->increment('balance', $unpaidAmount);

                    CustomerLedgerEntry::create([
                        'customer_id'     => $customer->id,
                        'type'            => 'sale',
                        'reason_category' => 'sale',
                        'amount'          => $unpaidAmount,
                        'balance_after'   => $customer->fresh()->balance,
                        'method'          => $sale->payment_mode ?: 'Credit',
                        'note'            => "Credit Sale Invoice #{$sale->invoice_no}",
                        'created_by'      => $effectiveUserId,
                    ]);
                }
            }

            // 3. Post General Ledger Entries
            $this->postSaleGLEntries($sale, $paidAmount, $unpaidAmount);
        });
    }

    /**
     * Record customer payment against outstanding balance/credit.
     */
    public function recordCustomerPayment(
        Customer $customer,
        float $amount,
        int $walletId,
        string $paymentMethod = 'Cash',
        ?string $note = null,
        ?int $userId = null
    ): CustomerLedgerEntry {
        return DB::transaction(function () use ($customer, $amount, $walletId, $paymentMethod, $note, $userId) {
            $effectiveUserId = $userId ?: (Auth::id() ?: 1);

            // 1. Adjust Wallet Balance
            $wallet = Wallet::findOrFail($walletId);
            $wallet->adjustBalance($amount);

            // 2. Decrement Customer Balance (Receivable)
            $customer->decrement('balance', $amount);
            $newBalance = $customer->fresh()->balance;

            // 3. Create Customer Ledger Entry
            $ledgerEntry = CustomerLedgerEntry::create([
                'customer_id'     => $customer->id,
                'type'            => 'payment_received',
                'reason_category' => 'payment',
                'amount'          => -$amount, // Payment reduces customer debt balance
                'balance_after'   => $newBalance,
                'method'          => $paymentMethod,
                'note'            => $note ?: "Customer Payment Received",
                'created_by'      => $effectiveUserId,
            ]);

            // 4. Create Sequential Receipt
            Receipt::create([
                'receipt_number'    => Receipt::generateNextReceiptNumber(),
                'receipt_no'        => 'RCPT-' . time(),
                'receipt_date'      => now(),
                'customer_id'       => $customer->id,
                'ledger_entry_id'   => $ledgerEntry->id,
                'amount'            => $amount,
                'amount_received'   => $amount,
                'total_adjusted'    => $amount,
                'remaining_balance' => $newBalance,
                'payment_method'    => $paymentMethod,
                'received_by'       => $effectiveUserId,
                'deposit_account'   => $wallet->name,
                'payment_mode'      => $paymentMethod,
                'memo'              => $note,
                'created_by'        => $effectiveUserId,
            ]);

            return $ledgerEntry;
        });
    }

    /**
     * Record accounting entries for a Purchase (Cash, Credit, or Partial).
     */
    public function recordPurchase(Purchase $purchase, ?int $walletId = null, ?int $userId = null): void
    {
        DB::transaction(function () use ($purchase, $walletId, $userId) {
            $effectiveUserId = $userId ?: (Auth::id() ?: 1);

            // Idempotency Check
            $existingEntry = SupplierLedgerEntry::where('note', 'LIKE', "%Purchase Invoice #{$purchase->invoice_no}%")->first();
            if ($existingEntry && $purchase->supplier_id) {
                return;
            }

            $netTotal = (float) ($purchase->net_total ?: $purchase->gross_total);
            $paidAmount = (float) ($purchase->paid_amount ?: 0);
            $unpaidAmount = max(0.00, round($netTotal - $paidAmount, 2));

            // 1. Process Paid Portion (Decrease Wallet Balance)
            if ($paidAmount > 0) {
                $targetWalletId = $walletId ?: Wallet::where('type', 'cash')->value('id');
                if ($targetWalletId) {
                    $wallet = Wallet::find($targetWalletId);
                    if ($wallet) {
                        $wallet->adjustBalance(-$paidAmount);
                    }
                }
            }

            // 2. Process Supplier Credit / Payable Portion
            if ($purchase->supplier_id && $unpaidAmount > 0) {
                $supplier = Supplier::find($purchase->supplier_id);
                if ($supplier) {
                    $supplier->increment('current_balance', $unpaidAmount);

                    SupplierLedgerEntry::create([
                        'supplier_id'   => $supplier->id,
                        'type'          => 'purchase',
                        'amount'        => $unpaidAmount,
                        'balance_after' => $supplier->fresh()->current_balance,
                        'method'        => 'Credit',
                        'note'          => "Purchase Invoice #{$purchase->invoice_no}",
                        'created_by'    => $effectiveUserId,
                    ]);
                }
            }
        });
    }

    /**
     * Record supplier payment disbursement.
     */
    public function recordSupplierPayment(
        Supplier $supplier,
        float $amount,
        int $walletId,
        string $paymentMethod = 'Cash',
        ?string $note = null,
        ?int $userId = null
    ): SupplierLedgerEntry {
        return DB::transaction(function () use ($supplier, $amount, $walletId, $paymentMethod, $note, $userId) {
            $effectiveUserId = $userId ?: (Auth::id() ?: 1);

            // 1. Decrement Wallet Balance
            $wallet = Wallet::findOrFail($walletId);
            $wallet->adjustBalance(-$amount);

            // 2. Decrement Supplier Payable Balance
            $supplier->decrement('current_balance', $amount);
            $newBalance = $supplier->fresh()->current_balance;

            // 3. Create Supplier Ledger Entry
            $ledgerEntry = SupplierLedgerEntry::create([
                'supplier_id'   => $supplier->id,
                'type'          => 'payment_made',
                'amount'        => -$amount, // Payment reduces supplier payable balance
                'balance_after' => $newBalance,
                'method'        => $paymentMethod,
                'note'          => $note ?: "Supplier Payment Paid",
                'created_by'    => $effectiveUserId,
            ]);

            // 4. Create Supplier Payment Voucher
            SupplierPaymentVoucher::create([
                'voucher_number'    => 'VOUCHER-' . time(),
                'supplier_id'       => $supplier->id,
                'ledger_entry_id'   => $ledgerEntry->id,
                'amount'            => $amount,
                'remaining_balance' => $newBalance,
                'payment_method'    => $paymentMethod,
                'store_name'        => 'Store Admin',
                'paid_by'           => $effectiveUserId,
            ]);

            return $ledgerEntry;
        });
    }

    /**
     * Reconcile financial balances when an invoice is edited.
     */
    public function reconcileInvoiceEdit(
        Sale $sale,
        float $oldGrandTotal,
        float $oldPaidAmount,
        float $newGrandTotal,
        float $newPaidAmount,
        int $userId
    ): void {
        DB::transaction(function () use ($sale, $oldGrandTotal, $oldPaidAmount, $newGrandTotal, $newPaidAmount, $userId) {
            $diffPaid = round($newPaidAmount - $oldPaidAmount, 2);
            $oldUnpaid = max(0.00, round($oldGrandTotal - $oldPaidAmount, 2));
            $newUnpaid = max(0.00, round($newGrandTotal - $newPaidAmount, 2));
            $diffUnpaid = round($newUnpaid - $oldUnpaid, 2);

            // 1. Reconcile Wallet Balance if paid amount changed
            if ($diffPaid != 0 && $sale->wallet_id) {
                $wallet = Wallet::find($sale->wallet_id);
                if ($wallet) {
                    $wallet->adjustBalance($diffPaid);
                }
            }

            // 2. Reconcile Customer Receivable Balance if unpaid amount changed
            if ($diffUnpaid != 0 && $sale->customer_id) {
                $customer = Customer::find($sale->customer_id);
                if ($customer) {
                    if ($diffUnpaid > 0) {
                        $customer->increment('balance', $diffUnpaid);
                    } else {
                        $customer->decrement('balance', abs($diffUnpaid));
                    }

                    CustomerLedgerEntry::create([
                        'customer_id'     => $customer->id,
                        'type'            => 'manual_adjustment',
                        'reason_category' => 'edit_adjustment',
                        'amount'          => $diffUnpaid,
                        'balance_after'   => $customer->fresh()->balance,
                        'method'          => 'System Adjustment',
                        'note'            => "Invoice #{$sale->invoice_no} edit adjustment (" . ($diffUnpaid > 0 ? "+{$diffUnpaid}" : "{$diffUnpaid}") . ")",
                        'created_by'      => $userId,
                    ]);
                }
            }
        });
    }

    /**
     * Post Double-Entry General Ledger rows if GL Accounts exist.
     */
    private function postSaleGLEntries(Sale $sale, float $paidAmount, float $unpaidAmount): void
    {
        try {
            $cashAccount = GeneralLedgerAccount::where('name', 'LIKE', '%Cash%')->first();
            $receivableAccount = GeneralLedgerAccount::where('name', 'LIKE', '%Receivable%')->first();
            $salesAccount = GeneralLedgerAccount::where('name', 'LIKE', '%Sales%')->first();

            if ($salesAccount) {
                if ($paidAmount > 0 && $cashAccount) {
                    GLEntry::create([
                        'account_id'  => $cashAccount->id,
                        'debit'       => $paidAmount,
                        'credit'      => 0,
                        'description' => "Cash Received Sale #{$sale->invoice_no}",
                        'date'        => now(),
                    ]);
                }

                if ($unpaidAmount > 0 && $receivableAccount) {
                    GLEntry::create([
                        'account_id'  => $receivableAccount->id,
                        'debit'       => $unpaidAmount,
                        'credit'      => 0,
                        'description' => "Receivable Debit Sale #{$sale->invoice_no}",
                        'date'        => now(),
                    ]);
                }

                GLEntry::create([
                    'account_id'  => $salesAccount->id,
                    'debit'       => 0,
                    'credit'      => $sale->grand_total,
                    'description' => "Revenue Sale #{$sale->invoice_no}",
                    'date'        => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // GL table optional fallback
        }
    }
}
