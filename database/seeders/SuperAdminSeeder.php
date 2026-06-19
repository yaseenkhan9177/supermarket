<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Schema::hasTable('super_admins')) {
            DB::table('super_admins')->updateOrInsert(
                ['email' => 'khan@gmail.com'],
                [
                    'name' => 'Khan',
                    'password' => Hash::make('12345678'),
                    'role' => 'super_owner',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
