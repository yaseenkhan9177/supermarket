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
        // 1. Expense Categories Table
        if (!Schema::hasTable('expense_categories')) {
            Schema::create('expense_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('code')->nullable()->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Expenses Table
        if (!Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table) {
                $table->id();
                $table->string('expense_no')->unique(); // e.g. EXP-20260824-001
                $table->date('expense_date');

                // Category
                $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
                $table->string('category_name'); // Denormalized for resilience

                // Details & Amount
                $table->string('description');
                $table->decimal('amount', 15, 2);

                // Payment / Accounting Info
                $table->string('payment_method')->default('Cash'); // Cash, Bank, Cheque, Card, Other
                $table->foreignId('wallet_id')->nullable()->constrained('wallets')->nullOnDelete();
                $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
                $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete(); // Synced Payment record

                // Reference / Cheque
                $table->string('reference_no')->nullable(); // Invoice #, receipt #, bill #, cheque #
                $table->text('notes')->nullable();
                $table->string('attachment_path')->nullable();

                // User Tracking
                $table->unsignedBigInteger('user_id')->nullable()->index();

                $table->timestamps();
                $table->softDeletes();

                // Indexes for performant filtering
                $table->index('expense_date');
                $table->index('payment_method');
                $table->index('wallet_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
