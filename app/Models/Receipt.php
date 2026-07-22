<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Receipt extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function ledgerEntry()
    {
        return $this->belongsTo(CustomerLedgerEntry::class, 'ledger_entry_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function allocations()
    {
        return $this->hasMany(ReceiptAllocation::class);
    }

    /**
     * Generate sequential receipt number in a concurrency-safe manner (RCPT-000123).
     * Must be called inside a DB transaction.
     */
    public static function generateNextReceiptNumber(): string
    {
        $lastReceipt = static::whereNotNull('receipt_number')
            ->lockForUpdate()
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($lastReceipt && preg_match('/RCPT-(\d+)/i', $lastReceipt->receipt_number, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        }

        return 'RCPT-' . str_pad($nextSeq, 6, '0', STR_PAD_LEFT);
    }
}
