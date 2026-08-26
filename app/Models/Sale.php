<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no',
        'sale_date',
        'customer_id',
        'customer_name',
        'user_id',
        'payment_mode',
        'subtotal',
        'discount_total',
        'tax_total',
        'tax_rate',
        'additional_charges_total',
        'grand_total',
        'status',
        'paid_amount', // Added
        'change_amount', // Added
        'return_adjustment',
        'wallet_id',
    ];

    protected $casts = [
        'sale_date'                => 'datetime',
        'subtotal'                 => 'decimal:2',
        'discount_total'           => 'decimal:2',
        'tax_total'                => 'decimal:2',
        'tax_rate'                 => 'decimal:2',
        'additional_charges_total' => 'decimal:2',
        'grand_total'              => 'decimal:2',
        'paid_amount'              => 'decimal:2',
        'change_amount'            => 'decimal:2',
        'return_adjustment'        => 'decimal:2',
    ];

    public function additionalCharges()
    {
        return $this->hasMany(SaleAdditionalCharge::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function refundItems()
    {
        return $this->hasMany(RefundItem::class, 'original_bill_id');
    }

    public function versions()
    {
        return $this->hasMany(SaleVersion::class)->orderBy('version_number', 'desc');
    }

    public function latestVersion()
    {
        return $this->hasOne(SaleVersion::class)->latestOfMany('version_number');
    }

    /**
     * Generate structured snapshot array of this sale and its line items.
     */
    public function toSnapshotArray(): array
    {
        $this->loadMissing(['items.item', 'customer', 'user', 'wallet']);

        return [
            'id'             => $this->id,
            'invoice_no'     => $this->invoice_no,
            'sale_date'      => $this->sale_date?->toDateTimeString(),
            'customer_id'    => $this->customer_id,
            'customer_name'  => $this->customer?->name ?? $this->customer_name ?? 'Walk-in Customer',
            'user_id'        => $this->user_id,
            'user_name'      => $this->user?->name ?? 'Staff',
            'payment_mode'   => $this->payment_mode,
            'wallet_id'      => $this->wallet_id,
            'wallet_name'    => $this->wallet?->name,
            'subtotal'          => (float) $this->subtotal,
            'discount_total'    => (float) ($this->discount_total ?? 0),
            'tax_total'         => (float) ($this->tax_total ?? 0),
            'tax_rate'          => (float) ($this->tax_rate ?? 0),
            'return_adjustment' => (float) ($this->return_adjustment ?? 0),
            'grand_total'       => (float) $this->grand_total,
            'paid_amount'    => (float) ($this->paid_amount ?? 0),
            'change_amount'  => (float) ($this->change_amount ?? 0),
            'status'         => $this->status,
            'items'          => $this->items->map(function ($item) {
                return [
                    'id'        => $item->id,
                    'item_id'   => $item->item_id,
                    'item_name' => $item->item_name,
                    'item_code' => $item->item?->code,
                    'item_type' => $item->item?->item_type ?? 'Inventory',
                    'batch_id'  => $item->batch_id,
                    'qty'       => (float) $item->qty,
                    'rate'      => (float) $item->rate,
                    'total'     => (float) $item->total,
                ];
            })->values()->toArray(),
        ];
    }
}
