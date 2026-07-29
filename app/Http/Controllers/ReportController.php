<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeneralLedgerAccount;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\Supplier;
use App\Models\SupplierLedgerEntry;
use App\Models\Item;
use App\Models\Batch;
use App\Models\Refund;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Reports Sidebar Structure grouped into 6 exact sections.
     */
    public function getReportCategories()
    {
        return [
            [
                'title' => 'FINANCIAL',
                'reports' => [
                    ['slug' => 'trial_balance',          'name' => 'Trial Balance'],
                    ['slug' => 'general_ledger',         'name' => 'General Ledger'],
                    ['slug' => 'balance_sheet_summary',  'name' => 'Balance Sheet Summary'],
                    ['slug' => 'profit_loss_summary',    'name' => 'Profit & Loss Summary'],
                ]
            ],
            [
                'title' => 'SALES',
                'reports' => [
                    ['slug' => 'sales_summary',          'name' => 'Sales Summary'],
                    ['slug' => 'sales_by_item',          'name' => 'Sales by Item'],
                    ['slug' => 'sales_by_customer',      'name' => 'Sales by Customer'],
                ]
            ],
            [
                'title' => 'PURCHASES',
                'reports' => [
                    ['slug' => 'purchase_by_item',       'name' => 'Purchase by Item'],
                    ['slug' => 'purchase_by_supplier',   'name' => 'Purchase by Supplier'],
                ]
            ],
            [
                'title' => 'CUSTOMERS / SUPPLIERS',
                'reports' => [
                    ['slug' => 'customer_statement',     'name' => 'Customer Statement'],
                    ['slug' => 'supplier_statement',     'name' => 'Supplier Statement'],
                ]
            ],
            [
                'title' => 'STOCK',
                'reports' => [
                    ['slug' => 'stock_below_minimum',    'name' => 'Stock Below Minimum'],
                    ['slug' => 'stock_expiry',           'name' => 'Stock Expiry'],
                    ['slug' => 'final_stock_list',       'name' => 'Final Stock List'],
                ]
            ],
            [
                'title' => 'PROFIT',
                'reports' => [
                    ['slug' => 'profit_by_item',         'name' => 'Profit by Item'],
                    ['slug' => 'profit_by_customer',     'name' => 'Profit by Customer'],
                ]
            ],
        ];
    }

    /**
     * Main Reports Hub View
     */
    public function index(Request $request)
    {
        $categories = $this->getReportCategories();
        $activeReport = $request->query('report', 'trial_balance');

        return view('reports.index', compact('categories', 'activeReport'));
    }

    /**
     * AJAX Endpoint to return report HTML table / partial.
     */
    public function data(Request $request)
    {
        $report = $request->input('report', 'trial_balance');

        // Default Date Range: Current Month
        $fromDate = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $toDate   = $request->input('date_to', Carbon::now()->endOfMonth()->toDateString());

        switch ($report) {

            // ── 1. FINANCIAL: Trial Balance ──────────────────────────────────────
            case 'trial_balance':
                $accounts = GeneralLedgerAccount::orderBy('gl_code')->get();
                $grouped = $accounts->groupBy('account_type');

                $totals = ['debit' => 0, 'credit' => 0];
                $processed = [];

                foreach ($grouped as $type => $accts) {
                    $processed[$type] = [];
                    foreach ($accts as $a) {
                        $bal = (float)$a->current_balance;
                        $debit = 0;
                        $credit = 0;

                        if (in_array(strtoupper($type), ['ASSETS', 'EXPENSE'])) {
                            if ($bal >= 0) $debit = $bal;
                            else $credit = abs($bal);
                        } else {
                            if ($bal >= 0) $credit = $bal;
                            else $debit = abs($bal);
                        }

                        $totals['debit'] += $debit;
                        $totals['credit'] += $credit;

                        $processed[$type][] = [
                            'account' => $a,
                            'debit'   => $debit,
                            'credit'  => $credit,
                        ];
                    }
                }

                return view('reports.partials.trial_balance', compact('processed', 'totals'));

            // ── 2. FINANCIAL: General Ledger (Account Balances) ──────────────────
            case 'general_ledger':
                $accounts = GeneralLedgerAccount::orderBy('gl_code')->get();
                $selectedAccountId = $request->input('account_id');
                $selectedAccount = $selectedAccountId ? GeneralLedgerAccount::find($selectedAccountId) : null;

                return view('reports.partials.general_ledger', compact('accounts', 'selectedAccountId', 'selectedAccount'));

            // ── 3. FINANCIAL: Balance Sheet Summary ──────────────────────────────
            case 'balance_sheet_summary':
                $assets = GeneralLedgerAccount::where('account_type', 'ASSETS')->get();
                $liabilities = GeneralLedgerAccount::where('account_type', 'LIABILITIES')->get();
                $equity = GeneralLedgerAccount::where('account_type', 'EQUITY')->get();

                $totalAssets = $assets->sum('current_balance');
                $totalLiabilities = $liabilities->sum('current_balance');
                $totalEquity = $equity->sum('current_balance');
                $totalLiabEquity = $totalLiabilities + $totalEquity;
                $difference = $totalAssets - $totalLiabEquity;

                return view('reports.partials.balance_sheet_summary', compact(
                    'assets', 'liabilities', 'equity',
                    'totalAssets', 'totalLiabilities', 'totalEquity',
                    'totalLiabEquity', 'difference'
                ));

            // ── 4. FINANCIAL: Profit & Loss Summary ──────────────────────────────
            case 'profit_loss_summary':
                $incomeTotal = GeneralLedgerAccount::where('account_type', 'INCOME')->sum('current_balance');
                $expenseTotal = GeneralLedgerAccount::where('account_type', 'EXPENSE')->sum('current_balance');

                // Sales & COGS for selectable date range
                $salesQuery = Sale::query();
                if ($fromDate) $salesQuery->whereDate('sale_date', '>=', $fromDate);
                if ($toDate)   $salesQuery->whereDate('sale_date', '<=', $toDate);
                $grossSales = $salesQuery->sum('grand_total');

                $refundsQuery = Refund::query();
                if ($fromDate) $refundsQuery->whereDate('refund_date', '>=', $fromDate);
                if ($toDate)   $refundsQuery->whereDate('refund_date', '<=', $toDate);
                $totalRefunds = $refundsQuery->sum('total_amount');
                $netRevenue = max(0, $grossSales - $totalRefunds);

                // COGS from SaleItems (FIFO cost)
                $saleItems = SaleItem::whereHas('sale', function ($q) use ($fromDate, $toDate) {
                    if ($fromDate) $q->whereDate('sale_date', '>=', $fromDate);
                    if ($toDate)   $q->whereDate('sale_date', '<=', $toDate);
                })->with(['batch', 'item'])->get();

                $cogs = 0;
                foreach ($saleItems as $si) {
                    $costRate = ($si->batch_id && $si->batch) ? $si->batch->cost_price : ($si->item->cost_rate ?? 0);
                    $cogs += ($si->qty * $costRate);
                }

                $grossProfit = $netRevenue - $cogs;

                // Operating Expenses & Write-offs
                $paymentsQuery = Payment::query();
                if ($fromDate) $paymentsQuery->whereDate('payment_date', '>=', $fromDate);
                if ($toDate)   $paymentsQuery->whereDate('payment_date', '<=', $toDate);
                $operatingExpenses = $paymentsQuery->sum('amount_paid');

                $badDebtQuery = CustomerLedgerEntry::where('type', 'write_off');
                if ($fromDate) $badDebtQuery->whereDate('created_at', '>=', $fromDate);
                if ($toDate)   $badDebtQuery->whereDate('created_at', '<=', $toDate);
                $totalBadDebt = abs($badDebtQuery->sum('amount'));

                $netProfit = $grossProfit - $operatingExpenses - $totalBadDebt;

                return view('reports.partials.profit_loss_summary', compact(
                    'fromDate', 'toDate', 'grossSales', 'totalRefunds', 'netRevenue',
                    'cogs', 'grossProfit', 'operatingExpenses', 'totalBadDebt', 'netProfit',
                    'incomeTotal', 'expenseTotal'
                ));

            // ── 5. SALES: Sales Summary ──────────────────────────────────────────
            case 'sales_summary':
                $query = Sale::query();
                if ($fromDate) $query->whereDate('sale_date', '>=', $fromDate);
                if ($toDate)   $query->whereDate('sale_date', '<=', $toDate);

                $dailySales = $query->select(
                    DB::raw('DATE(sale_date) as date'),
                    DB::raw('COUNT(*) as total_transactions'),
                    DB::raw('SUM(subtotal) as subtotal'),
                    DB::raw('SUM(tax_total) as tax_total'),
                    DB::raw('SUM(discount_total) as discount_total'),
                    DB::raw('SUM(grand_total) as grand_total')
                )
                ->groupBy(DB::raw('DATE(sale_date)'))
                ->orderBy('date', 'desc')
                ->get();

                $totals = [
                    'transactions' => $dailySales->sum('total_transactions'),
                    'subtotal'     => $dailySales->sum('subtotal'),
                    'tax'          => $dailySales->sum('tax_total'),
                    'discount'     => $dailySales->sum('discount_total'),
                    'grand_total'  => $dailySales->sum('grand_total'),
                ];

                return view('reports.partials.sales_summary', compact('dailySales', 'totals', 'fromDate', 'toDate'));

            // ── 6. SALES: Sales by Item ──────────────────────────────────────────
            case 'sales_by_item':
                $query = SaleItem::whereHas('sale', function ($q) use ($fromDate, $toDate) {
                    if ($fromDate) $q->whereDate('sale_date', '>=', $fromDate);
                    if ($toDate)   $q->whereDate('sale_date', '<=', $toDate);
                })->with('item');

                $itemsData = $query->select(
                    'item_id',
                    'item_name',
                    DB::raw('SUM(qty) as total_qty'),
                    DB::raw('SUM(total) as total_revenue')
                )
                ->groupBy('item_id', 'item_name')
                ->orderBy('total_revenue', 'desc')
                ->get();

                $totals = [
                    'qty'     => $itemsData->sum('total_qty'),
                    'revenue' => $itemsData->sum('total_revenue'),
                ];

                return view('reports.partials.sales_by_item', compact('itemsData', 'totals', 'fromDate', 'toDate'));

            // ── 7. SALES: Sales by Customer ──────────────────────────────────────
            case 'sales_by_customer':
                $query = Sale::query();
                if ($fromDate) $query->whereDate('sale_date', '>=', $fromDate);
                if ($toDate)   $query->whereDate('sale_date', '<=', $toDate);

                $customersData = $query->with('customer')
                    ->select(
                        'customer_id',
                        'customer_name',
                        DB::raw('COUNT(*) as total_orders'),
                        DB::raw('SUM(grand_total) as total_spent')
                    )
                    ->groupBy('customer_id', 'customer_name')
                    ->orderBy('total_spent', 'desc')
                    ->get();

                $totals = [
                    'orders' => $customersData->sum('total_orders'),
                    'spent'  => $customersData->sum('total_spent'),
                ];

                return view('reports.partials.sales_by_customer', compact('customersData', 'totals', 'fromDate', 'toDate'));

            // ── 8. PURCHASES: Purchase by Item ──────────────────────────────────
            case 'purchase_by_item':
                $query = PurchaseItem::whereHas('purchase', function ($q) use ($fromDate, $toDate) {
                    if ($fromDate) $q->where(function($w) use ($fromDate) {
                        $w->whereDate('invoice_date', '>=', $fromDate)->orWhereDate('created_at', '>=', $fromDate);
                    });
                    if ($toDate) $q->where(function($w) use ($toDate) {
                        $w->whereDate('invoice_date', '<=', $toDate)->orWhereDate('created_at', '<=', $toDate);
                    });
                })->with('item');

                $purchaseItems = $query->select(
                    'item_id',
                    DB::raw('SUM(qty) as total_qty'),
                    DB::raw('SUM(total) as total_cost')
                )
                ->groupBy('item_id')
                ->orderBy('total_cost', 'desc')
                ->get();

                $totals = [
                    'qty'  => $purchaseItems->sum('total_qty'),
                    'cost' => $purchaseItems->sum('total_cost'),
                ];

                return view('reports.partials.purchase_by_item', compact('purchaseItems', 'totals', 'fromDate', 'toDate'));

            // ── 9. PURCHASES: Purchase by Supplier ──────────────────────────────
            case 'purchase_by_supplier':
                $query = Purchase::query();
                if ($fromDate) {
                    $query->where(function($w) use ($fromDate) {
                        $w->whereDate('invoice_date', '>=', $fromDate)->orWhereDate('created_at', '>=', $fromDate);
                    });
                }
                if ($toDate) {
                    $query->where(function($w) use ($toDate) {
                        $w->whereDate('invoice_date', '<=', $toDate)->orWhereDate('created_at', '<=', $toDate);
                    });
                }

                $suppliersData = $query->with('supplier')
                    ->select(
                        'supplier_id',
                        DB::raw('COUNT(*) as total_bills'),
                        DB::raw('SUM(net_total) as total_purchased')
                    )
                    ->groupBy('supplier_id')
                    ->orderBy('total_purchased', 'desc')
                    ->get();

                $totals = [
                    'bills'     => $suppliersData->sum('total_bills'),
                    'purchased' => $suppliersData->sum('total_purchased'),
                ];

                return view('reports.partials.purchase_by_supplier', compact('suppliersData', 'totals', 'fromDate', 'toDate'));

            // ── 10. CUSTOMERS / SUPPLIERS: Customer Statement ────────────────────
            case 'customer_statement':
                $customers = Customer::orderBy('name')->get();
                $selectedCustomerId = $request->input('customer_id');
                $selectedCustomer = $selectedCustomerId ? Customer::find($selectedCustomerId) : null;
                $entries = collect();

                if ($selectedCustomer) {
                    $entryQuery = CustomerLedgerEntry::where('customer_id', $selectedCustomerId);
                    if ($fromDate) $entryQuery->whereDate('created_at', '>=', $fromDate);
                    if ($toDate)   $entryQuery->whereDate('created_at', '<=', $toDate);

                    $entries = $entryQuery->orderBy('created_at', 'asc')->get();
                }

                return view('reports.partials.customer_statement', compact(
                    'customers', 'selectedCustomerId', 'selectedCustomer', 'entries', 'fromDate', 'toDate'
                ));

            // ── 11. CUSTOMERS / SUPPLIERS: Supplier Statement ────────────────────
            case 'supplier_statement':
                $suppliers = Supplier::orderBy('name')->get();
                $selectedSupplierId = $request->input('supplier_id');
                $selectedSupplier = $selectedSupplierId ? Supplier::find($selectedSupplierId) : null;
                $entries = collect();

                if ($selectedSupplier) {
                    $entryQuery = SupplierLedgerEntry::where('supplier_id', $selectedSupplierId);
                    if ($fromDate) $entryQuery->whereDate('created_at', '>=', $fromDate);
                    if ($toDate)   $entryQuery->whereDate('created_at', '<=', $toDate);

                    $entries = $entryQuery->orderBy('created_at', 'asc')->get();
                }

                return view('reports.partials.supplier_statement', compact(
                    'suppliers', 'selectedSupplierId', 'selectedSupplier', 'entries', 'fromDate', 'toDate'
                ));

            // ── 12. STOCK: Stock Below Minimum ───────────────────────────────────
            case 'stock_below_minimum':
                $items = Item::query()
                    ->where(function ($q) {
                        $q->where(function ($inner) {
                            $inner->whereNotNull('min_stock_level')
                                  ->where('min_stock_level', '>', 0)
                                  ->whereColumn('on_hand', '<', 'min_stock_level');
                        })
                        ->orWhere('on_hand', '<=', 0);
                    })
                    ->orderByRaw('on_hand ASC')
                    ->get();

                return view('reports.partials.stock_below_minimum', compact('items'));

            // ── 13. STOCK: Stock Expiry ──────────────────────────────────────────
            case 'stock_expiry':
                $batches = Batch::with('item')
                    ->whereNotNull('expires_at')
                    ->orderBy('expires_at', 'asc')
                    ->get();

                return view('reports.partials.stock_expiry', compact('batches'));

            // ── 14. STOCK: Final Stock List ──────────────────────────────────────
            case 'final_stock_list':
                $items = Item::orderBy('description', 'asc')->get();
                $totalStockValue = $items->sum(fn($i) => (float)$i->on_hand * (float)$i->cost_rate);
                $totalOnHand = $items->sum('on_hand');

                return view('reports.partials.final_stock_list', compact('items', 'totalStockValue', 'totalOnHand'));

            // ── 15. PROFIT: Profit by Item ───────────────────────────────────────
            case 'profit_by_item':
                $saleItems = SaleItem::whereHas('sale', function ($q) use ($fromDate, $toDate) {
                    if ($fromDate) $q->whereDate('sale_date', '>=', $fromDate);
                    if ($toDate)   $q->whereDate('sale_date', '<=', $toDate);
                })->with(['batch', 'item'])->get();

                $profitByItem = [];
                foreach ($saleItems as $si) {
                    $key = $si->item_id ?? ('custom_' . $si->item_name);
                    if (!isset($profitByItem[$key])) {
                        $profitByItem[$key] = [
                            'item_code' => $si->item->code ?? 'N/A',
                            'item_name' => $si->item_name ?? ($si->item->description ?? 'Unknown'),
                            'qty_sold'  => 0,
                            'revenue'   => 0,
                            'cost'      => 0,
                        ];
                    }

                    $costRate = ($si->batch_id && $si->batch) ? (float)$si->batch->cost_price : (float)($si->item->cost_rate ?? 0);

                    $profitByItem[$key]['qty_sold'] += $si->qty;
                    $profitByItem[$key]['revenue']  += (float)$si->total;
                    $profitByItem[$key]['cost']     += ($si->qty * $costRate);
                }

                $profitList = collect($profitByItem)->map(function ($row) {
                    $row['profit'] = $row['revenue'] - $row['cost'];
                    $row['margin'] = $row['revenue'] > 0 ? ($row['profit'] / $row['revenue']) * 100 : 0;
                    return $row;
                })->sortByDesc('profit')->values();

                $totals = [
                    'revenue' => $profitList->sum('revenue'),
                    'cost'    => $profitList->sum('cost'),
                    'profit'  => $profitList->sum('profit'),
                ];

                return view('reports.partials.profit_by_item', compact('profitList', 'totals', 'fromDate', 'toDate'));

            // ── 16. PROFIT: Profit by Customer ───────────────────────────────────
            case 'profit_by_customer':
                $sales = Sale::whereHas('items')
                    ->when($fromDate, fn($q) => $q->whereDate('sale_date', '>=', $fromDate))
                    ->when($toDate,   fn($q) => $q->whereDate('sale_date', '<=', $toDate))
                    ->with(['customer', 'items.batch', 'items.item'])
                    ->get();

                $profitByCust = [];
                foreach ($sales as $sale) {
                    $key = $sale->customer_id ?? ('walkin_' . ($sale->customer_name ?: 'Cash Customer'));
                    if (!isset($profitByCust[$key])) {
                        $profitByCust[$key] = [
                            'customer_name' => $sale->customer->name ?? ($sale->customer_name ?: 'Cash Customer'),
                            'phone'         => $sale->customer->phone ?? '-',
                            'orders'        => 0,
                            'revenue'       => 0,
                            'cost'          => 0,
                        ];
                    }

                    $profitByCust[$key]['orders']++;
                    $profitByCust[$key]['revenue'] += (float)$sale->grand_total;

                    foreach ($sale->items as $si) {
                        $costRate = ($si->batch_id && $si->batch) ? (float)$si->batch->cost_price : (float)($si->item->cost_rate ?? 0);
                        $profitByCust[$key]['cost'] += ($si->qty * $costRate);
                    }
                }

                $customerProfitList = collect($profitByCust)->map(function ($row) {
                    $row['profit'] = $row['revenue'] - $row['cost'];
                    $row['margin'] = $row['revenue'] > 0 ? ($row['profit'] / $row['revenue']) * 100 : 0;
                    return $row;
                })->sortByDesc('profit')->values();

                $totals = [
                    'orders'  => $customerProfitList->sum('orders'),
                    'revenue' => $customerProfitList->sum('revenue'),
                    'cost'    => $customerProfitList->sum('cost'),
                    'profit'  => $customerProfitList->sum('profit'),
                ];

                return view('reports.partials.profit_by_customer', compact('customerProfitList', 'totals', 'fromDate', 'toDate'));

            default:
                return response('<div class="p-6 text-center text-red-500 font-bold">Report not found.</div>', 404);
        }
    }
}
