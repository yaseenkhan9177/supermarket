<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierReturnItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    /** The parent return record */
    public function supplierReturn()
    {
        return $this->belongsTo(SupplierReturn::class);
    }

    /** The item being returned */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /** The specific FIFO batch being returned */
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
