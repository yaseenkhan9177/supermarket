<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Item;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function create()
    {
        $suppliers = Supplier::all();
        return view('purchase_returns.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'items' => 'required|array|min:1'
        ]);

        try {
            DB::transaction(function () use ($request) {

                // 1. Create Return Header
                $return = PurchaseReturn::create([
                    'return_no' => $request->return_no,
                    'return_date' => $request->return_date,
                    'vendor_bill_no' => $request->vendor_bill_no,
                    'supplier_id' => $request->supplier_id,
                    'refund_mode' => $request->refund_mode,
                    'total_amount' => 0, // Calculated below
                    'memo' => $request->memo,
                    'user_id' => auth()->id() ?? 1,
                ]);

                $total = 0;

                // 2. Process Items
                foreach ($request->items as $row) {
                    if (!empty($row['item_id'])) {
                        // Validate Item Exists
                        $item = Item::find($row['item_id']);
                        if (!$item) continue;

                        $line_total = $row['qty'] * $row['rate'];
                        $total += $line_total;

                        PurchaseReturnItem::create([
                            'purchase_return_id' => $return->id,
                            'item_id' => $row['item_id'],
                            'qty' => $row['qty'],
                            'rate' => $row['rate'],
                            'total' => $line_total
                        ]);

                        // 3. DECREMENT Stock (Stock Out)
                        $item->decrement('on_hand', $row['qty']);
                    }
                }

                // 4. Update Header Total
                $return->update(['total_amount' => $total]);

                // 5. ACCOUNTING LOGIC
                // If it's a "Credit Note", we reduce the amount we owe the supplier.
                if ($request->refund_mode === 'Credit Note') {
                    Supplier::where('id', $request->supplier_id)->decrement('balance', $total);
                }
                // If "Cash", we technicaly increase our Cash on Hand, handled in CashBook later.
            });

            return back()->with('success', 'Goods Returned Successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
