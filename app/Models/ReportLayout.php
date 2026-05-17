<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportLayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_type',
        'layout_name',
        'visible_columns',
        'is_default',
        'user_id',
    ];

    protected $casts = [
        'visible_columns' => 'array',
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
