<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function store()
    {
        return $this->hasOne(Store::class);
    }

    public function userPermissions()
    {
        return $this->morphOne(UserPermission::class, 'model');
    }

    public function hasPermission($permission)
    {
        // For now, if the user role is 'owner' or 'admin', grant all permissions
        if (in_array($this->role, ['owner', 'admin', 'Store Admin'])) {
            return true;
        }

        // Example usage: hasPermission('sales_cash.view')
        if (str_contains($permission, '.')) {
            [$module, $action] = explode('.', $permission);
            if ($this->permissions && isset($this->permissions->$module)) {
                $modulePerms = $this->permissions->$module;
                // It's cast to array in model, but accessed as array or object? 
                // If cast to 'array', it's an array.
                return $modulePerms[$action] ?? false;
            }
        }

        return false;
    }
}
