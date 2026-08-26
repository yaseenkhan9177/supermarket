<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'version_number',
        'action_type',
        'user_id',
        'user_name',
        'reason',
        'old_values',
        'new_values',
        'changes_summary',
        'ip_address',
    ];

    protected $casts = [
        'version_number'  => 'integer',
        'old_values'      => 'array',
        'new_values'      => 'array',
        'changes_summary' => 'array',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
