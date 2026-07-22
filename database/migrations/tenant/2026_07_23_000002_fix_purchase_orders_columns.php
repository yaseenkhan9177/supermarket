<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations to fix purchase_orders and purchase_order_items tables across all tenant DBs.
     */
    public function up(): void
    {
        if (Schema::hasTable('purchase_orders')) {
            // Alter enum status to string if needed
            try {
                DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'draft'");
            } catch (\Throwable $e) {}

            Schema::table('purchase_orders', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_orders', 'estimated_total')) {
                    $table->decimal('estimated_total', 12, 2)->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('purchase_orders', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->change();
                }
                if (!Schema::hasColumn('purchase_orders', 'order_date')) {
                    $table->date('order_date')->nullable();
                } else {
                    $table->date('order_date')->nullable()->change();
                }
                if (!Schema::hasColumn('purchase_orders', 'expected_date')) {
                    $table->date('expected_date')->nullable();
                }
                if (!Schema::hasColumn('purchase_orders', 'note')) {
                    $table->text('note')->nullable();
                }
                if (!Schema::hasColumn('purchase_orders', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->index();
                }
            });
        }

        if (Schema::hasTable('purchase_order_items')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_order_items', 'qty')) {
                    $table->integer('qty')->nullable()->change();
                }
                if (Schema::hasColumn('purchase_order_items', 'rate')) {
                    $table->decimal('rate', 10, 2)->nullable()->change();
                }
                if (Schema::hasColumn('purchase_order_items', 'total')) {
                    $table->decimal('total', 12, 2)->nullable()->change();
                }
                if (!Schema::hasColumn('purchase_order_items', 'quantity_ordered')) {
                    $table->decimal('quantity_ordered', 15, 2)->default(0);
                }
                if (!Schema::hasColumn('purchase_order_items', 'quantity_received')) {
                    $table->decimal('quantity_received', 15, 2)->default(0);
                }
                if (!Schema::hasColumn('purchase_order_items', 'unit_cost')) {
                    $table->decimal('unit_cost', 15, 2)->default(0);
                }
                if (!Schema::hasColumn('purchase_order_items', 'line_total')) {
                    $table->decimal('line_total', 15, 2)->default(0);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
