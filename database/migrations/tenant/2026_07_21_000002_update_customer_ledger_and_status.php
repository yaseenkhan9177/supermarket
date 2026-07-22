<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update customer_ledger_entries schema
        Schema::table('customer_ledger_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_ledger_entries', 'reason_category')) {
                $table->string('reason_category')->nullable()->after('type');
            }
            if (!Schema::hasColumn('customer_ledger_entries', 'reversed_entry_id')) {
                $table->unsignedBigInteger('reversed_entry_id')->nullable()->after('created_by')->index();
            }
        });

        // Modify type enum column on customer_ledger_entries to include write_off and reversal types
        DB::statement("ALTER TABLE customer_ledger_entries MODIFY COLUMN type ENUM(
            'sale',
            'return',
            'payment_received',
            'payment_made',
            'manual_adjustment',
            'write_off',
            'write_off_reversal',
            'payment_reversal'
        ) NOT NULL");

        // 2. Update customers table schema
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'status')) {
                $table->enum('status', ['active', 'written_off', 'deactivated'])->default('active')->after('store_credit');
            }
            if (!Schema::hasColumn('customers', 'written_off_at')) {
                $table->timestamp('written_off_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('customers', 'written_off_by')) {
                $table->unsignedBigInteger('written_off_by')->nullable()->after('written_off_at')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'written_off_by')) {
                $table->dropColumn('written_off_by');
            }
            if (Schema::hasColumn('customers', 'written_off_at')) {
                $table->dropColumn('written_off_at');
            }
            if (Schema::hasColumn('customers', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('customer_ledger_entries', function (Blueprint $table) {
            if (Schema::hasColumn('customer_ledger_entries', 'reversed_entry_id')) {
                $table->dropColumn('reversed_entry_id');
            }
            if (Schema::hasColumn('customer_ledger_entries', 'reason_category')) {
                $table->dropColumn('reason_category');
            }
        });
    }
};
