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
            'items' => 'nullable|array'
        ]);

        // Filter out blank rows (has either item_id or code)
        $items = array_filter($request->items ?? [], fn($row) => !empty($row['item_id']) || !empty($row['code']));

        if (empty($items)) {
            return back()->with('error', 'Please add at least one valid item.')->withInput();
        }

        try {
            DB::transaction(function () use ($request, $items) {

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
                foreach ($items as $row) {
                    $itemId = $row['item_id'];
                    if (empty($itemId) || $itemId === 'new') {
                        $item = \App\Models\Item::firstOrCreate(
                            ['code' => trim($row['code'])],
                            [
                                'item_type' => 'Stock',
                                'description' => $row['name'] ?: 'New Item ' . $row['code'],
                                'cost_rate' => $row['rate'],
                                'sale_rate' => $row['rate'] * 1.25,
                                'on_hand' => 0
                            ]
                        );
                        $itemId = $item->id;
                    }

                    $line_total = $row['qty'] * $row['rate'];
                    $total += $line_total;

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'item_id' => $itemId,
                        'qty' => $row['qty'],
                        'rate' => $row['rate'],
                        'total' => $line_total
                    ]);
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
