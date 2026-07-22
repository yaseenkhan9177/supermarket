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
        return $this->hasMany(DebitSale::class);
    }

    public function cashSales()
    {
        return $this->hasMany(CashSale::class);
    }

    // POS Sales (Sale model from sales controller)
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
