<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$dbs = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);

foreach ($dbs as $db) {
    if (strpos($db, 'tenant') === false) continue;
    
    // Check if items table exists
    $has_items = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$db' AND table_name='items'")->fetchColumn();
    // Check tables in this db
    $tables = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema='$db'")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "DB: $db | has_items: " . ($has_items ? 'YES' : 'NO') . PHP_EOL;
    echo "  Tables: " . implode(', ', $tables) . PHP_EOL . PHP_EOL;
}
