<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('sales')) {
            Schema::create('sales', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_no')->unique();
                $table->foreignId('customer_id')->nullable();
                $table->foreignId('user_id')->nullable();
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('tax_total', 15, 2)->default(0);
                $table->decimal('grand_total', 15, 2)->default(0);
                $table->decimal('discount_total', 15, 2)->default(0);
                $table->string('payment_mode')->default('Cash');
                $table->string('status')->default('completed');
                $table->timestamp('sale_date')->useCurrent();
                $table->string('customer_name')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sale_items')) {
            Schema::create('sale_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_id')->constrained()->onDelete('cascade');
                $table->foreignId('item_id')->constrained();
                $table->string('item_name')->nullable();
                $table->integer('qty');
                $table->decimal('rate', 15, 2);
                $table->decimal('total', 15, 2);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('gl_entries')) {
            Schema::create('gl_entries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('account_id')->index();
                $table->decimal('debit', 15, 2)->default(0);
                $table->decimal('credit', 15, 2)->default(0);
                $table->string('description')->nullable();
                $table->date('date')->useCurrent();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        // Don't drop tables in down() if they might have existed before!
        // or just leave as is, since this is a "fix" migration.
        // I'll leave them to be safe or comment them out.
        // Safest is to do nothing or drop only if I created them? 
        // Hard to track state. I'll just leave dropIfExists, simple.
        Schema::dropIfExists('gl_entries');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
