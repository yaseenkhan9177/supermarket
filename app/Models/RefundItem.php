<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function refund()
    {
        return $this->belongsTo(Refund::class);
    }

    /**
     * The sellable item (product) being returned.
     * RefundItem stores the product as product_id referencing items.id.
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'product_id');
    }
}
