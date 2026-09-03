<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'credit_limit',
        'balance',
        'store_credit',
        'status',
        'written_off_at',
        'written_off_by',
    ];

    public function debitSales()
    {
        return $this->hasMany(Sale::class)->where('payment_mode', 'Debit');
    }

    public function cashSales()
    {
        return $this->hasMany(Sale::class)->where('payment_mode', '!=', 'Debit');
    }

    // All sales for this customer (POS, Cash, Debit)
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    // Returns / Refunds processed against this customer
    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function ledgerEntries()
    {
        return $this->hasMany(CustomerLedgerEntry::class);
    }

    public function writtenOffBy()
    {
        return $this->belongsTo(User::class, 'written_off_by');
    }
}
