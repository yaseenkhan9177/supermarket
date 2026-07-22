<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'receipt_no',
        'allocated_expense_amount',
        'supplier_total_amount',
        'landed_total_amount',
        'note',
        'received_by',
    ];

    protected $casts = [
        'allocated_expense_amount' => 'decimal:2',
        'supplier_total_amount'    => 'decimal:2',
        'landed_total_amount'      => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderReceiptItem::class, 'receipt_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
