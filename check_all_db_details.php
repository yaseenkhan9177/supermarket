<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$dbs = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);

foreach ($dbs as $db) {
    // Check if items table exists
    $has_items = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$db' AND table_name='items'")->fetchColumn();
    if (!$has_items) continue;

    $pdo->exec("USE `$db`");
    
    $total_items = $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
    $items_with_on_hand = $pdo->query("SELECT COUNT(*) FROM items WHERE on_hand > 0")->fetchColumn();
    
    // Check if batches table exists
    $has_batches = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$db' AND table_name='batches'")->fetchColumn();
    
    echo "========================================" . PHP_EOL;
    echo "DB: $db" . PHP_EOL;
    echo "========================================" . PHP_EOL;
    echo "  Total items: $total_items" . PHP_EOL;
    echo "  Items with on_hand > 0: $items_with_on_hand" . PHP_EOL;
    
    if ($has_batches) {
        $total_batches = $pdo->query("SELECT COUNT(*) FROM batches")->fetchColumn();
        $batch_qty = $pdo->query("SELECT SUM(quantity_available) FROM batches")->fetchColumn() ?: 0;
        
        $mismatches = $pdo->query("
            SELECT COUNT(*)
            FROM items i
            LEFT JOIN (SELECT item_id, SUM(quantity_available) as bsum FROM batches GROUP BY item_id) b ON b.item_id = i.id
            WHERE i.on_hand != COALESCE(b.bsum, 0)
        ")->fetchColumn();
        
        $oos_but_batches = $pdo->query("
            SELECT COUNT(*)
            FROM items i
            JOIN (SELECT item_id, SUM(quantity_available) as bsum FROM batches GROUP BY item_id HAVING SUM(quantity_available) > 0) b ON b.item_id = i.id
            WHERE i.on_hand <= 0
        ")->fetchColumn();
        
        echo "  Total batches: $total_batches" . PHP_EOL;
        echo "  Sum of batch quantity_available: $batch_qty" . PHP_EOL;
        echo "  Mismatches (on_hand != batch_sum): $mismatches" . PHP_EOL;
        echo "  Falsely Out of Stock (batches > 0 but on_hand <= 0): $oos_but_batches" . PHP_EOL;
        
        if ($oos_but_batches > 0) {
            $samples = $pdo->query("
                SELECT i.id, i.description, i.on_hand, b.bsum
                FROM items i
                JOIN (SELECT item_id, SUM(quantity_available) as bsum FROM batches GROUP BY item_id HAVING SUM(quantity_available) > 0) b ON b.item_id = i.id
                WHERE i.on_hand <= 0
                LIMIT 5
            ")->fetchAll(PDO::FETCH_ASSOC);
            echo "    Samples:" . PHP_EOL;
            foreach ($samples as $s) {
                echo "      ID: {$s['id']} | Name: {$s['description']} | on_hand: {$s['on_hand']} | batch_sum: {$s['bsum']}" . PHP_EOL;
            }
        }
    } else {
        echo "  [No batches table]" . PHP_EOL;
    }
    echo PHP_EOL;
}
