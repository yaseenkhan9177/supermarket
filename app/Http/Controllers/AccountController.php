<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = Account::orderBy('code');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('category', 'LIKE', "%{$search}%");
            });
        }

        $accounts = $query->get();

        $totalAccounts = Account::count();
        $totalAssets = Account::where('type', 'Asset')->sum('current_balance');
        $totalLiabilities = Account::where('type', 'Liability')->sum('current_balance');
        $totalEquity = Account::where('type', 'Equity')->sum('current_balance');
        $totalIncome = Account::where('type', 'Income')->sum('current_balance');
        $totalExpense = Account::where('type', 'Expense')->sum('current_balance');
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
            'code' => 'required|unique:accounts,code,' . $request->id,
            'name' => 'required',
            'type' => 'required'
        ]);

        if ($request->id) {
            $acc = Account::find($request->id);
            if ($acc->is_system && $request->code !== $acc->code) {
                return back()->with('error', 'Cannot change code of a system account.');
            }
            $acc->update($request->all());
            $msg = 'Account Updated Successfully';
        } else {
            // For new accounts, set current balance = opening balance initially
            $data = $request->except(['id']);
            $data['current_balance'] = $request->opening_balance ?? 0;
            Account::create($data);
            $msg = 'Account Created Successfully';
        }

        return back()->with('success', $msg);
    }

    public function destroy($id)
    {
        $acc = Account::findOrFail($id);
        if ($acc->is_system) {
            return back()->with('error', 'System accounts cannot be deleted.');
        }
        $acc->delete();
        return back()->with('success', 'Account deleted successfully.');
    }
}
