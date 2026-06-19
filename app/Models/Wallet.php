<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    /**
     * Helper to adjust the balance.
     */
    public function adjustBalance(float $amount)
    {
        if ($this->type === 'bank' && $this->bankAccount) {
            $this->bankAccount->increment('current_balance', $amount);
        } else {
            $this->increment('balance', $amount);
        }
    }
}
