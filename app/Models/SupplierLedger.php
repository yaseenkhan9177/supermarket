<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierLedger extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relationship: A ledger entry belongs to a supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
