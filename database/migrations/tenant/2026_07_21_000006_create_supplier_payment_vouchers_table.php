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
        if (!Schema::hasTable('supplier_payment_vouchers')) {
            Schema::create('supplier_payment_vouchers', function (Blueprint $table) {
                $table->id();
                $table->string('voucher_number')->unique();
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->foreignId('ledger_entry_id')->constrained('supplier_ledger_entries')->onDelete('cascade');
                $table->decimal('amount', 12, 2);
                $table->decimal('remaining_balance', 12, 2); // Supplier's payable balance after payment
                $table->string('payment_method');
                $table->unsignedBigInteger('paid_by')->nullable()->index();
                $table->string('store_name');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_payment_vouchers');
    }
};
