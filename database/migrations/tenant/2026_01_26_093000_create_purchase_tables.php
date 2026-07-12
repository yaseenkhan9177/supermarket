<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Suppliers
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->decimal('balance', 12, 2)->default(0); // For credit purchases
                $table->timestamps();
            });
        }

        // 2. Purchase Header
        if (!Schema::hasTable('purchases')) {
            Schema::create('purchases', function (Blueprint $table) {
                $table->id();
                $table->string('purchase_no')->unique(); // PO-2026-001
                $table->string('vendor_bill_no')->nullable();
                $table->date('purchase_date');

                $table->foreignId('supplier_id');
                $table->string('paid_from_account')->default('Cash Drawer');

                $table->decimal('subtotal', 12, 2);
                $table->decimal('tax_amount', 12, 2)->default(0);
                $table->decimal('discount', 12, 2)->default(0);
                $table->decimal('net_total', 12, 2);

                $table->text('memo')->nullable();
                $table->foreignId('user_id');
                $table->timestamps();
            });
        }

        // 3. Purchase Items
        if (!Schema::hasTable('purchase_items')) {
            Schema::create('purchase_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id');

                // Note: Using 'item_id' to match your Item model
                $table->foreignId('item_id');

                $table->string('batch_no')->nullable();
                $table->date('expiry_date')->nullable();

                $table->integer('qty');
                $table->decimal('cost_rate', 10, 2);
                $table->decimal('total', 12, 2);

                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('suppliers');
    }
};
