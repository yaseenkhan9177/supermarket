<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Item;
use App\Models\Batch;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * CRITICAL: This migration converts existing on_hand stock to FIFO batches
     * Run ONCE before deploying new POS system
     */
    public function up(): void
    {
        echo "Starting stock migration to FIFO batches...\n";

        // Get all items with stock > 0
        $items = Item::where('on_hand', '>', 0)->get();

        $totalItems = $items->count();
        $processed = 0;

        foreach ($items as $item) {
            // Create initial batch from current on_hand stock
            Batch::create([
                'item_id' => $item->id,
                'batch_no' => 'MIGRATION-' . date('Ymd') . '-' . str_pad($item->id, 6, '0', STR_PAD_LEFT),
                'quantity_available' => $item->on_hand,
                'cost_price' => $item->cost_rate ?? 0,
                'sale_price' => $item->sale_rate ?? 0,
                'received_at' => now(),
                'expires_at' => null,
            ]);

            $processed++;

            if ($processed % 100 == 0) {
                echo "Processed $processed / $totalItems items...\n";
            }
        }

        echo "Migration complete! Created batches for $totalItems items.\n";
        echo "Total stock migrated: " . $items->sum('on_hand') . " units\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "WARNING: Reversing this migration will DELETE all migration batches!\n";

        // Delete only migration batches
        $deleted = Batch::where('batch_no', 'LIKE', 'MIGRATION-%')->delete();

        echo "Deleted $deleted migration batches.\n";
    }
};
