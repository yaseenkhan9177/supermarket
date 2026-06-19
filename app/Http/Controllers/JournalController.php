<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Journal;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;

class JournalController extends Controller
{
    public function create()
    {
        // In real app: $accounts = Account::all();
        return view('journals.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'entries' => 'required|array|min:2',
            'date' => 'required|date'
        ]);

        try {
            DB::transaction(function () use ($request) {

                // 1. Calculate Totals Backend Side (Security)
                $debitTotal = 0;
                $creditTotal = 0;

                foreach ($request->entries as $entry) {
                    $debitTotal += $entry['debit'] ?? 0;
                    $creditTotal += $entry['credit'] ?? 0;
                }

                if (abs($debitTotal - $creditTotal) > 0.01) {
                    throw new \Exception("Journal is unbalanced! Debit: $debitTotal, Credit: $creditTotal");
                }

                // 2. Create Header
                $journal = Journal::create([
                    'journal_no' => 'JV-' . date('Ymd') . '-' . rand(100, 999),
                    'date' => $request->date,
                    'memo' => $request->memo,
                    'total_debit' => $debitTotal,
                    'total_credit' => $creditTotal,
                    'user_id' => auth()->id() ?? 1,
                ]);

                // 3. Create Lines
                foreach ($request->entries as $entry) {
                    if (!empty($entry['account_code'])) {
                        JournalEntry::create([
                            'journal_id' => $journal->id,
                            'account_code' => $entry['account_code'],
                            'account_name' => $entry['account_name'] ?? 'Ledger Account',
                            'description' => $entry['description'],
                            'debit' => $entry['debit'] ?? 0,
                            'credit' => $entry['credit'] ?? 0,
                        ]);
                    }
                }
            });

            return back()->with('success', 'Journal Voucher Posted Successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
