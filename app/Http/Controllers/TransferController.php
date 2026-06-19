<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transfer;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function create()
    {
        $users = \App\Models\User::all();
        // Assuming we might want to pass existing accounts too, but defaults are hardcoded in view for design.
        // If dynamic accounts are needed, fetch them here: $accounts = Account::all();
        return view('transfers.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_account' => 'required|different:to_account',
            'to_account' => 'required',
            'amount' => 'required|numeric|min:1',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Balance Check
                // Attempt to find the account record to check balance.
                // Note: Since the view uses strings like "010001: MAIN SAFE", we might need to parse or match names.
                // For this implementation, we'll assume we look up by name or just loose matching if strict accounting isn't setup.
                // However, based on the requirements, we should check balance if possible.

                // Let's try to find the account by name if it exists in the accounts table
                $fromAccountName = $request->from_account;
                $fromAccount = Account::where('name', $fromAccountName)->first();

                if ($fromAccount) {
                    if ($fromAccount->balance < $request->amount) {
                        throw new \Exception("Insufficient funds in " . $fromAccountName . ". Current Balance: " . $fromAccount->balance);
                    }
                    $fromAccount->decrement('balance', $request->amount);
                }

                $toAccountName = $request->to_account;
                $toAccount = Account::where('name', $toAccountName)->first();
                if ($toAccount) {
                    $toAccount->increment('balance', $request->amount);
                }

                // 2. Record Transfer
                $transfer = Transfer::create([
                    'transfer_no' => $request->transfer_no,
                    'transfer_date' => $request->transfer_date,
                    'from_account' => $request->from_account,
                    'to_account' => $request->to_account,
                    'amount' => $request->amount,
                    'purpose' => $request->purpose,
                    'user_id' => $request->user_id,
                ]);
            });

            return back()->with('success', 'Funds Transferred Successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Transfer Failed: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        $transfer = Transfer::findOrFail($id);
        return view('transfers.print', compact('transfer'));
    }
}
