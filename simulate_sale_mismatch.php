<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=vectabyte_tenant_cfbccadc_3809_49c2_899e_de7bf04f7993', 'root', '');

$items = [2207, 2494, 2496];

echo "=== STOCK BEFORE POS SALE SIMULATION ===" . PHP_EOL;
foreach ($items as $id) {
    $item = $pdo->query("SELECT description, on_hand FROM items WHERE id=$id")->fetch(PDO::FETCH_ASSOC);
    $batch_sum = $pdo->query("SELECT SUM(quantity_available) FROM batches WHERE item_id=$id")->fetchColumn();
    echo "ID: $id | Name: {$item['description']} | on_hand: {$item['on_hand']} | batches sum: $batch_sum" . PHP_EOL;
}

// Replicating POS sale decrement logic (bypassing batches)
$sales = [
    2207 => 1.00, // Sell all MOMUL 21KG
    2494 => 5.00, // Sell 5 BAHBUTONG BOP1 48KG
    2496 => 72.00, // Sell all GOLD 5KG to make on_hand 0
];

echo PHP_EOL . "=== SIMULATING SALES ===" . PHP_EOL;
foreach ($sales as $id => $qty) {
    $pdo->exec("UPDATE items SET on_hand = on_hand - $qty WHERE id=$id");
    echo "Decremented items.on_hand by $qty for Item ID: $id" . PHP_EOL;
}

echo PHP_EOL . "=== STOCK AFTER POS SALE SIMULATION (THE BUG IN ACTION) ===" . PHP_EOL;
foreach ($items as $id) {
    $item = $pdo->query("SELECT description, on_hand FROM items WHERE id=$id")->fetch(PDO::FETCH_ASSOC);
    $batch_sum = $pdo->query("SELECT SUM(quantity_available) FROM batches WHERE item_id=$id")->fetchColumn();
    echo "ID: $id | Name: {$item['description']} | on_hand: {$item['on_hand']} | batches sum: $batch_sum" . PHP_EOL;
}

// Revert the changes to keep the database in its original state
echo PHP_EOL . "=== REVERTING CHANGES ===" . PHP_EOL;
foreach ($sales as $id => $qty) {
    $pdo->exec("UPDATE items SET on_hand = on_hand + $qty WHERE id=$id");
    echo "Reverted items.on_hand for Item ID: $id" . PHP_EOL;
}
