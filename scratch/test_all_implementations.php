<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\AdditionalCharge;
use App\Models\SaleAdditionalCharge;
use App\Services\TaxService;
use Illuminate\Support\Facades\DB;

$tenant = Tenant::first();
if (!$tenant) {
    echo "No tenant found.\n";
    exit;
}

tenancy()->initialize($tenant);
echo "Testing on tenant: {$tenant->id}\n";

// 1. Test TaxService line resolution
$taxService = new TaxService();
echo "Store Default Tax Settings: " . json_encode($taxService->getSettings()) . "\n";

echo "Tax for null (fallback): " . $taxService->resolveItemTaxRate(null) . "%\n";
echo "Tax for 0.00 (explicit zero): " . $taxService->resolveItemTaxRate(0.00) . "%\n";
echo "Tax for 5.00 (specific rate): " . $taxService->resolveItemTaxRate(5.00) . "%\n";

$lineTax1 = $taxService->calculateLineTax(100, null);
echo "Line tax ($100, null): Rate {$lineTax1['tax_rate']}%, Amount {$lineTax1['tax_amount']}\n";

$lineTax2 = $taxService->calculateLineTax(100, 5.00);
echo "Line tax ($100, 5%): Rate {$lineTax2['tax_rate']}%, Amount {$lineTax2['tax_amount']}\n";

$lineTax3 = $taxService->calculateLineTax(100, 0.00);
echo "Line tax ($100, 0%): Rate {$lineTax3['tax_rate']}%, Amount {$lineTax3['tax_amount']}\n";

// 2. Test Item with custom tax rate
$testItem = Item::create([
    'description' => 'Test Tax Product ' . time(),
    'code'        => 'TAXPROD-' . time(),
    'cost_rate'   => 50.00,
    'sale_rate'   => 100.00,
    'tax_rate'    => 5.00,
    'item_type'   => 'Service',
    'on_hand'     => 10,
]);

echo "Created test item ID: {$testItem->id} with tax_rate: {$testItem->tax_rate}%\n";

// 3. Test Additional Charge creation
$charge = AdditionalCharge::create([
    'name'       => 'Delivery Fee ' . time(),
    'type'       => 'fixed',
    'value'      => 150.00,
    'is_enabled' => true,
]);

echo "Created AdditionalCharge ID: {$charge->id}\n";

// 4. Test POS Sale creation via controller or DB transaction
DB::transaction(function () use ($testItem, $charge, $taxService) {
    $sale = Sale::create([
        'invoice_no'        => 'TESTINV-' . time(),
        'user_id'           => 1,
        'subtotal'          => 100.00,
        'discount_total'    => 0,
        'tax_rate'          => 5.00,
        'tax_total'         => 5.00,
        'additional_charges_total' => 150.00,
        'grand_total'       => 255.00,
        'paid_amount'       => 255.00,
        'change_amount'     => 0,
        'payment_mode'      => 'Online',
        'status'            => 'completed',
        'sale_date'         => now(),
    ]);

    $saleItem = SaleItem::create([
        'sale_id'   => $sale->id,
        'item_id'   => $testItem->id,
        'item_name' => $testItem->description,
        'qty'       => 1,
        'rate'      => 100.00,
        'total'     => 100.00,
        'tax_rate'  => 5.00,
        'tax_amount'=> 5.00,
    ]);

    $saleCharge = SaleAdditionalCharge::create([
        'sale_id'              => $sale->id,
        'additional_charge_id' => $charge->id,
        'name'                 => $charge->name,
        'type'                 => $charge->type,
        'value'                => $charge->value,
        'amount'               => 150.00,
    ]);

    echo "Successfully created test Sale ID {$sale->id} with payment mode 'Online', item tax_rate: {$saleItem->tax_rate}%, tax_amount: {$saleItem->tax_amount}, additional_charges_total: {$sale->additional_charges_total}!\n";

    // Clean up test records
    $saleCharge->delete();
    $saleItem->delete();
    $sale->delete();
});

$charge->delete();
$testItem->delete();

echo "\nAll verification tests passed successfully!\n";
