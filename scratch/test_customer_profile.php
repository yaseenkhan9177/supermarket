<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$db = 'vectabyte_tenant_00add513_8058_4d4c_b28f_d3441d6fbd65';
$pdo->exec("USE `$db`");

$stmt = $pdo->query("SELECT s.*, (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) as items_count FROM sales s WHERE s.customer_id = 1204");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Sales found for Customer 1204:\n";
print_r($rows);
