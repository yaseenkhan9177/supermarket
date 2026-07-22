<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=vectabyte_tenant_cfbccadc_3809_49c2_899e_de7bf04f7993', 'root', '');
$batches = $pdo->query("SELECT * FROM batches")->fetchAll(PDO::FETCH_ASSOC);

echo "=== ALL BATCHES ===" . PHP_EOL;
foreach ($batches as $b) {
    echo "ID: {$b['id']} | Item ID: {$b['item_id']} | Batch No: {$b['batch_no']} | Qty Avail: {$b['quantity_available']} | Cost: {$b['cost_price']} | Sale: {$b['sale_price']}" . PHP_EOL;
}
