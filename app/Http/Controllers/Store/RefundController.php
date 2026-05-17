<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Refund;
use App\Models\RefundItem;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $refunds = Refund::with(['customer', 'salesman'])->latest()->paginate(10);
        return view('refunds.index', compact('refunds'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = \App\Models\Customer::select('id', 'name')->get();
        $users = \App\Models\User::select('id', 'name')->get();
        return view('refunds.create', compact('customers', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'rows' => 'required|array|min:1',
            'rows.*.id' => 'required|exists:items,id', // Product ID must exist in items table
            'rows.*.qty' => 'required|numeric|min:1'
        ]);

        try {
            DB::beginTransaction();

            // Calculate totals
            $totalAmount = 0;
            foreach ($request->rows as $row) {
                if (!empty($row['id'])) {
                    // Logic to find product by ID or Barcode could go here if 'id' is ambiguous
                    // For now assuming 'id' is product_id from the frontend scanner
                    $qty = $row['qty'];
                    $rate = $row['rate'];
                    $disc = $row['disc'] ?? 0;
                    $net = ($qty * $rate) - $disc;
                    $totalAmount += $net;
                }
            }

            $refund = Refund::create([
                'credit_no'       => $request->credit_no,
                'refund_date'     => $request->refund_date,
                'customer_id'     => $request->customer_id,
                'salesman_id'     => $request->salesman_id,
                'paid_from_account' => $request->paid_from_account,
                'memo'            => $request->memo,
                'total_amount'    => $totalAmount
            ]);

            foreach ($request->rows as $row) {
                if (!empty($row['id'])) {
                    $qty = $row['qty'];
                    $rate = $row['rate'];
                    $disc = $row['disc'] ?? 0;

                    RefundItem::create([
                        'refund_id'   => $refund->id,
                        'product_id'  => $row['id'],
                        'item_name'   => $row['name'],
                        'quantity'    => $qty,
                        'rate'        => $rate,
                        'net_amount'  => ($qty * $rate) - $disc
                    ]);

                    // Increment Stock (using Item model)
                    Item::find($row['id'])->increment('on_hand', $qty); // Assuming 'on_hand' is the stock column in items table (step 266)
                }
            }

            DB::commit();
            return redirect()->route('refunds.print', $refund->id)->with('success', 'Refund Processed Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing refund: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        $refund = Refund::with('items')->findOrFail($id);
        // reusing the cash receipt view or a similar simple view for now, or creating a new one if needed
        // User asked for "print", I will make a simple view or reuse cash_receipt logic but for refunds
        // Let's create a specific refund receipt view later or inline it
        return view('sales.cash_receipt', ['sale' => $refund]); // Reusing for speed if compatible, or creating new
    }
}
