<?php
/**
 * ============================================================
 * setuproles.php — One-Time RBAC Setup Script
 * ============================================================
 * Run via browser: https://mart.vectabyte.net/setuproles.php
 *
 * This script (NO Laravel bootstrap needed):
 *  1. Reads DB credentials from .env
 *  2. Creates Spatie permission tables (with "spatie_" prefix)
 *  3. Adds is_active + avatar columns to users table
 *  4. Creates all 4 roles + all permissions
 *  5. Assigns permissions to roles
 *  6. Assigns "owner" role to the first user (lowest ID)
 *  7. Clears Spatie permission cache (if file cache driver)
 *  8. Self-deletes for security
 * ============================================================
 * SECURITY: This file will delete itself after success.
 * DO NOT leave it in production without running it.
 * ============================================================
 */

// ─── Safety token — change this to something secret, then pass ?token=YOUR_SECRET in URL ───
$SETUP_TOKEN = 'rbac_setup_2025_secure';

if (!isset($_GET['token']) || $_GET['token'] !== $SETUP_TOKEN) {
    http_response_code(403);
    die('<h2>403 Forbidden</h2><p>Access token required. Usage: <code>/setuproles.php?token=YOUR_TOKEN</code></p>');
}

// ─── Output helper ───────────────────────────────────────────────────────────
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RBAC Setup — OwnStore</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #e2e8f0; padding: 2rem; }
        h1 { color: #818cf8; }
        h2 { color: #a5b4fc; margin-top: 1.5rem; border-bottom: 1px solid #334155; padding-bottom: 0.5rem; }
        .ok  { color: #4ade80; } /* green */
        .skip{ color: #facc15; } /* yellow */
        .err { color: #f87171; } /* red */
        .info{ color: #94a3b8; }
        pre  { background: #1e293b; padding: 1rem; border-radius: 8px; overflow-x: auto; font-size: 13px; }
        .box { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 1.5rem; margin-top: 1rem; }
        .success-banner { background: #14532d; border: 2px solid #22c55e; border-radius: 12px; padding: 1.5rem; margin-top: 2rem; color: #86efac; font-weight: bold; font-size: 1.1rem; }
        .error-banner   { background: #450a0a; border: 2px solid #ef4444; border-radius: 12px; padding: 1.5rem; margin-top: 2rem; color: #fca5a5; }
    </style>
</head>
<body>
<h1>⚡ OwnStore RBAC Setup Script</h1>
<p class="info">Running at: <?= date('Y-m-d H:i:s') ?></p>
<?php

// ─── Log helper ──────────────────────────────────────────────────────────────
$errors = [];
$log    = [];

function logLine(string $type, string $msg): void {
    global $log;
    $class = match($type) {
        'ok'   => 'ok',
        'skip' => 'skip',
        'err'  => 'err',
        default => 'info',
    };
    $icon = match($type) {
        'ok'   => '✅',
        'skip' => '⏭',
        'err'  => '❌',
        default => 'ℹ',
    };
    $log[] = "<span class=\"{$class}\">{$icon} {$msg}</span>";
    echo "<span class=\"{$class}\">{$icon} {$msg}</span><br>\n";
    flush();
}

// ─── Step 1: Parse .env ──────────────────────────────────────────────────────
echo '<h2>Step 1 — Reading .env</h2><pre>';

$envPath = dirname(__DIR__) . '/.env';
if (!file_exists($envPath)) {
    die('<span class="err">❌ .env file not found at: ' . htmlspecialchars($envPath) . '</span>');
}

$envContent = file_get_contents($envPath);

function getEnvValue(string $key, string $envContent, string $default = ''): string {
    if (preg_match('/^' . preg_quote($key, '/') . '=(.*)$/m', $envContent, $m)) {
        $val = trim($m[1]);
        // Strip surrounding quotes
        if (strlen($val) >= 2 && (($val[0] === '"' && $val[-1] === '"') || ($val[0] === "'" && $val[-1] === "'"))) {
            $val = substr($val, 1, -1);
        }
        return $val;
    }
    return $default;
}

$dbHost     = getEnvValue('DB_HOST',     $envContent, '127.0.0.1');
$dbPort     = getEnvValue('DB_PORT',     $envContent, '3306');
$dbName     = getEnvValue('DB_DATABASE', $envContent, '');
$dbUser     = getEnvValue('DB_USERNAME', $envContent, '');
$dbPass     = getEnvValue('DB_PASSWORD', $envContent, '');
$cacheDriver = getEnvValue('CACHE_DRIVER', $envContent, 'file');

echo "DB_HOST:     {$dbHost}\n";
echo "DB_PORT:     {$dbPort}\n";
echo "DB_DATABASE: {$dbName}\n";
echo "DB_USERNAME: {$dbUser}\n";
echo "DB_PASSWORD: " . (str_repeat('*', strlen($dbPass))) . "\n";
echo "CACHE_DRIVER: {$cacheDriver}\n";
echo '</pre>';

// ─── Step 2: Connect to DB ───────────────────────────────────────────────────
echo '<h2>Step 2 — Database Connection</h2>';

try {
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    logLine('ok', "Connected to MySQL: <b>{$dbName}</b> on {$dbHost}:{$dbPort}");
} catch (PDOException $e) {
    logLine('err', 'Connection failed: ' . $e->getMessage());
    echo '<div class="error-banner">❌ Cannot connect to database. Check .env credentials.</div>';
    die();
}

// ─── Helper: column existence check ─────────────────────────────────────────
function columnExists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS 
                           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $stmt->execute([$table, $column]);
    return (int)$stmt->fetchColumn() > 0;
}

function tableExists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES 
                           WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
    $stmt->execute([$table]);
    return (int)$stmt->fetchColumn() > 0;
}

// ─── Step 3: Add columns to users table ─────────────────────────────────────
echo '<h2>Step 3 — Users Table Columns</h2>';

try {
    if (!columnExists($pdo, 'users', 'is_active')) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `role`");
        logLine('ok', 'Added column <b>is_active</b> to users table');
    } else {
        logLine('skip', 'Column <b>is_active</b> already exists — skipped');
    }

    if (!columnExists($pdo, 'users', 'avatar')) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(255) NULL AFTER `is_active`");
        logLine('ok', 'Added column <b>avatar</b> to users table');
    } else {
        logLine('skip', 'Column <b>avatar</b> already exists — skipped');
    }
} catch (PDOException $e) {
    logLine('err', 'Users table alter failed: ' . $e->getMessage());
}

// ─── Step 4: Create Spatie tables ────────────────────────────────────────────
echo '<h2>Step 4 — Creating Spatie Permission Tables (prefixed: spatie_*)</h2>';

$tables = [
    'spatie_permissions' => "
        CREATE TABLE IF NOT EXISTS `spatie_permissions` (
            `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name`       VARCHAR(255)    NOT NULL,
            `guard_name` VARCHAR(255)    NOT NULL,
            `created_at` TIMESTAMP       NULL,
            `updated_at` TIMESTAMP       NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `spatie_permissions_name_guard_name_unique` (`name`, `guard_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    'spatie_roles' => "
        CREATE TABLE IF NOT EXISTS `spatie_roles` (
            `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name`       VARCHAR(255)    NOT NULL,
            `guard_name` VARCHAR(255)    NOT NULL,
            `created_at` TIMESTAMP       NULL,
            `updated_at` TIMESTAMP       NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `spatie_roles_name_guard_name_unique` (`name`, `guard_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    'spatie_model_has_permissions' => "
        CREATE TABLE IF NOT EXISTS `spatie_model_has_permissions` (
            `permission_id` BIGINT UNSIGNED NOT NULL,
            `model_type`    VARCHAR(255)    NOT NULL,
            `model_id`      BIGINT UNSIGNED NOT NULL,
            PRIMARY KEY (`permission_id`, `model_id`, `model_type`),
            KEY `model_has_permissions_model_id_model_type_index` (`model_id`, `model_type`),
            CONSTRAINT `spatie_model_has_permissions_permission_id_foreign`
                FOREIGN KEY (`permission_id`) REFERENCES `spatie_permissions` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    'spatie_model_has_roles' => "
        CREATE TABLE IF NOT EXISTS `spatie_model_has_roles` (
            `role_id`    BIGINT UNSIGNED NOT NULL,
            `model_type` VARCHAR(255)    NOT NULL,
            `model_id`   BIGINT UNSIGNED NOT NULL,
            PRIMARY KEY (`role_id`, `model_id`, `model_type`),
            KEY `model_has_roles_model_id_model_type_index` (`model_id`, `model_type`),
            CONSTRAINT `spatie_model_has_roles_role_id_foreign`
                FOREIGN KEY (`role_id`) REFERENCES `spatie_roles` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    'spatie_role_has_permissions' => "
        CREATE TABLE IF NOT EXISTS `spatie_role_has_permissions` (
            `permission_id` BIGINT UNSIGNED NOT NULL,
            `role_id`       BIGINT UNSIGNED NOT NULL,
            PRIMARY KEY (`permission_id`, `role_id`),
            CONSTRAINT `spatie_role_has_permissions_permission_id_foreign`
                FOREIGN KEY (`permission_id`) REFERENCES `spatie_permissions` (`id`) ON DELETE CASCADE,
            CONSTRAINT `spatie_role_has_permissions_role_id_foreign`
                FOREIGN KEY (`role_id`) REFERENCES `spatie_roles` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
];

foreach ($tables as $tableName => $sql) {
    try {
        if (tableExists($pdo, $tableName)) {
            logLine('skip', "Table <b>{$tableName}</b> already exists — skipped");
        } else {
            $pdo->exec($sql);
            logLine('ok', "Created table <b>{$tableName}</b>");
        }
    } catch (PDOException $e) {
        logLine('err', "Failed to create <b>{$tableName}</b>: " . $e->getMessage());
        $errors[] = $e->getMessage();
    }
}

// ─── Step 5: Seed Roles ───────────────────────────────────────────────────────
echo '<h2>Step 5 — Creating Roles</h2>';

$roles = ['owner', 'manager', 'cashier', 'warehouse'];
$roleIds = [];
$now = date('Y-m-d H:i:s');

$insertRole = $pdo->prepare("INSERT IGNORE INTO `spatie_roles` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES (?, 'web', ?, ?)");
$selectRole = $pdo->prepare("SELECT `id` FROM `spatie_roles` WHERE `name` = ? AND `guard_name` = 'web'");

foreach ($roles as $roleName) {
    try {
        $insertRole->execute([$roleName, $now, $now]);
        $selectRole->execute([$roleName]);
        $row = $selectRole->fetch();
        $roleIds[$roleName] = $row['id'];
        logLine('ok', "Role <b>{$roleName}</b> → ID {$row['id']}");
    } catch (PDOException $e) {
        logLine('err', "Role {$roleName}: " . $e->getMessage());
        $errors[] = $e->getMessage();
    }
}

// ─── Step 6: Seed Permissions ─────────────────────────────────────────────────
echo '<h2>Step 6 — Creating Permissions</h2>';

$allPermissions = [
    // Sales
    'sales.view', 'sales.create', 'sales.delete',
    // Purchases
    'purchases.view', 'purchases.create', 'purchases.delete',
    // Suppliers
    'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
    // Customers
    'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
    // Items
    'items.view', 'items.create', 'items.edit', 'items.delete', 'items.import',
    // Reports
    'reports.view',
    // Godams
    'godams.view', 'godams.create', 'godams.edit', 'godams.delete',
    // Stock Transfers
    'stock-transfers.view', 'stock-transfers.create',
    // Settings
    'settings.view', 'settings.edit',
    // Staff
    'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
];

$permissionIds = [];
$insertPerm = $pdo->prepare("INSERT IGNORE INTO `spatie_permissions` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES (?, 'web', ?, ?)");
$selectPerm = $pdo->prepare("SELECT `id` FROM `spatie_permissions` WHERE `name` = ? AND `guard_name` = 'web'");

foreach ($allPermissions as $perm) {
    try {
        $insertPerm->execute([$perm, $now, $now]);
        $selectPerm->execute([$perm]);
        $row = $selectPerm->fetch();
        $permissionIds[$perm] = $row['id'];
        logLine('ok', "Permission <b>{$perm}</b> → ID {$row['id']}");
    } catch (PDOException $e) {
        logLine('err', "Permission {$perm}: " . $e->getMessage());
        $errors[] = $e->getMessage();
    }
}

// ─── Step 7: Assign Permissions to Roles ─────────────────────────────────────
echo '<h2>Step 7 — Assigning Permissions to Roles</h2>';

/**
 * Role permission mapping:
 * - owner:     ALL permissions
 * - manager:   ALL except settings.edit, staff.create, staff.delete
 * - cashier:   sales.view, sales.create, items.view
 * - warehouse: godams.view, godams.create, godams.edit, items.view, stock-transfers.view, stock-transfers.create
 */
$rolePermissions = [
    'owner' => $allPermissions, // ALL

    'manager' => array_filter($allPermissions, fn($p) => !in_array($p, [
        'settings.edit', 'staff.create', 'staff.delete',
    ])),

    'cashier' => [
        'sales.view', 'sales.create', 'items.view',
    ],

    'warehouse' => [
        'godams.view', 'godams.create', 'godams.edit',
        'items.view',
        'stock-transfers.view', 'stock-transfers.create',
    ],
];

$insertRolePerm = $pdo->prepare("INSERT IGNORE INTO `spatie_role_has_permissions` (`permission_id`, `role_id`) VALUES (?, ?)");

foreach ($rolePermissions as $roleName => $permissions) {
    if (!isset($roleIds[$roleName])) {
        logLine('err', "Role <b>{$roleName}</b> not found — skipping permission assignment");
        continue;
    }
    $roleId = $roleIds[$roleName];
    $count  = 0;
    foreach ($permissions as $perm) {
        if (!isset($permissionIds[$perm])) {
            logLine('err', "Permission <b>{$perm}</b> not found");
            continue;
        }
        try {
            $insertRolePerm->execute([$permissionIds[$perm], $roleId]);
            $count++;
        } catch (PDOException $e) {
            logLine('err', "Assign {$perm} → {$roleName}: " . $e->getMessage());
        }
    }
    logLine('ok', "Role <b>{$roleName}</b>: assigned {$count} permissions");
}

// ─── Step 8: Assign Owner Role to First User ──────────────────────────────────
echo '<h2>Step 8 — Assigning Owner Role to First User</h2>';

try {
    $firstUser = $pdo->query("SELECT `id`, `name`, `email` FROM `users` ORDER BY `id` ASC LIMIT 1")->fetch();

    if (!$firstUser) {
        logLine('err', 'No users found in the database. Create an account first, then re-run this script.');
        $errors[] = 'No users found';
    } else {
        logLine('info', "First user: <b>{$firstUser['name']}</b> ({$firstUser['email']}) — ID {$firstUser['id']}");

        // Check if already assigned
        $existsCheck = $pdo->prepare("SELECT COUNT(*) FROM `spatie_model_has_roles` WHERE `role_id` = ? AND `model_id` = ? AND `model_type` = 'App\\\\Models\\\\User'");
        $existsCheck->execute([$roleIds['owner'], $firstUser['id']]);

        if ($existsCheck->fetchColumn() > 0) {
            logLine('skip', "User already has owner role — skipped");
        } else {
            $assignRole = $pdo->prepare("INSERT INTO `spatie_model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES (?, 'App\\\\Models\\\\User', ?)");
            $assignRole->execute([$roleIds['owner'], $firstUser['id']]);
            logLine('ok', "Owner role assigned to <b>{$firstUser['name']}</b> (ID {$firstUser['id']})");
        }

        // Also ensure is_active = 1 for the first user
        $pdo->exec("UPDATE `users` SET `is_active` = 1 WHERE `id` = {$firstUser['id']}");
        logLine('ok', "Set is_active = 1 for first user");
    }
} catch (PDOException $e) {
    logLine('err', 'Owner assignment failed: ' . $e->getMessage());
    $errors[] = $e->getMessage();
}

// ─── Step 9: Activate all existing users (set is_active = 1 where NULL) ──────
echo '<h2>Step 9 — Activating Existing Users</h2>';

try {
    $affected = $pdo->exec("UPDATE `users` SET `is_active` = 1 WHERE `is_active` IS NULL");
    logLine('ok', "Set is_active = 1 for {$affected} existing user(s) where it was NULL");
} catch (PDOException $e) {
    logLine('skip', 'Could not update existing users: ' . $e->getMessage());
}

// ─── Step 10: Clear Spatie Cache ──────────────────────────────────────────────
echo '<h2>Step 10 — Clearing Spatie Permission Cache</h2>';

// Try to clear file-based cache for Spatie
$cacheDir = dirname(__DIR__) . '/storage/framework/cache/data';
$cacheKey = 'spatie.permission.cache';

if ($cacheDriver === 'file') {
    // File cache: find and delete any cache entry containing the Spatie key
    $deleted = 0;
    if (is_dir($cacheDir)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cacheDir));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $content = @file_get_contents($file->getPathname());
                if ($content && str_contains($content, 'spatie.permission.cache')) {
                    @unlink($file->getPathname());
                    $deleted++;
                }
            }
        }
    }
    logLine('ok', "File cache: deleted {$deleted} Spatie cache file(s)");
} else {
    logLine('info', "Cache driver is '{$cacheDriver}' — please clear cache manually if needed (e.g. php artisan cache:clear)");
}

// ─── Summary ─────────────────────────────────────────────────────────────────
echo '<h2>Summary</h2>';

// Print roles table
$rows = $pdo->query("SELECT r.name, r.guard_name, COUNT(rp.permission_id) as perms
                     FROM spatie_roles r
                     LEFT JOIN spatie_role_has_permissions rp ON r.id = rp.role_id
                     GROUP BY r.id")->fetchAll();
echo '<pre>';
echo str_pad('Role', 12) . str_pad('Guard', 10) . "Permissions\n";
echo str_repeat('-', 35) . "\n";
foreach ($rows as $row) {
    echo str_pad($row['name'], 12) . str_pad($row['guard_name'], 10) . $row['perms'] . "\n";
}
echo '</pre>';

// Print assigned user
$ownerAssigned = $pdo->query("SELECT u.name, u.email FROM spatie_model_has_roles mhr 
                               JOIN spatie_roles r ON r.id = mhr.role_id 
                               JOIN users u ON u.id = mhr.model_id 
                               WHERE r.name = 'owner'")->fetchAll();
echo '<pre>Users with owner role: ' . count($ownerAssigned) . "\n";
foreach ($ownerAssigned as $u) {
    echo "  → {$u['name']} ({$u['email']})\n";
}
echo '</pre>';

if (empty($errors)) {
    echo '<div class="success-banner">
        ✅ RBAC setup completed successfully!<br><br>
        <strong>Next steps:</strong><br>
        1. Upload updated <code>vendor/</code>, <code>composer.json</code>, <code>composer.lock</code> after running <code>composer require spatie/laravel-permission:^6.0</code> locally<br>
        2. Login as the owner at <a href="/login" style="color:#86efac">/login</a><br>
        3. Visit <a href="/staff" style="color:#86efac">/staff</a> to manage staff roles<br>
        4. This script will now self-delete for security
    </div>';

    // ─── SELF-DELETE ──────────────────────────────────────────────────────────
    @unlink(__FILE__);
    echo '<p class="info" style="margin-top:1rem;font-size:12px;">🗑 Script deleted from server.</p>';

} else {
    echo '<div class="error-banner">
        ⚠ Setup completed with ' . count($errors) . ' error(s). Review the log above.<br>
        Fix the issues and re-run the script. It will NOT self-delete when there are errors.
    </div>';
}
?>
</body>
</html>
<?php
ob_end_flush();
