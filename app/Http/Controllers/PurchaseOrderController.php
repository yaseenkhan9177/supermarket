<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function create()
    {
        $suppliers = \App\Models\Supplier::all();
        return view('purchase_orders.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'items' => 'required|array|min:1'
        ]);

        try {
            DB::transaction(function () use ($request) {

                // 1. Create PO Header
                $po = PurchaseOrder::create([
                    'po_number' => $request->po_number,
                    'order_date' => $request->order_date,
                    'delivery_date' => $request->delivery_date,
                    'supplier_id' => $request->supplier_id,
                    'status' => 'Pending',
                    'estimated_total' => 0, // Calculated below
                    'user_id' => auth()->id() ?? 1,
                    'memo' => $request->memo,
                ]);

                $total = 0;

                // 2. Save Items (NO STOCK UPDATE)
                foreach ($request->items as $row) {
                    if (!empty($row['item_id'])) {
                        $line_total = $row['qty'] * $row['rate'];
                        $total += $line_total;

                        PurchaseOrderItem::create([
                            'purchase_order_id' => $po->id,
                            'item_id' => $row['item_id'],
                            'qty' => $row['qty'],
                            'rate' => $row['rate'],
                            'total' => $line_total
                        ]);
                    }
                }

                // 3. Update Header Total
                $po->update(['estimated_total' => $total]);
            });

            return back()->with('success', 'Purchase Order Created! (Draft Mode)');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
