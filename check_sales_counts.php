<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$tenant_dbs = [
    'vectabyte_tenant_4c9ba769_1cee_40d3_809e_bd55a4d585bb',
    'vectabyte_tenant_8d4bcbdf_4b10_4efd_8d3a_0a84e4fd323d',
    'vectabyte_tenant_cde8d12e_dd5f_42fe_a14b_0f5067806d87',
    'vectabyte_tenant_cfbccadc_3809_49c2_899e_de7bf04f7993',
];

foreach ($tenant_dbs as $db) {
    $pdo->exec("USE `$db`");
    $sales_count = $pdo->query("SELECT COUNT(*) FROM sales")->fetchColumn();
    $sale_items_count = $pdo->query("SELECT COUNT(*) FROM sale_items")->fetchColumn();
    echo "DB: $db | sales count: $sales_count | sale_items count: $sale_items_count" . PHP_EOL;
}
