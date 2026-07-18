<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'item_id',
        'batch_id',   // populated by FIFO deduction; null for Service items or legacy rows
        'item_name',
        'qty',
        'rate',
        'total',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * The FIFO batch this line was drawn from.
     * Returns null for Service items or rows created before the FIFO migration.
     */
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
