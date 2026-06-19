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
        if (!Schema::hasTable('adjustments')) {
            Schema::create('adjustments', function (Blueprint $table) {
                $table->id();
                $table->string('adjustment_no')->unique(); // e.g., ADJ-2026-001
                $table->date('adjustment_date');

                // Type of Adjustment
                $table->string('type')->default('Correction'); // 'Correction', 'Opening Stock', 'Damaged'
                $table->text('description')->nullable();

                $table->foreignId('user_id'); // Who did it?
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('adjustment_items')) {
            Schema::create('adjustment_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('adjustment_id');
                $table->foreignId('product_id'); // Link to items table

                $table->string('item_name');
                $table->integer('system_stock');   // Stock BEFORE change
                $table->integer('physical_stock'); // Stock AFTER change
                $table->integer('difference');     // The change (+5 or -2)

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adjustment_items');
        Schema::dropIfExists('adjustments');
    }
};
