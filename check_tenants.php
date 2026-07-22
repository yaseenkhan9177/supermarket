<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=ownstore_db', 'root', '');
echo "=== TENANTS ===" . PHP_EOL;
$tenants = $pdo->query("SELECT * FROM tenants")->fetchAll(PDO::FETCH_ASSOC);
foreach ($tenants as $t) {
    print_r($t);
}

echo "=== DOMAINS ===" . PHP_EOL;
$domains = $pdo->query("SELECT * FROM domains")->fetchAll(PDO::FETCH_ASSOC);
foreach ($domains as $d) {
    print_r($d);
}
