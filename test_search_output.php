<?php
// Initialize Laravel app
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Force initialize tenancy with the active tenant
$tenant = \App\Models\Tenant::find('cfbccadc-3809-49c2-899e-de7bf04f7993');
tenancy()->initialize($tenant);

$query = 'MOMUL';
$items = \App\Models\Item::where('description', 'like', "%{$query}%")
    ->orWhere('code', 'like', "%{$query}%")
    ->select('id', 'description as name', 'code', 'sale_rate as price', 'on_hand as stock_qty', 'item_type')
    ->limit(50)
    ->get();

echo "JSON output for '$query':" . PHP_EOL;
echo json_encode($items, JSON_PRETTY_PRINT) . PHP_EOL;
