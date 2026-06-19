<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            [
                'name' => 'Store Admin', // Owner
                'default_permissions' => ['*'] // All access
            ],
            [
                'name' => 'Manager',
                'default_permissions' => [
                    'sales.view',
                    'sales.create',
                    'sales.edit',
                    'sales.delete',
                    'sales.refund',
                    'sales.discount',
                    'products.view',
                    'products.create',
                    'products.edit',
                    'products.stock',
                    'products.adjustment',
                    'customers.view',
                    'customers.create',
                    'customers.edit',
                    'customers.balance',
                    'customers.payment',
                    'reports.sales',
                    'reports.stock',
                    'reports.profit',
                    'expenses.view',
                    'expenses.create'
                ]
            ],
            [
                'name' => 'Cashier',
                'default_permissions' => [
                    'sales.view',
                    'sales.create',
                    'sales.refund', // Maybe restrict refund
                    'customers.view',
                    'customers.create',
                    'products.view'
                ]
            ],
            [
                'name' => 'Salesman',
                'default_permissions' => [
                    'sales.view',
                    'sales.create',
                    'products.view'
                ]
            ],
            [
                'name' => 'Accountant',
                'default_permissions' => [
                    'sales.view',
                    'reports.sales',
                    'reports.financial',
                    'reports.ledger',
                    'expenses.view',
                    'expenses.create',
                    'expenses.edit',
                    'accounts.view',
                    'accounts.create',
                    'accounts.edit'
                ]
            ],
            [
                'name' => 'Stock Keeper',
                'default_permissions' => [
                    'products.view',
                    'products.create',
                    'products.edit',
                    'products.stock',
                    'products.adjustment'
                ]
            ]
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
