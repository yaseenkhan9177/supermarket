<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'status',
        'order_date',
        'expected_date',
        'note',
        'created_by',
    ];

    protected $casts = [
        'order_date'    => 'date',
        'expected_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($po) {
            if (empty($po->order_date)) {
                $po->order_date = now()->toDateString();
            }
        });
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function expenses()
    {
        return $this->hasMany(PurchaseOrderExpense::class);
    }

    public function receipts()
    {
        return $this->hasMany(PurchaseOrderReceipt::class)->orderBy('created_at', 'desc');
    }

    /** Total value of items ordered */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->items()->sum('line_total');
    }

    /** Total expenses added so far */
    public function getTotalExpensesAttribute(): float
    {
        return (float) $this->expenses()->sum('amount');
    }

    /** Total PO value (Items Subtotal + Expenses) */
    public function getGrandTotalAttribute(): float
    {
        return $this->subtotal + $this->total_expenses;
    }

    /** Overall receiving completion percentage (0-100%) */
    public function getReceivingProgressAttribute(): int
    {
        $ordered = $this->items()->sum('quantity_ordered');
        if ($ordered <= 0) return 0;
        $received = $this->items()->sum('quantity_received');
        return min(100, (int) round(($received / $ordered) * 100));
    }
}
