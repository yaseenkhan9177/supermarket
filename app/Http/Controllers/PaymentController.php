<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function create()
    {
        $users = \App\Models\User::all();
        // You might also want to fetch a list of suppliers or expense accounts here
        return view('payments.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'paid_to_account' => 'required|string',
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date'
        ]);

        try {
            DB::transaction(function () use ($request) {
                Payment::create([
                    'payment_no' => $request->payment_no,
                    'payment_date' => $request->payment_date,
                    'paid_to_account' => $request->paid_to_account,
                    'paid_from_account' => $request->paid_from_account,
                    'amount_paid' => $request->amount_paid,
                    'discount_received' => $request->discount_received ?? 0,
                    'cheque_no' => $request->cheque_no,
                    'cheque_date' => $request->cheque_date,
                    'memo' => $request->memo,
                    'user_id' => $request->user_id ?? auth()->id(), // Fallback to current user
                ]);

                // Note: If you have a Supplier Balance to update, do it here:
                // if($request->supplier_id) { 
                //    Supplier::find($request->supplier_id)->decrement('balance', $request->amount_paid);
                // }
            });

            return back()->with('success', 'Payment Recorded Successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
