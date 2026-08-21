<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\GLEntry;
use App\Models\Item;
use App\Models\Journal;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseOrder;
use App\Models\Receipt;
use App\Models\Sale;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GlobalSearchController extends Controller
{
    /**
     * Search across Mart business entities strictly within current tenant database.
     */
    public function search(Request $request)
    {
        try {
            $query = trim((string) $request->input('q', ''));

            // Minimum 2 characters required
            if (mb_strlen($query) < 2) {
                return response()->json([
                    'categories' => (object) [],
                    'total'      => 0,
                    'query'      => $query,
                ]);
            }

            // Cap query length for safety
            $query = mb_substr($query, 0, 100);

            // Determine user permissions / role
            $user = Auth::user() ?? auth('employee')->user();
            $canAccessManagement = $this->checkManagementAccess($user);

            $categories = [];
            $totalCount = 0;

            // 1. CUSTOMERS (Owner / Manager)
            if ($canAccessManagement) {
                try {
                    $customers = Customer::where(function ($q) use ($query) {
                        $q->where('name', 'LIKE', "%{$query}%")
                          ->orWhere('phone', 'LIKE', "%{$query}%")
                          ->orWhere('id', $query);
                    })
                    ->where('status', '!=', 'deactivated')
                    ->select('id', 'name', 'phone', 'balance', 'credit_limit')
                    ->orderBy('name')
                    ->limit(5)
                    ->get()
                    ->map(function ($c) {
                        $phone = $c->phone ? e($c->phone) : 'No phone';
                        $balance = number_format((float) ($c->balance ?? 0), 2);
                        return [
                            'id'       => $c->id,
                            'title'    => $c->name,
                            'subtitle' => "Phone: {$phone} • Bal: Rs. {$balance}",
                            'url'      => route('customers.show', $c->id),
                            'icon'     => 'fas fa-user',
                            'badge'    => 'Customer',
                            'badge_color' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                        ];
                    });

                    if ($customers->isNotEmpty()) {
                        $categories['customers'] = [
                            'label' => 'CUSTOMERS',
                            'icon'  => 'fas fa-users',
                            'items' => $customers,
                        ];
                        $totalCount += $customers->count();
                    }
                } catch (\Throwable $e) {
                    Log::warning('Global search customers error: ' . $e->getMessage());
                }
            }

            // 2. INVOICES / SALES (All Staff)
            try {
                $sales = Sale::where(function ($q) use ($query) {
                    $q->where('invoice_no', 'LIKE', "%{$query}%")
                      ->orWhere('customer_name', 'LIKE', "%{$query}%")
                      ->orWhere('id', $query);
                })
                ->select('id', 'invoice_no', 'customer_name', 'grand_total', 'payment_mode', 'sale_date', 'created_at')
                ->latest('id')
                ->limit(5)
                ->get()
                ->map(function ($s) {
                    $inv = $s->invoice_no ?: ('#' . $s->id);
                    $customer = $s->customer_name ?: 'Walk-in';
                    $amount = number_format((float) ($s->grand_total ?? 0), 2);
                    $date = $s->sale_date ? Carbon::parse($s->sale_date)->format('d M Y') : ($s->created_at ? $s->created_at->format('d M Y') : '');
                    return [
                        'id'       => $s->id,
                        'title'    => "Invoice {$inv}",
                        'subtitle' => "{$customer} • Rs. {$amount}" . ($date ? " • {$date}" : ''),
                        'url'      => route('sales.print', $s->id),
                        'icon'     => 'fas fa-file-invoice-dollar',
                        'badge'    => 'Invoice',
                        'badge_color' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                    ];
                });

                if ($sales->isNotEmpty()) {
                    $categories['invoices'] = [
                        'label' => 'INVOICES / SALES',
                        'icon'  => 'fas fa-file-invoice-dollar',
                        'items' => $sales,
                    ];
                    $totalCount += $sales->count();
                }
            } catch (\Throwable $e) {
                Log::warning('Global search sales error: ' . $e->getMessage());
            }

            // 3. PRODUCTS / ITEMS (All Staff)
            try {
                $items = Item::where(function ($q) use ($query) {
                    $q->where('description', 'LIKE', "%{$query}%")
                      ->orWhere('code', 'LIKE', "%{$query}%")
                      ->orWhere('short_code', 'LIKE', "%{$query}%");
                })
                ->select('id', 'description', 'code', 'sale_rate', 'on_hand')
                ->limit(5)
                ->get()
                ->map(function ($i) {
                    $name = $i->description ?: 'Unnamed Item';
                    $code = $i->code ?: 'No Code';
                    $stock = number_format((float) ($i->on_hand ?? 0), 2);
                    $price = number_format((float) ($i->sale_rate ?? 0), 2);
                    return [
                        'id'       => $i->id,
                        'title'    => $name,
                        'subtitle' => "Barcode: {$code} • Stock: {$stock} • Rs. {$price}",
                        'url'      => route('items.show', $i->id),
                        'icon'     => 'fas fa-box',
                        'badge'    => 'Product',
                        'badge_color' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                    ];
                });

                if ($items->isNotEmpty()) {
                    $categories['products'] = [
                        'label' => 'PRODUCTS / ITEMS',
                        'icon'  => 'fas fa-boxes-stacked',
                        'items' => $items,
                    ];
                    $totalCount += $items->count();
                }
            } catch (\Throwable $e) {
                Log::warning('Global search items error: ' . $e->getMessage());
            }

            // 4. SUPPLIERS (Owner / Manager)
            if ($canAccessManagement) {
                try {
                    $suppliers = Supplier::where(function ($q) use ($query) {
                        $q->where('name', 'LIKE', "%{$query}%")
                          ->orWhere('code', 'LIKE', "%{$query}%")
                          ->orWhere('phone', 'LIKE', "%{$query}%")
                          ->orWhere('id', $query);
                    })
                    ->select('id', 'name', 'code', 'phone', 'balance', 'current_balance')
                    ->limit(5)
                    ->get()
                    ->map(function ($s) {
                        $code = $s->code ? " ({$s->code})" : '';
                        $phone = $s->phone ? e($s->phone) : 'No phone';
                        $bal = number_format((float) ($s->current_balance ?? $s->balance ?? 0), 2);
                        return [
                            'id'       => $s->id,
                            'title'    => $s->name . $code,
                            'subtitle' => "Phone: {$phone} • Bal: Rs. {$bal}",
                            'url'      => route('suppliers.show', $s->id),
                            'icon'     => 'fas fa-truck',
                            'badge'    => 'Supplier',
                            'badge_color' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                        ];
                    });

                    if ($suppliers->isNotEmpty()) {
                        $categories['suppliers'] = [
                            'label' => 'SUPPLIERS',
                            'icon'  => 'fas fa-truck',
                            'items' => $suppliers,
                        ];
                        $totalCount += $suppliers->count();
                    }
                } catch (\Throwable $e) {
                    Log::warning('Global search suppliers error: ' . $e->getMessage());
                }
            }

            // 5. PURCHASES (Owner / Manager)
            if ($canAccessManagement) {
                try {
                    $purchases = Purchase::with('supplier:id,name')
                    ->where(function ($q) use ($query) {
                        $q->where('purchase_no', 'LIKE', "%{$query}%")
                          ->orWhere('vendor_bill_no', 'LIKE', "%{$query}%")
                          ->orWhere('memo', 'LIKE', "%{$query}%")
                          ->orWhere('id', $query)
                          ->orWhereHas('supplier', function ($sq) use ($query) {
                              $sq->where('name', 'LIKE', "%{$query}%");
                          });
                    })
                    ->select('id', 'purchase_no', 'vendor_bill_no', 'supplier_id', 'net_total', 'purchase_date', 'created_at')
                    ->latest('id')
                    ->limit(5)
                    ->get()
                    ->map(function ($p) {
                        $pNo = $p->purchase_no ?: ('#' . $p->id);
                        $supplier = $p->supplier?->name ?: 'Vendor';
                        $amount = number_format((float) ($p->net_total ?? 0), 2);
                        $date = $p->purchase_date ? Carbon::parse($p->purchase_date)->format('d M Y') : ($p->created_at ? $p->created_at->format('d M Y') : '');
                        return [
                            'id'       => $p->id,
                            'title'    => "Purchase {$pNo}",
                            'subtitle' => "{$supplier} • Rs. {$amount}" . ($date ? " • {$date}" : ''),
                            'url'      => route('purchases.show', $p->id),
                            'icon'     => 'fas fa-cart-flatbed',
                            'badge'    => 'Purchase',
                            'badge_color' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                        ];
                    });

                    if ($purchases->isNotEmpty()) {
                        $categories['purchases'] = [
                            'label' => 'PURCHASES',
                            'icon'  => 'fas fa-cart-flatbed',
                            'items' => $purchases,
                        ];
                        $totalCount += $purchases->count();
                    }
                } catch (\Throwable $e) {
                    Log::warning('Global search purchases error: ' . $e->getMessage());
                }
            }

            // 6. PURCHASE ORDERS (Owner / Manager)
            if ($canAccessManagement) {
                try {
                    $purchaseOrders = PurchaseOrder::with('supplier:id,name')
                    ->where(function ($q) use ($query) {
                        $q->where('po_number', 'LIKE', "%{$query}%")
                          ->orWhere('note', 'LIKE', "%{$query}%")
                          ->orWhere('id', $query)
                          ->orWhereHas('supplier', function ($sq) use ($query) {
                              $sq->where('name', 'LIKE', "%{$query}%");
                          });
                    })
                    ->select('id', 'po_number', 'supplier_id', 'status', 'order_date')
                    ->latest('id')
                    ->limit(5)
                    ->get()
                    ->map(function ($po) {
                        $poNo = $po->po_number ?: ('#' . $po->id);
                        $supplier = $po->supplier?->name ?: 'Vendor';
                        $status = ucfirst($po->status ?? 'pending');
                        $date = $po->order_date ? Carbon::parse($po->order_date)->format('d M Y') : '';
                        return [
                            'id'       => $po->id,
                            'title'    => "PO {$poNo}",
                            'subtitle' => "{$supplier} • Status: {$status}" . ($date ? " • {$date}" : ''),
                            'url'      => route('purchase-orders.show', $po->id),
                            'icon'     => 'fas fa-clipboard-list',
                            'badge'    => 'PO',
                            'badge_color' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
                        ];
                    });

                    if ($purchaseOrders->isNotEmpty()) {
                        $categories['purchase_orders'] = [
                            'label' => 'PURCHASE ORDERS',
                            'icon'  => 'fas fa-clipboard-list',
                            'items' => $purchaseOrders,
                        ];
                        $totalCount += $purchaseOrders->count();
                    }
                } catch (\Throwable $e) {
                    Log::warning('Global search PO error: ' . $e->getMessage());
                }
            }

            // 7. PAYMENTS / EXPENSES (Owner / Manager)
            if ($canAccessManagement) {
                try {
                    $payments = Payment::where(function ($q) use ($query) {
                        $q->where('payment_no', 'LIKE', "%{$query}%")
                          ->orWhere('paid_to_account', 'LIKE', "%{$query}%")
                          ->orWhere('memo', 'LIKE', "%{$query}%")
                          ->orWhere('reference', 'LIKE', "%{$query}%");
                    })
                    ->select('id', 'payment_no', 'paid_to_account', 'amount_paid', 'payment_date')
                    ->latest('id')
                    ->limit(5)
                    ->get()
                    ->map(function ($pay) {
                        $pNo = $pay->payment_no ?: ('#' . $pay->id);
                        $to = $pay->paid_to_account ?: 'Expense';
                        $amount = number_format((float) ($pay->amount_paid ?? 0), 2);
                        $date = $pay->payment_date ? Carbon::parse($pay->payment_date)->format('d M Y') : '';
                        return [
                            'id'       => $pay->id,
                            'title'    => "Payment {$pNo}",
                            'subtitle' => "{$to} • Rs. {$amount}" . ($date ? " • {$date}" : ''),
                            'url'      => route('payments.index') . '?search=' . urlencode($pay->payment_no ?: $pay->id),
                            'icon'     => 'fas fa-money-bill-wave',
                            'badge'    => 'Payment',
                            'badge_color' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                        ];
                    });

                    if ($payments->isNotEmpty()) {
                        $categories['payments'] = [
                            'label' => 'PAYMENTS & EXPENSES',
                            'icon'  => 'fas fa-money-bill-wave',
                            'items' => $payments,
                        ];
                        $totalCount += $payments->count();
                    }
                } catch (\Throwable $e) {
                    Log::warning('Global search payments error: ' . $e->getMessage());
                }
            }

            // 8. RECEIPTS (Owner / Manager)
            if ($canAccessManagement) {
                try {
                    $receipts = Receipt::with('customer:id,name')
                    ->where(function ($q) use ($query) {
                        $q->where('receipt_no', 'LIKE', "%{$query}%")
                          ->orWhere('notes', 'LIKE', "%{$query}%")
                          ->orWhereHas('customer', function ($cq) use ($query) {
                              $cq->where('name', 'LIKE', "%{$query}%");
                          });
                    })
                    ->select('id', 'receipt_no', 'customer_id', 'amount_received', 'receipt_date')
                    ->latest('id')
                    ->limit(5)
                    ->get()
                    ->map(function ($r) {
                        $rNo = $r->receipt_no ?: ('#' . $r->id);
                        $customer = $r->customer?->name ?: 'Customer';
                        $amount = number_format((float) ($r->amount_received ?? 0), 2);
                        $date = $r->receipt_date ? Carbon::parse($r->receipt_date)->format('d M Y') : '';
                        return [
                            'id'       => $r->id,
                            'title'    => "Receipt {$rNo}",
                            'subtitle' => "{$customer} • Rs. {$amount}" . ($date ? " • {$date}" : ''),
                            'url'      => route('customer.receipts.show', $r->id),
                            'icon'     => 'fas fa-receipt',
                            'badge'    => 'Receipt',
                            'badge_color' => 'bg-teal-500/10 text-teal-400 border-teal-500/20',
                        ];
                    });

                    if ($receipts->isNotEmpty()) {
                        $categories['receipts'] = [
                            'label' => 'CUSTOMER RECEIPTS',
                            'icon'  => 'fas fa-receipt',
                            'items' => $receipts,
                        ];
                        $totalCount += $receipts->count();
                    }
                } catch (\Throwable $e) {
                    Log::warning('Global search receipts error: ' . $e->getMessage());
                }
            }

            // 9. ACCOUNTS (Owner / Manager)
            if ($canAccessManagement) {
                try {
                    $accounts = Account::where(function ($q) use ($query) {
                        $q->where('name', 'LIKE', "%{$query}%")
                          ->orWhere('code', 'LIKE', "%{$query}%");
                    })
                    ->select('id', 'name', 'code', 'type', 'current_balance')
                    ->limit(5)
                    ->get()
                    ->map(function ($a) {
                        $code = $a->code ? " ({$a->code})" : '';
                        $bal = number_format((float) ($a->current_balance ?? 0), 2);
                        return [
                            'id'       => $a->id,
                            'title'    => $a->name . $code,
                            'subtitle' => "Type: {$a->type} • Bal: Rs. {$bal}",
                            'url'      => route('accounts.index'),
                            'icon'     => 'fas fa-book',
                            'badge'    => 'Account',
                            'badge_color' => 'bg-violet-500/10 text-violet-400 border-violet-500/20',
                        ];
                    });

                    if ($accounts->isNotEmpty()) {
                        $categories['accounts'] = [
                            'label' => 'CHART OF ACCOUNTS',
                            'icon'  => 'fas fa-book',
                            'items' => $accounts,
                        ];
                        $totalCount += $accounts->count();
                    }
                } catch (\Throwable $e) {
                    Log::warning('Global search accounts error: ' . $e->getMessage());
                }
            }

            // 10. JOURNALS (Owner / Manager)
            if ($canAccessManagement) {
                try {
                    $journals = Journal::where(function ($q) use ($query) {
                        $q->where('journal_no', 'LIKE', "%{$query}%")
                          ->orWhere('memo', 'LIKE', "%{$query}%");
                    })
                    ->select('id', 'journal_no', 'date', 'memo', 'total_debit')
                    ->latest('id')
                    ->limit(5)
                    ->get()
                    ->map(function ($j) {
                        $jNo = $j->journal_no ?: ('#' . $j->id);
                        $memo = $j->memo ?: 'General Journal';
                        $amount = number_format((float) ($j->total_debit ?? 0), 2);
                        $date = $j->date ? Carbon::parse($j->date)->format('d M Y') : '';
                        return [
                            'id'       => $j->id,
                            'title'    => "Journal {$jNo}",
                            'subtitle' => "{$memo} • Rs. {$amount}" . ($date ? " • {$date}" : ''),
                            'url'      => route('journals.create'),
                            'icon'     => 'fas fa-book-open',
                            'badge'    => 'JV',
                            'badge_color' => 'bg-fuchsia-500/10 text-fuchsia-400 border-fuchsia-500/20',
                        ];
                    });

                    if ($journals->isNotEmpty()) {
                        $categories['journals'] = [
                            'label' => 'GENERAL JOURNALS',
                            'icon'  => 'fas fa-book-open',
                            'items' => $journals,
                        ];
                        $totalCount += $journals->count();
                    }
                } catch (\Throwable $e) {
                    Log::warning('Global search journals error: ' . $e->getMessage());
                }
            }

            // 11. GENERAL LEDGER (Owner / Manager)
            if ($canAccessManagement) {
                try {
                    $glEntries = GLEntry::where(function ($q) use ($query) {
                        $q->where('description', 'LIKE', "%{$query}%");
                    })
                    ->select('id', 'account_id', 'debit', 'credit', 'description', 'date')
                    ->latest('id')
                    ->limit(5)
                    ->get()
                    ->map(function ($gl) {
                        $desc = $gl->description ?: 'GL Entry';
                        $debit = number_format((float) ($gl->debit ?? 0), 2);
                        $credit = number_format((float) ($gl->credit ?? 0), 2);
                        $date = $gl->date ? Carbon::parse($gl->date)->format('d M Y') : '';
                        return [
                            'id'       => $gl->id,
                            'title'    => "GL: {$desc}",
                            'subtitle' => "Debit: Rs. {$debit} • Credit: Rs. {$credit}" . ($date ? " • {$date}" : ''),
                            'url'      => route('general-ledger.index'),
                            'icon'     => 'fas fa-balance-scale',
                            'badge'    => 'GL',
                            'badge_color' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                        ];
                    });

                    if ($glEntries->isNotEmpty()) {
                        $categories['general_ledger'] = [
                            'label' => 'GENERAL LEDGER',
                            'icon'  => 'fas fa-balance-scale',
                            'items' => $glEntries,
                        ];
                        $totalCount += $glEntries->count();
                    }
                } catch (\Throwable $e) {
                    Log::warning('Global search GL error: ' . $e->getMessage());
                }
            }

            return response()->json([
                'categories' => $categories,
                'total'      => $totalCount,
                'query'      => $query,
            ]);

        } catch (\Throwable $e) {
            Log::error('Global search unhandled error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'error'      => 'Unable to search right now. Please try again.',
                'categories' => (object) [],
                'total'      => 0,
            ], 500);
        }
    }

    /**
     * Check if the authenticated user has management/accounting privileges.
     */
    private function checkManagementAccess($user): bool
    {
        if (!$user) {
            return false;
        }

        // If user is from legacy employee guard, check legacy permissions or role
        if (!($user instanceof \App\Models\User)) {
            $role = strtolower((string) ($user->role ?? ''));
            return in_array($role, ['owner', 'admin', 'manager']);
        }

        // Owner or Admin always has full access
        $userRole = strtolower((string) ($user->role ?? ''));
        if (in_array($userRole, ['owner', 'admin', 'store admin']) || (method_exists($user, 'hasRole') && ($user->hasRole('owner') || $user->hasRole('manager')))) {
            return true;
        }

        if (method_exists($user, 'hasLegacyPermission')) {
            return $user->hasLegacyPermission('accounting.view') || $user->hasLegacyPermission('purchases.view');
        }

        return false;
    }
}
