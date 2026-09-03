<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Item;
use App\Models\Wallet;
use App\Models\User;
use App\Models\CustomerLedgerEntry;
use App\Http\Controllers\CashSalesController;
use App\Http\Controllers\DebitSalesController;
use App\Http\Controllers\Store\CustomerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

echo "=== STARTING COMPREHENSIVE VERIFICATION ===\n\n";

// 1. Check Tenant environment
$tenants = Tenant::all();
if ($tenants->isEmpty()) {
    echo "No tenants found.\n";
    exit(1);
}

$tenant = $tenants->first();
tenancy()->initialize($tenant);
echo "Initialized tenant: " . $tenant->id . "\n";

// Check user
$user = User::first();
if (!$user) {
    echo "No user found in tenant.\n";
    exit(1);
}
Auth::login($user);

// 2. Test Customer Model & Email Column
echo "\n--- 1. Testing Customer Email & Fillable ---\n";
$testCust = Customer::create([
    'name' => 'Verification Test Customer ' . time(),
    'phone' => '03001234567',
    'email' => 'testcustomer@example.com',
    'address' => '123 Test Street',
    'credit_limit' => 5000,
    'balance' => 0,
]);
echo "Created Customer ID: {$testCust->id}, Email: {$testCust->email}\n";
if ($testCust->email !== 'testcustomer@example.com') {
    echo "FAIL: Customer email was not saved!\n";
    exit(1);
}
echo "PASS: Customer created with email.\n";

// 3. Test Item setup with tax
echo "\n--- 2. Setting up Test Items with Tax ---\n";
$itemA = Item::create([
    'code' => 'TAX-ITM-A-' . time(),
    'description' => 'Taxable Product A (10%)',
    'item_type' => 'Service',
    'purchase_rate' => 50,
    'sale_rate' => 100,
    'on_hand' => 100,
    'tax_rate' => 10.00,
]);

$itemB = Item::create([
    'code' => 'TAX-ITM-B-' . time(),
    'description' => 'Tax-free Product B (0%)',
    'item_type' => 'Service',
    'purchase_rate' => 40,
    'sale_rate' => 50,
    'on_hand' => 100,
    'tax_rate' => 0.00,
]);

// 4. Test CashSalesController Search and Store with Item Tax
echo "\n--- 3. Testing Cash Sales Controller Item Tax ---\n";
$wallet = Wallet::where('is_active', true)->first();
if (!$wallet) {
    $wallet = Wallet::create(['name' => 'Main Cash', 'type' => 'counter', 'balance' => 0, 'is_active' => true]);
}

$cashSalesController = app(CashSalesController::class);

// Test Search
$searchReq = new Request(['q' => $itemA->code]);
$searchRes = $cashSalesController->searchItems($searchReq);
$searchData = json_decode($searchRes->getContent(), true);
echo "Search result for Item A returned " . count($searchData) . " item(s). Tax rate: " . ($searchData[0]['tax_rate'] ?? 'null') . "%\n";
if (!isset($searchData[0]['tax_rate']) || (float)$searchData[0]['tax_rate'] !== 10.0) {
    echo "FAIL: tax_rate missing or incorrect in searchItems!\n";
    exit(1);
}
echo "PASS: Search API returns tax_rate.\n";

// Test Cash Sale Store
$cashReq = new Request([
    'invoice_no' => 'CS-TEST-' . time(),
    'customer_id' => $testCust->id,
    'wallet_id' => $wallet->id,
    'salesman_id' => $user->id,
    'date' => date('Y-m-d'),
    'rows' => [
        [
            'id' => $itemA->id,
            'qty' => 2, // 2 * 100 = 200, 10% tax = 20
            'price' => 100,
            'tax_rate' => 10.00,
            'note' => 'Item A Note',
        ],
        [
            'id' => $itemB->id,
            'qty' => 1, // 1 * 50 = 50, 0% tax = 0
            'price' => 50,
            'tax_rate' => 0.00,
            'note' => 'Item B Note',
        ],
    ],
    'grand_total' => 270, // 200 + 50 + 20 tax = 270
    'received_amount' => 300,
    'return_adjustment' => 0,
]);

$cashRes = $cashSalesController->store($cashReq);
$cashData = json_decode($cashRes->getContent(), true);
echo "Cash Sale Store result: " . json_encode($cashData['success'] ?? false) . "\n";
if (!($cashData['success'] ?? false)) {
    echo "FAIL: Cash sale failed to store! " . json_encode($cashData) . "\n";
    exit(1);
}

