<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=vectabyte_tenant_cfbccadc_3809_49c2_899e_de7bf04f7993', 'root', '');

echo "=== CURRENT TENANT STOCK INFO ===" . PHP_EOL;

$total_items = $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
echo "Total items: $total_items" . PHP_EOL;

$items_with_on_hand = $pdo->query("SELECT COUNT(*) FROM items WHERE on_hand > 0")->fetchColumn();
echo "Items with on_hand > 0: $items_with_on_hand" . PHP_EOL;

$total_batches = $pdo->query("SELECT COUNT(*) FROM batches")->fetchColumn();
echo "Total batches: $total_batches" . PHP_EOL;

$batches_with_qty = $pdo->query("SELECT COUNT(*) FROM batches WHERE quantity_available > 0")->fetchColumn();
echo "Batches with quantity_available > 0: $batches_with_qty" . PHP_EOL;

// Check if any items have batches with quantity > 0 but on_hand = 0
$mismatches = $pdo->query("
    SELECT i.id, i.description, i.on_hand, COALESCE(b.bsum, 0) as batch_sum
    FROM items i
    JOIN (SELECT item_id, SUM(quantity_available) as bsum FROM batches GROUP BY item_id HAVING SUM(quantity_available) > 0) b ON b.item_id = i.id
    WHERE i.on_hand != b.bsum
")->fetchAll(PDO::FETCH_ASSOC);

echo "Mismatches count: " . count($mismatches) . PHP_EOL;
foreach ($mismatches as $m) {
    echo "  ID: {$m['id']} | Name: {$m['description']} | on_hand: {$m['on_hand']} | batch_sum: {$m['batch_sum']}" . PHP_EOL;
}

// Print some items with on_hand > 0
echo PHP_EOL . "=== ITEMS WITH STOCK ===" . PHP_EOL;
$in_stock = $pdo->query("SELECT id, description, on_hand FROM items WHERE on_hand > 0 LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
foreach ($in_stock as $item) {
    $batches = $pdo->query("SELECT id, batch_no, quantity_available FROM batches WHERE item_id={$item['id']}")->fetchAll(PDO::FETCH_ASSOC);
    echo "  ID: {$item['id']} | Name: {$item['description']} | on_hand: {$item['on_hand']}" . PHP_EOL;
    foreach ($batches as $batch) {
        echo "    - Batch ID: {$batch['id']} | No: {$batch['batch_no']} | Qty: {$batch['quantity_available']}" . PHP_EOL;
    }
}
