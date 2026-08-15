<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with('user')->latest('payment_date');

        if ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_no', 'LIKE', "%{$search}%")
                  ->orWhere('paid_to_account', 'LIKE', "%{$search}%")
                  ->orWhere('memo', 'LIKE', "%{$search}%");
            });
        }

        $totalAmount = (clone $query)->sum('amount_paid');
        $payments = $query->paginate(20)->withQueryString();

        return view('payments.index', compact('payments', 'totalAmount'));
    }

    public function create()
    {
        $users = \App\Models\User::all();
        $wallets = \App\Models\Wallet::where('is_active', true)->get();
        return view('payments.create', compact('users', 'wallets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'paid_to_account' => 'required|string',
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'wallet_id' => 'required|exists:wallets,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $wallet = \App\Models\Wallet::findOrFail($request->wallet_id);

                Payment::create([
                    'payment_no' => $request->payment_no,
                    'payment_date' => $request->payment_date,
                    'paid_to_account' => $request->paid_to_account,
                    'paid_from_account' => $wallet->name,
                    'wallet_id' => $wallet->id,
                    'amount_paid' => $request->amount_paid,
                    'discount_received' => $request->discount_received ?? 0,
                    'cheque_no' => $request->cheque_no,
                    'cheque_date' => $request->cheque_date,
                    'memo' => $request->memo,
                    'user_id' => $request->user_id ?? auth()->id(), // Fallback to current user
                ]);

                // Expenses decrease wallet balance (negative amount passed)
                $wallet->adjustBalance(-abs((float) $request->amount_paid));
            });

            return back()->with('success', 'Payment Recorded Successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
