<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * Force the central database connection.
     *
     * @var string
     */
    protected $connection = 'mysql';

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
        'is_active',
        'avatar',
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
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    /**
     * The guard name to use when storing/retrieving Spatie roles & permissions.
     * Must match the guard name used in config/auth.php for the User model.
     */
    protected $guard_name = 'web';

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function store()
    {
        return $this->hasOne(Store::class);
    }

    public function userPermissions()
    {
        return $this->morphOne(UserPermission::class, 'model');
    }

    // -------------------------------------------------------------------------
    // Legacy Permission Helper (custom JSON-based permission system)
    // NOTE: Renamed from hasPermission() to avoid conflict with Spatie's
    //       HasRoles trait which also injects a hasPermission() method.
    //       If any controller/view was calling Auth::user()->hasPermission()
    //       for the web-guard User model, update those calls to hasLegacyPermission().
    //       The employee.blade.php still calls auth('employee')->user()->hasPermission()
    //       which hits the Employee model — that is unaffected by this rename.
    // -------------------------------------------------------------------------
    public function hasLegacyPermission($permission)
    {
        // For now, if the user role is 'owner' or 'admin', grant all permissions
        if (in_array($this->role, ['owner', 'admin', 'Store Admin', 'Owner'])) {
            return true;
        }

        // Check Spatie role as well
        if ($this->hasRole('owner')) {
            return true;
        }

        // Example usage: hasLegacyPermission('sales_cash.view')
        if (str_contains($permission, '.')) {
            [$module, $action] = explode('.', $permission);
            if ($this->permissions && isset($this->permissions->$module)) {
                $modulePerms = $this->permissions->$module;
                return $modulePerms[$action] ?? false;
            }
        }

        return false;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Get the user's primary Spatie role name for display.
     */
    public function getRoleNameAttribute(): string
    {
        $spatieRole = $this->roles->first();
        if ($spatieRole) {
            return ucfirst($spatieRole->name);
        }
        // Fallback to legacy string column
        return $this->role ? ucfirst($this->role) : 'No Role';
    }

    /**
     * Get the user's Spatie role slug (lowercase) for badge styling.
     */
    public function getRoleSlugAttribute(): string
    {
        $spatieRole = $this->roles->first();
        if ($spatieRole) {
            return strtolower($spatieRole->name);
        }
        return strtolower($this->role ?? 'user');
    }
}
