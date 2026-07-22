<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\CustomerLedgerEntry;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitLossController extends Controller
{
    public function index(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $preset = $request->input('preset', 'this_month');

        // Apply default 'this_month' if no dates provided
        if (empty($fromDate) && empty($toDate) && $preset === 'this_month') {
            $fromDate = now()->startOfMonth()->toDateString();
            $toDate = now()->endOfMonth()->toDateString();
        }

        // 1. Sales Query & Gross Revenue
        $salesQuery = Sale::query();
        if ($fromDate) $salesQuery->whereDate('sale_date', '>=', $fromDate);
        if ($toDate)   $salesQuery->whereDate('sale_date', '<=', $toDate);

        $grossSales = (clone $salesQuery)->sum('grand_total');

        // 2. Refunds / Returns
        $refundsQuery = Refund::query();
        if ($fromDate) $refundsQuery->where(function($q) use ($fromDate) {
            $q->whereDate('refund_date', '>=', $fromDate)->orWhereDate('created_at', '>=', $fromDate);
        });
        if ($toDate)   $refundsQuery->where(function($q) use ($toDate) {
            $q->whereDate('refund_date', '<=', $toDate)->orWhereDate('created_at', '<=', $toDate);
        });

        $totalRefunds = (clone $refundsQuery)->sum('total_amount');
        $netRevenue = max(0, $grossSales - $totalRefunds);

        // 3. COGS (Cost of Goods Sold)
        $saleItemsQuery = SaleItem::whereHas('sale', function ($q) use ($fromDate, $toDate) {
            if ($fromDate) $q->whereDate('sale_date', '>=', $fromDate);
            if ($toDate)   $q->whereDate('sale_date', '<=', $toDate);
        })->with(['batch', 'item']);

        $saleItems = $saleItemsQuery->get();

        $cogs = 0;
        $hasLegacyNonBatchSales = false;

        foreach ($saleItems as $item) {
            $costRate = 0;
            if ($item->batch_id && $item->batch) {
                $costRate = $item->batch->cost_price;
            } else {
                $hasLegacyNonBatchSales = true;
                $costRate = $item->item->cost_rate ?? 0;
            }
            $cogs += ($item->qty * $costRate);
        }

        $grossProfit = $netRevenue - $cogs;

        // 4. Operating Expenses (Payments)
        $paymentsQuery = Payment::query();
        if ($fromDate) $paymentsQuery->whereDate('payment_date', '>=', $fromDate);
        if ($toDate)   $paymentsQuery->whereDate('payment_date', '<=', $toDate);

        $totalExpenses = (clone $paymentsQuery)->sum('amount_paid');

        // Expense Breakdown by Account / Head
        $expenseBreakdown = (clone $paymentsQuery)
            ->select('paid_to_account', DB::raw('SUM(amount_paid) as total_amount'))
            ->groupBy('paid_to_account')
            ->orderBy('total_amount', 'desc')
            ->get();

        // 5. Bad Debt Losses (Customer Write-offs)
        $badDebtQuery = CustomerLedgerEntry::where('type', 'write_off');
        if ($fromDate) $badDebtQuery->whereDate('created_at', '>=', $fromDate);
        if ($toDate)   $badDebtQuery->whereDate('created_at', '<=', $toDate);

        $totalBadDebt = (clone $badDebtQuery)->sum('amount');

        // 6. Net Profit
        $netProfit = $grossProfit - $totalExpenses - $totalBadDebt;

        // 7. Daily Trend Chart Data
        $trendData = [];
        if ($fromDate && $toDate) {
            $start = \Carbon\Carbon::parse($fromDate);
            $end = \Carbon\Carbon::parse($toDate);

            // Limit daily trend to max 60 days to prevent chart overload
            if ($start->diffInDays($end) <= 60) {
                $period = \Carbon\CarbonPeriod::create($start, $end);
                foreach ($period as $date) {
                    $dStr = $date->toDateString();

                    $dayRevenue = Sale::whereDate('sale_date', $dStr)->sum('grand_total')
                        - Refund::where(function($q) use ($dStr) {
                            $q->whereDate('refund_date', $dStr)->orWhereDate('created_at', $dStr);
                        })->sum('total_amount');

                    $daySaleItems = SaleItem::whereHas('sale', function ($q) use ($dStr) {
                        $q->whereDate('sale_date', $dStr);
                    })->with(['batch', 'item'])->get();

                    $dayCogs = 0;
                    foreach ($daySaleItems as $dsi) {
                        $cRate = ($dsi->batch_id && $dsi->batch) ? $dsi->batch->cost_price : ($dsi->item->cost_rate ?? 0);
                        $dayCogs += ($dsi->qty * $cRate);
                    }

                    $dayExpenses = Payment::whereDate('payment_date', $dStr)->sum('amount_paid');
                    $dayBadDebt = CustomerLedgerEntry::where('type', 'write_off')->whereDate('created_at', $dStr)->sum('amount');

                    $dayNetProfit = ($dayRevenue - $dayCogs) - $dayExpenses - $dayBadDebt;

                    $trendData['labels'][] = $date->format('d M');
                    $trendData['values'][] = round($dayNetProfit, 2);
                }
            }
        }

        return view('reports.profit_loss', compact(
            'fromDate',
            'toDate',
            'preset',
            'grossSales',
            'totalRefunds',
            'netRevenue',
            'cogs',
            'grossProfit',
            'totalExpenses',
            'expenseBreakdown',
            'totalBadDebt',
            'netProfit',
            'hasLegacyNonBatchSales',
            'trendData'
        ));
    }
}
