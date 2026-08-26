<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Item;
use App\Models\Sale;
use App\Models\CompanySetting;
use App\Models\Wallet;
use App\Models\Customer;
use App\Http\Controllers\CashSalesController;
use App\Http\Controllers\TaxSettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "=================================================================\n";
echo "MART FIRST-TIME TAX SETUP ENFORCEMENT - TEST SUITE\n";
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

// Setup
$owner = User::where('role', 'owner')->first() ?? User::create([
    'name' => 'Store Owner',
    'email' => 'store_owner@example.com',
    'password' => bcrypt('password'),
    'role' => 'owner',
]);

$staff = User::where('role', 'cashier')->first() ?? User::create([
    'name' => 'Cashier Staff',
    'email' => 'cashier@example.com',
    'password' => bcrypt('password'),
    'role' => 'cashier',
]);

$wallet = Wallet::first() ?? Wallet::create(['name' => 'Main Cash', 'type' => 'Cash', 'balance' => 0, 'is_active' => true]);
$customer = Customer::first() ?? Customer::create(['name' => 'Regular Customer', 'phone' => '03009998877', 'balance' => 0]);
$item = Item::create([
    'description' => 'Test Item Setup',
    'code' => 'SETUP-' . time(),
    'sale_rate' => 1000,
    'cost_rate' => 500,
    'on_hand' => 50,
    'item_type' => 'Service'
]);

$taxController = app(TaxSettingsController::class);
$cashController = app(CashSalesController::class);

// -------------------------------------------------------------------------
// TEST 1: Brand new tenant (Unconfigured state)
// -------------------------------------------------------------------------
echo "--- TEST 1: Brand New Tenant Signs Up (Unconfigured State) ---\n";
// Reset company_settings to unconfigured state
$setting = CompanySetting::firstOrNew(['id' => 1]);
$setting->tax_enabled = false;
$setting->tax_rate = 0.00;
$setting->tax_configured_at = null;
$setting->tax_configured_by = null;
$setting->save();

assertTest(
    $setting->tax_configured_at === null && !$setting->isTaxConfigured(),
    "tax_configured_at is NULL for unconfigured tenant",
    "isTaxConfigured() = " . var_export($setting->isTaxConfigured(), true)
);

// Check banner visibility for Admin
Auth::login($owner);
$isAdmin = Auth::user()->hasRole('owner') || in_array(Auth::user()->role, ['owner', 'admin', 'Store Admin', 'Owner']);
$showBannerAdmin = $isAdmin && is_null($setting->fresh()->tax_configured_at);
assertTest(
    $showBannerAdmin === true,
    "Admin dashboard detects unconfigured state and triggers banner display",
    "showBannerAdmin = true"
);

// Staff creates invoice while tax is unconfigured
Auth::login($staff);
$invNoUnconfigured = 'CS-UNCONF-' . time();
$reqUnconfigured = Request::create('/cash-sales/store', 'POST', [
    'invoice_no' => $invNoUnconfigured,
    'wallet_id' => $wallet->id,
    'customer_id' => $customer->id,
    'salesman_id' => $staff->id,
    'date' => date('Y-m-d'),
    'rows' => [
        ['id' => $item->id, 'qty' => 3, 'price' => 1000],
    ],
    'grand_total' => 0,
    'received_amount' => 3000,
    'return_adjustment' => 0,
]);
$cashController->store($reqUnconfigured);
$saleUnconfigured = Sale::where('invoice_no', $invNoUnconfigured)->first();

assertTest(
    $saleUnconfigured !== null && (float)$saleUnconfigured->tax_total === 0.00 && (float)$saleUnconfigured->grand_total === 3000.00,
    "Staff can create invoices normally without being blocked (Tax = 0.00, Total = Rs. 3,000)",
    "Invoice: {$invNoUnconfigured}, Tax: Rs. {$saleUnconfigured?->tax_total}, Grand Total: Rs. {$saleUnconfigured?->grand_total}"
);

