<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$dbs = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);

foreach ($dbs as $db) {
    if (strpos($db, 'tenant') !== false || $db === 'ownstore_db') {
        echo "=== DB: {$db} ===\n";
        foreach (['sales', 'sale_items', 'cash_sales', 'cash_sale_items', 'debit_sales', 'debit_sale_items', 'customers', 'customer_ledger_entries'] as $tbl) {
            $exists = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='{$db}' AND table_name='{$tbl}'")->fetchColumn();
            if ($exists) {
                $cnt = $pdo->query("SELECT COUNT(*) FROM `{$db}`.`{$tbl}`")->fetchColumn();
                echo "  {$tbl}: {$cnt}\n";
            } else {
                echo "  {$tbl}: NOT FOUND\n";
            }
        }
    }
}
