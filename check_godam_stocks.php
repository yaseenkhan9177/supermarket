<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$tenant_dbs = [
    'vectabyte_tenant_4c9ba769_1cee_40d3_809e_bd55a4d585bb',
    'vectabyte_tenant_cde8d12e_dd5f_42fe_a14b_0f5067806d87',
];

foreach ($tenant_dbs as $db) {
    $pdo->exec("USE `$db`");
    echo "========================================" . PHP_EOL;
    echo "DATABASE: $db" . PHP_EOL;
    echo "========================================" . PHP_EOL;

    // Check godam_stocks
    $has_godam = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$db' AND table_name='godam_stocks'")->fetchColumn();
    if ($has_godam) {
        $total_godam = $pdo->query("SELECT COUNT(*) FROM godam_stocks")->fetchColumn();
        $godam_qty = $pdo->query("SELECT SUM(quantity) FROM godam_stocks")->fetchColumn() ?: 0;
        echo "  Total godam_stocks rows: $total_godam" . PHP_EOL;
        echo "  Sum of godam_stocks quantity: $godam_qty" . PHP_EOL;

        // Show items in godam_stocks with quantity > 0
        $godam_items = $pdo->query("
            SELECT gs.item_id, i.description, gs.quantity, i.on_hand, gs.godam_id
            FROM godam_stocks gs
            JOIN items i ON i.id = gs.item_id
            WHERE gs.quantity > 0
            LIMIT 10
        ")->fetchAll(PDO::FETCH_OBJ);
        
        if (count($godam_items) > 0) {
            echo "  Items with stock in godam_stocks:" . PHP_EOL;
            foreach ($godam_items as $r) {
                echo sprintf("    Item ID:%-5s | %-30s | godam_id:%s | godam_qty:%-8s | on_hand:%s",
                    $r->item_id, substr($r->description, 0, 30), $r->godam_id, $r->quantity, $r->on_hand) . PHP_EOL;
            }
        } else {
            echo "  No items with positive quantity in godam_stocks" . PHP_EOL;
        }
    } else {
        echo "  [No godam_stocks table]" . PHP_EOL;
    }
    echo PHP_EOL;
}
