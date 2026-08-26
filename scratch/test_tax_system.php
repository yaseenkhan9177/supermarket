<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\CompanySetting;
use App\Models\TaxSettingsHistory;
use App\Models\Wallet;
use App\Models\Customer;
use App\Services\TaxService;
use App\Services\InvoiceEditService;
use App\Http\Controllers\CashSalesController;
use App\Http\Controllers\DebitSalesController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\TaxSettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "=================================================================\n";
echo "MART TAX SETTINGS & AUTOMATIC INVOICE TAX - TEST SUITE\n";
echo "=================================================================\n\n";

$passedCount = 0;
$failedCount = 0;

function assertTest($condition, $testName, $details = "") {
    global $passedCount, $failedCount;
    if ($condition) {
        echo " [PASS] $testName\n";
        if ($details) echo "        $details\n";
        $passedCount++;
    } else {
        echo " [FAIL] $testName\n";
        if ($details) echo "        Error: $details\n";
        $failedCount++;
    }
}

// 0. Setup test environment
$owner = User::where('role', 'owner')->first() ?? User::first();
if (!$owner) {
    $owner = User::create([
        'name' => 'Store Owner',
        'email' => 'owner@example.com',
        'password' => bcrypt('password'),
        'role' => 'owner',
    ]);
}
Auth::login($owner);

$wallet = Wallet::where('is_active', true)->first();
if (!$wallet) {
    $wallet = Wallet::create(['name' => 'Counter Cash', 'type' => 'Cash', 'balance' => 0, 'is_active' => true]);
}

$customer = Customer::first();
if (!$customer) {
    $customer = Customer::create(['name' => 'Walk-in Customer', 'phone' => '03001234567', 'balance' => 0]);
}

$item = Item::create([
    'description' => 'Test Tax Service Product',
    'code' => 'TAX-' . time(),
    'sale_rate' => 5000,
    'cost_rate' => 3000,
    'on_hand' => 100,
    'item_type' => 'Service'
]);

$taxService = new TaxService();

// -------------------------------------------------------------------------
// TEST 1: Store Admin enables 2% tax
// -------------------------------------------------------------------------
echo "--- TEST 1: Store Admin enables 2% tax ---\n";
$taxController = app(TaxSettingsController::class);
$request = Request::create('/settings/tax', 'POST', [
    'tax_enabled' => 1,
    'tax_rate' => 2.00,
]);
$taxController->update($request);

$setting = CompanySetting::first();
assertTest(
    $setting->tax_enabled === true && (float)$setting->tax_rate === 2.00,
    "Tax settings updated to Enabled (2.00%) in CompanySetting",
    "Stored tax_enabled=" . var_export($setting->tax_enabled, true) . ", tax_rate=" . $setting->tax_rate
);

$historyEntry = TaxSettingsHistory::latest()->first();
assertTest(
    $historyEntry && (float)$historyEntry->new_tax_rate === 2.00 && $historyEntry->new_tax_enabled === true,
    "TaxSettingsHistory audit log recorded the change",
    "History ID: {$historyEntry->id}, Rate: {$historyEntry->new_tax_rate}%, User: {$historyEntry->user_name}"
);

// -------------------------------------------------------------------------
// TEST 2: Staff creates Cash Sale invoice (Tax = 2% automatically applied)
// -------------------------------------------------------------------------
echo "\n--- TEST 2: Staff creates Cash Sale invoice (Tax = 2% automatically applied) ---\n";
$cashController = app(CashSalesController::class);
$invNo1 = 'TEST-CS-' . time();
$cashRequest = Request::create('/cash-sales/store', 'POST', [
    'invoice_no' => $invNo1,
    'wallet_id' => $wallet->id,
    'customer_id' => $customer->id,
    'salesman_id' => $owner->id,
    'date' => date('Y-m-d'),
    'rows' => [
        ['id' => $item->id, 'qty' => 2, 'price' => 5000], // Subtotal = 10,000
    ],
    'grand_total' => 10000, // Dummy client value
    'received_amount' => 10200,
    'return_adjustment' => 0,
]);

