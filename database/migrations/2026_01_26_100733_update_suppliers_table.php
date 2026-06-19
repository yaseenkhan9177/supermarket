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
        Schema::table('suppliers', function (Blueprint $table) {
            // Basic Info
            $table->string('email')->nullable();
            $table->string('city')->nullable();
            $table->string('tax_id')->nullable(); // NTN / VAT ID

            // Financials
            $table->string('account_code')->nullable(); // e.g., 060010 (GL Code)
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->integer('credit_days')->default(30); // Net 30 terms


            // Contact Person
            $table->string('contact_person')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['email', 'city', 'tax_id', 'account_code', 'credit_limit', 'credit_days', 'opening_balance', 'contact_person']);
        });
    }
};
