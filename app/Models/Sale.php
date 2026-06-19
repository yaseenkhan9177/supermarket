<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'sale_date',
        'customer_id',
        'customer_name',
        'user_id',
        'payment_mode',
        'subtotal',
        'discount_total',
        'tax_total',
        'grand_total',
        'status',
        'paid_amount', // Added
        'change_amount', // Added
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
