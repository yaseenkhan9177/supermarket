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
        Schema::create('general_ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('gl_code')->unique(); // e.g., 01-001
            $table->string('gl_type'); // e.g., "01: CASH/BANKS"
            $table->string('name'); // e.g., Cash Drawer
            $table->string('account_type')->default('ASSETS'); // ASSETS, LIABILITIES, etc.

            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_ledger_accounts');
    }
};
