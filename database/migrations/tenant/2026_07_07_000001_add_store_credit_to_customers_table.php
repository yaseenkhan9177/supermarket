<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds store_credit column to customers table for the Store Credit refund method.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'store_credit')) {
                // Store credit balance a customer has earned from returns.
                // Positive value = credit available to spend.
                $table->decimal('store_credit', 15, 2)->default(0)->after('balance');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'store_credit')) {
                $table->dropColumn('store_credit');
            }
        });
    }
};
