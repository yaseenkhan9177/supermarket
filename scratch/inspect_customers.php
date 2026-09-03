<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$db = 'vectabyte_tenant_00add513_8058_4d4c_b28f_d3441d6fbd65';
$pdo->exec("USE `$db`");

echo "=== COLUMNS OF customers ===\n";
$stmt = $pdo->query("DESCRIBE customers");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$r['Field']} ({$r['Type']})\n";
}
