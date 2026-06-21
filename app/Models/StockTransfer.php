<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransfer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:2',
        'transfer_date' => 'date',
    ];

    /**
     * Source Godam (null = Shop Floor).
     */
    public function fromGodam(): BelongsTo
    {
        return $this->belongsTo(Godam::class, 'from_godam_id');
    }

    /**
     * Destination Godam (null = Shop Floor).
     */
    public function toGodam(): BelongsTo
    {
        return $this->belongsTo(Godam::class, 'to_godam_id');
    }

    /**
     * Item being transferred.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * User who processed the transfer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
