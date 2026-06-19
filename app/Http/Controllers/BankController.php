<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankAccount;
use App\Models\GeneralLedgerAccount; // Link to GL
use Illuminate\Support\Facades\DB;

class BankController extends Controller
{
    public function index()
    {
        return view('banks.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_title' => 'required',
            'gl_code' => 'required|unique:bank_accounts,gl_code,' . $request->id,
        ]);

        try {
            DB::transaction(function () use ($request) {

                // 1. Save Bank Details
                if ($request->id) {
                    $bank = BankAccount::find($request->id);
                    $bank->update($request->all());
                    $gl_action = 'update';
                } else {
                    $bank = BankAccount::create($request->all());
                    $gl_action = 'create';
                }

                // 2. AUTO-SYNC with General Ledger
                // This ensures "Meezan Bank" shows up in your Balance Sheet automatically.
                $glData = [
                    'gl_code' => $request->gl_code,
                    'name' => $request->account_title,
                    'gl_type' => '01', // 01: CASH/BANKS
                    'account_type' => 'ASSETS',
                    'opening_balance' => $request->opening_balance ?? 0,
                    'current_balance' => $request->opening_balance ?? 0
                ];

                if ($gl_action == 'create') {
                    // Check if GL exists just in case
                    $exists = GeneralLedgerAccount::where('gl_code', $request->gl_code)->exists();
                    if (!$exists) {
                        GeneralLedgerAccount::create($glData);
                    }
                } else {
                    GeneralLedgerAccount::where('gl_code', $request->gl_code)->update($glData);
                }
            });

            return back()->with('success', 'Bank Account Saved & GL Updated!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $bank = BankAccount::findOrFail($id);
        // transactions are currently hardcoded in the view as per design prototype
        return view('banks.show', compact('bank'));
    }
}
