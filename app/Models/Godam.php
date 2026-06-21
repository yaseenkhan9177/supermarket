<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Godam extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationship to the stock entries in this godam.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(GodamStock::class, 'godam_id');
    }

    /**
     * Relationship to the distinct items stored in this godam.
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'godam_stock')
            ->withPivot('quantity', 'last_received_at')
            ->withTimestamps();
    }
}
