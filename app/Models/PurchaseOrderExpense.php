<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'expense_type',
        'description',
        'amount',
        'added_by',
        'payment_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
