<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Receipt;
use App\Models\ReceiptAllocation;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\DebitSale;
use App\Models\Wallet;
use App\Models\BankAccount;
use App\Models\Account;
use App\Models\GeneralLedgerAccount;
use App\Models\GLEntry;
use App\Models\Payment;
use App\Models\CompanySetting;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class ReceiptController extends Controller
{
    public function index()
    {
        return redirect()->route('receipts.create');
    }

    public function create()
    {
        $customers = Customer::where('status', '!=', 'deactivated')
            ->select('id', 'name', 'phone', 'balance')
            ->orderBy('name')
            ->get();

        $users = User::select('id', 'name')->orderBy('name')->get();

        return view('receipts.create', compact('customers', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'     => 'required|exists:customers,id',
            'amount_received' => 'required|numeric|min:0',
            'discount_given'  => 'nullable|numeric|min:0',
            'receipt_date'    => 'nullable|date',
            'deposit_account' => 'required|string',
            'payment_mode'    => 'required|string',
            'salesman_id'     => 'nullable|integer',
            'cheque_no'       => 'nullable|string|max:255',
            'cheque_date'     => 'nullable|date',
            'bank_name'       => 'nullable|string|max:255',
            'memo'            => 'nullable|string|max:1000',
        ]);

        $amountReceived = round((float) $request->amount_received, 2);
        $discountGiven  = round((float) ($request->discount_given ?? 0), 2);
        $totalAdjusted  = round($amountReceived + $discountGiven, 2);

        if ($totalAdjusted <= 0) {
            return back()->withErrors([
                'amount_received' => 'The total settlement (Amount Received + Discount Given) must be greater than zero.'
            ])->withInput();
        }

        if ($amountReceived < 0 || $discountGiven < 0) {
            return back()->withErrors([
                'amount_received' => 'Negative amounts are not allowed.'
            ])->withInput();
        }

        try {
            DB::beginTransaction();

            // 1. Lock customer row to prevent concurrent race conditions
            $customer = Customer::lockForUpdate()->findOrFail($request->customer_id);

            // 2. Fetch customer's pending debit invoices with lock
            $pendingInvoices = DebitSale::where('customer_id', $customer->id)
                ->where(function ($q) {
                    $q->where('status', '!=', 'paid')->orWhereNull('status');
                })
                ->whereRaw('net_total > COALESCE(paid_amount, 0)')
                ->lockForUpdate()
                ->orderBy('invoice_date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $currentCustomerDebt = (float) $customer->balance;
            $totalInvoiceDue = (float) $pendingInvoices->sum(function ($inv) {
                return max(0.00, round((float) $inv->net_total - (float) ($inv->paid_amount ?? 0), 2));
            });

            $allowableBalance = max($currentCustomerDebt, $totalInvoiceDue);

            // 3. Overpayment check (Never allow payment/discount to exceed outstanding balance)
            if ($allowableBalance > 0 && $totalAdjusted > ($allowableBalance + 0.01)) {
                DB::rollBack();
                return back()->withErrors([
                    'amount_received' => 'Payment amount plus discount (Rs. ' . number_format($totalAdjusted, 2) . ') cannot exceed customer\'s outstanding balance (Rs. ' . number_format($allowableBalance, 2) . ').'
                ])->withInput();
            }

            if ($allowableBalance <= 0 && $totalAdjusted > 0) {
                DB::rollBack();
                return back()->withErrors([
                    'amount_received' => 'Customer has no outstanding balance or pending invoices to settle.'
                ])->withInput();
            }

            // 4. Update Customer Balance
            $newBalance = max(0.00, round($currentCustomerDebt - $totalAdjusted, 2));
            $customer->balance = $newBalance;
            $customer->save();

            // 5. Generate concurrency-safe sequential Receipt Number
            $receiptNumber = Receipt::generateNextReceiptNumber();
            $receiptNo = $request->receipt_no ? trim($request->receipt_no) : $receiptNumber;
            if (Receipt::where('receipt_no', $receiptNo)->exists()) {
                $receiptNo = $receiptNumber;
            }

            // 6. Create Customer Ledger Entry
            $note = $request->memo ? trim($request->memo) : "Payment Received (Receipt #{$receiptNo})";
            if ($discountGiven > 0) {
                $note .= " [Received: Rs. " . number_format($amountReceived, 2) . ", Discount: Rs. " . number_format($discountGiven, 2) . "]";
            }

            $ledgerEntry = CustomerLedgerEntry::create([
                'customer_id'     => $customer->id,
                'type'            => 'payment_received',
                'reason_category' => 'payment',
                'amount'          => -$totalAdjusted,
                'balance_after'   => $newBalance,
                'method'          => $request->payment_mode ?: 'Cash',
                'note'            => $note,
                'created_by'      => Auth::id() ?: 1,
                'created_at'      => $request->receipt_date ? ($request->receipt_date . ' ' . now()->format('H:i:s')) : now(),
            ]);

            // 7. Create Receipt Record
            $companySetting = CompanySetting::first();
            $storeName = $companySetting?->business_name ?: config('app.name', 'Supermarket');

            $receipt = Receipt::create([
                'receipt_number'    => $receiptNumber,
                'receipt_no'        => $receiptNo,
                'receipt_date'      => $request->receipt_date ?: now()->toDateString(),
                'customer_id'       => $customer->id,
                'ledger_entry_id'   => $ledgerEntry->id,
                'salesman_id'       => $request->salesman_id ?: null,
                'amount'            => $amountReceived,
                'amount_received'   => $amountReceived,
                'discount_given'    => $discountGiven,
                'total_adjusted'    => $totalAdjusted,
                'remaining_balance' => $newBalance,
                'deposit_account'   => $request->deposit_account ?: 'Cash Account / Drawer',
                'payment_mode'      => $request->payment_mode ?: 'Cash',
                'payment_method'    => $request->payment_mode ?: 'Cash',
                'cheque_no'         => $request->cheque_no ?: null,
                'cheque_date'       => $request->cheque_date ?: null,
                'bank_name'         => $request->bank_name ?: null,
                'memo'              => $request->memo ?: null,
                'received_by'       => Auth::id() ?: 1,
                'created_by'        => Auth::id() ?: 1,
                'store_name'        => $storeName,
            ]);

            // 8. Allocate Payment to Pending Invoices (FIFO)
            $remainingToAllocate = $totalAdjusted;
            foreach ($pendingInvoices as $inv) {
                if ($remainingToAllocate <= 0) {
                    break;
                }

                $invoiceDue = max(0.00, round((float) $inv->net_total - (float) ($inv->paid_amount ?? 0), 2));
                if ($invoiceDue > 0) {
                    $allocated = min($remainingToAllocate, $invoiceDue);
                    $newPaid = round((float) ($inv->paid_amount ?? 0) + $allocated, 2);
                    $newStatus = ($newPaid >= ((float) $inv->net_total - 0.009)) ? 'paid' : 'partial';

                    $inv->update([
                        'paid_amount' => $newPaid,
                        'status'      => $newStatus,
                    ]);

                    ReceiptAllocation::create([
                        'receipt_id'       => $receipt->id,
                        'debit_sale_id'    => $inv->id,
                        'allocated_amount' => $allocated,
                    ]);

                    $remainingToAllocate = round($remainingToAllocate - $allocated, 2);
                }
            }

            // 8b. Allocate remaining payment to pending Debit Sales in sales table (FIFO)
            if ($remainingToAllocate > 0) {
                $pendingSales = \App\Models\Sale::where('customer_id', $customer->id)
                    ->where('payment_mode', 'Debit')
                    ->whereRaw('grand_total > COALESCE(paid_amount, 0)')
                    ->orderBy('sale_date', 'asc')
                    ->orderBy('id', 'asc')
                    ->lockForUpdate()
                    ->get();

                foreach ($pendingSales as $sale) {
                    if ($remainingToAllocate <= 0) break;
                    $saleDue = max(0.00, round((float) $sale->grand_total - (float) ($sale->paid_amount ?? 0), 2));
                    if ($saleDue > 0) {
                        $allocated = min($remainingToAllocate, $saleDue);
                        $sale->increment('paid_amount', $allocated);
                        $remainingToAllocate = round($remainingToAllocate - $allocated, 2);
                    }
                }
            }

            // 9. Deposit / Accounting Ledger updates
            $depositAc = $request->deposit_account ?: 'Cash Account / Drawer';
            $matchedWalletId = null;

            if ($amountReceived > 0) {
                $isCash = stripos($depositAc, 'Cash') !== false || stripos($depositAc, 'Drawer') !== false;
                $isMeezan = stripos($depositAc, 'Meezan') !== false;
                $isHBL = stripos($depositAc, 'HBL') !== false;

                if ($isCash) {
                    $wallet = Wallet::where('type', 'counter')->where('is_active', true)->first()
                        ?: Wallet::where('type', 'cash')->first()
                        ?: Wallet::where('name', 'LIKE', '%Cash%')->first()
                        ?: Wallet::first();

                    if ($wallet) {
                        $wallet->adjustBalance($amountReceived);
                        $matchedWalletId = $wallet->id;
                    }

                    $account = Account::where('name', 'LIKE', '%Cash%')->first();
                    if ($account) {
                        $account->increment('current_balance', $amountReceived);
                    }

                    $glCash = GeneralLedgerAccount::where('gl_type', '01')->where('name', 'LIKE', '%Cash%')->first();
                    if ($glCash) {
                        $glCash->increment('current_balance', $amountReceived);
                    }
                } elseif ($isMeezan) {
                    $bank = BankAccount::where('account_title', 'LIKE', '%Meezan%')->orWhere('bank_name', 'LIKE', '%Meezan%')->first();
                    if ($bank) {
                        $bank->increment('current_balance', $amountReceived);
                    }

                    $wallet = Wallet::where('name', 'LIKE', '%Meezan%')->first();
                    if ($wallet) {
                        $wallet->adjustBalance($amountReceived);
                        $matchedWalletId = $wallet->id;
                    }

                    $glMeezan = GeneralLedgerAccount::where('name', 'LIKE', '%Meezan%')->first();
                    if ($glMeezan) {
                        $glMeezan->increment('current_balance', $amountReceived);
                    }

                    $account = Account::where('name', 'LIKE', '%Meezan%')->first();
                    if ($account) {
                        $account->increment('current_balance', $amountReceived);
                    }
                } elseif ($isHBL) {
                    $bank = BankAccount::where('account_title', 'LIKE', '%HBL%')->orWhere('bank_name', 'LIKE', '%HBL%')->first();
                    if ($bank) {
                        $bank->increment('current_balance', $amountReceived);
                    }

                    $wallet = Wallet::where('name', 'LIKE', '%HBL%')->first();
                    if ($wallet) {
                        $wallet->adjustBalance($amountReceived);
                        $matchedWalletId = $wallet->id;
                    }

                    $glHBL = GeneralLedgerAccount::where('name', 'LIKE', '%HBL%')->first();
                    if ($glHBL) {
                        $glHBL->increment('current_balance', $amountReceived);
                    }

                    $account = Account::where('name', 'LIKE', '%HBL%')->first();
                    if ($account) {
                        $account->increment('current_balance', $amountReceived);
                    }
                } else {
                    $wallet = Wallet::where('name', 'LIKE', "%{$depositAc}%")->first();
                    if ($wallet) {
                        $wallet->adjustBalance($amountReceived);
                        $matchedWalletId = $wallet->id;
                    }

                    $bank = BankAccount::where('account_title', 'LIKE', "%{$depositAc}%")->first();
                    if ($bank) {
                        $bank->increment('current_balance', $amountReceived);
                    }
                }

                // General Ledger double-entry record if gl_entries table exists
                try {
                    if (Schema::hasTable('gl_entries')) {
                        $targetGL = GeneralLedgerAccount::where('name', 'LIKE', '%' . ($isCash ? 'Cash' : ($isMeezan ? 'Meezan' : ($isHBL ? 'HBL' : 'Cash'))) . '%')->first()
                            ?: GeneralLedgerAccount::where('account_type', 'ASSETS')->first();
                        $receivableGL = GeneralLedgerAccount::where('name', 'LIKE', '%Receivable%')->first()
                            ?: GeneralLedgerAccount::where('account_type', 'ASSETS')->first();

                        if ($targetGL) {
                            GLEntry::create([
                                'account_id'  => $targetGL->id,
                                'debit'       => $amountReceived,
                                'credit'      => 0,
                                'description' => "Receipt #{$receipt->receipt_no} from {$customer->name}",
                                'date'        => $request->receipt_date ?: now(),
                            ]);
                        }

                        if ($receivableGL) {
                            GLEntry::create([
                                'account_id'  => $receivableGL->id,
                                'debit'       => 0,
                                'credit'      => $totalAdjusted,
                                'description' => "Receivable Payment Receipt #{$receipt->receipt_no} from {$customer->name}",
                                'date'        => $request->receipt_date ?: now(),
                            ]);
                        }
                    }
                } catch (\Throwable $e) {
                    // GL fallback
                }

                // Payment audit record
                try {
                    Payment::create([
                        'payment_no'      => 'PAY-RCPT-' . $receipt->id,
                        'payment_date'    => $request->receipt_date ?: now(),
                        'payment_method'  => in_array($request->payment_mode, ['Cash', 'Cheque', 'Online', 'Bank']) ? $request->payment_mode : 'Cash',
                        'paid_to_account' => $depositAc,
                        'wallet_id'       => $matchedWalletId,
                        'amount_paid'     => $amountReceived,
                        'memo'            => "Receipt #{$receipt->receipt_no} from {$customer->name}",
                        'user_id'         => Auth::id() ?: 1,
                    ]);
                } catch (\Throwable $e) {
                    // Payment audit fallback
                }

                // Audit Log
                try {
                    if (class_exists(AuditLog::class)) {
                        AuditLog::record(
                            'customer_receipt',
                            "Received payment of Rs. " . number_format($amountReceived, 2) . " from customer {$customer->name} (Receipt #{$receipt->receipt_no})",
                            'Receipt',
                            $receipt->id,
                            [
                                'customer_id'     => $customer->id,
                                'amount_received' => $amountReceived,
                                'discount_given'  => $discountGiven,
                                'total_adjusted'  => $totalAdjusted,
                                'deposit_account' => $depositAc,
                                'payment_mode'    => $request->payment_mode,
                            ]
                        );
                    }
                } catch (\Throwable $e) {
                    // Audit log fallback
                }
            }

            DB::commit();

            return redirect()->route('receipts.print', $receipt->id)->with('success', 'Payment Received Successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing receipt: ' . $e->getMessage())->withInput();
        }
    }

    public function print($id)
    {
        $sale = Receipt::with(['customer', 'salesman', 'allocations.debitSale', 'receivedBy', 'ledgerEntry'])->findOrFail($id);
        return view('receipts.print', compact('sale'));
    }

    public function getPendingInvoices($customerId)
    {
        try {
            $customer = Customer::find($customerId);
            if (!$customer) {
                return response()->json([
                    'success'  => false,
                    'message'  => 'Customer not found.',
                    'customer' => null,
                    'summary'  => null,
                    'invoices' => [],
                ]);
            }

            $rawInvoices = DebitSale::where('customer_id', $customerId)
                ->where(function ($q) {
                    $q->where('status', '!=', 'paid')->orWhereNull('status');
                })
                ->whereRaw('net_total > COALESCE(paid_amount, 0)')
                ->orderBy('invoice_date', 'asc')
                ->orderBy('id', 'asc')
                ->select('id', 'invoice_date', 'invoice_no', 'net_total', 'paid_amount', 'status')
                ->get();

            if ($rawInvoices->isEmpty()) {
                $rawInvoices = \App\Models\Sale::where('customer_id', $customerId)
                    ->where('payment_mode', 'Debit')
                    ->whereRaw('grand_total > COALESCE(paid_amount, 0)')
                    ->orderBy('sale_date', 'asc')
                    ->orderBy('id', 'asc')
                    ->select('id', 'sale_date as invoice_date', 'invoice_no', 'grand_total as net_total', 'paid_amount', 'status')
                    ->get();
            }

            $totalInvoicesAmount = 0.0;
            $totalPaidAmount = 0.0;
            $totalInvoiceOutstanding = 0.0;
            $oldestDate = null;

            $invoices = $rawInvoices->map(function ($inv) use (&$totalInvoicesAmount, &$totalPaidAmount, &$totalInvoiceOutstanding, &$oldestDate) {
                $paid = round((float) ($inv->paid_amount ?? 0), 2);
                $total = round((float) $inv->net_total, 2);
                $bal = max(0.00, round($total - $paid, 2));
                $status = $inv->status ?: ($paid > 0 ? 'partial' : 'open');
                $statusLabel = ($status === 'paid') ? 'Paid' : (($status === 'partial') ? 'Partially Paid' : 'Unpaid');

                $totalInvoicesAmount += $total;
                $totalPaidAmount += $paid;
                $totalInvoiceOutstanding += $bal;

                $invDateFormatted = $inv->invoice_date ? \Carbon\Carbon::parse($inv->invoice_date)->format('Y-m-d') : '-';
                $invDateHuman = $inv->invoice_date ? \Carbon\Carbon::parse($inv->invoice_date)->format('d M Y') : '-';

                if ($oldestDate === null && $inv->invoice_date) {
                    $oldestDate = \Carbon\Carbon::parse($inv->invoice_date)->format('d M Y');
                }

                return [
                    'id'           => $inv->id,
                    'date'         => $invDateFormatted,
                    'date_human'   => $invDateHuman,
                    'no'           => $inv->invoice_no ?: ('INV-' . $inv->id),
                    'total'        => number_format($total, 2),
                    'paid'         => number_format($paid, 2),
                    'balance'      => number_format($bal, 2),
                    'raw_total'    => $total,
                    'raw_paid'     => $paid,
                    'raw_balance'  => $bal,
                    'status'       => $status,
                    'status_label' => $statusLabel,
                ];
            });

            $accountBalance = round((float) $customer->balance, 2);
            $invoiceOutstanding = round($totalInvoiceOutstanding, 2);
            $balanceDiff = round(abs($accountBalance - $invoiceOutstanding), 2);
            $hasDiff = ($balanceDiff > 0.01);

            return response()->json([
                'success' => true,
                'customer' => [
                    'id'                        => $customer->id,
                    'name'                      => $customer->name,
                    'phone'                     => $customer->phone ?? 'N/A',
                    'account_balance'           => $accountBalance,
                    'formatted_account_balance' => 'Rs. ' . number_format($accountBalance, 2),
                ],
                'summary' => [
                    'account_balance'               => $accountBalance,
                    'formatted_account_balance'     => 'Rs. ' . number_format($accountBalance, 2),
                    'total_invoices_amount'         => $totalInvoicesAmount,
                    'formatted_invoices_total'      => 'Rs. ' . number_format($totalInvoicesAmount, 2),
                    'total_paid_amount'             => $totalPaidAmount,
                    'formatted_paid_total'          => 'Rs. ' . number_format($totalPaidAmount, 2),
                    'invoice_outstanding'           => $invoiceOutstanding,
                    'formatted_invoice_outstanding' => 'Rs. ' . number_format($invoiceOutstanding, 2),
                    'pending_count'                 => $invoices->count(),
                    'oldest_invoice_date'           => $oldestDate ?: 'None',
                    'balance_diff'                  => $balanceDiff,
                    'formatted_balance_diff'        => 'Rs. ' . number_format($balanceDiff, 2),
                    'has_diff'                      => $hasDiff,
                ],
                'invoices' => $invoices,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success'  => false,
                'message'  => 'Failed to load invoices: ' . $e->getMessage(),
                'invoices' => [],
            ], 500);
        }
    }
}