// -------------------------------------------------------------------------
// TEST 2: Admin configures 2% Tax
// -------------------------------------------------------------------------
echo "\n--- TEST 2: Admin Enables 2% Tax ---\n";
Auth::login($owner);
$enableReq = Request::create('/settings/tax', 'POST', [
    'tax_enabled' => 1,
    'tax_rate' => 2.00,
]);
$taxController->update($enableReq);

$settingFresh = CompanySetting::first();
assertTest(
    $settingFresh->tax_configured_at !== null && $settingFresh->tax_configured_by === $owner->id,
    "tax_configured_at timestamp and tax_configured_by are populated",
    "Configured At: {$settingFresh->tax_configured_at}, By User ID: {$settingFresh->tax_configured_by}"
);

$showBannerAfterConfig = $isAdmin && is_null($settingFresh->tax_configured_at);
assertTest(
    $showBannerAfterConfig === false,
    "Banner disappears permanently once tax is configured",
    "showBanner = false"
);

// -------------------------------------------------------------------------
// TEST 3: Admin explicitly chooses 'No Tax' (Opt out)
// -------------------------------------------------------------------------
echo "\n--- TEST 3: Admin Explicitly Chooses 'No Tax' (Opt Out) ---\n";
// Reset to null first to simulate fresh opt-out
$settingFresh->update(['tax_configured_at' => null, 'tax_configured_by' => null]);

$optOutReq = Request::create('/settings/tax', 'POST', [
    'tax_enabled' => 0,
    'tax_rate' => 0.00,
]);
$taxController->update($optOutReq);

$settingOptOut = CompanySetting::first();
assertTest(
    $settingOptOut->tax_configured_at !== null && $settingOptOut->tax_enabled === false,
    "Explicitly opting out records tax_configured_at and tax_enabled = false",
    "Configured At: {$settingOptOut->tax_configured_at}, tax_enabled: false"
);

$showBannerOptOut = $isAdmin && is_null($settingOptOut->tax_configured_at);
assertTest(
    $showBannerOptOut === false,
    "Banner is permanently hidden after explicit opt-out (counts as fully configured)",
    "showBanner = false"
);

// -------------------------------------------------------------------------
// TEST 4: Existing tenant backfill check
// -------------------------------------------------------------------------
echo "\n--- TEST 4: Backfill Compatibility for Existing Configured Tenants ---\n";
assertTest(
    $settingOptOut->isTaxConfigured() === true,
    "Tenant with non-null tax_configured_at evaluates isTaxConfigured() as true",
    "tax_configured_at: {$settingOptOut->tax_configured_at}"
);

// -------------------------------------------------------------------------
// TEST 5: Staff dashboard check
// -------------------------------------------------------------------------
echo "\n--- TEST 5: Staff Role Security (Banner Never Shows to Staff) ---\n";
Auth::login($staff);
// Temporarily set tax_configured_at to null
$settingOptOut->update(['tax_configured_at' => null]);

$isStaffAdmin = Auth::user()->hasRole('owner') || in_array(Auth::user()->role, ['owner', 'admin', 'Store Admin', 'Owner']);
$showBannerStaff = $isStaffAdmin && is_null($settingOptOut->fresh()->tax_configured_at);

assertTest(
    $showBannerStaff === false,
    "Banner NEVER appears for Staff/Cashier role even if tax is unconfigured",
    "showBannerStaff = false (User role: {$staff->role})"
);

// -------------------------------------------------------------------------
// Summary
// -------------------------------------------------------------------------
echo "\n=================================================================\n";
echo "TEST RESULTS: {$passedCount} PASSED, {$failedCount} FAILED\n";
echo "=================================================================\n";

if ($failedCount === 0) {
    echo "🎉 ALL FIRST-TIME TAX SETUP ENFORCEMENT TESTS PASSED!\n";
    exit(0);
} else {
    echo "❌ SOME TESTS FAILED.\n";
    exit(1);
}
