<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierLedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'type',
        'amount',
        'balance_after',
        'method',
        'note',
        'reversed_entry_id',
        'created_by',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reversedEntry()
    {
        return $this->belongsTo(SupplierLedgerEntry::class, 'reversed_entry_id');
    }

    public function reversal()
    {
        return $this->hasOne(SupplierLedgerEntry::class, 'reversed_entry_id');
    }

    public function voucher()
    {
        return $this->hasOne(SupplierPaymentVoucher::class, 'ledger_entry_id');
    }
}
