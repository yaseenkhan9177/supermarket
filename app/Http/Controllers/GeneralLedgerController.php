<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GeneralLedgerAccount;

class GeneralLedgerController extends Controller
{
    public function index()
    {
        // Fetch all accounts
        $accounts = GeneralLedgerAccount::orderBy('gl_code')->get();

        // Calculate Totals (Mocking groupings based on gl_type for now)
        $totalCash = GeneralLedgerAccount::where('gl_type', '01')->sum('current_balance');
        $totalInventory = GeneralLedgerAccount::where('gl_type', '02')->sum('current_balance');

        return view('general_ledger.index', compact('accounts', 'totalCash', 'totalInventory'));
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
