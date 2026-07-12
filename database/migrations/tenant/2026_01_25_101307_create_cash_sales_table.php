<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('cash_sales')) {
            Schema::create('cash_sales', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_no')->unique(); // e.g., CS-2026-0001
                $table->dateTime('sale_date');

                $table->foreignId('customer_id')->nullable(); // Nullable for generic "Walk-in"
                $table->string('customer_name')->default('Walk-in Customer'); // Snapshot of name
                $table->unsignedBigInteger('salesman_id')->nullable();

                // Financials
                $table->decimal('subtotal', 10, 2);
                $table->decimal('discount_total', 10, 2)->default(0);
                $table->decimal('tax_total', 10, 2)->default(0);
                $table->decimal('grand_total', 10, 2);

                // Payment Details (The Logic from Legacy App)
                $table->string('deposit_account')->default('Cash Drawer'); // Where money went
                $table->decimal('cash_received', 10, 2);
                $table->decimal('change_returned', 10, 2);

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cash_sale_items')) {
            Schema::create('cash_sale_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cash_sale_id')->constrained('cash_sales')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('items'); // UPDATED: Link to items table
                $table->string('item_name');
                $table->integer('quantity');
                $table->decimal('rate', 10, 2);
                $table->decimal('total', 10, 2);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('cash_sale_items');
        Schema::dropIfExists('cash_sales');
    }
};
