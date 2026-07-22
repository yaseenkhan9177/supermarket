<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=vectabyte_tenant_cfbccadc_3809_49c2_899e_de7bf04f7993', 'root', '');

$has_purchase_items = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='vectabyte_tenant_cfbccadc_3809_49c2_899e_de7bf04f7993' AND table_name='purchase_items'")->fetchColumn();

if ($has_purchase_items) {
    $total_purchase_items = $pdo->query("SELECT COUNT(*) FROM purchase_items")->fetchColumn();
    echo "Total purchase_items rows: $total_purchase_items" . PHP_EOL;

    // Join purchase_items with items and check their stock
    $purchased_stock = $pdo->query("
        SELECT pi.item_id, i.description, SUM(pi.qty) as purchased_qty, i.on_hand
        FROM purchase_items pi
        JOIN items i ON i.id = pi.item_id
        GROUP BY pi.item_id, i.description, i.on_hand
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo "Sample purchased items:" . PHP_EOL;
    foreach ($purchased_stock as $ps) {
        echo "  Item ID: {$ps['item_id']} | Name: {$ps['description']} | Purchased Qty: {$ps['purchased_qty']} | on_hand: {$ps['on_hand']}" . PHP_EOL;
    }
} else {
    echo "No purchase_items table" . PHP_EOL;
}
