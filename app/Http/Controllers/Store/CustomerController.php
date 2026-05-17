<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function quickStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'balance' => 0,
            'credit_limit' => 0, // Default limit
        ]);

        return response()->json([
            'success' => true,
            'customer' => $customer
        ]);
    }

    public function index()
    {
        // Use full path or import model if not imported.
        // Assuming App\Models\Customer exists.
        $customers = \App\Models\Customer::latest()->paginate(10);
        return view('store.customers.index', compact('customers'));
    }
}
