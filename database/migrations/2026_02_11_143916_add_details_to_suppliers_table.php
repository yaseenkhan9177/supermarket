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
            if (Schema::hasColumn('suppliers', 'balance') && !Schema::hasColumn('suppliers', 'current_balance')) {
                $table->renameColumn('balance', 'current_balance');
            }
        });

        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'opening_balance')) {
                $table->decimal('opening_balance', 15, 2)->default(0)->after('address');
            }
            if (!Schema::hasColumn('suppliers', 'balance_type')) {
                $table->string('balance_type')->default('payable')->after('opening_balance'); // payable, advance
            }
            if (!Schema::hasColumn('suppliers', 'status')) {
                $afterCol = Schema::hasColumn('suppliers', 'current_balance') ? 'current_balance' : 'balance';
                $table->string('status')->default('active')->after($afterCol); // active, blocked
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['opening_balance', 'balance_type', 'status']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'current_balance') && !Schema::hasColumn('suppliers', 'balance')) {
                $table->renameColumn('current_balance', 'balance');
            }
        });
    }
};
