<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the supplier_return_items table (one row per item in a return).
     */
    public function up(): void
    {
        if (!Schema::hasTable('supplier_return_items')) {
            Schema::create('supplier_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_return_id')->constrained('supplier_returns')->onDelete('cascade');
                $table->foreignId('item_id')->constrained('items');

                // Batch being returned (nullable in case batch tracking isn't used for old stock)
                $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();

                $table->string('batch_no')->nullable();
                $table->date('expiry_date')->nullable();
                $table->integer('qty_returned');
                $table->decimal('cost_rate', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_return_items');
    }
};
