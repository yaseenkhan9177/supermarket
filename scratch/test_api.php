<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cust = \App\Models\Customer::create([
    'name' => 'ABC General Store',
    'phone' => '03001234567',
    'balance' => 25000,
    'credit_limit' => 50000,
    'status' => 'active'
]);

\App\Models\DebitSale::create([
    'invoice_no' => 'DS-INV-' . time() . '-1',
    'customer_id' => $cust->id,
    'invoice_date' => '2026-08-15',
    'due_date' => '2026-08-22',
    'gross_total' => 15000,
    'discount' => 0,
    'net_total' => 15000,
    'paid_amount' => 5000,
    'status' => 'partial'
]);

\App\Models\DebitSale::create([
    'invoice_no' => 'DS-INV-' . time() . '-2',
    'customer_id' => $cust->id,
    'invoice_date' => '2026-08-20',
    'due_date' => '2026-08-27',
    'gross_total' => 10000,
    'discount' => 0,
    'net_total' => 10000,
    'paid_amount' => 0,
    'status' => 'open'
]);

\App\Models\DebitSale::create([
    'invoice_no' => 'DS-INV-' . time() . '-3',
    'customer_id' => $cust->id,
    'invoice_date' => '2026-08-25',
    'due_date' => '2026-09-01',
    'gross_total' => 5000,
    'discount' => 0,
    'net_total' => 5000,
    'paid_amount' => 0,
    'status' => 'open'
]);

$controller = new \App\Http\Controllers\Store\ReceiptController();
$response = $controller->getPendingInvoices($cust->id);

echo "Customer ID: " . $cust->id . " (" . $cust->name . ")\n";
echo "Response JSON:\n" . json_encode($response->getData(), JSON_PRETTY_PRINT) . "\n";
