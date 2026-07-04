<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Local use only — on live server, use public/setuproles.php instead.
     *
     * Usage: php artisan db:seed --class=RoleSeeder
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── Create all permissions ───────────────────────────────────────────

        $permissions = [
            // Sales
            'sales.view', 'sales.create', 'sales.delete',

            // Purchases
            'purchases.view', 'purchases.create', 'purchases.delete',

            // Suppliers
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',

            // Customers
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',

            // Items
            'items.view', 'items.create', 'items.edit', 'items.delete', 'items.import',

            // Reports
            'reports.view',

            // Godams (Warehouses)
            'godams.view', 'godams.create', 'godams.edit', 'godams.delete',

            // Stock Transfers
            'stock-transfers.view', 'stock-transfers.create',

            // Settings
            'settings.view', 'settings.edit',

            // Staff Management
            'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $allPermissions = Permission::where('guard_name', 'web')->get();

        // ─── Owner — Full access to everything ───────────────────────────────

        $owner = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner->syncPermissions($allPermissions);

        // ─── Manager — All except settings.edit, staff.create, staff.delete ──

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $managerPermissions = $allPermissions->filter(fn($p) => !in_array($p->name, [
            'settings.edit',
            'staff.create',
            'staff.delete',
        ]));
        $manager->syncPermissions($managerPermissions);

        // ─── Cashier — POS, Sales, Items view only ────────────────────────────

        $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $cashier->syncPermissions([
            'sales.view',
            'sales.create',
            'items.view',
        ]);

        // ─── Warehouse — Godams, Stock Transfers, Items view ─────────────────

        $warehouse = Role::firstOrCreate(['name' => 'warehouse', 'guard_name' => 'web']);
        $warehouse->syncPermissions([
            'godams.view',
            'godams.create',
            'godams.edit',
            'items.view',
            'stock-transfers.view',
            'stock-transfers.create',
        ]);

        $this->command->info('Roles and permissions seeded successfully.');
        $this->command->table(
            ['Role', 'Permissions Count'],
            [
                [$owner->name,     $owner->permissions->count()],
                [$manager->name,   $manager->permissions->count()],
                [$cashier->name,   $cashier->permissions->count()],
                [$warehouse->name, $warehouse->permissions->count()],
            ]
        );
    }
}
