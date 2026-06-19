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
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->string('journal_no')->unique(); // JV-2026-001
            $table->date('date');
            $table->text('memo')->nullable();

            // Safety check: Debits must equal Credits
            $table->decimal('total_debit', 12, 2);
            $table->decimal('total_credit', 12, 2);

            $table->foreignId('user_id')->index(); // Relaxed constraint
            $table->timestamps();
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->index(); // Relaxed constraint

            // Linking to your Chart of Accounts
            $table->string('account_code');
            $table->string('account_name');

            $table->decimal('debit', 12, 2)->default(0);
            $table->decimal('credit', 12, 2)->default(0);

            $table->text('description')->nullable(); // Line-level memo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('journals');
    }
};
