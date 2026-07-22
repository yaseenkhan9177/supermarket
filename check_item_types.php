<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=vectabyte_tenant_cfbccadc_3809_49c2_899e_de7bf04f7993', 'root', '');
$res = $pdo->query("SELECT item_type, COUNT(*) as count FROM items GROUP BY item_type")->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
