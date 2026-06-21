<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Godam;
use App\Models\GodamStock;
use App\Models\Item;

class GodamController extends Controller
{
    /**
     * Display a listing of the warehouses with stats.
     */
    public function index()
    {
        $godams = Godam::with(['stocks.item'])->get()->map(function ($godam) {
            $activeStocks = $godam->stocks->filter(fn($s) => $s->quantity > 0);
            
            $godam->total_items = $activeStocks->count();
            $godam->total_qty = $activeStocks->sum('quantity');
            $godam->total_value = $activeStocks->sum(fn($s) => $s->quantity * ($s->item->cost_rate ?? 0));
            
            return $godam;
        });

        return view('godams.index', compact('godams'));
    }

    /**
     * Show the form for creating a new warehouse.
     */
    public function create()
    {
        return view('godams.create');
    }

    /**
     * Store a newly created warehouse.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        Godam::create([
            'name' => $request->name,
            'location' => $request->location,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
            'notes' => $request->notes,
        ]);

        return redirect()->route('godams.index')->with('success', 'Godam created successfully.');
    }

    /**
     * Show the edit form for a warehouse.
     */
    public function show($id)
    {
        $godam = Godam::findOrFail($id);
        return view('godams.edit', compact('godam'));
    }

    /**
     * Update warehouse details.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $godam = Godam::findOrFail($id);
        $godam->update([
            'name' => $request->name,
            'location' => $request->location,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : false,
            'notes' => $request->notes,
        ]);

        return redirect()->route('godams.index')->with('success', 'Godam updated successfully.');
    }

    /**
     * Delete a warehouse if it has no stock.
     */
    public function destroy($id)
    {
        $godam = Godam::findOrFail($id);

        // Check if there is any item in stock with qty > 0
        $hasStock = GodamStock::where('godam_id', $id)
            ->where('quantity', '>', 0)
            ->exists();

        if ($hasStock) {
            return back()->with('error', 'Cannot delete this godam. It currently holds stock. Please transfer all stock out before deleting.');
        }

        $godam->delete();

        return redirect()->route('godams.index')->with('success', 'Godam deleted successfully.');
    }

    /**
     * Display all items currently stored in this warehouse.
     */
    public function inventory(Request $request, $id)
    {
        $godam = Godam::findOrFail($id);

        $query = GodamStock::with(['item.department'])
            ->where('godam_id', $id)
            ->where('quantity', '>', 0);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('item', function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $stocks = $query->get();
        $totalValue = $stocks->sum(fn($s) => $s->quantity * ($s->item->cost_rate ?? 0));

        return view('godams.show', compact('godam', 'stocks', 'totalValue'));
    }
}
