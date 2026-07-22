<?php
// Quick reset: restore MOMUL 21KG (ID 2207) batch to quantity_available=1.00
// (It was partially zeroed by the previous crashed run)
$pdo = new PDO('mysql:host=127.0.0.1;dbname=vectabyte_tenant_cfbccadc_3809_49c2_899e_de7bf04f7993', 'root', '');
$pdo->exec("UPDATE batches SET quantity_available = 1.00 WHERE item_id = 2207");
$pdo->exec("UPDATE items   SET on_hand           = 1.00 WHERE id       = 2207");
$pdo->exec("DELETE FROM stock_audit_logs WHERE sale_id = 99999");
echo "Reset complete." . PHP_EOL;
echo "MOMUL batch: " . $pdo->query("SELECT quantity_available FROM batches WHERE item_id=2207")->fetchColumn() . PHP_EOL;
echo "MOMUL on_hand: " . $pdo->query("SELECT on_hand FROM items WHERE id=2207")->fetchColumn() . PHP_EOL;
