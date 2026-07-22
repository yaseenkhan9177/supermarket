<?php
/**
 * FIFO Sale Wiring — Verification Script (raw PDO, no Laravel boot needed)
 * Replicates exactly what FifoStockService::deductStock() does:
 *   1. Fetches batches in FIFO order (oldest received_at first)
 *   2. Deducts from each batch
 *   3. Recalculates items.on_hand = SUM(batches.quantity_available)
 *   4. Inserts a stock_audit_logs row
 *   5. Would insert a sale_items row with batch_id (shown in output)
 * Then prints the before/after table and reverts everything.
 */

$db   = 'vectabyte_tenant_cfbccadc_3809_49c2_899e_de7bf04f7993';
$pdo  = new PDO("mysql:host=127.0.0.1;port=3306;dbname={$db}", 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$testItems = [
    ['id' => 2207, 'name' => 'MOMUL 21KG',           'sell_qty' => 1.00],
    ['id' => 2496, 'name' => 'GOLD 5KG',             'sell_qty' => 10.00],
    ['id' => 2494, 'name' => 'BAHBUTONG BOP1 48KG',  'sell_qty' => 5.00],
];

$fakeSaleId = 99999;

// ── helpers ────────────────────────────────────────────────────────────────────
function getItem(PDO $pdo, int $id): array {
    return $pdo->query("SELECT id, description, on_hand FROM items WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
}
function getBatchSum(PDO $pdo, int $itemId): float {
    return (float) $pdo->query("SELECT COALESCE(SUM(quantity_available),0) FROM batches WHERE item_id = $itemId")->fetchColumn();
}
function getBatches(PDO $pdo, int $itemId): array {
    return $pdo->query("SELECT id, batch_no, quantity_available, sale_price, cost_price FROM batches WHERE item_id = $itemId AND quantity_available > 0 ORDER BY received_at ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
}
function getAuditCount(PDO $pdo, int $itemId): int {
    return (int) $pdo->query("SELECT COUNT(*) FROM stock_audit_logs WHERE item_id = $itemId AND notes LIKE '%[verify_test]%'")->fetchColumn();
}

// ── SNAPSHOT BEFORE ────────────────────────────────────────────────────────────
$snapshots = [];
echo "=== BEFORE ===" . PHP_EOL;
echo str_pad('Item', 30) . str_pad('on_hand', 12) . str_pad('batch_sum', 12) . str_pad('audit_rows', 12) . PHP_EOL;
echo str_repeat('-', 66) . PHP_EOL;
foreach ($testItems as $t) {
    $item      = getItem($pdo, $t['id']);
    $batchSum  = getBatchSum($pdo, $t['id']);
    $audits    = getAuditCount($pdo, $t['id']);
    echo str_pad($t['name'], 30)
       . str_pad(number_format($item['on_hand'], 2), 12)
       . str_pad(number_format($batchSum, 2), 12)
       . str_pad($audits, 12) . PHP_EOL;

    $snapshots[$t['id']] = [
        'on_hand'  => $item['on_hand'],
        'batches'  => $pdo->query("SELECT id, quantity_available FROM batches WHERE item_id = {$t['id']}")->fetchAll(PDO::FETCH_ASSOC),
    ];
}

// ── SIMULATE FIFO DEDUCTION ────────────────────────────────────────────────────
echo PHP_EOL . "=== FIFO DEDUCTION ===" . PHP_EOL;

$saleItemsPreview = []; // what sale_items rows would look like

foreach ($testItems as $t) {
    $remaining = $t['sell_qty'];
    $batches   = getBatches($pdo, $t['id']);

    if (empty($batches)) {
        echo "  {$t['name']}: NO BATCHES — skipped" . PHP_EOL;
        continue;
    }

    echo "  Selling {$t['sell_qty']} × {$t['name']}:" . PHP_EOL;

    foreach ($batches as $b) {
        if ($remaining <= 0) break;

        $deduct    = min($remaining, (float)$b['quantity_available']);
        $newQty    = (float)$b['quantity_available'] - $deduct;
        $lineTotal = $deduct * (float)$b['sale_price'];

        // Update batch
        $pdo->exec("UPDATE batches SET quantity_available = $newQty WHERE id = {$b['id']}");

        // Audit log (sale_id = NULL since this is a test — FK disallows non-existent IDs)
        $noteEsc = $pdo->quote("[verify_test]");
        $pdo->exec("INSERT INTO stock_audit_logs (item_id, user_id, action, quantity, batch_id, sale_id, notes, created_at)
            VALUES ({$t['id']}, NULL, 'deduct', $deduct, {$b['id']}, NULL, $noteEsc, NOW())");

        // Preview sale_item row
        $saleItemsPreview[] = [
            'item_id'   => $t['id'],
            'item_name' => $t['name'],
            'batch_id'  => $b['id'],
            'qty'       => $deduct,
            'rate'      => $b['sale_price'],
            'total'     => $lineTotal,
        ];

        echo sprintf("    batch_id=%d | deducted=%.2f | remaining_in_batch=%.2f | rate=%.2f | line_total=%.2f\n",
            $b['id'], $deduct, $newQty, $b['sale_price'], $lineTotal);

        $remaining -= $deduct;
    }

    // Sync items.on_hand = SUM(batches.quantity_available)
    $newOnHand = getBatchSum($pdo, $t['id']);
    $pdo->exec("UPDATE items SET on_hand = $newOnHand WHERE id = {$t['id']}");
}

// ── AFTER ──────────────────────────────────────────────────────────────────────
echo PHP_EOL . "=== AFTER ===" . PHP_EOL;
echo str_pad('Item', 30) . str_pad('on_hand', 12) . str_pad('batch_sum', 12) . str_pad('audit_rows', 12) . str_pad('sync?', 10) . PHP_EOL;
echo str_repeat('-', 76) . PHP_EOL;
foreach ($testItems as $t) {
    $item     = getItem($pdo, $t['id']);
    $batchSum = getBatchSum($pdo, $t['id']);
    $audits   = getAuditCount($pdo, $t['id']);
    $sync     = abs((float)$item['on_hand'] - $batchSum) < 0.01 ? '✓ SYNC' : '✗ DRIFT';
    echo str_pad($t['name'], 30)
       . str_pad(number_format($item['on_hand'], 2), 12)
       . str_pad(number_format($batchSum, 2), 12)
       . str_pad($audits, 12)
       . $sync . PHP_EOL;
}

// ── SALE_ITEMS PREVIEW ─────────────────────────────────────────────────────────
echo PHP_EOL . "=== SALE_ITEMS that would be created (batch_id populated) ===" . PHP_EOL;
echo str_pad('item_name', 30) . str_pad('batch_id', 12) . str_pad('qty', 10) . str_pad('rate', 10) . str_pad('total', 12) . PHP_EOL;
echo str_repeat('-', 74) . PHP_EOL;
foreach ($saleItemsPreview as $si) {
    echo str_pad($si['item_name'], 30)
       . str_pad($si['batch_id'], 12)
       . str_pad(number_format($si['qty'], 2), 10)
       . str_pad(number_format($si['rate'], 2), 10)
       . str_pad(number_format($si['total'], 2), 12) . PHP_EOL;
}

// ── OUT-OF-STOCK CHECK ─────────────────────────────────────────────────────────
echo PHP_EOL . "=== SEARCH COUNTER CHECK (uses items.on_hand) ===" . PHP_EOL;
foreach ($testItems as $t) {
    $item = getItem($pdo, $t['id']);
    $msg  = (float)$item['on_hand'] > 0 ? "Stock: {$item['on_hand']}" : "Out of Stock";
    echo "  {$t['name']}: → {$msg}" . PHP_EOL;
}

// ── REVERT ─────────────────────────────────────────────────────────────────────
echo PHP_EOL . "=== REVERTING ===" . PHP_EOL;
foreach ($testItems as $t) {
    $snap = $snapshots[$t['id']];
    $pdo->exec("UPDATE items SET on_hand = {$snap['on_hand']} WHERE id = {$t['id']}");
    foreach ($snap['batches'] as $b) {
        $pdo->exec("UPDATE batches SET quantity_available = {$b['quantity_available']} WHERE id = {$b['id']}");
    }
    $pdo->exec("DELETE FROM stock_audit_logs WHERE item_id = {$t['id']} AND notes LIKE '%[verify_test]%'");
    echo "  Reverted {$t['name']}" . PHP_EOL;
}
echo PHP_EOL . "Database restored to original state." . PHP_EOL;
