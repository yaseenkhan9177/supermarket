<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Item;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function create($item_id)
    {
        $item = Item::findOrFail($item_id);
        return view('batches.create', compact('item'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'batch_no' => 'required|string|max:255',
            'quantity_available' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'received_at' => 'required|date',
            'expires_at' => 'nullable|date',
        ]);

        Batch::create($request->all());

        return redirect()->route('items.edit', $request->item_id)->with('success', 'New Stock Batch Added!');
    }

    public function edit($id)
    {
        $batch = Batch::with('item')->findOrFail($id);
        return view('batches.edit', compact('batch'));
    }

    public function update(Request $request, $id)
    {
        $batch = Batch::findOrFail($id);

        $request->validate([
            'quantity_available' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'received_at' => 'required|date',
            'expires_at' => 'nullable|date',
        ]);

        $batch->update($request->only([
            'quantity_available',
            'sale_price',
            'cost_price',
            'received_at',
            'expires_at'
        ]));

        return redirect()->route('items.edit', $batch->item_id)->with('success', 'Batch Details Updated!');
    }
}
