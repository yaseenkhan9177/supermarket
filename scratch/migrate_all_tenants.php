<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;

$tenants = Tenant::all();
echo "Found " . $tenants->count() . " tenant records in central database.\n\n";

foreach ($tenants as $tenant) {
    $dbName = $tenant->database()->getName();
    $exists = $tenant->database()->manager()->databaseExists($dbName);
    
    echo "Tenant [{$tenant->id}] -> DB: {$dbName} ";
    
    if (!$exists) {
        echo " [SKIPPED - Database does not exist]\n";
        continue;
    }
    
    echo "\n";
    try {
        tenancy()->initialize($tenant);
        
        Artisan::call('migrate', [
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);
        
        echo Artisan::output();
        
        tenancy()->end();
        echo "✓ Migrated successfully for {$tenant->id}\n\n";
    } catch (\Throwable $e) {
        echo "✗ Error migrating {$tenant->id}: " . $e->getMessage() . "\n\n";
        if (tenancy()->initialized) {
            tenancy()->end();
        }
    }
}

echo "All valid tenant databases processed.\n";
