<?php

use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$hasOnHand = Schema::hasColumn('items', 'on_hand');
$hasStockQty = Schema::hasColumn('items', 'stock_qty');

echo "on_hand exists: " . ($hasOnHand ? 'YES' : 'NO') . "\n";
echo "stock_qty exists: " . ($hasStockQty ? 'YES' : 'NO') . "\n";
