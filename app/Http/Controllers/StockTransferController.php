<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockTransfer;
use App\Models\Godam;
use App\Models\GodamStock;
use App\Models\Item;
use App\Models\Batch;
use App\Services\FifoStockService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockTransferController extends Controller
{
    /**
     * Display a listing of stock transfers with filters.
     */
    public function index(Request $request)
    {
        $query = StockTransfer::with(['fromGodam', 'toGodam', 'item', 'user']);

        // Filter by Date Range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('transfer_date', [$request->start_date, $request->end_date]);
        }

        // Filter by Godam (either source or destination)
        if ($request->filled('godam_id')) {
            $godamId = $request->godam_id;
            $query->where(function ($q) use ($godamId) {
                $q->where('from_godam_id', $godamId)
                  ->orWhere('to_godam_id', $godamId);
            });
        }

        // Filter by Item
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        $transfers = $query->orderBy('transfer_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate Monthly Summary Stats
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $monthlyTransfers = StockTransfer::whereBetween('transfer_date', [$startOfMonth, $endOfMonth])->get();
        $totalTransfersCount = $monthlyTransfers->count();
        $totalTransfersQty = $monthlyTransfers->sum('quantity');

        $godams = Godam::orderBy('name')->get();
        $items = Item::orderBy('description')->get();

        return view('stock-transfers.index', compact(
            'transfers',
            'godams',
            'items',
            'totalTransfersCount',
            'totalTransfersQty'
        ));
    }

    /**
     * Show the form to create a new transfer.
     */
    public function create()
    {
        $items = Item::orderBy('description')->get();
        $godams = Godam::where('is_active', true)->orderBy('name')->get();
        
        $prefilledItemId = request('item_id');
        $prefilledFromGodamId = request('from_godam_id');

        return view('stock-transfers.create', compact('items', 'godams', 'prefilledItemId', 'prefilledFromGodamId'));
    }

    /**
     * AJAX endpoint to check stock of an item in a specific warehouse or shop floor.
     */
    public function stockCheck(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'godam_id' => 'nullable',
        ]);

        $itemId = $request->item_id;
        $godamId = $request->godam_id;
        $qty = 0;

        if (empty($godamId) || $godamId == '0') {
            // Shop Floor
            $item = Item::find($itemId);
            $qty = $item ? (float)$item->on_hand : 0;
        } else {
            // Godam
            $stock = GodamStock::where('godam_id', $godamId)
                ->where('item_id', $itemId)
                ->first();
            $qty = $stock ? (float)$stock->quantity : 0;
        }

        return response()->json([
            'godam_id' => $godamId ?: null,
            'item_id' => (int)$itemId,
            'available_qty' => $qty
        ]);
    }

    /**
     * Store and process a stock transfer.
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'from_godam_id' => 'nullable',
            'to_godam_id' => 'nullable',
            'quantity' => 'required|numeric|min:0.01',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $itemId = $request->item_id;
        $fromGodamId = $request->from_godam_id ?: null;
        $toGodamId = $request->to_godam_id ?: null;
        $qty = (float)$request->quantity;

        // Validation: Source and destination must be different
        if ($fromGodamId === $toGodamId) {
            return back()->with('error', 'Source and destination locations must be different.')->withInput();
        }

        $item = Item::findOrFail($itemId);

        // Validation: Verify sufficient stock exists in the source location
        if (empty($fromGodamId)) {
            // Source is Shop Floor
            if ((float)$item->on_hand < $qty) {
                return back()->with('error', "Insufficient stock on Shop Floor. Available: {$item->on_hand}, Requested: {$qty}")->withInput();
            }
        } else {
            // Source is a Godam
            $fromStock = GodamStock::where('godam_id', $fromGodamId)
                ->where('item_id', $itemId)
                ->first();

            if (!$fromStock || (float)$fromStock->quantity < $qty) {
                $available = $fromStock ? (float)$fromStock->quantity : 0;
                return back()->with('error', "Insufficient stock in selected Godam. Available: {$available}, Requested: {$qty}")->withInput();
            }
        }

        try {
            DB::transaction(function () use ($item, $itemId, $fromGodamId, $toGodamId, $qty, $request) {
                // ── 1. Deduct Stock from Source ──────────────────────────────────
                if (empty($fromGodamId)) {
                    // Source is Shop Floor: Use FIFO logic to deduct batches
                    $fifoService = app(FifoStockService::class);
                    $fifoService->deductStock($itemId, $qty, null, auth()->id() ?? 1);
                } else {
                    // Source is Godam: Simple decrement
                    $fromStock = GodamStock::where('godam_id', $fromGodamId)
                        ->where('item_id', $itemId)
                        ->first();
                    $fromStock->decrement('quantity', $qty);
                }

                // ── 2. Add Stock to Destination ──────────────────────────────────
                if (empty($toGodamId)) {
                    // Destination is Shop Floor: Add back to FIFO batches
                    $fifoService = app(FifoStockService::class);
                    $batchNo = 'TR-' . date('Ymd') . '-' . mt_rand(1000, 9999);
                    $fifoService->addStock(
                        $itemId,
                        $qty,
                        $item->cost_rate ?? 0,
                        $item->sale_rate ?? 0,
                        $batchNo,
                        null,
                        auth()->id() ?? 1
                    );
                } else {
                    // Destination is Godam: Simple increment / upsert
                    $toStock = GodamStock::firstOrNew([
                        'godam_id' => $toGodamId,
                        'item_id'  => $itemId,
                    ]);
                    $toStock->quantity += $qty;
                    $toStock->last_received_at = now();
                    $toStock->save();
                }

                // ── 3. Create Transfer Log Record ────────────────────────────────
                StockTransfer::create([
                    'from_godam_id' => $fromGodamId,
                    'to_godam_id' => $toGodamId,
                    'item_id' => $itemId,
                    'quantity' => $qty,
                    'transfer_date' => $request->transfer_date,
                    'transferred_by' => auth()->id() ?? 1,
                    'notes' => $request->notes,
                ]);
            });

            return redirect()->route('stock-transfers.index')->with('success', 'Stock transferred successfully.');

        } catch (\Exception $e) {
            \Log::error('Stock Transfer Error: ' . $e->getMessage());
            return back()->with('error', 'Error during transfer: ' . $e->getMessage())->withInput();
        }
    }
}
