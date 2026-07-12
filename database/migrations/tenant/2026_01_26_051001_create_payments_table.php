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
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->string('payment_no')->unique(); // e.g., PAY-2026-0501
                $table->date('payment_date');

                // Who are we paying?
                $table->string('paid_to_account'); // e.g., "Electricity Expense", "Supplier Name"
                $table->foreignId('supplier_id')->nullable(); // Optional link if paying a supplier

                // Money Source
                $table->string('paid_from_account')->default('Cash Drawer'); // Cash or Bank
                $table->string('cheque_no')->nullable();
                $table->date('cheque_date')->nullable();

                // Financials
                $table->decimal('amount_paid', 10, 2);
                $table->decimal('discount_received', 10, 2)->default(0); // If supplier gave us a discount

                $table->text('memo')->nullable(); // Description like "Electricity Bill Dec 2025"
                $table->foreignId('user_id'); // Who recorded this

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
