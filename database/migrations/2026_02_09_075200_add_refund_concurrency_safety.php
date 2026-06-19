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
        // Add invoice locking for concurrency safety
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'refund_locked')) {
                $table->boolean('refund_locked')->default(false)->after('status');
            }
            if (!Schema::hasColumn('sales', 'refund_locked_by')) {
                $table->foreignId('refund_locked_by')->nullable()->after('refund_locked')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('sales', 'refund_locked_at')) {
                $table->timestamp('refund_locked_at')->nullable()->after('refund_locked_by');
            }
        });

        // Add refund mode to refunds table
        Schema::table('refunds', function (Blueprint $table) {
            if (!Schema::hasColumn('refunds', 'refund_mode')) {
                $table->enum('refund_mode', ['CASH', 'ORIGINAL_METHOD', 'STORE_CREDIT'])->default('ORIGINAL_METHOD')->after('status');
            }
        });

        // Add configurable manager threshold to company_settings
        if (Schema::hasTable('company_settings')) {
            Schema::table('company_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('company_settings', 'refund_manager_threshold')) {
                    $table->decimal('refund_manager_threshold', 10, 2)->default(10000)->after('id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['refund_locked_by']);
            $table->dropColumn(['refund_locked', 'refund_locked_by', 'refund_locked_at']);
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->dropColumn('refund_mode');
        });

        if (Schema::hasTable('company_settings')) {
            Schema::table('company_settings', function (Blueprint $table) {
                $table->dropColumn('refund_manager_threshold');
            });
        }
    }
};
