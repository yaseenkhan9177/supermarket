<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Adjustment;
use App\Models\AdjustmentItem;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class AdjustmentController extends Controller
{
    public function create()
    {
        return view('adjustments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Create Header
                $adj = Adjustment::create([
                    'adjustment_no' => $request->adjustment_no,
                    'adjustment_date' => $request->adjustment_date,
                    'type' => $request->type,
                    'description' => $request->description,
                    'user_id' => auth()->id() ?? 1,
                ]);

                // 2. Process Items
                foreach ($request->items as $row) {
                    if (!empty($row['product_id'])) {

                        $system = $row['system_stock'];
                        $physical = $row['physical_stock'];
                        $diff = $physical - $system;

                        // Only record if there is a difference or if it's an opening stock entry
                        if ($diff != 0 || $request->type == 'Opening Stock') {

                            AdjustmentItem::create([
                                'adjustment_id' => $adj->id,
                                'product_id' => $row['product_id'],
                                'item_name' => $row['name'], // Snapshot name
                                'system_stock' => $system,
                                'physical_stock' => $physical,
                                'difference' => $diff
                            ]);

                            // 3. Update Real Product Stock (Item Model)
                            // Logic: If Opening Stock, overwrite on_hand. If Correction, adjust.
                            if ($request->type == 'Opening Stock') {
                                Item::where('id', $row['product_id'])->update(['on_hand' => $physical]);
                            } else {
                                // Standard Correction: Adjust by the difference
                                Item::find($row['product_id'])->increment('on_hand', $diff);
                            }
                        }
                    }
                }
            });

            return back()->with('success', 'Stock Adjusted Successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        $adjustment = Adjustment::with('items')->findOrFail($id);
        return view('adjustments.print', compact('adjustment'));
    }
}
