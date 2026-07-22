<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CashReconciliation;
use App\Models\Refund;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DailyClosingController extends Controller
{
    /**
     * Display today's (or selected date's) cash summary and reconciliation history.
     */
    public function index(Request $request)
    {
        $selectedDate = $request->input('date', today()->toDateString());

        // 1. Calculate Expected Cash for the selected date
        $cashSalesTotal = Sale::where('payment_mode', 'Cash')
            ->whereDate('sale_date', $selectedDate)
            ->sum('grand_total');

        $cashRefundsTotal = Refund::where('refund_mode', 'CASH')
            ->whereDate('created_at', $selectedDate)
            ->sum('total_amount');

        $expectedCash = max(0, (float) $cashSalesTotal - (float) $cashRefundsTotal);

        // 2. Check if selected date is already closed
        $existingClosing = CashReconciliation::with('closedBy')
            ->whereDate('date', $selectedDate)
            ->first();

        // 3. History of past reconciliations (filterable by date range)
        $query = CashReconciliation::with('closedBy')->orderBy('date', 'desc');

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $history = $query->paginate(15)->withQueryString();

        return view('reports.daily_closing', compact(
            'selectedDate',
            'cashSalesTotal',
            'cashRefundsTotal',
            'expectedCash',
            'existingClosing',
            'history'
        ));
    }

    /**
     * Store a daily cash reconciliation closing record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date'         => 'required|date',
            'counted_cash' => 'required|numeric|min:0',
        ]);

        $date = $request->date;

        // Check if date is already closed (Immutability check)
        $existing = CashReconciliation::whereDate('date', $date)->first();
        if ($existing) {
            return back()->withErrors(['date' => "Daily closing for {$date} has already been recorded and cannot be edited."]);
        }

        // Recalculate server-side for accuracy
        $cashSalesTotal = Sale::where('payment_mode', 'Cash')
            ->whereDate('sale_date', $date)
            ->sum('grand_total');

        $cashRefundsTotal = Refund::where('refund_mode', 'CASH')
            ->whereDate('created_at', $date)
            ->sum('total_amount');

        $expectedCash = (float) $cashSalesTotal - (float) $cashRefundsTotal;
        $countedCash  = (float) $request->counted_cash;
        $difference   = $countedCash - $expectedCash;

        // If difference != 0, require explanatory note
        if (abs($difference) > 0.001) {
            $request->validate([
                'note' => 'required|string|min:5',
            ], [
                'note.required' => 'A note explaining the cash mismatch is required when difference is not zero.',
                'note.min'      => 'Please provide a detailed note explaining the cash mismatch (at least 5 characters).',
            ]);
        }

        $closing = CashReconciliation::create([
            'date'          => $date,
            'opening_cash'  => 0,
            'expected_cash' => $expectedCash,
            'counted_cash'  => $countedCash,
            'difference'    => $difference,
            'note'          => $request->note,
            'closed_by'     => Auth::id(),
        ]);

        // Audit log entry
        $statusText = $difference == 0 ? 'Balanced' : ($difference > 0 ? "Over by Rs. " . number_format(abs($difference), 2) : "Short by Rs. " . number_format(abs($difference), 2));
        AuditLog::record(
            'daily_closing',
            "Daily Cash Closing for {$date}: Expected Rs. " . number_format($expectedCash, 2) . ", Counted Rs. " . number_format($countedCash, 2) . " ({$statusText})",
            'CashReconciliation',
            $closing->id,
            [
                'expected'   => $expectedCash,
                'counted'    => $countedCash,
                'difference' => $difference,
                'note'       => $request->note,
            ]
        );

        return redirect()->route('reports.daily-closing', ['date' => $date])
            ->with('success', 'Daily cash closing successfully recorded.');
    }
}
