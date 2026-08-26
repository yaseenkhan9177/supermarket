<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Seed default categories if none exist.
     */
    public static function seedDefaultsIfEmpty(): void
    {
        if (static::count() === 0) {
            $defaults = [
                ['name' => 'Rent', 'code' => 'RENT', 'description' => 'Store & warehouse rent expenses'],
                ['name' => 'Electricity', 'code' => 'ELEC', 'description' => 'Electricity utility bills'],
                ['name' => 'Water', 'code' => 'WATER', 'description' => 'Water utility expenses'],
                ['name' => 'Internet', 'code' => 'NET', 'description' => 'Internet and phone bills'],
                ['name' => 'Salary', 'code' => 'SALARY', 'description' => 'Staff payroll and wages'],
                ['name' => 'Transportation', 'code' => 'TRANS', 'description' => 'Fuel, transport, and logistics'],
                ['name' => 'Maintenance', 'code' => 'MAINT', 'description' => 'Repairs and maintenance'],
                ['name' => 'Office Supplies', 'code' => 'OFFICE', 'description' => 'Stationery and consumables'],
                ['name' => 'Marketing', 'code' => 'MKTG', 'description' => 'Advertising and promotions'],
                ['name' => 'Taxes', 'code' => 'TAX', 'description' => 'Government fees and taxes'],
                ['name' => 'Other', 'code' => 'OTHER', 'description' => 'Miscellaneous operational expenses'],
            ];

            foreach ($defaults as $cat) {
                static::create($cat);
            }
        }
    }
}
