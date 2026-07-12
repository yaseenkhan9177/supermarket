<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('receipts')) {
            Schema::create('receipts', function (Blueprint $table) {
                $table->id();
                $table->string('receipt_no')->unique(); // e.g., REC-2026-1001
                $table->date('receipt_date');

                // Who paid?
                $table->foreignId('customer_id')->constrained('customers');
                $table->unsignedBigInteger('salesman_id')->nullable()->index() /* central user ref */; // Assuming users table for salesman

                // Money Details
                $table->decimal('amount_received', 10, 2);
                $table->decimal('discount_given', 10, 2)->default(0);
                $table->decimal('total_adjusted', 10, 2); // Amount + Discount

                // Deposit Details
                $table->string('deposit_account')->default('Cash Account'); // Cash or Bank
                $table->string('payment_mode')->default('Cash'); // Cash, Cheque, Online

                // Cheque / Bank Details
                $table->string('cheque_no')->nullable();
                $table->date('cheque_date')->nullable();
                $table->string('bank_name')->nullable();

                $table->text('memo')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('receipts');
    }
};
