<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sale_items') && !Schema::hasColumn('sale_items', 'batch_id')) {
            Schema::table('sale_items', function (Blueprint $table) {
                // Links each sale line to the exact batch it was drawn from (FIFO)
                $table->foreignId('batch_id')
                    ->nullable()
                    ->after('item_id')
                    ->constrained('batches')
                    ->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sale_items') && Schema::hasColumn('sale_items', 'batch_id')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->dropForeign(['batch_id']);
                $table->dropColumn('batch_id');
            });
        }
    }
};
