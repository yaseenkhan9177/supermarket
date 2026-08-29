<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebitSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'customer_id',
        'salesman_id',
        'invoice_date',
        'due_date',
        'pricing_type',
        'gross_total',
        'discount',
        'net_total',
        'paid_amount',
        'adjusted_amount',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesman()
    {
        return $this->belongsTo(Employee::class, 'salesman_id');
    }

    public function items()
    {
        return $this->hasMany(DebitSaleItem::class);
    }

    public function allocations()
    {
        return $this->hasMany(ReceiptAllocation::class);
    }

    public function refundItems()
    {
        return $this->hasMany(RefundItem::class, 'original_bill_id')->where('sale_source', 'debit_sale');
    }
}