$response = $cashController->store($cashRequest);
if ($response instanceof \Illuminate\Http\JsonResponse) {
    $data = $response->getData(true);
    if (!($data['success'] ?? false)) {
        echo "Controller error: " . json_encode($data) . "\n";
    }
}
$sale1 = Sale::where('invoice_no', $invNo1)->first();

assertTest(
    $sale1 !== null,
    "Invoice created in database: {$invNo1}",
    $sale1 ? "" : "Sale was not created. Response: " . ($response->getContent() ?? "")
);
assertTest(
    (float)$sale1->subtotal === 10000.00,
    "Subtotal is exactly Rs. 10,000.00",
    "Actual: {$sale1->subtotal}"
);
assertTest(
    (float)$sale1->tax_rate === 2.00,
    "Tax Rate snapshot is recorded as 2.00%",
    "Actual: {$sale1->tax_rate}"
);
assertTest(
    (float)$sale1->tax_total === 200.00,
    "Tax Total is exactly Rs. 200.00 (2% of 10,000)",
    "Actual: {$sale1->tax_total}"
);
assertTest(
    (float)$sale1->grand_total === 10200.00,
    "Grand Total is exactly Rs. 10,200.00",
    "Actual: {$sale1->grand_total}"
);

// -------------------------------------------------------------------------
// TEST 3: Staff attempts payload manipulation (send tax_rate = 0)
// -------------------------------------------------------------------------
echo "\n--- TEST 3: Staff attempts payload manipulation (sending tax_rate: 0, grand_total: 10000) ---\n";
$invNo2 = 'TEST-CS-HACK-' . time();
$tamperedRequest = Request::create('/cash-sales/store', 'POST', [
    'invoice_no' => $invNo2,
    'wallet_id' => $wallet->id,
    'customer_id' => $customer->id,
    'salesman_id' => $owner->id,
    'date' => date('Y-m-d'),
    'rows' => [
        ['id' => $item->id, 'qty' => 2, 'price' => 5000],
    ],
    'tax_rate' => 0, // Malicious override attempt
    'tax_total' => 0, // Malicious override attempt
    'grand_total' => 10000, // Malicious override attempt
    'received_amount' => 10200,
    'return_adjustment' => 0,
]);

$cashController->store($tamperedRequest);
$sale2 = Sale::where('invoice_no', $invNo2)->first();

assertTest(
    (float)$sale2->tax_rate === 2.00 && (float)$sale2->tax_total === 200.00 && (float)$sale2->grand_total === 10200.00,
    "Backend rejected/ignored client-side tax override; enforced 2% (Rs. 200) tax",
    "Actual tax_rate: {$sale2->tax_rate}%, tax_total: Rs. {$sale2->tax_total}, grand_total: Rs. {$sale2->grand_total}"
);

// -------------------------------------------------------------------------
// TEST 4: Admin updates tax rate (2% -> 5%)
// -------------------------------------------------------------------------
echo "\n--- TEST 4: Store Admin changes tax from 2% -> 5% ---\n";
$updateRequest = Request::create('/settings/tax', 'POST', [
    'tax_enabled' => 1,
    'tax_rate' => 5.00,
]);
$taxController->update($updateRequest);

$invNo3 = 'TEST-CS-5PCT-' . time();
$cashRequest3 = Request::create('/cash-sales/store', 'POST', [
    'invoice_no' => $invNo3,
    'wallet_id' => $wallet->id,
    'customer_id' => $customer->id,
    'salesman_id' => $owner->id,
    'date' => date('Y-m-d'),
    'rows' => [
        ['id' => $item->id, 'qty' => 2, 'price' => 5000],
    ],
    'grand_total' => 0,
    'received_amount' => 10500,
    'return_adjustment' => 0,
]);
$cashController->store($cashRequest3);
$sale3 = Sale::where('invoice_no', $invNo3)->first();

assertTest(
    (float)$sale3->tax_rate === 5.00 && (float)$sale3->tax_total === 500.00 && (float)$sale3->grand_total === 10500.00,
    "New invoice automatically uses new 5% tax rate (Rs. 500 tax, Rs. 10,500 total)",
    "Actual tax_rate: {$sale3->tax_rate}%, tax_total: Rs. {$sale3->tax_total}, grand_total: Rs. {$sale3->grand_total}"
);

