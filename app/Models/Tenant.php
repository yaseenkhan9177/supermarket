<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasFactory, HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'store_name',
        'owner_name',
        'owner_email',
        'owner_phone',
        'status',
        'database_name',
        'subscription_plan',
        'valid_until',
        'data',
    ];

    protected $casts = [
        'valid_until' => 'date',
    ];

    /**
     * Define the columns on the tenants table that are custom
     * and should not be serialized inside the JSON 'data' field.
     *
     * @return array
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'store_name',
            'owner_name',
            'owner_email',
            'owner_phone',
            'status',
            'database_name',
            'subscription_plan',
            'valid_until',
        ];
    }

    /**
     * Get the owner user associated with the tenant centrally.
     */
    public function user()
    {
        return $this->hasOne(User::class, 'tenant_id')->where('role', 'owner');
    }
}
