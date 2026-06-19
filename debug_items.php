<?php

use App\Models\Item;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$items = Item::all(['id', 'description', 'item_type', 'on_hand', 'code']);

foreach ($items as $item) {
    echo "ID: {$item->id} | Name: {$item->description} | Type: {$item->item_type} | Stock: {$item->on_hand} | Code: {$item->code}\n";
}
