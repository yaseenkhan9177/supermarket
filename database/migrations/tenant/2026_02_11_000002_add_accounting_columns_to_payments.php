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
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('payment_method', ['Cash', 'Bank', 'Cheque'])->default('Cash')->after('payment_date');
            $table->foreignId('cash_account_id')->nullable()->after('payment_method'); // Link to accounts table
            $table->foreignId('bank_account_id')->nullable()->after('cash_account_id'); // Link to bank_accounts table
            $table->string('expense_type')->nullable()->after('paid_to_account'); // Rent, Salary, Utility, etc.
            $table->boolean('is_locked')->default(false)->after('user_id'); // Prevent edits after save
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'cash_account_id', 'bank_account_id', 'expense_type', 'is_locked']);
        });
    }
};
