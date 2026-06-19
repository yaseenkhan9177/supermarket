<?php

use App\Models\Sale;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Debugging Invoice Data Integrity ---\n";

$targets = ['CS-2026-0099', 'INV-1770363677'];

foreach ($targets as $t) {
    echo "\nChecking '$t':\n";
    $sale = Sale::where('invoice_no', $t)->first();
    if ($sale) {
        echo "FOUND ID: {$sale->id}\n";
        echo "Invoice No: '{$sale->invoice_no}' (Length: " . strlen($sale->invoice_no) . ")\n";
        echo "Status: '{$sale->status}'\n";

        // Check for invisible characters
        echo "Hex Dump: " . bin2hex($sale->invoice_no) . "\n";
    } else {
        echo "NOT FOUND by exact match.\n";
        // Try LIKE
        $like = Sale::where('invoice_no', 'LIKE', "%$t%")->first();
        if ($like) {
            echo "FOUND via LIKE match: '{$like->invoice_no}' (ID: {$like->id})\n";
        } else {
            echo "NOT FOUND via LIKE match either.\n";
        }
    }
}
