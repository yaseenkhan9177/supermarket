<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleVersion;
use App\Models\StockAuditLog;
use App\Models\User;
use App\Models\Wallet;
use App\Services\TaxService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceEditService
{
    protected FifoStockService $fifoService;
    protected TaxService $taxService;

    public function __construct(FifoStockService $fifoService, TaxService $taxService)
    {
        $this->fifoService = $fifoService;
        $this->taxService  = $taxService;
    }

    /**
     * Validate whether sufficient stock is available for the delta requirements of an edited invoice.
     *
     * @param Sale  $sale
     * @param array $newItems Array of ['item_id' => int, 'qty' => float, ...]
     * @return array ['valid' => bool, 'errors' => array, 'deltas' => array]
     */
    public function validateStockAvailability(Sale $sale, array $newItems): array
    {
        $sale->loadMissing('items.item');

        // Aggregate old item quantities
        $oldQuantities = [];
        foreach ($sale->items as $saleItem) {
            $oldQuantities[$saleItem->item_id] = ($oldQuantities[$saleItem->item_id] ?? 0) + (float) $saleItem->qty;
        }

        // Aggregate new item quantities
        $newQuantities = [];
        foreach ($newItems as $line) {
            $itemId = (int) $line['item_id'];
            $newQuantities[$itemId] = ($newQuantities[$itemId] ?? 0) + (float) $line['qty'];
        }

        $allItemIds = array_unique(array_merge(array_keys($oldQuantities), array_keys($newQuantities)));
        $errors = [];
        $deltas = [];

        foreach ($allItemIds as $itemId) {
            $item = Item::find($itemId);
            if (!$item) {
                $errors[] = "Item ID {$itemId} not found in database.";
                continue;
            }

            $oldQty = (float) ($oldQuantities[$itemId] ?? 0);
            $newQty = (float) ($newQuantities[$itemId] ?? 0);
            $diff   = $newQty - $oldQty;

            $isService = ($item->item_type === 'Service' || ($item->category ?? '') === 'Service');

            $deltas[$itemId] = [
                'item_id'    => $itemId,
                'item_name'  => $item->description,
                'is_service' => $isService,
                'old_qty'    => $oldQty,
                'new_qty'    => $newQty,
                'diff'       => $diff,
            ];

            // If quantity increased on an inventory item, verify shop-floor batch stock availability
            if ($diff > 0 && !$isService) {
                $availableStock = $this->fifoService->getAvailableStock($itemId);
                if ($availableStock < $diff) {
                    $errors[] = "Insufficient stock for '{$item->description}' (Code: {$item->code}). Need additional: {$diff}, Available: {$availableStock}.";
                }
            }
        }

        return [
            'valid'   => empty($errors),
            'errors'  => $errors,
            'deltas'  => $deltas,
        ];
    }

    /**
     * Execute full invoice edit with atomic transactions, stock delta sync, financial sync, and version history.
     */
    public function updateInvoice(
        int $saleId,
        array $payload,
        int $userId,
        ?string $userName = null,
        ?string $reason = null,
        ?string $ipAddress = null,
        ?string $originalUpdatedAt = null
    ): Sale {
        return DB::transaction(function () use ($saleId, $payload, $userId, $userName, $reason, $ipAddress, $originalUpdatedAt) {

            // 1. Lock Sale Row & Concurrency Check
            $sale = Sale::lockForUpdate()->with(['items.item', 'customer', 'wallet', 'user'])->findOrFail($saleId);

            if ($sale->status === 'cancelled') {
                throw new \Exception("Cannot edit invoice #{$sale->invoice_no} because it has been cancelled.");
            }

            if ($originalUpdatedAt) {
                $currentIso = Carbon::parse($sale->updated_at)->toIso8601String();
                $origIso    = Carbon::parse($originalUpdatedAt)->toIso8601String();
                if ($currentIso !== $origIso) {
                    throw new \Exception("This invoice was modified by another user. Please reload the invoice before making changes.");
                }
            }

            // Resolve User Name if missing
            if (empty($userName)) {
                if (\Illuminate\Support\Facades\Auth::guard('employee')->check()) {
                    $userName = \Illuminate\Support\Facades\Auth::guard('employee')->user()?->full_name ?? 'Employee';
                } else {
                    $userName = User::find($userId)?->name ?? \Illuminate\Support\Facades\Auth::user()?->name ?? 'Store Admin';
                }
            }

            // 2. Ensure initial Version 1 snapshot exists for historical baseline
            if ($sale->versions()->count() === 0) {
                SaleVersion::create([
                    'sale_id'         => $sale->id,
                    'version_number'  => 1,
                    'action_type'     => 'created',
                    'user_id'         => $sale->user_id,
                    'user_name'       => $sale->user?->name ?? 'Staff',
                    'reason'          => 'Initial Sale Creation',
                    'old_values'      => null,
                    'new_values'      => array_merge($sale->toSnapshotArray(), [
                        'created_by_id'   => $sale->user_id,
                        'created_by_name' => $sale->user?->name ?? 'Staff',
                        'created_at'      => $sale->created_at?->toDateTimeString() ?? $sale->sale_date?->toDateTimeString(),
                    ]),
                    'changes_summary' => ['note' => 'Original invoice creation.'],
                    'ip_address'      => $ipAddress,
                    'created_at'      => $sale->created_at ?? $sale->sale_date ?? now(),
                ]);
            }

            $oldSnapshot = $sale->toSnapshotArray();
            $nextVersionNumber = ($sale->versions()->max('version_number') ?? 1) + 1;

            // 3. Stock Delta Validation
            $validation = $this->validateStockAvailability($sale, $payload['items']);
            if (!$validation['valid']) {
                throw ValidationException::withMessages([
                    'stock' => $validation['errors'],
                ]);
            }

            // 4. Apply Stock Movements (Delta Only)
            $stockMovementsLog = [];
            foreach ($validation['deltas'] as $itemId => $delta) {
                if ($delta['is_service'] || $delta['diff'] == 0) {
                    continue;
                }

                $item = Item::lockForUpdate()->find($itemId);
                $diff = $delta['diff'];

                if ($diff > 0) {
                    // Additional quantity needed -> Deduct from FIFO batches
                    $deductResult = $this->fifoService->deductStock(
                        $itemId,
                        $diff,
                        $sale->id,
                        $userId,
                        "Invoice #{$sale->invoice_no} (Version {$nextVersionNumber}) edit (quantity increased by {$diff})"
                    );

                    StockAuditLog::create([
                        'item_id'    => $itemId,
                        'user_id'    => $userId,
                        'action'     => 'Invoice Edit (Deduct)',
                        'quantity'   => -$diff,
                        'sale_id'    => $sale->id,
                        'notes'      => "Invoice #{$sale->invoice_no} (Version {$nextVersionNumber}) edit: increased from {$delta['old_qty']} to {$delta['new_qty']} (+{$diff})",
                        'created_at' => now(),
                    ]);

                    $stockMovementsLog[] = [
                        'item_id'    => $itemId,
                        'item_name'  => $item->description,
                        'direction'  => 'deduct',
                        'quantity'   => $diff,
                        'net_change' => -$diff,
                    ];
                } elseif ($diff < 0) {
                    // Surplus quantity released -> Return to FIFO batches
                    $returnQty = abs($diff);
                    $batchNo   = 'RET-EDIT-' . date('Ymd') . '-' . mt_rand(1000, 9999);

                    $this->fifoService->addStock(
                        $itemId,
                        $returnQty,
                        $item->cost_rate ?? 0,
                        $item->sale_rate ?? 0,
                        $batchNo,
                        null,
                        $userId,
                        "Invoice #{$sale->invoice_no} (Version {$nextVersionNumber}) edit (quantity returned {$returnQty})"
                    );

                    StockAuditLog::create([
                        'item_id'    => $itemId,
                        'user_id'    => $userId,
                        'action'     => 'Invoice Edit (Return)',
                        'quantity'   => $returnQty,
                        'sale_id'    => $sale->id,
                        'notes'      => "Invoice #{$sale->invoice_no} (Version {$nextVersionNumber}) edit: reduced from {$delta['old_qty']} to {$delta['new_qty']} (-{$returnQty})",
                        'created_at' => now(),
                    ]);

                    $stockMovementsLog[] = [
                        'item_id'    => $itemId,
                        'item_name'  => $item->description,
                        'direction'  => 'return',
                        'quantity'   => $returnQty,
                        'net_change' => +$returnQty,
                    ];
                }
            }

            // 5. Replace Sale Items with New Line Items
            // Delete old items
            $sale->items()->delete();

            $calculatedSubtotal = 0;
            $newItemsCreated    = [];

            foreach ($payload['items'] as $row) {
                $item = Item::find($row['item_id']);
                if (!$item) continue;

                $qty       = (float) $row['qty'];
                $rate      = (float) $row['rate'];
                $lineTotal = round($qty * $rate, 2);

                $saleItem = SaleItem::create([
                    'sale_id'   => $sale->id,
                    'item_id'   => $item->id,
                    'item_name' => $item->description,
                    'batch_id'  => $row['batch_id'] ?? null,
                    'qty'       => $qty,
                    'rate'      => $rate,
                    'total'     => $lineTotal,
                ]);

                $calculatedSubtotal += $lineTotal;
                $newItemsCreated[]   = $saleItem;
            }

            // 6. Recalculate Totals & Update Sale Header (NOTE: sale_date is IMMUTABLE and NOT updated!)
            $discountTotal = (float) ($payload['discount_total'] ?? $sale->discount_total ?? 0);
            $returnAdj     = (float) ($payload['return_adjustment'] ?? $sale->return_adjustment ?? 0);

            // Authoritative Tax Calculation using current Store Admin settings
            $taxResult     = $this->taxService->calculate($calculatedSubtotal, $discountTotal, $returnAdj);
            $taxTotal      = $taxResult['tax_amount'];
            $taxRate       = $taxResult['tax_rate'];
            $grandTotal    = $taxResult['grand_total'];

            $paidAmount    = (float) ($payload['paid_amount'] ?? $grandTotal);
            $changeAmount  = max(0, $paidAmount - $grandTotal);

            $oldGrandTotal = (float) $sale->grand_total;
            $oldPaidAmount = (float) $sale->paid_amount;
            $oldPaymentMode = $sale->payment_mode;

            $sale->update([
                'customer_id'       => $payload['customer_id'] ?? $sale->customer_id,
                'customer_name'     => $payload['customer_name'] ?? $sale->customer_name,
                'payment_mode'      => $payload['payment_mode'] ?? $sale->payment_mode,
                'wallet_id'         => $payload['wallet_id'] ?? $sale->wallet_id,
                'subtotal'          => $calculatedSubtotal,
                'discount_total'    => $discountTotal,
                'tax_rate'          => $taxRate,
                'tax_total'         => $taxTotal,
                'return_adjustment' => $returnAdj,
                'grand_total'       => $grandTotal,
                'paid_amount'       => $paidAmount,
                'change_amount'     => $changeAmount,
                'status'            => 'completed',
            ]);

            // 7. Financial & Ledger Synchronization via AccountingService
            $accountingService = new AccountingService();
            $accountingService->reconcileInvoiceEdit($sale, $oldGrandTotal, $oldPaidAmount, $grandTotal, $paidAmount, $userId);

            // 8. Build Detailed Changes Summary
            $newSnapshot = array_merge(
                $sale->fresh(['items.item', 'customer', 'wallet', 'user'])->toSnapshotArray(),
                [
                    'edited_by_id'   => $userId,
                    'edited_by_name' => $userName,
                    'edited_at'      => now()->toDateTimeString(),
                    'version_number' => $nextVersionNumber,
                ]
            );

            $changesSummary = $this->buildChangesSummary($oldSnapshot, $newSnapshot, $stockMovementsLog, $reason);

            // 9. Record New Sale Version
            SaleVersion::create([
                'sale_id'         => $sale->id,
                'version_number'  => $nextVersionNumber,
                'action_type'     => 'edited',
                'user_id'         => $userId,
                'user_name'       => $userName,
                'reason'          => $reason ?? 'Invoice Edited',
                'old_values'      => $oldSnapshot,
                'new_values'      => $newSnapshot,
                'changes_summary' => $changesSummary,
                'ip_address'      => $ipAddress,
            ]);

            // 10. Record General Audit Log
            AuditLog::record(
                'sale.edited',
                "Invoice #{$sale->invoice_no} edited to Version {$nextVersionNumber} by {$userName} (Total: Rs. {$oldGrandTotal} → Rs. {$grandTotal})",
                'Sale',
                $sale->id,
                [
                    'version'         => $nextVersionNumber,
                    'edited_by'       => $userName,
                    'reason'          => $reason,
                    'stock_movements' => $stockMovementsLog,
                ],
                $userId
            );

            return $sale;
        });
    }

    /**
     * Safely cancel an invoice by returning remaining sold quantities to stock and marking status cancelled.
     */
    public function cancelInvoice(int $saleId, int $userId, ?string $reason = null, ?string $ipAddress = null): Sale
    {
        return DB::transaction(function () use ($saleId, $userId, $reason, $ipAddress) {
            $sale = Sale::lockForUpdate()->with(['items.item', 'customer', 'wallet', 'user'])->findOrFail($saleId);

            if ($sale->status === 'cancelled') {
                throw new \Exception("Invoice #{$sale->invoice_no} is already cancelled.");
            }

            $oldSnapshot = $sale->toSnapshotArray();

            // Return sold inventory items to stock
            $stockMovementsLog = [];
            foreach ($sale->items as $saleItem) {
                $item = $saleItem->item;
                if (!$item || $item->item_type === 'Service') {
                    continue;
                }

                $qty = (float) $saleItem->qty;
                if ($qty <= 0) continue;

                $batchNo = 'CAN-RET-' . date('Ymd') . '-' . mt_rand(1000, 9999);
                $this->fifoService->addStock(
                    $item->id,
                    $qty,
                    $item->cost_rate ?? 0,
                    $item->sale_rate ?? 0,
                    $batchNo,
                    null,
                    $userId,
                    "Invoice #{$sale->invoice_no} cancelled (returned {$qty} units)"
                );

                StockAuditLog::create([
                    'item_id'    => $item->id,
                    'user_id'    => $userId,
                    'action'     => 'Invoice Cancel (Return)',
                    'quantity'   => $qty,
                    'sale_id'    => $sale->id,
                    'notes'      => "Invoice #{$sale->invoice_no} cancelled: returned {$qty} units",
                    'created_at' => now(),
                ]);

                $stockMovementsLog[] = [
                    'item_id'    => $item->id,
                    'item_name'  => $item->description,
                    'direction'  => 'return',
                    'quantity'   => $qty,
                    'net_change' => +$qty,
                ];
            }

            // Financial Reversals
            if ($sale->payment_mode === 'Cash' && !empty($sale->wallet_id)) {
                $wallet = Wallet::lockForUpdate()->find($sale->wallet_id);
                $wallet?->adjustBalance(-$sale->grand_total);
            }

            if ($sale->payment_mode === 'Debit' && !empty($sale->customer_id)) {
                $due = max(0, (float) $sale->grand_total - (float) $sale->paid_amount);
                if ($due > 0) {
                    $customer = Customer::lockForUpdate()->find($sale->customer_id);
                    if ($customer) {
                        $customer->decrement('balance', $due);
                        CustomerLedgerEntry::create([
                            'customer_id'   => $customer->id,
                            'type'          => 'return',
                            'amount'        => $due,
                            'balance_after' => $customer->fresh()->balance,
                            'method'        => 'Debit',
                            'note'          => "Invoice #{$sale->invoice_no} cancelled (Debt reduced by {$due})",
                            'created_by'    => $userId,
                        ]);
                    }
                }
            }

            $sale->update([
                'status' => 'cancelled',
            ]);

            $newSnapshot = $sale->fresh(['items.item', 'customer', 'wallet', 'user'])->toSnapshotArray();
            $nextVersionNumber = ($sale->versions()->max('version_number') ?? 1) + 1;
            $user = User::find($userId);

            SaleVersion::create([
                'sale_id'         => $sale->id,
                'version_number'  => $nextVersionNumber,
                'action_type'     => 'cancelled',
                'user_id'         => $userId,
                'user_name'       => $user?->name ?? 'Staff',
                'reason'          => $reason ?? 'Invoice Cancelled by Admin',
                'old_values'      => $oldSnapshot,
                'new_values'      => $newSnapshot,
                'changes_summary' => [
                    'reason'          => $reason,
                    'stock_movements' => $stockMovementsLog,
                    'status_change'   => 'completed → cancelled',
                ],
                'ip_address'      => $ipAddress,
            ]);

            AuditLog::record(
                'sale.cancelled',
                "Invoice #{$sale->invoice_no} cancelled (Stock and balances reversed)",
                'Sale',
                $sale->id,
                ['reason' => $reason],
                $userId
            );

            return $sale;
        });
    }

    /**
     * Build side-by-side product and financial diff for historical version display.
     */
    protected function buildChangesSummary(array $old, array $new, array $stockMovements, ?string $reason): array
    {
        $oldItems = collect($old['items'] ?? [])->keyBy('item_id');
        $newItems = collect($new['items'] ?? [])->keyBy('item_id');

        $added    = [];
        $removed  = [];
        $modified = [];

        foreach ($newItems as $itemId => $item) {
            if (!$oldItems->has($itemId)) {
                $added[] = [
                    'item_id'   => $itemId,
                    'item_name' => $item['item_name'],
                    'qty'       => $item['qty'],
                    'rate'      => $item['rate'],
                    'total'     => $item['total'],
                ];
            } else {
                $oldItem = $oldItems->get($itemId);
                if ($oldItem['qty'] != $item['qty'] || $oldItem['rate'] != $item['rate']) {
                    $modified[] = [
                        'item_id'   => $itemId,
                        'item_name' => $item['item_name'],
                        'old_qty'   => $oldItem['qty'],
                        'new_qty'   => $item['qty'],
                        'old_rate'  => $oldItem['rate'],
                        'new_rate'  => $item['rate'],
                        'old_total' => $oldItem['total'],
                        'new_total' => $item['total'],
                    ];
                }
            }
        }

        foreach ($oldItems as $itemId => $item) {
            if (!$newItems->has($itemId)) {
                $removed[] = [
                    'item_id'   => $itemId,
                    'item_name' => $item['item_name'],
                    'qty'       => $item['qty'],
                    'rate'      => $item['rate'],
                    'total'     => $item['total'],
                ];
            }
        }

        return [
            'reason'          => $reason,
            'financial'       => [
                'subtotal_before'    => $old['subtotal'],
                'subtotal_after'     => $new['subtotal'],
                'discount_before'    => $old['discount_total'],
                'discount_after'     => $new['discount_total'],
                'tax_rate_before'    => $old['tax_rate'] ?? 0,
                'tax_rate_after'     => $new['tax_rate'] ?? 0,
                'tax_before'         => $old['tax_total'],
                'tax_after'          => $new['tax_total'],
                'return_adj_before'  => $old['return_adjustment'] ?? 0,
                'return_adj_after'   => $new['return_adjustment'] ?? 0,
                'grand_total_before' => $old['grand_total'],
                'grand_total_after'  => $new['grand_total'],
                'total_difference'   => round($new['grand_total'] - $old['grand_total'], 2),
            ],
            'items_added'     => $added,
            'items_removed'   => $removed,
            'items_modified'  => $modified,
            'stock_movements' => $stockMovements,
        ];
    }
}
