<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Current confirmed ENUM values on refunds.refund_mode (from migration 2026_02_09_075200):
     *   CASH | ORIGINAL_METHOD | STORE_CREDIT   (default: ORIGINAL_METHOD)
     *
     * We add REDUCE_DEBIT for the "Reduce Debit Balance" refund method on the returns page.
     * Using raw SQL because doctrine/dbal is not installed in this project.
     */
    public function up(): void
    {
        // Extend refund_mode ENUM — preserves all 3 existing values, adds REDUCE_DEBIT
        if (Schema::hasColumn('refunds', 'refund_mode')) {
            DB::statement("ALTER TABLE refunds MODIFY COLUMN refund_mode ENUM('CASH','ORIGINAL_METHOD','STORE_CREDIT','REDUCE_DEBIT') NOT NULL DEFAULT 'ORIGINAL_METHOD'");
        }

        // Add sale_source and original_bill_id to refund_items so we can trace exactly
        // which bill (cash_sale / debit_sale / sale) each returned item came from.
        Schema::table('refund_items', function (Blueprint $table) {
            if (!Schema::hasColumn('refund_items', 'sale_source')) {
                // Values: 'cash_sale' | 'debit_sale' | 'pos_sale'
                $table->string('sale_source')->nullable()->after('note');
            }
            if (!Schema::hasColumn('refund_items', 'original_bill_id')) {
                // The PK of the originating bill header row (cash_sales.id / debit_sales.id / sales.id)
                $table->unsignedBigInteger('original_bill_id')->nullable()->after('sale_source');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore ENUM to original 3 values
        if (Schema::hasColumn('refunds', 'refund_mode')) {
            DB::statement("ALTER TABLE refunds MODIFY COLUMN refund_mode ENUM('CASH','ORIGINAL_METHOD','STORE_CREDIT') NOT NULL DEFAULT 'ORIGINAL_METHOD'");
        }

        Schema::table('refund_items', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('refund_items', 'sale_source')) {
                $cols[] = 'sale_source';
            }
            if (Schema::hasColumn('refund_items', 'original_bill_id')) {
                $cols[] = 'original_bill_id';
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
