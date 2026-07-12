<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    use HasFactory;

    /**
     * Force central database connection.
     *
     * @var string
     */
    protected $connection = 'mysql';

    protected $guarded = [];

    protected $casts = [
        'sales_cash' => 'array',
        'sales_debt' => 'array',
        'sales_return_cash' => 'array',
        'sales_return_crdt' => 'array',
        'inventory_transfer' => 'array',
        'accounts_receipts' => 'array',
        'accounts_payments' => 'array',
        'items_stock' => 'array',
        'can_change_discount' => 'boolean',
        'can_close_session' => 'boolean',
        'allow_credit_override' => 'boolean',
        'view_all_counters' => 'boolean',
        'sys_add_users' => 'boolean',
        'sys_restore_data' => 'boolean',
        'sys_view_reports' => 'boolean',
        'sys_reconcile_banks' => 'boolean',
    ];

    public function model()
    {
        return $this->morphTo();
    }
}
