<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'system_quantity',
        'physical_quantity',
        'difference'
    ];

    /**
     * Relationship: Parent adjustment
     */
    public function adjustment()
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    /**
     * Relationship: Product being adjusted
     */
    public function product()
    {
        return $this->belongsTo(\App\Models\Item::class, 'product_id');
    }
}
