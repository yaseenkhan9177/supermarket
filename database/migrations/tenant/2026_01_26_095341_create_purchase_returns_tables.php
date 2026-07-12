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
        if (!Schema::hasTable('purchase_returns')) {
            Schema::create('purchase_returns', function (Blueprint $table) {
                $table->id();
                $table->string('return_no')->unique(); // PR-2026-001
                $table->date('return_date');
                $table->string('vendor_bill_no')->nullable(); // Original Bill Reference

                // Removing strict constraints to fix errno: 150
                $table->foreignId('supplier_id')->index();

                // Financials
                $table->decimal('total_amount', 12, 2);

                // Logic: Did we get Cash back or Credit note?
                $table->enum('refund_mode', ['Cash', 'Credit Note'])->default('Credit Note');

                $table->text('memo')->nullable();
                $table->foreignId('user_id')->index();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('purchase_return_items')) {
            Schema::create('purchase_return_items', function (Blueprint $table) {
                $table->id();
                // Relaxing constraints here too
                $table->foreignId('purchase_return_id')->index();
                $table->foreignId('item_id')->index();

                $table->integer('qty');
                $table->decimal('rate', 10, 2); // Cost Price
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
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
    }
};