// -------------------------------------------------------------------------
// TEST 5: Old Invoice Integrity (Old invoice must NOT change)
// -------------------------------------------------------------------------
echo "\n--- TEST 5: Old Invoice Integrity Check ---\n";
$oldSaleReloaded = Sale::find($sale1->id);
assertTest(
    (float)$oldSaleReloaded->tax_rate === 2.00 && (float)$oldSaleReloaded->tax_total === 200.00 && (float)$oldSaleReloaded->grand_total === 10200.00,
    "Old invoice created at 2% remains exactly 2% (Rs. 200 tax, Rs. 10,200 total)",
    "Old invoice #{$oldSaleReloaded->invoice_no}: tax_rate={$oldSaleReloaded->tax_rate}%, tax_total={$oldSaleReloaded->tax_total}"
);

// -------------------------------------------------------------------------
// TEST 6: Tax Disabled (Tax Enabled = OFF)
// -------------------------------------------------------------------------
echo "\n--- TEST 6: Tax Disabled by Admin (OFF) ---\n";
$disableRequest = Request::create('/settings/tax', 'POST', [
    'tax_enabled' => 0,
    'tax_rate' => 5.00,
]);
$taxController->update($disableRequest);

$invNo4 = 'TEST-CS-NOTAX-' . time();
$cashRequest4 = Request::create('/cash-sales/store', 'POST', [
    'invoice_no' => $invNo4,
    'wallet_id' => $wallet->id,
    'customer_id' => $customer->id,
    'salesman_id' => $owner->id,
    'date' => date('Y-m-d'),
    'rows' => [
        ['id' => $item->id, 'qty' => 2, 'price' => 5000],
    ],
    'grand_total' => 0,
    'received_amount' => 10000,
    'return_adjustment' => 0,
]);
$cashController->store($cashRequest4);
$sale4 = Sale::where('invoice_no', $invNo4)->first();

assertTest(
    (float)$sale4->tax_rate === 0.00 && (float)$sale4->tax_total === 0.00 && (float)$sale4->grand_total === 10000.00,
    "When tax is OFF, tax_rate is 0.00, tax_total is Rs. 0.00, grand_total is Rs. 10,000.00",
    "Actual tax_rate: {$sale4->tax_rate}%, tax_total: Rs. {$sale4->tax_total}, grand_total: Rs. {$sale4->grand_total}"
);

// -------------------------------------------------------------------------
// TEST 7: Debit Sale & POS flow tax integration
// -------------------------------------------------------------------------
echo "\n--- TEST 7: Debit Sale & POS flow tax integration ---\n";
// Re-enable 2% tax
$taxController->update(Request::create('/settings/tax', 'POST', ['tax_enabled' => 1, 'tax_rate' => 2.00]));

$debitController = app(DebitSalesController::class);
$invNoDebit = 'TEST-DS-' . time();
$debitRequest = Request::create('/debit-sales/store', 'POST', [
    'invoice_no' => $invNoDebit,
    'customer_id' => $customer->id,
    'salesman_id' => $owner->id,
    'date' => date('Y-m-d'),
    'rows' => [
        ['id' => $item->id, 'qty' => 1, 'price' => 5000],
    ],
    'grand_total' => 0,
    'received_amount' => 1000,
]);
$debitController->store($debitRequest);
$debitSale = Sale::where('invoice_no', $invNoDebit)->first();

assertTest(
    (float)$debitSale->tax_rate === 2.00 && (float)$debitSale->tax_total === 100.00 && (float)$debitSale->grand_total === 5100.00,
    "Debit sale automatically applied 2% tax (Rs. 100 tax on Rs. 5000, total Rs. 5100)",
    "Actual: tax_rate={$debitSale->tax_rate}%, tax_total=Rs. {$debitSale->tax_total}, grand_total=Rs. {$debitSale->grand_total}"
);

