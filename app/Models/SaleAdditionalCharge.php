<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleAdditionalCharge extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'value' => 'float',
        'amount' => 'float',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function additionalCharge()
    {
        return $this->belongsTo(AdditionalCharge::class, 'additional_charge_id');
    }
}
