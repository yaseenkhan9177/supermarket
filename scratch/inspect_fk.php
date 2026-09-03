<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$db = 'vectabyte_tenant_00add513_8058_4d4c_b28f_d3441d6fbd65';
$pdo->exec("USE `$db`");

$stmt = $pdo->query("SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA='$db' AND TABLE_NAME='receipt_allocations' AND REFERENCED_TABLE_NAME IS NOT NULL");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
