<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxSettingsHistory extends Model
{
    use HasFactory;

    protected $table = 'tax_settings_history';

    protected $fillable = [
        'user_id',
        'user_name',
        'previous_tax_enabled',
        'new_tax_enabled',
        'previous_tax_rate',
        'new_tax_rate',
        'ip_address',
    ];

    protected $casts = [
        'previous_tax_enabled' => 'boolean',
        'new_tax_enabled'      => 'boolean',
        'previous_tax_rate'    => 'decimal:2',
        'new_tax_rate'         => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
