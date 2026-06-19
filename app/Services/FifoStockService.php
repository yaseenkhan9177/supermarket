<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Batch;
use App\Models\StockAuditLog;
use Illuminate\Support\Facades\DB;
use Exception;

class FifoStockService
{
    /**
     * Get total available stock for an item from all batches
     *
     * @param int $itemId
     * @return float
     */
    public function getAvailableStock(int $itemId): float
    {
        return Batch::where('item_id', $itemId)
            ->where('quantity_available', '>', 0)
            ->sum('quantity_available');
    }

    /**
     * Validate if sufficient stock is available
     *
     * @param int $itemId
     * @param float $quantity
     * @return array ['available' => bool, 'current_stock' => float, 'requested' => float]
     */
    public function validateStockAvailability(int $itemId, float $quantity): array
    {
        $currentStock = $this->getAvailableStock($itemId);

        return [
            'available' => $currentStock >= $quantity,
            'current_stock' => $currentStock,
            'requested' => $quantity,
            'shortfall' => max(0, $quantity - $currentStock)
        ];
    }

    /**
     * Deduct stock using FIFO logic
     * 
     * @param int $itemId
     * @param float $quantity
     * @param int|null $saleId
     * @param int|null $userId
     * @return array ['success' => bool, 'batches_used' => array, 'message' => string]
     * @throws Exception
     */
    public function deductStock(int $itemId, float $quantity, ?int $saleId = null, ?int $userId = null): array
    {
        // Validate stock availability first
        $validation = $this->validateStockAvailability($itemId, $quantity);

        if (!$validation['available']) {
            throw new Exception("Insufficient stock. Available: {$validation['current_stock']}, Requested: {$quantity}");
        }

        $batchesUsed = [];
        $remainingQty = $quantity;

        // Get batches in FIFO order (oldest first)
        $batches = Batch::where('item_id', $itemId)
            ->where('quantity_available', '>', 0)
            ->orderBy('received_at', 'asc')
            ->orderBy('id', 'asc') // Secondary sort for same-day batches
            ->lockForUpdate() // Prevent race conditions
            ->get();

        foreach ($batches as $batch) {
            if ($remainingQty <= 0) {
                break;
            }

            $deductFromBatch = min($remainingQty, $batch->quantity_available);

            // Update batch
            $batch->quantity_available -= $deductFromBatch;
            $batch->save();

            // Track which batches were used
            $batchesUsed[] = [
                'batch_id' => $batch->id,
                'batch_no' => $batch->batch_no,
                'quantity_deducted' => $deductFromBatch,
                'cost_price' => $batch->cost_price,
                'sale_price' => $batch->sale_price,
            ];

            // Log the deduction
            $this->logStockAction($itemId, 'deduct', $deductFromBatch, $batch->id, $saleId, $userId, "FIFO deduction from batch {$batch->batch_no}");

            $remainingQty -= $deductFromBatch;
        }

        // Update item's cached on_hand stock
        $item = Item::find($itemId);
        if ($item) {
            $item->on_hand = $this->getAvailableStock($itemId);
            $item->save();
        }

        return [
            'success' => true,
            'batches_used' => $batchesUsed,
            'total_deducted' => $quantity,
            'message' => "Successfully deducted {$quantity} units using FIFO"
        ];
    }

    /**
     * Add stock by creating a new batch
     *
     * @param int $itemId
     * @param float $quantity
     * @param float $costPrice
     * @param float $salePrice
     * @param string|null $batchNo
     * @param string|null $expiresAt
     * @param int|null $userId
     * @return Batch
     */
    public function addStock(
        int $itemId,
        float $quantity,
        float $costPrice,
        float $salePrice,
        ?string $batchNo = null,
        ?string $expiresAt = null,
        ?int $userId = null
    ): Batch {
        // Generate batch number if not provided
        if (!$batchNo) {
            $batchNo = 'BATCH-' . date('ymd-His') . '-' . $itemId;
        }

        // Create new batch
        $batch = Batch::create([
            'item_id' => $itemId,
            'batch_no' => $batchNo,
            'quantity_available' => $quantity,
            'cost_price' => $costPrice,
            'sale_price' => $salePrice,
            'received_at' => now(),
            'expires_at' => $expiresAt,
        ]);

        // Log the addition
        $this->logStockAction($itemId, 'add', $quantity, $batch->id, null, $userId, "New batch created: {$batchNo}");

        // Update item's cached on_hand stock
        $item = Item::find($itemId);
        if ($item) {
            $item->on_hand = $this->getAvailableStock($itemId);
            $item->save();
        }

        return $batch;
    }

    /**
     * Get active batches for an item (FIFO order)
     *
     * @param int $itemId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBatchesForItem(int $itemId)
    {
        return Batch::where('item_id', $itemId)
            ->where('quantity_available', '>', 0)
            ->orderBy('received_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Get batch breakdown with details
     *
     * @param int $itemId
     * @return array
     */
    public function getBatchBreakdown(int $itemId): array
    {
        $batches = $this->getBatchesForItem($itemId);
        $totalStock = 0;
        $breakdown = [];

        foreach ($batches as $batch) {
            $totalStock += $batch->quantity_available;
            $breakdown[] = [
                'batch_no' => $batch->batch_no,
                'quantity' => $batch->quantity_available,
                'cost_price' => $batch->cost_price,
                'sale_price' => $batch->sale_price,
                'received_at' => $batch->received_at->format('Y-m-d'),
                'expires_at' => $batch->expires_at ? $batch->expires_at->format('Y-m-d') : null,
                'age_days' => $batch->received_at->diffInDays(now()),
            ];
        }

        return [
            'total_stock' => $totalStock,
            'batch_count' => count($breakdown),
            'batches' => $breakdown,
        ];
    }

    /**
     * Log stock actions for audit trail
     *
     * @param int $itemId
     * @param string $action
     * @param float $quantity
     * @param int|null $batchId
     * @param int|null $saleId
     * @param int|null $userId
     * @param string|null $notes
     * @return void
     */
    protected function logStockAction(
        int $itemId,
        string $action,
        float $quantity,
        ?int $batchId = null,
        ?int $saleId = null,
        ?int $userId = null,
        ?string $notes = null
    ): void {
        // Only log if the audit table exists
        if (!$this->schema()->hasTable('stock_audit_logs')) {
            return;
        }

        StockAuditLog::create([
            'item_id' => $itemId,
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'quantity' => $quantity,
            'batch_id' => $batchId,
            'sale_id' => $saleId,
            'notes' => $notes,
        ]);
    }

    /**
     * Helper function to check schema
     */
    protected function schema()
    {
        return DB::connection()->getSchemaBuilder();
    }
}
