<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Display a list of low stock items.
     * Criteria: on_hand < min_stock_level AND min_stock_level > 0.
     * Eager loads the preferredSupplier relationship.
     */
    public function lowStock()
    {
        $items = Item::whereNotNull('min_stock_level')
            ->where('min_stock_level', '>', 0)
            ->whereColumn('on_hand', '<', 'min_stock_level')
            ->select('items.*')
            ->selectRaw('(min_stock_level - on_hand) as shortage')
            ->selectSub(
                \App\Models\PurchaseItem::join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                    ->whereColumn('purchase_items.item_id', 'items.id')
                    ->selectRaw('max(purchases.invoice_date)'),
                'last_purchase_date'
            )
            ->with(['preferredSupplier' => function($query) {
                $query->select('id', 'name', 'phone');
            }])
            ->orderByRaw('(min_stock_level - on_hand) DESC')
            ->get();

        $totalLowStockCount = $items->count();
        $totalConfiguredCount = Item::whereNotNull('min_stock_level')
            ->where('min_stock_level', '>', 0)
            ->count();

        return view('stock.low_stock', compact('items', 'totalLowStockCount', 'totalConfiguredCount'));
    }
}
