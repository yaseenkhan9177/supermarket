<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePaymentSplit extends Model
{
    use HasFactory;

    protected $guarded = [];

    /** The purchase bill this split belongs to */
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    /** The account debited for this portion of the payment */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
