<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$db = 'vectabyte_tenant_00add513_8058_4d4c_b28f_d3441d6fbd65';
$pdo->exec("USE `$db`");

echo "\n=== SAMPLE SALES ===\n";
$sales = $pdo->query("SELECT id, invoice_no, customer_id, sale_date, subtotal, tax_total, grand_total, paid_amount FROM sales LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($sales);

echo "\n=== SAMPLE CUSTOMERS (with sales) ===\n";
$custs = $pdo->query("SELECT DISTINCT c.id, c.name, c.phone, c.balance FROM customers c JOIN sales s ON s.customer_id = c.id LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($custs);

echo "\n=== SAMPLE DEBIT SALES ===\n";
$debitSales = $pdo->query("SELECT * FROM debit_sales LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
print_r($debitSales);
