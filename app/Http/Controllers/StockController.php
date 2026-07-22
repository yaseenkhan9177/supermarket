<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Display a list of low stock items.
     *
     * Criteria (union of two cases):
     *   1. Items with min_stock_level set and on_hand < min_stock_level (LOW STOCK)
     *   2. Items with on_hand = 0 regardless of min_stock_level (OUT OF STOCK)
     *
     * Status badges:
     *   - on_hand = 0           → OUT OF STOCK (red)
     *   - on_hand < min_level   → LOW STOCK    (orange)
     */
    public function lowStock()
    {
        // Build base query — two inclusion criteria merged via orWhere
        $query = Item::query()
            ->where(function ($q) {
                // Case 1: below configured minimum threshold
                $q->where(function ($inner) {
                    $inner->whereNotNull('min_stock_level')
                          ->where('min_stock_level', '>', 0)
                          ->whereColumn('on_hand', '<', 'min_stock_level');
                })
                // Case 2: completely out of stock (always flag)
                ->orWhere('on_hand', '<=', 0);
            })
            ->select('items.*')
            ->selectSub(
                \App\Models\PurchaseItem::join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                    ->whereColumn('purchase_items.item_id', 'items.id')
                    ->selectRaw('max(purchases.invoice_date)'),
                'last_purchase_date'
            )
            ->with(['preferredSupplier' => function ($query) {
                $query->select('id', 'name', 'phone');
            }])
            ->orderByRaw('on_hand ASC')          // zero-stock items first
            ->orderByRaw('(COALESCE(min_stock_level,0) - on_hand) DESC');

        $items = $query->get();

        // Count breakdowns
        $outOfStockCount   = $items->where('on_hand', '<=', 0)->count();
        $lowStockOnlyCount = $items->where('on_hand', '>', 0)->count();
        $totalLowStockCount = $items->count();

        $totalConfiguredCount = Item::whereNotNull('min_stock_level')
            ->where('min_stock_level', '>', 0)
            ->count();

        return view('stock.low_stock', compact(
            'items',
            'outOfStockCount',
            'lowStockOnlyCount',
            'totalLowStockCount',
            'totalConfiguredCount'
        ));
    }
}
