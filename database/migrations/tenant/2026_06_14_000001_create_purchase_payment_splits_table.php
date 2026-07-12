<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the purchase_payment_splits table.
     * Each row represents one payment source for a bill (e.g. Cash Rs.20k, Bank Rs.15k, EasyPaisa Rs.15k).
     */
    public function up(): void
    {
        if (!Schema::hasTable('purchase_payment_splits')) {
            Schema::create('purchase_payment_splits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');

                // The label for the payment method — e.g. "Cash Drawer", "Bank Transfer", "EasyPaisa"
                $table->string('payment_method')->default('Cash Drawer');

                // Optional FK to the accounts table for proper double-entry bookkeeping
                $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();

                $table->decimal('amount', 15, 2)->default(0);

                $table->string('reference_no')->nullable(); // Cheque no, transaction ID, etc.
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_payment_splits');
    }
};
