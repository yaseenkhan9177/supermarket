<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'outlook_integration' => 'boolean',
    ];

    public function activeWallet()
    {
        return $this->belongsTo(Wallet::class, 'active_wallet_id');
    }
}
