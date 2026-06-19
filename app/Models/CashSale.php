<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'sale_date',
        'customer_id',
        'customer_name',
        'salesman_id',
        'subtotal',
        'discount_total',
        'tax_total',
        'grand_total',
        'deposit_account',
        'cash_received',
        'change_returned'
    ];

    public function items()
    {
        return $this->hasMany(CashSaleItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id');
    }
}
