<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasFactory, HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'store_name',
        'address',
        'owner_name',
        'owner_email',
        'owner_phone',
        'status',
        'database_name',
        'subscription_plan',
        'valid_until',
        'paid_until',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejection_reason',
        'provisioning_error',
        'data',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'paid_until' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
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
            'address',
            'owner_name',
            'owner_email',
            'owner_phone',
            'status',
            'database_name',
            'subscription_plan',
            'valid_until',
            'paid_until',
            'approved_at',
            'approved_by',
            'rejected_at',
            'rejection_reason',
            'provisioning_error',
        ];
    }

    /**
     * Get the owner user associated with the tenant centrally.
     */
    public function user()
    {
        return $this->hasOne(User::class, 'tenant_id')->where('role', 'owner');
    }

    /**
     * Override internal getter to use our custom database_name column
     * for the physical database name, instead of the default prefix+uuid.
     */
    public function getInternal(string $key)
    {
        if ($key === 'db_name') {
            return $this->getAttribute('database_name');
        }

        return parent::getInternal($key);
    }

    /**
     * Generate a safe, sanitized database name from the store name.
     * Example: "Al-Madina Mart" -> "tenant_al_madina_mart_a1b2c3"
     *
     * @param string $storeName
     * @param string|null $tenantId
     * @return string
     */
    public static function generateDatabaseName(string $storeName, ?string $tenantId = null): string
    {
        $cleanName = Str::slug($storeName, '_');
        if (empty($cleanName)) {
            $cleanName = 'store';
        }

        // Limit store slug to 25 chars for clean and valid MySQL naming
        $cleanName = substr($cleanName, 0, 25);

        // Append 6-character unique suffix from tenant ID (or random string)
        $suffix = $tenantId ? substr(str_replace('-', '', $tenantId), 0, 6) : strtolower(Str::random(6));

        return 'tenant_' . trim($cleanName, '_') . '_' . $suffix;
    }
}
