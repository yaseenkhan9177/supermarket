<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = Schema::getColumnListing('users');
$type = Schema::getColumnType('users', 'id');

echo "Users table columns: " . implode(', ', $columns) . "\n";
echo "ID Column Type: " . $type . "\n";

// Detailed info if possible (raw SQL)
$result = DB::select("SHOW COLUMNS FROM users WHERE Field = 'id'");
print_r($result);
