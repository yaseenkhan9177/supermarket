<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'company_name', 'phone', 'address', 'opening_balance', 'current_balance', 'category_id', 'email', 'city', 'tax_id', 'account_code', 'credit_limit', 'credit_days', 'contact_person'];

    // ─── Relationships ────────────────────────────────────────────────────────
    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    /** All purchases made from this supplier */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /** Ledger entries (purchases, payments, return credits) */
    public function ledger()
    {
        return $this->hasMany(SupplierLedger::class)->orderBy('created_at', 'asc');
    }

    /** Unified ledger entries */
    public function ledgerEntries()
    {
        return $this->hasMany(SupplierLedgerEntry::class)->orderBy('created_at', 'desc');
    }

    /** Stock returns to this supplier */
    public function returns()
    {
        return $this->hasMany(SupplierReturn::class);
    }

    /** Supplier Category */
    public function category()
    {
        return $this->belongsTo(SupplierCategory::class);
    }

    // ─── Computed Attributes ──────────────────────────────────────────────────

    /**
     * Balance Status:
     *   Positive (+) = We owe them money (Payable / Debt)
     *   Negative (-)  = They owe us   (Credit / Advance)
     *   Zero          = Settled
     */
    public function getBalanceStatusAttribute(): string
    {
        if ($this->current_balance > 0) return 'Payable';
        if ($this->current_balance < 0) return 'Credit';
        return 'Settled';
    }

    /**
     * The absolute amount of return credit we hold against this supplier.
     * Only meaningful when current_balance < 0.
     */
    public function getReturnCreditAttribute(): float
    {
        return $this->current_balance < 0 ? abs($this->current_balance) : 0.0;
    }

    /**
     * True when we have a credit balance (supplier owes us goods/money back).
     */
    public function getHasCreditAttribute(): bool
    {
        return $this->current_balance < 0;
    }
}
