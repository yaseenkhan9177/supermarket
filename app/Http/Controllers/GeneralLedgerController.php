<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeneralLedgerAccount;

class GeneralLedgerController extends Controller
{
    public function index(Request $request)
    {
        // Fetch all accounts
        $accounts = GeneralLedgerAccount::orderBy('gl_code')->get();

        $selectedAccountCode = $request->get('account_code');
        $selectedAccount = null;
        if ($selectedAccountCode) {
            $selectedAccount = GeneralLedgerAccount::where('gl_code', $selectedAccountCode)->first();
        }

        $entriesQuery = \App\Models\JournalEntry::with('journal')->latest('id');

        if ($selectedAccountCode) {
            $entriesQuery->where('account_code', $selectedAccountCode);
        }

        if ($request->filled('from_date')) {
            $entriesQuery->whereHas('journal', function ($q) use ($request) {
                $q->whereDate('date', '>=', $request->from_date);
            });
        }

        if ($request->filled('to_date')) {
            $entriesQuery->whereHas('journal', function ($q) use ($request) {
                $q->whereDate('date', '<=', $request->to_date);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $entriesQuery->where(function ($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('account_name', 'LIKE', "%{$search}%")
                  ->orWhereHas('journal', function ($jq) use ($search) {
                      $jq->where('journal_no', 'LIKE', "%{$search}%")
                         ->orWhere('memo', 'LIKE', "%{$search}%");
                  });
            });
        }

        $entries = $entriesQuery->paginate(20)->withQueryString();

        // Calculate Totals by gl_type
        $totalCash = GeneralLedgerAccount::where('gl_type', '01')->sum('current_balance');
        $totalInventory = GeneralLedgerAccount::where('gl_type', '02')->sum('current_balance');
        $totalExpenses = GeneralLedgerAccount::where('gl_type', '50')->sum('current_balance');

        return view('general_ledger.index', compact('accounts', 'selectedAccount', 'entries', 'totalCash', 'totalInventory', 'totalExpenses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'gl_code' => 'required|unique:general_ledger_accounts,gl_code,' . $request->id,
            'name' => 'required',
            'gl_type' => 'required'
        ]);

        if ($request->id) {
            $acc = GeneralLedgerAccount::find($request->id);
            $acc->update($request->all());
            $msg = 'GL Account Updated Successfully';
        } else {
            $data = $request->all();
            $data['current_balance'] = $request->opening_balance ?? 0;
            GeneralLedgerAccount::create($data);
            $msg = 'GL Account Created Successfully';
        }

        return back()->with('success', $msg);
    }
}
