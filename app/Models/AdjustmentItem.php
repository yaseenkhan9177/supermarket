<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdjustmentItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function adjustment()
    {
        return $this->belongsTo(Adjustment::class);
    }

    public function product()
    {
        return $this->belongsTo(Item::class, 'product_id');
    }
}
