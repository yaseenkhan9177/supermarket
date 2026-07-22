<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$dbs = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);
foreach ($dbs as $db) {
    // Check if this db has an 'items' table (tenant db)
    $check = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$db' AND table_name='items'")->fetchColumn();
    if ($check > 0) {
        echo "[HAS items] $db" . PHP_EOL;
    } else {
        echo "            $db" . PHP_EOL;
    }
}
