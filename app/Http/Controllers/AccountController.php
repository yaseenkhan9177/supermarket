<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeneralLedgerAccount;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = GeneralLedgerAccount::orderBy('gl_code');

        if ($request->filled('type')) {
            $query->where('account_type', strtoupper($request->type));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('gl_code', 'LIKE', "%{$search}%")
                  ->orWhere('gl_type', 'LIKE', "%{$search}%");
            });
        }

        $accounts = $query->get();

        $totalAccounts = GeneralLedgerAccount::count();
        $totalAssets = GeneralLedgerAccount::where('account_type', 'ASSETS')->sum('current_balance');
        $totalLiabilities = GeneralLedgerAccount::where('account_type', 'LIABILITIES')->sum('current_balance');
        $totalEquity = GeneralLedgerAccount::where('account_type', 'EQUITY')->sum('current_balance');
        $totalIncome = GeneralLedgerAccount::where('account_type', 'INCOME')->sum('current_balance');
        $totalExpense = GeneralLedgerAccount::where('account_type', 'EXPENSE')->sum('current_balance');
        $netEquity = $totalAssets - $totalLiabilities;

        return view('accounts.index', compact(
            'accounts',
            'totalAccounts',
            'totalAssets',
            'totalLiabilities',
            'totalEquity',
            'totalIncome',
            'totalExpense',
            'netEquity'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'gl_code' => 'required|unique:general_ledger_accounts,gl_code,' . $request->id,
            'name' => 'required',
            'account_type' => 'required'
        ]);

        $data = $request->except(['id']);
        $data['account_type'] = strtoupper($request->account_type);

        if (empty($data['gl_type'])) {
            $data['gl_type'] = substr(trim($request->gl_code), 0, 2);
        }

        if ($request->id) {
            $acc = GeneralLedgerAccount::find($request->id);
            $acc->update($data);
            $msg = 'GL Account Updated Successfully';
        } else {
            $data['opening_balance'] = $request->opening_balance ?? 0;
            $data['current_balance'] = $request->opening_balance ?? 0;
            GeneralLedgerAccount::create($data);
            $msg = 'GL Account Created Successfully';
        }

        return back()->with('success', $msg);
    }

    public function destroy($id)
    {
        $acc = GeneralLedgerAccount::findOrFail($id);
        $acc->delete();
        return back()->with('success', 'Account deleted successfully.');
    }
}
