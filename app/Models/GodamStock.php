<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GodamStock extends Model
{
    use HasFactory;

    protected $table = 'godam_stock';

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:2',
        'last_received_at' => 'datetime',
    ];

    /**
     * Relationship to the Godam.
     */
    public function godam(): BelongsTo
    {
        return $this->belongsTo(Godam::class, 'godam_id');
    }

    /**
     * Relationship to the Item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
