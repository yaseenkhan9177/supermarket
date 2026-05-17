<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'quantity',
        'reason',
        'refund_id',
        'user_id',
        'logged_at'
    ];

    protected $casts = [
        'logged_at' => 'datetime',
        'quantity' => 'decimal:2'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function refund()
    {
        return $this->belongsTo(Refund::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
