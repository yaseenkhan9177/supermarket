<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Helper to see if we owe them money
    // Positive (+) means we owe them money (Payable)
    // Negative (-) means they owe us (Advance)
    public function getBalanceStatusAttribute()
    {
        if ($this->balance > 0) return 'Payable';
        if ($this->balance < 0) return 'Advance';
        return 'Settled';
    }
}
