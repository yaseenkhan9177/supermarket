<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', // 'folder' or 'report'
        'icon',
        'parent_id',
        'route_name',
        'description',
        'sort_order',
        'is_hidden_global',
        'is_owner_only',
        'requires_permission',
    ];

    public function children()
    {
        return $this->hasMany(Report::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function parent()
    {
        return $this->belongsTo(Report::class, 'parent_id');
    }
}
