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
        if (!Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->string('po_number')->unique(); // PO-2026-001
                $table->date('order_date');
                $table->date('delivery_date')->nullable();

                $table->foreignId('supplier_id');
                $table->enum('status', ['Pending', 'Received', 'Cancelled'])->default('Pending');

                // Financials (Estimates)
                $table->decimal('estimated_total', 12, 2);

                $table->text('memo')->nullable();
                $table->foreignId('user_id');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id');
                $table->foreignId('item_id');

                $table->integer('qty');
                $table->decimal('rate', 10, 2); // Estimated Cost
                $table->decimal('total', 12, 2);

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
