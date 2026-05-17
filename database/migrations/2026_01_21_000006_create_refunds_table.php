<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('refunds')) {
            Schema::create('refunds', function (Blueprint $table) {
                $table->id();
                $table->string('credit_no')->unique();
                $table->date('refund_date');

                // Parties
                $table->foreignId('customer_id')->nullable();
                $table->foreignId('salesman_id')->nullable();

                // Financials
                $table->string('paid_from_account')->default('Cash Drawer');
                $table->text('memo')->nullable();
                $table->decimal('total_amount', 10, 2);

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('refund_items')) {
            Schema::create('refund_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('refund_id')->constrained()->onDelete('cascade');
                $table->foreignId('product_id')->constrained('items');
                $table->string('item_name');
                $table->integer('quantity');
                $table->decimal('rate', 10, 2);
                $table->decimal('net_amount', 10, 2);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('refund_items');
        Schema::dropIfExists('refunds');
    }
};