// -------------------------------------------------------------------------
// TEST 8: Invoice Edit Workflow (InvoiceEditService)
// -------------------------------------------------------------------------
echo "\n--- TEST 8: Invoice Edit Workflow (InvoiceEditService) ---\n";
$editService = app(InvoiceEditService::class);
$editPayload = [
    'customer_id' => $customer->id,
    'payment_mode' => 'Cash',
    'wallet_id' => $wallet->id,
    'sale_date' => date('Y-m-d'),
    'discount_total' => 1000.00, // Rs. 1000 discount on 10,000 -> 9,000 taxable -> 2% tax = 180 -> Grand Total = 9,180
    'items' => [
        ['item_id' => $item->id, 'qty' => 2, 'rate' => 5000],
    ],
];
$editedSale = $editService->updateInvoice(
    $sale1->id,
    $editPayload,
    $owner->id,
    "Testing tax recalculation with discount on edit",
    "127.0.0.1",
    $sale1->updated_at->toIso8601String()
);

assertTest(
    (float)$editedSale->tax_total === 180.00 && (float)$editedSale->grand_total === 9180.00,
    "Edited invoice authoritatively recalculated tax with discount (9,000 taxable * 2% = 180 tax, 9,180 total)",
    "Actual discount={$editedSale->discount_total}, tax_total={$editedSale->tax_total}, grand_total={$editedSale->grand_total}"
);

// -------------------------------------------------------------------------
// TEST 9: POS Terminal sale flow (SalesController@store)
// -------------------------------------------------------------------------
echo "\n--- TEST 9: POS Terminal sale flow (SalesController@store) ---\n";
$posController = app(SalesController::class);
$posRequest = Request::create('/sales/store', 'POST', [
    'cart' => [
        ['id' => $item->id, 'qty' => 2, 'price' => 5000],
    ],
    'amount_received' => 10200,
    'return_adjustment' => 0,
]);
$posResponse = $posController->store($posRequest);
$posData = $posResponse->getData(true);
$posInvoiceNo = $posData['invoice_no'] ?? null;
$posSale = Sale::where('invoice_no', $posInvoiceNo)->first();

assertTest(
    $posSale !== null && (float)$posSale->tax_rate === 2.00 && (float)$posSale->tax_total === 200.00 && (float)$posSale->grand_total === 10200.00,
    "POS sale automatically applied 2% tax (Rs. 200 tax on Rs. 10,000 subtotal, Rs. 10,200 grand total)",
    "POS Invoice: {$posInvoiceNo}, tax_rate: {$posSale?->tax_rate}%, tax_total: Rs. {$posSale?->tax_total}, grand_total: Rs. {$posSale?->grand_total}"
);

// -------------------------------------------------------------------------
// TEST 10: Validation Rejections
// -------------------------------------------------------------------------
echo "\n--- TEST 10: Validation Rejections (negative tax, invalid values) ---\n";
try {
    $taxController->update(Request::create('/settings/tax', 'POST', ['tax_enabled' => 1, 'tax_rate' => -5]));
    assertTest(false, "Negative tax rate must be rejected");
} catch (\Illuminate\Validation\ValidationException $e) {
    assertTest(true, "Negative tax rate rejected by validation rules", $e->getMessage());
}

try {
    $taxController->update(Request::create('/settings/tax', 'POST', ['tax_enabled' => 1, 'tax_rate' => 150]));
    assertTest(false, "Tax rate > 100% must be rejected");
} catch (\Illuminate\Validation\ValidationException $e) {
    assertTest(true, "Tax rate > 100% rejected by validation rules", $e->getMessage());
}

// -------------------------------------------------------------------------
// Summary
// -------------------------------------------------------------------------
echo "\n=================================================================\n";
echo "TEST RESULTS: {$passedCount} PASSED, {$failedCount} FAILED\n";
echo "=================================================================\n";

if ($failedCount === 0) {
    echo "🎉 ALL TAX SYSTEM TESTS PASSED SUCCESSFULLY!\n";
    exit(0);
} else {
    echo "❌ SOME TESTS FAILED.\n";
    exit(1);
}
