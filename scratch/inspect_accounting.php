<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tenant = Tenant::first();
if (!$tenant) {
    echo "No tenant found.\n";
    exit;
}

tenancy()->initialize($tenant);
echo "=== ACCOUNTING SCHEMA MAP FOR TENANT [{$tenant->id}] ===\n\n";

$tables = [
    'accounts',
    'bank_accounts',
    'wallets',
    'customers',
    'customer_ledger_entries',
    'suppliers',
    'supplier_ledgers',
    'supplier_ledger_entries',
    'supplier_payment_vouchers',
    'payments',
    'receipts',
    'receipt_allocations',
    'general_ledger_accounts',
    'gl_entries',
    'journals',
    'journal_entries',
    'transfers',
    'adjustments',
    'refunds',
    'expenses',
];

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        $cols = Schema::getColumnListing($table);
        $count = DB::table($table)->count();
        echo "Table: [{$table}] (Rows: {$count})\n";
        echo "  Columns: " . implode(', ', $cols) . "\n\n";
    } else {
        echo "Table: [{$table}] DOES NOT EXIST\n\n";
    }
}
