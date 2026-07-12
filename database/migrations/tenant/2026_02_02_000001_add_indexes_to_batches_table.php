<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            // Index for FIFO queries (most common query pattern)
            $table->index(['item_id', 'received_at'], 'idx_batches_fifo');

            // Index for stock availability checks
            $table->index(['item_id', 'quantity_available'], 'idx_batches_stock');

            // Index for batch lookup
            $table->index('batch_no', 'idx_batches_batch_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropIndex('idx_batches_fifo');
            $table->dropIndex('idx_batches_stock');
            $table->dropIndex('idx_batches_batch_no');
        });
    }
};
