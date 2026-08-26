<?php

$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
$dbs = $pdo->query('SHOW DATABASES')->fetchAll(PDO::FETCH_COLUMN);

$totalTenantCount = 0;
$tenantStats = [];

$globalStats = [
    'total_items'                  => 0,
    'total_inventory_items'        => 0,
    'total_service_items'          => 0,
    'group_A_on_hand_gt_batches'   => 0, // on_hand > batch_sum
    'group_B_on_hand_lt_batches'   => 0, // on_hand < batch_sum
    'group_C_on_hand_gt_0_no_batch'=> 0, // on_hand > 0, batch_sum = 0
    'group_D_on_hand_0_batch_gt_0' => 0, // on_hand = 0, batch_sum > 0
    'group_E_both_zero'            => 0, // on_hand = 0, batch_sum = 0
    'group_exact_match'            => 0, // on_hand == batch_sum (stock > 0)
    'godam_stock_rows'             => 0,
    'godam_stock_qty'              => 0,
];

foreach ($dbs as $db) {
    $has_items = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$db' AND table_name='items'")->fetchColumn();
    if (!$has_items) continue;

    $pdo->exec("USE `$db`");
    $total_items = $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
    if ($total_items == 0) continue;

    $totalTenantCount++;
    $has_batches = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$db' AND table_name='batches'")->fetchColumn();
    $has_godam   = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$db' AND table_name='godam_stock'")->fetchColumn();

    $inv_items = $pdo->query("SELECT COUNT(*) FROM items WHERE item_type != 'Service' OR item_type IS NULL")->fetchColumn();
    $srv_items = $pdo->query("SELECT COUNT(*) FROM items WHERE item_type = 'Service'")->fetchColumn();

    $globalStats['total_items'] += $total_items;
    $globalStats['total_inventory_items'] += $inv_items;
    $globalStats['total_service_items'] += $srv_items;

    $tStat = [
        'db' => $db,
        'total_items' => $total_items,
        'inv_items' => $inv_items,
        'srv_items' => $srv_items,
        'group_A' => 0,
        'group_B' => 0,
        'group_C' => 0,
        'group_D' => 0,
        'group_E' => 0,
        'match'   => 0,
        'examples' => []
    ];

    if ($has_batches) {
        $rows = $pdo->query("
            SELECT i.id, i.description, i.code, i.item_type, i.on_hand, COALESCE(b.total_batch, 0) as batch_qty, COALESCE(b.batch_count, 0) as batch_count
            FROM items i
            LEFT JOIN (
                SELECT item_id, SUM(quantity_available) as total_batch, COUNT(*) as batch_count
                FROM batches
                WHERE quantity_available > 0
                GROUP BY item_id
            ) b ON b.item_id = i.id
            WHERE i.item_type != 'Service' OR i.item_type IS NULL
        ")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $r) {
            $on_hand = (float)($r['on_hand'] ?? 0);
            $batch_qty = (float)($r['batch_qty'] ?? 0);
            $diff = round($on_hand - $batch_qty, 2);

            if ($on_hand == 0 && $batch_qty == 0) {
                $tStat['group_E']++;
                $globalStats['group_E_both_zero']++;
            } elseif ($diff == 0) {
                $tStat['match']++;
                $globalStats['group_exact_match']++;
            } elseif ($diff > 0) {
                $tStat['group_A']++;
                $globalStats['group_A_on_hand_gt_batches']++;
                if ($batch_qty == 0) {
                    $tStat['group_C']++;
                    $globalStats['group_C_on_hand_gt_0_no_batch']++;
                }
                if (count($tStat['examples']) < 3) {
                    $tStat['examples'][] = "Item #{$r['id']} '{$r['description']}' ({$r['code']}): on_hand={$on_hand}, batches={$batch_qty} (Diff: +{$diff})";
                }
            } else {
                $tStat['group_B']++;
                $globalStats['group_B_on_hand_lt_batches']++;
                if ($on_hand == 0) {
                    $tStat['group_D']++;
                    $globalStats['group_D_on_hand_0_batch_gt_0']++;
                }
                if (count($tStat['examples']) < 3) {
                    $tStat['examples'][] = "Item #{$r['id']} '{$r['description']}' ({$r['code']}): on_hand={$on_hand}, batches={$batch_qty} (Diff: {$diff})";
                }
            }
        }
    }

    if ($has_godam) {
        $gCount = $pdo->query("SELECT COUNT(*) FROM godam_stock WHERE quantity > 0")->fetchColumn();
        $gSum   = $pdo->query("SELECT SUM(quantity) FROM godam_stock WHERE quantity > 0")->fetchColumn() ?: 0;
        $globalStats['godam_stock_rows'] += $gCount;
        $globalStats['godam_stock_qty'] += $gSum;
    }

    $tenantStats[] = $tStat;
}

echo "=================================================================\n";
echo "STAGE 2 DATABASE INVENTORY AUDIT RESULTS\n";
echo "=================================================================\n";
echo "Total Active Databases Audited: $totalTenantCount\n";
echo "Total Items across all DBs:     {$globalStats['total_items']}\n";
echo "  - Inventory Items:            {$globalStats['total_inventory_items']}\n";
echo "  - Service Items:              {$globalStats['total_service_items']}\n\n";

echo "BREAKDOWN BY AUDIT GROUPS (Inventory Items):\n";
echo "  1. Exact Match (on_hand == batch_sum > 0):       {$globalStats['group_exact_match']}\n";
echo "  2. Both Zero   (on_hand == 0 && batch_sum == 0): {$globalStats['group_E_both_zero']}\n";
echo "  3. Group A: on_hand > batch_sum:                 {$globalStats['group_A_on_hand_gt_batches']}\n";
echo "     ↳ Group C subset (on_hand > 0, batches = 0):  {$globalStats['group_C_on_hand_gt_0_no_batch']}\n";
echo "  4. Group B: on_hand < batch_sum:                 {$globalStats['group_B_on_hand_lt_batches']}\n";
echo "     ↳ Group D subset (on_hand = 0, batches > 0):  {$globalStats['group_D_on_hand_0_batch_gt_0']}\n\n";

echo "Warehouse (Godam) Stock Summary:\n";
echo "  - Godam stock records > 0:    {$globalStats['godam_stock_rows']}\n";
echo "  - Total Godam stock quantity: {$globalStats['godam_stock_qty']}\n\n";

echo "INDIVIDUAL TENANT AUDIT BREAKDOWN:\n";
foreach ($tenantStats as $ts) {
    echo "--- Database: {$ts['db']} (Items: {$ts['total_items']}) ---\n";
    echo "    Matches (>0): {$ts['match']} | Both Zero: {$ts['group_E']} | on_hand > batches: {$ts['group_A']} (no batches: {$ts['group_C']}) | on_hand < batches: {$ts['group_B']} (on_hand=0: {$ts['group_D']})\n";
    if (!empty($ts['examples'])) {
        foreach ($ts['examples'] as $ex) {
            echo "      * $ex\n";
        }
    }
}
echo "\nAudit Complete.\n";
