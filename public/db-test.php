<?php
// Simple DB connection test to see what database and tables Apache/PHP sees.
$env = parse_ini_file(__DIR__ . '/../.env');
$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$db   = $env['DB_DATABASE'] ?? 'ownstore_db';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';

echo "<h3>PHP env config:</h3>";
echo "Host: $host<br>Port: $port<br>Database: $db<br>User: $user<br><br>";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "<b>Successfully connected!</b><br><br><h3>Tables found:</h3>";
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "- " . $row[0] . "<br>";
    }
} catch (Exception $e) {
    echo "<b>Connection failed:</b> " . $e->getMessage();
}
unlink(__FILE__); // self-delete
