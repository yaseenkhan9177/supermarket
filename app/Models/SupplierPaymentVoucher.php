<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierPaymentVoucher extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function ledgerEntry()
    {
        return $this->belongsTo(SupplierLedgerEntry::class, 'ledger_entry_id');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Generate sequential voucher number in a concurrency-safe manner (VCHR-000123).
     * Must be called inside a DB transaction.
     */
    public static function generateNextVoucherNumber(): string
    {
        $lastVoucher = static::whereNotNull('voucher_number')
            ->lockForUpdate()
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($lastVoucher && preg_match('/VCHR-(\d+)/i', $lastVoucher->voucher_number, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        }

        return 'VCHR-' . str_pad($nextSeq, 6, '0', STR_PAD_LEFT);
    }
}
