<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenants = App\Models\Tenant::all();
foreach ($tenants as $t) {
    try {
        if ($t->database()->manager()->databaseExists($t->database()->getName())) {
            echo "Migrating tenant: " . $t->id . "\n";
            tenancy()->initialize($t);
            \Illuminate\Support\Facades\Artisan::call('migrate', [
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);
            echo \Illuminate\Support\Facades\Artisan::output();
            tenancy()->end();
        }
    } catch (\Throwable $e) {
        echo "Skipping tenant " . $t->id . ": " . $e->getMessage() . "\n";
    }
}
echo "Migration complete.\n";
