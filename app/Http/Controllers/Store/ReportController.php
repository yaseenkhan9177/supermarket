<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DebitSale;
use App\Models\Purchase;
use App\Models\Account;
use App\Models\CustomerLedgerEntry;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $sales = DebitSale::with('customer')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->latest('invoice_date')
            ->get();

        $totalSales = $sales->sum('net_total'); // Assuming net_total exists
        $totalPaid = $sales->sum('paid_amount'); // Assuming paid_amount exists
        $totalDue = $sales->sum('balance'); // or calculated

        return view('store.reports.sales', compact('sales', 'startDate', 'endDate', 'totalSales', 'totalPaid', 'totalDue'));
    }

    public function purchases(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Assuming Purchase model will have similar structure
        $purchases = Purchase::whereBetween('invoice_date', [$startDate, $endDate])
            ->latest('invoice_date')
            ->get();

        $totalPurchases = $purchases->sum('net_total');

        return view('store.reports.purchases', compact('purchases', 'startDate', 'endDate', 'totalPurchases'));
    }

    public function accounts(Request $request)
    {
        $accounts = Account::all();

        // Calculate totals dynamically if needed, or rely on model 'balance'
        $totalAssets = $accounts->where('type', 'Asset')->sum('balance'); // Example logic

        return view('store.reports.accounts', compact('accounts'));
    }

    /**
     * Report page for Written Off Customers & Balances.
     */
    public function writtenOffCustomers(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        $query = CustomerLedgerEntry::with(['customer', 'creator'])
            ->where('type', 'write_off');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        $writeOffEntries = $query->latest('created_at')->get();

        $totalWrittenOffAmount = abs($writeOffEntries->sum('amount'));

        $breakdown = $writeOffEntries->groupBy(function ($item) {
            return $item->reason_category ?: 'other';
        })->map(function ($items, $category) {
            return [
                'category' => $category,
                'count'    => $items->count(),
                'total'    => abs($items->sum('amount')),
            ];
        });

        return view('store.reports.written_off_customers', compact(
            'writeOffEntries',
            'startDate',
            'endDate',
            'totalWrittenOffAmount',
            'breakdown'
        ));
    }
}
