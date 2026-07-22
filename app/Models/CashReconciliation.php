<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashReconciliation extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'opening_cash',
        'expected_cash',
        'counted_cash',
        'difference',
        'note',
        'closed_by',
    ];

    protected $casts = [
        'date'          => 'date',
        'opening_cash'  => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'counted_cash'  => 'decimal:2',
        'difference'    => 'decimal:2',
    ];

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
