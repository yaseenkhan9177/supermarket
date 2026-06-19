<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        Supplier::create([
            'name' => 'Metro Cash & Carry',
            'phone' => '021-111-222-333',
            'address' => 'Main University Road, Karachi',
            'balance' => 0
        ]);

        Supplier::create([
            'name' => 'Al-Fatah Distributors',
            'phone' => '042-333-444-555',
            'address' => 'Gulberg III, Lahore',
            'balance' => 0
        ]);
    }
}
