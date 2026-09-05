<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerBalanceConversion extends Model
{
    use HasFactory;

    protected $connection = 'mysql'; // Central database

    protected $table = 'customer_balance_conversions';

    protected $fillable = [
        'tenant_id',
        'super_admin_id',
        'super_admin_name',
        'customers_processed',
        'positive_converted',
        'negative_converted',
        'zero_unchanged',
        'total_balance_before',
        'total_balance_after',
        'converted_at',
    ];

    protected $casts = [
        'converted_at' => 'datetime',
        'customers_processed' => 'integer',
        'positive_converted' => 'integer',
        'negative_converted' => 'integer',
        'zero_unchanged' => 'integer',
        'total_balance_before' => 'decimal:2',
        'total_balance_after' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function superAdmin()
    {
        return $this->belongsTo(SuperAdmin::class, 'super_admin_id');
    }
}
