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
        Schema::table('refund_items', function (Blueprint $table) {
            // Add note field for refund reasons that require additional explanation
            if (!Schema::hasColumn('refund_items', 'note')) {
                $table->text('note')->nullable()->after('condition');
            }
        });

        Schema::table('refunds', function (Blueprint $table) {
            // Add processed_by to track who actually processed the refund
            if (!Schema::hasColumn('refunds', 'processed_by')) {
                $table->foreignId('processed_by')->nullable()->after('salesman_id')->constrained('users')->onDelete('set null');
            }

            // Add approval timestamp
            if (!Schema::hasColumn('refunds', 'approval_timestamp')) {
                $table->timestamp('approval_timestamp')->nullable()->after('authorized_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refund_items', function (Blueprint $table) {
            $table->dropColumn('note');
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropColumn(['processed_by', 'approval_timestamp']);
        });
    }
};
