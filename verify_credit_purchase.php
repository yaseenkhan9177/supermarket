<?php

use App\Http\Controllers\PurchaseController;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\User;
use App\Models\Purchase;
use App\Models\SupplierLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Starting Credit Purchase Verification...\n";

// 1. Setup Test Data
$user = User::first();
if (!$user) {
    echo "Creating Test User...\n";
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password')
    ]);
}
Auth::login($user);

// Create Test Supplier
$supplier = Supplier::create([
    'name' => 'Test Supplier ' . time(),
    'phone' => '999' . time(),
    'current_balance' => 0
]);
echo "Created Supplier: {$supplier->name} (Balance: {$supplier->current_balance})\n";

// Create Test Item
$item = Item::create([
    'description' => 'Test Item ' . time(),
    'code' => 'TEST-' . time(),
    'sale_rate' => 100,
    'on_hand' => 10
]);
echo "Created Item: {$item->description} (Stock: {$item->on_hand})\n";

// 2. Prepare Request
$qty = 5;
$rate = 50; // Cost Price
$total = $qty * $rate; // 250

$data = [
    'payment_type' => 'Credit',
    'supplier_id' => $supplier->id,
    'purchase_no' => 'PUR-TEST-' . time(),
    'vendor_bill_no' => 'INV-' . time(),
    'purchase_date' => date('Y-m-d'),
    'due_date' => date('Y-m-d', strtotime('+30 days')),
    'items' => [
        [
            'item_id' => $item->id,
            'qty' => $qty,
            'rate' => $rate
        ]
    ],
    // 'payment_type' is already set above but Request::create might need body parsing
];

$request = Request::create('/purchases/store', 'POST', $data);
// Manually set user resolver for the request if needed, but Auth::login should work with global helper

// 3. Execute Controller
$controller = app(PurchaseController::class);

try {
    echo "Sending Purchase Request...\n";
    $response = $controller->store($request);

    // 4. Verify Results

    // Refresh Models
    $supplier->refresh();
    $item->refresh();

    echo "--------------------------------------------------\n";
    echo "VERIFICATION RESULTS:\n";
    echo "--------------------------------------------------\n";

    // Stock Check
    $expectedStock = 10 + $qty;
    if ($item->on_hand == $expectedStock) {
        echo "[PASS] Stock Updated: {$item->on_hand} (Expected: {$expectedStock})\n";
    } else {
        echo "[FAIL] Stock Incorrect: {$item->on_hand} (Expected: {$expectedStock})\n";
    }

    // Supplier Balance Check
    $expectedBalance = $total; // 250
    if ($supplier->current_balance == $expectedBalance) {
        echo "[PASS] Supplier Balance Updated: {$supplier->current_balance} (Expected: {$expectedBalance})\n";
    } else {
        echo "[FAIL] Supplier Balance Incorrect: {$supplier->current_balance} (Expected: {$expectedBalance})\n";
    }

    // Ledger Check
    $ledger = SupplierLedger::where('supplier_id', $supplier->id)->latest()->first();
    if ($ledger && $ledger->credit == $total && $ledger->reference_type == 'purchase') {
        echo "[PASS] Ledger Entry Created: Credit {$ledger->credit}\n";
    } else {
        echo "[FAIL] Ledger Entry Missing or Incorrect\n";
        if ($ledger) print_r($ledger->toArray());
    }

    // Purchase Record Check
    $purchase = Purchase::where('purchase_no', $data['purchase_no'])->first();
    if ($purchase) {
        echo "[PASS] Purchase Record Created: ID {$purchase->id}\n";
    } else {
        echo "[FAIL] Purchase Record Not Found\n";
    }
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

// Cleanup
echo "Cleaning up test data...\n";
if (isset($purchase)) {
    DB::table('purchase_items')->where('purchase_id', $purchase->id)->delete();
    $purchase->delete();
}
if (isset($ledger)) $ledger->delete();
$supplier->delete();
$item->delete();
