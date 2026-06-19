<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Employee extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'address',
        'city',
        'designation',
        'employee_code',
        'commission_rate',
        'avatar_path',
        'is_active',
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'commission_rate' => 'decimal:2',
    ];

    public function userPermissions()
    {
        return $this->morphOne(UserPermission::class, 'model');
    }

    public function hasPermission($permission)
    {
        // New logic using UserPermission relationship
        if (str_contains($permission, '.')) {
            [$module, $action] = explode('.', $permission);
            if ($this->permissions && isset($this->permissions->$module)) {
                $modulePerms = $this->permissions->$module;
                return $modulePerms[$action] ?? false;
            }
        }
        return false;
    }

    public function roleTemplate()
    {
        return $this->belongsTo(Role::class, 'role', 'name');
    }
}
