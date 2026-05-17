<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'hide_sale_price' => 'boolean',
        'parse_bar' => 'boolean',
        'open_price' => 'boolean',
        'is_container' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function salesman(): BelongsTo
    {
        return $this->belongsTo(Salesman::class);
    }

    public function itemClass(): BelongsTo
    {
        return $this->belongsTo(ItemClass::class, 'class_id');
    }

    public function account(): BelongsTo
    {
        // This handles sales_account_id, cogs_account_id, asset_account_id
        // Wait, the resource used ->relationship('account', 'name') for all 3.
        // This implies 3 separate relationships or they just copy-pasted.
        // Usually: ->relationship('salesAccount', 'name')
        // Using "account" for all 3 implies "account" is the method name, which doesn't make sense for 3 diff keys.
        // Filament's `relationship()` method 1st arg is the relationship METHOD name.
        // So `Select::make('sales_account_id')->relationship('account', 'name')` tries to use `account()` method.
        // But `sales_account_id` column needs to match foreign key if not specified? 
        // Actually, belongsTo defaults to {name}_id. So `account()` -> `account_id`.
        // But we have `sales_account_id`.
        // So we need specific relationships: salesAccount, cogsAccount, assetAccount.
        // The user code seems to rely on generic `account` relationship which is weird if they want to bind to diff columns.
        // I will Create specific relationships and update the Resource code to match them.
        return $this->belongsTo(Account::class);
    }

    public function salesAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'sales_account_id');
    }

    public function cogsAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'cogs_account_id');
    }

    public function assetAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'asset_account_id');
    }

    public function subItems(): HasMany
    {
        return $this->hasMany(ItemSubItem::class, 'parent_item_id');
    }

    // ✅ FIFO Relationship
    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    // ✅ FIFO Active Scope
    public function activeBatches()
    {
        return $this->batches()->where('quantity_available', '>', 0)->orderBy('received_at');
    }

    // ✅ Step 4 Logic: Check if we have enough stock
    public function hasSufficientStock($requestedQty)
    {
        // Service items (like 'Repair Fee') usually don't track stock
        if ($this->item_type === 'Service') {
            return true;
        }
        return $this->batches()->sum('quantity_available') >= $requestedQty;
    }
}
