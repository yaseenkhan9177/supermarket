<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierReturn extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'return_date' => 'date',
    ];

    /** The supplier this return is for */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /** Line items in this return */
    public function items()
    {
        return $this->hasMany(SupplierReturnItem::class);
    }

    /** The account that receives cash (only for cash_refund resolution) */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /** The user who processed this return */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
