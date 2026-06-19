<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebitSaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'debit_sale_id',
        'product_id',
        'item_name',
        'quantity',
        'rate',
        'total',
        'discount_percent',
        'discount_amount',
        'net_amount',
        'department',
    ];

    public function sale()
    {
        return $this->belongsTo(DebitSale::class);
    }
}
