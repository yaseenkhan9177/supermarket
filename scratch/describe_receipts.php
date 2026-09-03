<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$db = 'vectabyte_tenant_00add513_8058_4d4c_b28f_d3441d6fbd65';
$pdo->exec("USE `$db`");
$cols = $pdo->query('DESCRIBE receipts')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    echo "{$c['Field']} - Null: {$c['Null']} - Default: {$c['Default']}\n";
}
