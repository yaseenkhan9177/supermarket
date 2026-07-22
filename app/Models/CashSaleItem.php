<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Item;

class CashSaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_sale_id',
        'product_id',
        'item_name',
        'quantity',
        'rate',
        'total'
    ];

    public function sale()
    {
        return $this->belongsTo(CashSale::class, 'cash_sale_id');
    }

    // Alias used in customer profile KPI queries
    public function cashSale()
    {
        return $this->belongsTo(CashSale::class, 'cash_sale_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'product_id');
    }
}
