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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_title'); // e.g., "Meezan Bank - Main" or "Cash Drawer 1"
            $table->string('bank_name')->nullable(); // e.g., "Meezan Bank", "Internal"
            $table->string('account_number')->nullable(); // Actual Bank Acct No
            $table->string('branch_code')->nullable();

            // Link to General Ledger (GL)
            // When we create a bank here, we auto-create a GL account (e.g. 01-005)
            $table->string('gl_code')->unique();

            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);

            $table->text('address')->nullable();
            $table->string('contact_person')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
