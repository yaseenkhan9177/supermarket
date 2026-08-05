<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

echo "=== ALL TENANTS IN DATABASE ===\n\n";
$all = Tenant::select(['id', 'store_name', 'owner_email', 'status', 'database_name', 'paid_until'])->get();
foreach ($all as $t) {
    echo "ID: {$t->id}\n";
    echo "  Store: {$t->store_name}\n";
    echo "  Email: {$t->owner_email}\n";
    echo "  Status: {$t->status}\n";
    echo "  DB: {$t->database_name}\n";
    echo "  Paid Until: {$t->paid_until}\n\n";
}

echo "\n=== MATCHING THE 6 LIVE TENANTS ===\n\n";
$prefixes = ['0cdcf7b7', '42c48002', '721dd49f', '844ef2c7', 'ad5cdc83', 'afc25693'];
$matched = Tenant::where(function($q) use ($prefixes) {
    foreach ($prefixes as $p) {
        $q->orWhere('id', 'like', $p . '%');
    }
})->get(['id', 'store_name', 'status', 'database_name', 'paid_until']);

echo "Count matched: " . $matched->count() . "\n\n";
foreach ($matched as $t) {
    echo "ID: {$t->id} | {$t->store_name} | {$t->status} | paid_until: {$t->paid_until}\n";
}
