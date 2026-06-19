<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuperAdminPin extends Model
{
    use HasFactory;

    protected $table = 'super_admin_pins';

    protected $fillable = [
        'pin',
        'used_at',
        'used_by_email',
    ];

    protected $casts = [
        'used_at' => 'datetime',
    ];
}
