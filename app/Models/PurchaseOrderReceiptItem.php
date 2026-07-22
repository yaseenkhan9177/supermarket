<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_id',
        'po_item_id',
        'item_id',
        'batch_id',
        'quantity_received',
        'unit_supplier_cost',
        'unit_landed_cost',
        'sale_price_set',
    ];

    protected $casts = [
        'quantity_received'  => 'decimal:2',
        'unit_supplier_cost' => 'decimal:2',
        'unit_landed_cost'   => 'decimal:2',
        'sale_price_set'     => 'decimal:2',
    ];

    public function receipt()
    {
        return $this->belongsTo(PurchaseOrderReceipt::class, 'receipt_id');
    }

    public function poItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'po_item_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
}
