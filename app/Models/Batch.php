<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'batch_no',
        'quantity_available',
        'sale_price',
        'cost_price',
        'received_at',
        'expires_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'expires_at' => 'datetime',
        'quantity_available' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