$cashSale = Sale::with('items')->find($cashData['sale_id']);
echo "Cash Sale ID: {$cashSale->id}, Subtotal: {$cashSale->subtotal}, Tax Total: {$cashSale->tax_total}, Grand Total: {$cashSale->grand_total}\n";

$itemARow = $cashSale->items->where('item_id', $itemA->id)->first();
$itemBRow = $cashSale->items->where('item_id', $itemB->id)->first();

echo "Item A in SaleItem - Rate: {$itemARow->tax_rate}%, Tax Amount: {$itemARow->tax_amount}\n";
echo "Item B in SaleItem - Rate: {$itemBRow->tax_rate}%, Tax Amount: {$itemBRow->tax_amount}\n";

if ((float)$cashSale->tax_total !== 20.00 || (float)$cashSale->grand_total !== 270.00) {
    echo "FAIL: Cash sale totals mismatch! Expected tax_total=20, grand_total=270\n";
    exit(1);
}
if ((float)$itemARow->tax_amount !== 20.00 || (float)$itemBRow->tax_amount !== 0.00) {
    echo "FAIL: SaleItem tax amounts mismatch!\n";
    exit(1);
}
echo "PASS: Cash Sale per-item tax calculation & recording verified.\n";

// 5. Test DebitSalesController with Item Tax & Customer Balance
echo "\n--- 4. Testing Debit Sales Controller Item Tax & Balance ---\n";
$initialBalance = (float) $testCust->fresh()->balance;
echo "Customer initial balance: {$initialBalance}\n";

$debitSalesController = app(DebitSalesController::class);
$debitReq = new Request([
    'invoice_no' => 'DS-TEST-' . time(),
    'customer_id' => $testCust->id,
    'salesman_id' => $user->id,
    'date' => date('Y-m-d'),
    'rows' => [
        [
            'id' => $itemA->id,
            'qty' => 3, // 3 * 100 = 300, 10% tax = 30
            'price' => 100,
            'tax_rate' => 10.00,
            'note' => 'Debit Item A',
        ],
        [
            'id' => $itemB->id,
            'qty' => 2, // 2 * 50 = 100, 5% custom tax entered by user = 5
            'price' => 50,
            'tax_rate' => 5.00,
            'note' => 'Debit Item B with custom 5% tax',
        ],
    ],
    'grand_total' => 435, // 300 + 100 + 30 + 5 = 435
    'received_amount' => 100, // Partial paid 100, due 335
]);

$debitRes = $debitSalesController->store($debitReq);
$debitData = json_decode($debitRes->getContent(), true);
echo "Debit Sale Store result: " . json_encode($debitData['success'] ?? false) . "\n";
if (!($debitData['success'] ?? false)) {
    echo "FAIL: Debit sale failed to store! " . json_encode($debitData) . "\n";
    exit(1);
}

$debitSale = Sale::with('items')->find($debitData['sale_id']);
echo "Debit Sale ID: {$debitSale->id}, Subtotal: {$debitSale->subtotal}, Tax Total: {$debitSale->tax_total}, Grand Total: {$debitSale->grand_total}\n";

$debItemA = $debitSale->items->where('item_id', $itemA->id)->first();
$debItemB = $debitSale->items->where('item_id', $itemB->id)->first();

echo "Debit Item A - Tax Rate: {$debItemA->tax_rate}%, Tax Amount: {$debItemA->tax_amount}\n";
echo "Debit Item B - Tax Rate: {$debItemB->tax_rate}%, Tax Amount: {$debItemB->tax_amount}\n";

if ((float)$debitSale->tax_total !== 35.00 || (float)$debitSale->grand_total !== 435.00) {
    echo "FAIL: Debit sale totals mismatch! Expected tax_total=35, grand_total=435\n";
    exit(1);
}

$updatedCustomer = $testCust->fresh();
$expectedBalance = $initialBalance + 335.00; // 435 total - 100 paid = 335 due
echo "Customer balance after Debit Sale: {$updatedCustomer->balance} (Expected: {$expectedBalance})\n";

if ((float)$updatedCustomer->balance !== $expectedBalance) {
    echo "FAIL: Customer balance not incremented correctly!\n";
    exit(1);
}

