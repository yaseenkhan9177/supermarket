<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxChargeType extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_custom' => 'boolean',
    ];

    public function purchaseCharges()
    {
        return $this->hasMany(PurchaseCharge::class);
    }
}
