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
        // 1. Refunds Table
        Schema::table('refunds', function (Blueprint $table) {
            $table->foreignId('original_sale_id')->nullable()->after('id')->constrained('sales')->nullOnDelete();
            $table->unsignedBigInteger('authorized_by')->nullable()->after('salesman_id')->index(); // Central employee ref — no FK across DBs
            $table->string('reason')->nullable()->after('memo'); // Global reason if needed
            $table->string('status')->default('completed')->after('total_amount'); // completed, pending, rejected
        });

        // 2. Refund Items Table
        Schema::table('refund_items', function (Blueprint $table) {
            $table->foreignId('sale_item_id')->nullable()->after('product_id')->constrained('sale_items')->nullOnDelete();
            $table->string('reason')->nullable()->after('net_amount'); // Per-item reason
            $table->string('condition')->default('sellable')->after('reason'); // sellable, damaged, expired, waste
        });

        // Note: employees.pin is in the central DB — not managed in tenant migrations
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropColumn(['original_sale_id', 'authorized_by', 'reason', 'status']);
        });

        Schema::table('refund_items', function (Blueprint $table) {
            $table->dropForeign(['sale_item_id']);
            $table->dropColumn(['sale_item_id', 'reason', 'condition']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('pin');
        });
    }
};
