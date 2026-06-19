<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockAdjustmentController extends Controller
{
    /**
     * Display a listing of stock adjustments
     */
    public function index()
    {
        $adjustments = StockAdjustment::with('user')
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('adjustments.index', compact('adjustments'));
    }

    /**
     * Show the form for creating a new stock adjustment
     */
    public function create()
    {
        $types = [
            'correction' => 'Stock Correction',
            'damage' => 'Damaged Goods',
            'loss' => 'Loss/Theft',
            'transfer' => 'Transfer',
            'stock_take' => 'Stock Take/Audit'
        ];

        return view('adjustments.create', compact('types'));
    }

    /**
     * Store a newly created stock adjustment
     */
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'date' => 'required|date',
            'type' => 'required|in:correction,damage,loss,transfer,stock_take',
            'reason' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:items,id',
            'items.*.system_quantity' => 'required|integer|min:0',
            'items.*.physical_quantity' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            // 1. Create Stock Adjustment Header
            $adjustment = StockAdjustment::create([
                'date' => $validated['date'],
                'type' => $validated['type'],
                'reason' => $validated['reason'] ?? null,
                'user_id' => auth()->id() ?? 1,
                'total_items' => count($validated['items'])
            ]);

            // 2. Process Each Item
            foreach ($validated['items'] as $itemData) {
                $product = Item::find($itemData['product_id']);

                if (!$product) {
                    throw new \Exception("Product not found: ID {$itemData['product_id']}");
                }

                // Calculate difference
                $difference = $itemData['physical_quantity'] - $itemData['system_quantity'];

                // Create adjustment item record
                StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_id' => $itemData['product_id'],
                    'system_quantity' => $itemData['system_quantity'],
                    'physical_quantity' => $itemData['physical_quantity'],
                    'difference' => $difference
                ]);

                // Update product stock in items table
                if ($difference != 0) {
                    // Check if items table has on_hand column
                    $columnExists = \Schema::hasColumn('items', 'on_hand');

                    if ($columnExists) {
                        $product->increment('on_hand', $difference);
                    } else {
                        // Fallback: update stock or qty column if on_hand doesn't exist
                        if (\Schema::hasColumn('items', 'stock')) {
                            $product->increment('stock', $difference);
                        } elseif (\Schema::hasColumn('items', 'qty')) {
                            $product->increment('qty', $difference);
                        }
                    }

                    Log::info("Stock adjusted for product #{$product->id}: {$difference} units", [
                        'adjustment_id' => $adjustment->id,
                        'product_id' => $product->id,
                        'old_qty' => $itemData['system_quantity'],
                        'new_qty' => $itemData['physical_quantity']
                    ]);
                }
            }

            DB::commit();

            Log::info("Stock adjustment {$adjustment->reference_no} created successfully by user " . auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Stock adjustment saved successfully!',
                'adjustment' => $adjustment->load('items.product'),
                'reference_no' => $adjustment->reference_no
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Stock adjustment error: " . $e->getMessage(), [
                'user_id' => auth()->id(),
                'items_count' => count($request->items ?? [])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error saving adjustment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified stock adjustment
     */
    public function show($id)
    {
        $adjustment = StockAdjustment::with(['items.product', 'user'])->findOrFail($id);
        return view('adjustments.show', compact('adjustment'));
    }

    /**
     * Search products for adjustment (AJAX endpoint)
     */
    public function searchProducts(Request $request)
    {
        $query = trim($request->query('q'));

        if (strlen($query) < 1) {
            return response()->json([]);
        }

        $products = Item::where('description', 'LIKE', "%{$query}%")
            ->orWhere('barcode', 'LIKE', "%{$query}%")
            ->orWhere('code', 'LIKE', "%{$query}%")
            ->select('id', 'description', 'barcode', 'code', 'on_hand')
            ->limit(15)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->description, // Map description to name for frontend
                    'barcode' => $item->barcode ?? $item->code,
                    'code' => $item->code,
                    'on_hand' => (float) $item->on_hand // Ensure float
                ];
            });

        return response()->json($products);
    }
}
