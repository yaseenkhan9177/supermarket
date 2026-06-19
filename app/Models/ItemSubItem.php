<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemSubItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function parentItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'parent_item_id');
    }

    public function childItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'child_item_id');
    }
}
