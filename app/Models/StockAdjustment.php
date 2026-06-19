<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'date',
        'type',
        'reason',
        'user_id',
        'total_items'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Boot function to auto-generate reference number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($adjustment) {
            if (empty($adjustment->reference_no)) {
                $adjustment->reference_no = self::generateReferenceNo();
            }
        });
    }

    /**
     * Generate unique reference number: ADJ-YYYYMMDD-XX
     */
    public static function generateReferenceNo()
    {
        $date = date('Ymd');
        $prefix = "ADJ-{$date}-";

        // Find the last adjustment for today
        $lastAdjustment = self::where('reference_no', 'LIKE', "{$prefix}%")
            ->orderBy('reference_no', 'DESC')
            ->first();

        if ($lastAdjustment) {
            // Extract the sequence number and increment
            $lastSequence = (int) substr($lastAdjustment->reference_no, -2);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        return $prefix . str_pad($newSequence, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Relationship: User who performed the adjustment
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Relationship: Adjustment items
     */
    public function items()
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }
}
