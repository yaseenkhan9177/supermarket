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
        // Unified Sales Table
        if (!Schema::hasTable('sales')) {
            Schema::create('sales', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_no')->unique(); // e.g., INV-2026-0001
                $table->dateTime('sale_date');

                $table->foreignId('customer_id')->nullable();
                $table->string('customer_name')->default('Walk-in Customer');
                $table->unsignedBigInteger('user_id')->nullable(); // Salesman/User

                // Payment Info
                $table->string('payment_mode')->default('Cash'); // Cash, Credit, Bank, Split

                // Financials
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('discount_total', 15, 2)->default(0);
                $table->decimal('tax_total', 15, 2)->default(0);
                $table->decimal('grand_total', 15, 2)->default(0);

                // Status
                $table->string('status')->default('completed'); // completed, pending, returned

                $table->timestamps();
            });
        }

        // Unified Sale Items Table
        if (!Schema::hasTable('sale_items')) {
            Schema::create('sale_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
                $table->foreignId('item_id')->nullable(); // Link to items table if exists

                $table->string('item_name');
                $table->integer('qty');
                $table->decimal('rate', 15, 2);
                $table->decimal('total', 15, 2);

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
