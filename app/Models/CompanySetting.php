<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Default attribute values — mirrors the migration defaults so that
     * firstOrNew() on a brand-new record won't leave NOT NULL columns empty
     * (Laravel's ConvertEmptyStringsToNull middleware turns blank form
     * fields into null, which would violate the DB constraint on INSERT).
     */
    protected $attributes = [
        'business_name'        => 'NEW BLANK COMPANY',
        'currency_symbol'      => 'Rs.',
        'currency_code'        => 'PKR',
        'printer_default'      => 'Microsoft Print to PDF',
        'pos_printer_name'     => 'Printer',
        'pos_drawer_name'      => 'Drawer',
        'pos_display_name'     => 'Display',
        'comm_port_drawer'     => 0,
        'comm_port_display'    => 0,
        'barcode_labels_per_row' => 0,
        'barcode_labels_per_col' => 0,
        'receipt_width'        => 200,
        'number_of_counters'   => 1,
        'outlook_integration'  => false,
        'tax_enabled'          => false,
        'tax_rate'             => 0.00,
        'tax_configured_at'    => null,
        'tax_configured_by'    => null,
    ];

    protected $casts = [
        'outlook_integration' => 'boolean',
        'tax_enabled'         => 'boolean',
        'tax_rate'            => 'decimal:2',
        'tax_configured_at'   => 'datetime',
    ];

    /**
     * Check whether Store Admin has ever configured tax for this tenant.
     */
    public function isTaxConfigured(): bool
    {
        return !is_null($this->tax_configured_at);
    }

    public function taxConfiguredUser()
    {
        return $this->belongsTo(User::class, 'tax_configured_by');
    }

    public function activeWallet()
    {
        return $this->belongsTo(Wallet::class, 'active_wallet_id');
    }
}
