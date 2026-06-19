<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $guarded = [];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Payment splits — one row per payment source (Cash, Bank, EasyPaisa, etc.) */
    public function paymentSplits()
    {
        return $this->hasMany(PurchasePaymentSplit::class);
    }

    /** Import / Clearing Tax & Charge line items */
    public function charges()
    {
        return $this->hasMany(PurchaseCharge::class);
    }

    // ─── Computed Attributes ──────────────────────────────────────────────────

    /**
     * Total amount actually paid via split payments (excludes credit applied).
     */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->paymentSplits()->sum('amount');
    }
}
