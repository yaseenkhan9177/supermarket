<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'item_id',
        'quantity_ordered',
        'quantity_received',
        'unit_cost',
        'line_total',
    ];

    protected $casts = [
        'quantity_ordered'  => 'decimal:2',
        'quantity_received' => 'decimal:2',
        'unit_cost'         => 'decimal:2',
        'line_total'        => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function getPendingQuantityAttribute(): float
    {
        return max(0, $this->quantity_ordered - $this->quantity_received);
    }
}
