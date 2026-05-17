<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Report;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Sales
        $sales = Report::create(['name' => 'Sales Reports', 'type' => 'folder', 'icon' => 'fas fa-shopping-cart', 'sort_order' => 1]);
        Report::create(['parent_id' => $sales->id, 'name' => 'Daily Sales Summary', 'type' => 'report', 'icon' => 'fas fa-calendar-day', 'sort_order' => 1]);
        Report::create(['parent_id' => $sales->id, 'name' => 'Item-wise Sales', 'type' => 'report', 'icon' => 'fas fa-tags', 'sort_order' => 2]);
        Report::create(['parent_id' => $sales->id, 'name' => 'Customer Ledger', 'type' => 'report', 'icon' => 'fas fa-user-tag', 'sort_order' => 3]);

        // 2. Purchase
        $purchase = Report::create(['name' => 'Purchase Reports', 'type' => 'folder', 'icon' => 'fas fa-truck', 'sort_order' => 2]);
        Report::create(['parent_id' => $purchase->id, 'name' => 'Purchase Register', 'type' => 'report', 'icon' => 'fas fa-file-invoice', 'sort_order' => 1]);
        Report::create(['parent_id' => $purchase->id, 'name' => 'Supplier Ledger', 'type' => 'report', 'icon' => 'fas fa-user-tie', 'sort_order' => 2]);

        // 3. Inventory
        $inventory = Report::create(['name' => 'Inventory / Stock', 'type' => 'folder', 'icon' => 'fas fa-boxes', 'sort_order' => 3]);
        Report::create(['parent_id' => $inventory->id, 'name' => 'Stock Valuation', 'type' => 'report', 'icon' => 'fas fa-coins', 'sort_order' => 1]);
        Report::create(['parent_id' => $inventory->id, 'name' => 'Low Stock Alert', 'type' => 'report', 'icon' => 'fas fa-exclamation-triangle', 'sort_order' => 2]);

        // 4. Accounts
        $accounts = Report::create(['name' => 'Accounts / GL', 'type' => 'folder', 'icon' => 'fas fa-book', 'sort_order' => 4]);
        Report::create(['parent_id' => $accounts->id, 'name' => 'Cash Book', 'type' => 'report', 'icon' => 'fas fa-book-open', 'sort_order' => 1]);
        Report::create(['parent_id' => $accounts->id, 'name' => 'Profit & Loss', 'type' => 'report', 'icon' => 'fas fa-chart-line', 'sort_order' => 2]);
    }
}
