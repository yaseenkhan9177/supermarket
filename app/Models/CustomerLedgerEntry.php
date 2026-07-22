<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerLedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'type',
        'reason_category',
        'amount',
        'balance_after',
        'method',
        'note',
        'created_by',
        'reversed_entry_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reversedEntry()
    {
        return $this->belongsTo(CustomerLedgerEntry::class, 'reversed_entry_id');
    }

    public function reversal()
    {
        return $this->hasOne(CustomerLedgerEntry::class, 'reversed_entry_id');
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class, 'ledger_entry_id');
    }
}
