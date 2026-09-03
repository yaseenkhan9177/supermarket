<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$dbs = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);

foreach ($dbs as $db) {
    if (strpos($db, 'tenant') !== false || $db === 'ownstore_db') {
        $hasCust = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$db' AND table_name='customers'")->fetchColumn();
        if ($hasCust) {
            $hasEmail = $pdo->query("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema='$db' AND table_name='customers' AND column_name='email'")->fetchColumn();
            if (!$hasEmail) {
                $pdo->exec("ALTER TABLE `$db`.`customers` ADD COLUMN `email` VARCHAR(255) NULL AFTER `phone`");
                echo "Added email to $db.customers\n";
            } else {
                echo "email already exists in $db.customers\n";
            }
        }
    }
}
