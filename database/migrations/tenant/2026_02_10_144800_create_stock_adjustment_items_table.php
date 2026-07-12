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
        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->constrained('stock_adjustments')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('items')->comment('Links to items table');
            $table->integer('system_quantity')->comment('Stock level before adjustment');
            $table->integer('physical_quantity')->comment('Actual counted stock')->unsigned();
            $table->integer('difference')->comment('Calculated: physical - system');
            $table->timestamps();

            // Indexes for performance
            $table->index('stock_adjustment_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_items');
    }
};
