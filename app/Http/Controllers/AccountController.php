<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;

class AccountController extends Controller
{
    public function index()
    {
        // Fetch accounts for the view
        $accounts = Account::orderBy('code')->get();
        // Calculate totals for the overview widget
        $totalAssets = Account::where('type', 'Asset')->sum('current_balance');
        $totalLiabilities = Account::where('type', 'Liability')->sum('current_balance');
        $totalEquity = Account::where('type', 'Equity')->sum('current_balance');
        // Simple Net Equity calc (Asset - Liability) for display if needed, or just sum of Equity accounts
        $netEquity = $totalAssets - $totalLiabilities;

        return view('accounts.index', compact('accounts', 'totalAssets', 'totalLiabilities', 'netEquity'));
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
}
