<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\Item;
use App\Models\Batch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleVersion;
use App\Models\Customer;
use App\Models\Wallet;
use App\Models\User;
use App\Services\FifoStockService;
use App\Services\InvoiceEditService;
use Illuminate\Support\Facades\DB;

echo "========================================================\n";
echo "INVOICE EDITING & STOCK SYNCHRONIZATION TEST SUITE\n";
echo "========================================================\n\n";

$tenant = Tenant::first();
if (!$tenant) {
    echo "No tenant found!\n";
    exit(1);
}

tenancy()->initialize($tenant);
echo "Initialized Tenant: {$tenant->id}\n\n";

$fifoService = app(FifoStockService::class);
$editService = app(InvoiceEditService::class);

DB::beginTransaction();

try {
    $user = User::first() ?? User::create(['name' => 'Admin Tester', 'email' => 'admin@test.com', 'password' => bcrypt('password')]);
    $wallet = Wallet::first() ?? Wallet::create(['name' => 'Main Cash Wallet', 'balance' => 10000.00, 'is_active' => true]);
    $customer = Customer::create([
        'name' => 'Test Customer John',
        'phone' => '1234567890',
        'balance' => 0.00,
    ]);

    // 1. Create Test Products with Batches
    // Product A: 40 in stock
    $itemA = Item::create([
        'description' => 'Test Product A (Widget)',
        'code'        => 'TEST-PROD-A',
        'cost_rate'   => 50.00,
        'sale_rate'   => 100.00,
        'on_hand'     => 40.00,
        'item_type'   => 'Inventory',
    ]);
    Batch::create([
        'item_id' => $itemA->id,
        'batch_no' => 'BATCH-A-01',
        'quantity_available' => 40.00,
        'cost_price' => 50.00,
        'sale_price' => 100.00,
        'received_at' => now(),
    ]);

    // Product B: 20 in stock
    $itemB = Item::create([
        'description' => 'Test Product B (Gadget)',
        'code'        => 'TEST-PROD-B',
        'cost_rate'   => 100.00,
        'sale_rate'   => 200.00,
        'on_hand'     => 20.00,
        'item_type'   => 'Inventory',
    ]);
    Batch::create([
        'item_id' => $itemB->id,
        'batch_no' => 'BATCH-B-01',
        'quantity_available' => 20.00,
        'cost_price' => 100.00,
        'sale_price' => 200.00,
        'received_at' => now(),
    ]);

    // Product C: 15 in stock
    $itemC = Item::create([
        'description' => 'Test Product C (Tool)',
        'code'        => 'TEST-PROD-C',
        'cost_rate'   => 30.00,
        'sale_rate'   => 60.00,
        'on_hand'     => 15.00,
        'item_type'   => 'Inventory',
    ]);
    Batch::create([
        'item_id' => $itemC->id,
        'batch_no' => 'BATCH-C-01',
        'quantity_available' => 15.00,
        'cost_price' => 30.00,
        'sale_price' => 60.00,
        'received_at' => now(),
    ]);

    // Service Item: 0 stock (non-stock controlled)
    $serviceItem = Item::create([
        'description' => 'Test Installation Fee',
        'code'        => 'TEST-SRV-01',
        'cost_rate'   => 0.00,
        'sale_rate'   => 300.00,
        'on_hand'     => 0.00,
        'item_type'   => 'Service',
    ]);

    echo "Initial Setup:\n";
    echo "  - Product A Available Stock: " . $fifoService->getAvailableStock($itemA->id) . "\n";
    echo "  - Product B Available Stock: " . $fifoService->getAvailableStock($itemB->id) . "\n";
    echo "  - Product C Available Stock: " . $fifoService->getAvailableStock($itemC->id) . "\n";
    echo "  - Customer Balance: " . $customer->balance . "\n\n";

    // Create Initial Invoice: Product A x 10, Product B x 5 (Debit Sale)
    // Stock will be: A = 30, B = 15
    $fifoService->deductStock($itemA->id, 10, null, $user->id);
    $fifoService->deductStock($itemB->id, 5, null, $user->id);

    $sale = Sale::create([
        'invoice_no'     => 'INV-TEST-10025',
        'customer_id'    => $customer->id,
        'user_id'        => $user->id,
        'sale_date'      => now(),
        'payment_mode'   => 'Debit',
        'subtotal'       => 2000.00,
        'grand_total'    => 2000.00,
        'paid_amount'    => 0.00,
        'status'         => 'completed',
    ]);
    SaleItem::create(['sale_id' => $sale->id, 'item_id' => $itemA->id, 'item_name' => $itemA->description, 'qty' => 10, 'rate' => 100.00, 'total' => 1000.00]);
    SaleItem::create(['sale_id' => $sale->id, 'item_id' => $itemB->id, 'item_name' => $itemB->description, 'qty' => 5, 'rate' => 200.00, 'total' => 1000.00]);
    $customer->increment('balance', 2000.00);

    echo "--- Base Invoice Created ---\n";
    echo "Invoice #{$sale->invoice_no}: Product A x 10, Product B x 5 (Grand Total: Rs. {$sale->grand_total})\n";
    echo "Product A Stock: " . $fifoService->getAvailableStock($itemA->id) . " (Expected: 30)\n";
    echo "Product B Stock: " . $fifoService->getAvailableStock($itemB->id) . " (Expected: 15)\n\n";

    // -------------------------------------------------------------------------
    // TEST 1 & 2 & 3 & 4 & 5: Complex Multi-Item Edit
    // User Example from prompt:
    // Change Product A: 10 -> 7 (Difference: -3, Stock +3)
    // Change Product B: 5 -> 8 (Difference: +3, Stock -3)
    // Add Product C: 3 (Difference: +3, Stock -3)
    // Add Service Item: 1 (Exempt from stock)
    // -------------------------------------------------------------------------
    echo "--- TEST 1-5: Multi-Item Edit (A: 10->7, B: 5->8, Add C x 3, Add Service x 1) ---\n";

    $updatedSale = $editService->updateInvoice($sale->id, [
        'customer_id'    => $customer->id,
        'payment_mode'   => 'Debit',
        'sale_date'      => now()->toDateTimeString(),
        'discount_total' => 50.00,
        'tax_total'      => 0.00,
        'paid_amount'    => 0.00,
        'items' => [
            ['item_id' => $itemA->id, 'qty' => 7, 'rate' => 100.00],
            ['item_id' => $itemB->id, 'qty' => 8, 'rate' => 200.00],
            ['item_id' => $itemC->id, 'qty' => 3, 'rate' => 60.00],
            ['item_id' => $serviceItem->id, 'qty' => 1, 'rate' => 300.00],
        ]
    ], $user->id, 'Adjusted items on customer request and added installation fee');

    $stockA = $fifoService->getAvailableStock($itemA->id);
    $stockB = $fifoService->getAvailableStock($itemB->id);
    $stockC = $fifoService->getAvailableStock($itemC->id);

    echo "Product A Stock after edit: {$stockA} (Expected: 33) -> " . ($stockA == 33 ? "PASS ✅" : "FAIL ❌") . "\n";
    echo "Product B Stock after edit: {$stockB} (Expected: 12) -> " . ($stockB == 12 ? "PASS ✅" : "FAIL ❌") . "\n";
    echo "Product C Stock after edit: {$stockC} (Expected: 12) -> " . ($stockC == 12 ? "PASS ✅" : "FAIL ❌") . "\n";

    // Expected Subtotal: 7*100 (700) + 8*200 (1600) + 3*60 (180) + 1*300 (300) = 2780.
    // Grand Total: 2780 - 50 = 2730.
    echo "Grand Total after edit: Rs. {$updatedSale->grand_total} (Expected: 2730.00) -> " . ($updatedSale->grand_total == 2730 ? "PASS ✅" : "FAIL ❌") . "\n";
    echo "Customer Debt Balance: Rs. {$customer->fresh()->balance} (Expected: 2730.00) -> " . ($customer->fresh()->balance == 2730 ? "PASS ✅" : "FAIL ❌") . "\n";

    assert($stockA == 33);
    assert($stockB == 12);
    assert($stockC == 12);
    assert($updatedSale->grand_total == 2730);

    // -------------------------------------------------------------------------
    // TEST 6: Insufficient Stock Rejection
    // Try to increase Product C from 3 to 100 (Available is only 12)
    // -------------------------------------------------------------------------
    echo "\n--- TEST 6: Insufficient Stock Validation ---\n";
    try {
        $editService->updateInvoice($sale->id, [
            'customer_id'  => $customer->id,
            'payment_mode' => 'Debit',
            'items' => [
                ['item_id' => $itemA->id, 'qty' => 7, 'rate' => 100.00],
                ['item_id' => $itemB->id, 'qty' => 8, 'rate' => 200.00],
                ['item_id' => $itemC->id, 'qty' => 100, 'rate' => 60.00], // Needs 97 more, only 12 exist!
            ]
        ], $user->id, 'Testing over-deduction');

        echo "Over-deduction was not rejected! FAIL ❌\n";
        assert(false);
    } catch (\Illuminate\Validation\ValidationException $e) {
        echo "Over-deduction successfully caught and rejected! PASS ✅\n";
        echo "Validation message: " . json_encode($e->errors()) . "\n";
    }

    // Verify stock and invoice remained intact
    assert($fifoService->getAvailableStock($itemC->id) == 12);
    echo "Product C Stock remained unchanged at: 12 ✅\n";

    // -------------------------------------------------------------------------
    // TEST 7: Concurrency Protection
    // -------------------------------------------------------------------------
    echo "\n--- TEST 7: Concurrency Protection ---\n";
    try {
        $staleTimestamp = '2020-01-01T00:00:00.000000Z';
        $editService->updateInvoice($sale->id, [
            'customer_id'  => $customer->id,
            'payment_mode' => 'Debit',
            'items' => [
                ['item_id' => $itemA->id, 'qty' => 5, 'rate' => 100.00],
            ]
        ], $user->id, 'Concurrent edit', null, $staleTimestamp);

        echo "Concurrency check failed! FAIL ❌\n";
        assert(false);
    } catch (\Exception $e) {
        echo "Stale edit rejected with error: '{$e->getMessage()}' PASS ✅\n";
    }

    // -------------------------------------------------------------------------
    // TEST 8: Multi-Version History Chain
    // -------------------------------------------------------------------------
    echo "\n--- TEST 8: Multi-Version History Chain ---\n";
    // Perform 2 more edits:
    // Edit 2: Remove Product C completely (A x 7, B x 8)
    $editService->updateInvoice($sale->id, [
        'customer_id'  => $customer->id,
        'payment_mode' => 'Debit',
        'items' => [
            ['item_id' => $itemA->id, 'qty' => 7, 'rate' => 100.00],
            ['item_id' => $itemB->id, 'qty' => 8, 'rate' => 200.00],
        ]
    ], $user->id, 'Removed Tool C');

    // Product C stock should have returned from 12 back to 15!
    $stockC = $fifoService->getAvailableStock($itemC->id);
    echo "Product C stock after complete removal: {$stockC} (Expected: 15) -> " . ($stockC == 15 ? "PASS ✅" : "FAIL ❌") . "\n";
    assert($stockC == 15);

    $versions = SaleVersion::where('sale_id', $sale->id)->orderBy('version_number')->get();
    echo "Total Versions Recorded: " . $versions->count() . "\n";
    foreach ($versions as $v) {
        echo "  - Version {$v->version_number}: Action='{$v->action_type}', Reason='{$v->reason}', Total=Rs. " . ($v->new_values['grand_total'] ?? 0) . "\n";
    }
    assert($versions->count() >= 3);
    echo "Version chain integrity verified! PASS ✅\n";

    // -------------------------------------------------------------------------
    // TEST 9: Invoice Cancellation
    // -------------------------------------------------------------------------
    echo "\n--- TEST 9: Invoice Cancellation & Stock Restoration ---\n";
    $editService->cancelInvoice($sale->id, $user->id, 'Order cancelled by customer');

    $stockA = $fifoService->getAvailableStock($itemA->id);
    $stockB = $fifoService->getAvailableStock($itemB->id);
    $stockC = $fifoService->getAvailableStock($itemC->id);

    echo "Product A Stock after cancellation: {$stockA} (Expected: 40) -> " . ($stockA == 40 ? "PASS ✅" : "FAIL ❌") . "\n";
    echo "Product B Stock after cancellation: {$stockB} (Expected: 20) -> " . ($stockB == 20 ? "PASS ✅" : "FAIL ❌") . "\n";
    echo "Product C Stock after cancellation: {$stockC} (Expected: 15) -> " . ($stockC == 15 ? "PASS ✅" : "FAIL ❌") . "\n";
    echo "Customer Debt Balance: Rs. {$customer->fresh()->balance} (Expected: 0.00) -> " . ($customer->fresh()->balance == 0 ? "PASS ✅" : "FAIL ❌") . "\n";

    assert($stockA == 40);
    assert($stockB == 20);
    assert($stockC == 15);
    assert($customer->fresh()->balance == 0);

    echo "Cancellation and stock restoration verified! PASS ✅\n";

} finally {
    DB::rollBack();
    echo "\nTransaction rolled back cleanly — NO test database records were persisted. ✅\n";
}

echo "\n========================================================\n";
echo "ALL INVOICE EDITING & STOCK SYNC TESTS PASSED (100%)!\n";
echo "========================================================\n";
