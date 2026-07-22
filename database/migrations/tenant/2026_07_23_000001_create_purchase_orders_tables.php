<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for Purchase Order (PO) workflow and landed cost tracking.
     */
    public function up(): void
    {
        // 1. purchase_orders
        if (!Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->string('po_number')->unique();
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->string('status', 50)->default('draft');
                $table->date('order_date')->nullable();
                $table->date('expected_date')->nullable();
                $table->text('note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();
            });
        } else {
            DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'draft'");
            Schema::table('purchase_orders', function (Blueprint $table) {
                if (Schema::hasColumn('purchase_orders', 'estimated_total')) {
                    $table->decimal('estimated_total', 12, 2)->nullable()->default(0)->change();
                }
                if (Schema::hasColumn('purchase_orders', 'user_id')) {
                    $table->unsignedBigInteger('user_id')->nullable()->change();
                }
                if (Schema::hasColumn('purchase_orders', 'order_date')) {
                    $table->date('order_date')->nullable()->change();
                }
                if (!Schema::hasColumn('purchase_orders', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->index();
                }
                if (!Schema::hasColumn('purchase_orders', 'expected_date')) {
                    $table->date('expected_date')->nullable();
                }
                if (!Schema::hasColumn('purchase_orders', 'note')) {
                    $table->text('note')->nullable();
                }
            });
        }

        // 2. purchase_order_items
        if (!Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
                $table->foreignId('item_id')->constrained('items');
                $table->decimal('quantity_ordered', 15, 2);
                $table->decimal('quantity_received', 15, 2)->default(0);
                $table->decimal('unit_cost', 15, 2);
                $table->decimal('line_total', 15, 2);
                $table->timestamps();
            });
        } else {
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

        // 3. purchase_order_expenses (Landed Cost Tracking)
        if (!Schema::hasTable('purchase_order_expenses')) {
            Schema::create('purchase_order_expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
                $table->string('expense_type')->default('freight'); // freight, rent, tax, labor, other
                $table->text('description')->nullable();
                $table->decimal('amount', 15, 2);
                $table->unsignedBigInteger('added_by')->nullable()->index();
                $table->unsignedBigInteger('payment_id')->nullable()->index(); // linked payment if auto-created post 100% receipt
                $table->timestamps();
            });
        }

        // 4. purchase_order_receipts (Receiving Events)
        if (!Schema::hasTable('purchase_order_receipts')) {
            Schema::create('purchase_order_receipts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
                $table->string('receipt_no')->unique();
                $table->decimal('allocated_expense_amount', 15, 2)->default(0);
                $table->decimal('supplier_total_amount', 15, 2)->default(0);
                $table->decimal('landed_total_amount', 15, 2)->default(0);
                $table->text('note')->nullable();
                $table->unsignedBigInteger('received_by')->nullable()->index();
                $table->timestamps();
            });
        }

        // 5. purchase_order_receipt_items (Item Receiving Line Items)
        if (!Schema::hasTable('purchase_order_receipt_items')) {
            Schema::create('purchase_order_receipt_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('receipt_id')->constrained('purchase_order_receipts')->onDelete('cascade');
                $table->foreignId('po_item_id')->constrained('purchase_order_items')->onDelete('cascade');
                $table->foreignId('item_id')->constrained('items');
                $table->foreignId('batch_id')->nullable()->constrained('batches')->onDelete('set null');
                $table->decimal('quantity_received', 15, 2);
                $table->decimal('unit_supplier_cost', 15, 2);
                $table->decimal('unit_landed_cost', 15, 2);
                $table->decimal('sale_price_set', 15, 2);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_receipt_items');
        Schema::dropIfExists('purchase_order_receipts');
        Schema::dropIfExists('purchase_order_expenses');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