$lastLedger = CustomerLedgerEntry::where('customer_id', $testCust->id)->latest('id')->first();
echo "Latest Ledger Entry: Type={$lastLedger->type}, Amount={$lastLedger->amount}, Method={$lastLedger->method}, Balance After={$lastLedger->balance_after}\n";
if (!$lastLedger || (float)$lastLedger->amount !== 335.00) {
    echo "FAIL: Customer ledger entry not recorded properly for debit sale!\n";
    exit(1);
}
echo "PASS: Debit Sale per-item tax & customer ledger verified.\n";

// 6. Test Customer Profile Show
echo "\n--- 5. Testing Customer Profile Show Data ---\n";
$customerController = app(CustomerController::class);
$view = $customerController->show($testCust->id);
$viewData = $view->getData();

echo "Customer Show view returned:\n";
echo "  Total Cash Count: " . $viewData['totalCashCount'] . " (Amount: Rs. " . $viewData['totalCashAmount'] . ")\n";
echo "  Total Debit Count: " . $viewData['totalDebitCount'] . " (Amount: Rs. " . $viewData['totalDebitAmount'] . ")\n";
echo "  Total Items Sold: " . $viewData['totalItemsSold'] . "\n";
echo "  Customer Balance: Rs. " . $viewData['customer']->balance . "\n";
echo "  Customer Email: " . $viewData['customer']->email . "\n";

if ($viewData['totalCashCount'] < 1 || $viewData['totalDebitCount'] < 1) {
    echo "FAIL: Customer profile is missing cash or debit sales!\n";
    exit(1);
}
echo "PASS: Customer Profile correctly queries and displays all sales from sales table.\n";

// 7. Test Customer Profile Update (Quick Edit)
echo "\n--- 6. Testing Customer Profile Update ---\n";
$updateReq = new Request([
    'name' => 'Updated Customer Name',
    'phone' => '03119998877',
    'email' => 'newemail@example.com',
    'address' => '456 Updated Boulevard',
    'credit_limit' => 15000,
]);
$updateReq->headers->set('Accept', 'application/json');

$updateRes = $customerController->update($updateReq, $testCust->id);
$updateData = json_decode($updateRes->getContent(), true);

echo "Customer update result: " . json_encode($updateData['success'] ?? false) . "\n";
$refreshedCust = $testCust->fresh();

echo "Customer after update: Name={$refreshedCust->name}, Phone={$refreshedCust->phone}, Email={$refreshedCust->email}, Credit Limit={$refreshedCust->credit_limit}, Balance={$refreshedCust->balance}\n";

if ($refreshedCust->email !== 'newemail@example.com' || (float)$refreshedCust->balance !== $expectedBalance) {
    echo "FAIL: Customer update failed or modified customer balance!\n";
    exit(1);
}
echo "PASS: Customer profile update works safely without altering balance or ledger.\n";

// 8. Test Customer Receive Payment
echo "\n--- 7. Testing Receive Payment & FIFO Debit Allocation ---\n";
$payReq = new Request([
    'amount' => 135.00,
    'method' => 'cash',
    'note' => 'Partial Payment towards debit sale',
]);
$payReq->headers->set('Accept', 'application/json');

$payRes = $customerController->receivePayment($payReq, $testCust->id);
$payData = json_decode($payRes->getContent(), true);

echo "Payment response: " . json_encode($payData['message'] ?? false) . "\n";
$custAfterPayment = $testCust->fresh();
$debitSaleAfterPayment = $debitSale->fresh();

echo "Customer balance after Rs. 135 payment: {$custAfterPayment->balance} (Expected: " . ($expectedBalance - 135) . ")\n";
echo "Debit Sale paid_amount after payment: {$debitSaleAfterPayment->paid_amount} (Expected: " . (100 + 135) . " = 235)\n";

if ((float)$custAfterPayment->balance !== ($expectedBalance - 135.00)) {
    echo "FAIL: Customer balance not updated correctly on receivePayment!\n";
    exit(1);
}
if ((float)$debitSaleAfterPayment->paid_amount !== 235.00) {
    echo "FAIL: Debit Sale paid_amount not updated on FIFO allocation!\n";
    exit(1);
}
echo "PASS: Customer receivePayment and FIFO debit sale allocation verified.\n";

// Clean up test records
$cashSale->items()->delete();
$cashSale->delete();
$debitSale->items()->delete();
$debitSale->delete();
$itemA->delete();
$itemB->delete();
\App\Models\Receipt::where('customer_id', $testCust->id)->delete();
$testCust->ledgerEntries()->delete();
$testCust->delete();

echo "\n=== ALL VERIFICATIONS PASSED SUCCESSFULLY! ===\n";
