<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Tenant extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_name',
        'owner_name',
        'owner_email',
        'owner_phone',
        'status',
        'database_name',
        'subscription_plan',
        'valid_until',
    ];

    protected $casts = [
        'valid_until' => 'date',
    ];

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }
}
