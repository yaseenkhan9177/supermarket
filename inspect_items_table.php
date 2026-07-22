<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=vectabyte_tenant_cfbccadc_3809_49c2_899e_de7bf04f7993', 'root', '');

// Get column list of items table
$stmt = $pdo->query("SHOW COLUMNS FROM items");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== COLUMNS IN items ===" . PHP_EOL;
foreach ($columns as $col) {
    echo "  Column: {$col['Field']} | Type: {$col['Type']}" . PHP_EOL;
}

// Find any rows where on_hand is 0 but some other quantity/stock field is not 0
echo PHP_EOL . "=== ROWS IN items WITH OTHER STOCK INFO ===" . PHP_EOL;
$check_cols = [];
foreach ($columns as $col) {
    if (stripos($col['Field'], 'stock') !== false || stripos($col['Field'], 'qty') !== false || stripos($col['Field'], 'quantity') !== false || stripos($col['Field'], 'hand') !== false) {
        $check_cols[] = $col['Field'];
    }
}
echo "Fields containing stock/qty/hand: " . implode(', ', $check_cols) . PHP_EOL;

if (!empty($check_cols)) {
    $select_fields = implode(', ', $check_cols);
    $query = "SELECT id, description, $select_fields FROM items WHERE on_hand > 0 LIMIT 5";
    $results = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    echo PHP_EOL . "Sample rows where on_hand > 0:" . PHP_EOL;
    foreach ($results as $row) {
        print_r($row);
    }
}
