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
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            // Polymorphic: model_type + model_id
            $table->morphs('model');

            // --- 1. Transaction Modules (JSON stores: {view:1, add:1, edit:1, delete:0}) ---
            $table->json('sales_cash')->nullable();      // Legacy: CASH Sales
            $table->json('sales_debt')->nullable();      // Legacy: DEBT Sales
            $table->json('sales_return_cash')->nullable(); // Legacy: CASH Return
            $table->json('sales_return_crdt')->nullable(); // Legacy: CRDT Return

            $table->json('inventory_transfer')->nullable();
            $table->json('accounts_receipts')->nullable();
            $table->json('accounts_payments')->nullable();
            $table->json('items_stock')->nullable(); // Added based on UI "Items & Stock Management"

            // --- 2. Specific Toggles (Booleans) ---
            $table->boolean('can_change_discount')->default(false);
            $table->boolean('can_close_session')->default(false);
            $table->boolean('allow_credit_override')->default(false); // Legacy: Allow credit above limit
            $table->boolean('view_all_counters')->default(false);

            // --- 3. Constraints ---
            $table->integer('min_qty_limit')->default(0); // Legacy: Minimum QTY to use

            // --- 4. Admin System Rights ---
            $table->boolean('sys_add_users')->default(false);
            $table->boolean('sys_restore_data')->default(false);
            $table->boolean('sys_view_reports')->default(true);
            $table->boolean('sys_reconcile_banks')->default(false); // Added based on UI

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
    }
};
