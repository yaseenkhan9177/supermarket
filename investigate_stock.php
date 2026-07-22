<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');

$tenant_dbs = [
    'vectabyte_tenant_4c9ba769_1cee_40d3_809e_bd55a4d585bb',
    'vectabyte_tenant_cde8d12e_dd5f_42fe_a14b_0f5067806d87',
];

foreach ($tenant_dbs as $db) {
    $pdo->exec("USE `$db`");
    echo PHP_EOL . "========================================" . PHP_EOL;
    echo "DATABASE: $db" . PHP_EOL;
    echo "========================================" . PHP_EOL;

    // Total items and stock distribution
    $dist = $pdo->query("
        SELECT
            COUNT(*) as total_items,
            SUM(CASE WHEN on_hand > 0 THEN 1 ELSE 0 END) as items_with_on_hand,
            SUM(CASE WHEN on_hand <= 0 THEN 1 ELSE 0 END) as items_zero_or_neg_on_hand,
            SUM(CASE WHEN on_hand < 0 THEN 1 ELSE 0 END) as items_negative_on_hand
        FROM items
    ")->fetch(PDO::FETCH_OBJ);
    echo "  Total items: " . $dist->total_items . PHP_EOL;
    echo "  Items where on_hand > 0:  " . $dist->items_with_on_hand . PHP_EOL;
    echo "  Items where on_hand <= 0: " . $dist->items_zero_or_neg_on_hand . " <-- these show as OOS" . PHP_EOL;
    echo "  Items where on_hand < 0:  " . $dist->items_negative_on_hand . " <-- went negative (oversold)" . PHP_EOL;

    // Batch totals
    $bat = $pdo->query("SELECT COUNT(*) as batch_rows, SUM(quantity_available) as total_qty FROM batches")->fetch(PDO::FETCH_OBJ);
    echo "  Total batch rows: " . $bat->batch_rows . PHP_EOL;
    echo "  Total batch qty:  " . $bat->total_qty . PHP_EOL;

    // Sample items that are showing OOS (on_hand <= 0) -- these are the ones the user is complaining about
    echo PHP_EOL . "  --- SAMPLE ITEMS SHOWN AS OOS (on_hand <= 0) ---" . PHP_EOL;
    $oos_items = $pdo->query("
        SELECT i.id, i.description, i.on_hand, COALESCE(b.bsum,0) as batch_sum, i.item_type
        FROM items i
        LEFT JOIN (SELECT item_id, SUM(quantity_available) as bsum FROM batches GROUP BY item_id) b ON b.item_id = i.id
        WHERE i.on_hand <= 0
        LIMIT 8
    ")->fetchAll(PDO::FETCH_OBJ);
    foreach ($oos_items as $r) {
        echo sprintf("    ID:%-5s | %-32s | on_hand=%-8s | batch_sum=%-8s | type=%s",
            $r->id, substr($r->description, 0, 32), $r->on_hand, $r->batch_sum, $r->item_type) . PHP_EOL;
    }

    // Sample items that DO have stock (on_hand > 0) and ARE in batches
    echo PHP_EOL . "  --- SAMPLE ITEMS WITH STOCK (on_hand > 0) ---" . PHP_EOL;
    $in_stock = $pdo->query("
        SELECT i.id, i.description, i.on_hand, COALESCE(b.bsum,0) as batch_sum
        FROM items i
        LEFT JOIN (SELECT item_id, SUM(quantity_available) as bsum FROM batches GROUP BY item_id) b ON b.item_id = i.id
        WHERE i.on_hand > 0
        LIMIT 5
    ")->fetchAll(PDO::FETCH_OBJ);
    foreach ($in_stock as $r) {
        echo sprintf("    ID:%-5s | %-32s | on_hand=%-8s | batch_sum=%s",
            $r->id, substr($r->description, 0, 32), $r->on_hand, $r->batch_sum) . PHP_EOL;
    }

    // Check sale_items - how many times was stock deducted?
    $sales_count = $pdo->query("SELECT COUNT(*) FROM sale_items")->fetchColumn();
    $sales_total_qty = $pdo->query("SELECT SUM(qty) FROM sale_items")->fetchColumn();
    echo PHP_EOL . "  --- SALES HISTORY ---" . PHP_EOL;
    echo "  sale_items rows: $sales_count" . PHP_EOL;
    echo "  Total qty sold (sale_items): $sales_total_qty" . PHP_EOL;

    // Check how many audit logs exist (i.e., was FIFO service used?)
    $has_audit = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$db' AND table_name='stock_audit_logs'")->fetchColumn();
    if ($has_audit) {
        $audit_count = $pdo->query("SELECT COUNT(*) FROM stock_audit_logs")->fetchColumn();
        $audit_deduct = $pdo->query("SELECT COUNT(*) FROM stock_audit_logs WHERE action='deduct'")->fetchColumn();
        echo "  stock_audit_log rows: $audit_count (deduct rows: $audit_deduct)" . PHP_EOL;
    } else {
        echo "  [no stock_audit_logs table]" . PHP_EOL;
    }

    // Negative on_hand items detail
    echo PHP_EOL . "  --- ITEMS WITH NEGATIVE on_hand (oversold via SalesController) ---" . PHP_EOL;
    $neg = $pdo->query("
        SELECT i.id, i.description, i.on_hand, COALESCE(b.bsum,0) as batch_sum
        FROM items i
        LEFT JOIN (SELECT item_id, SUM(quantity_available) as bsum FROM batches GROUP BY item_id) b ON b.item_id = i.id
        WHERE i.on_hand < 0
        LIMIT 5
    ")->fetchAll(PDO::FETCH_OBJ);
    if (count($neg) === 0) {
        echo "    (none)" . PHP_EOL;
    }
    foreach ($neg as $r) {
        echo sprintf("    ID:%-5s | %-32s | on_hand=%-8s | batch_sum=%s",
            $r->id, substr($r->description, 0, 32), $r->on_hand, $r->batch_sum) . PHP_EOL;
    }
}
