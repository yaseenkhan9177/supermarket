<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'paid_until')) {
                $table->date('paid_until')->nullable()->after('status');
            }
            if (!Schema::hasColumn('tenants', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->foreign('approved_by')->references('id')->on('super_admins')->nullOnDelete();
            }
            if (!Schema::hasColumn('tenants', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
        });

        // Update status ENUM to include 'expired'
        DB::statement("ALTER TABLE tenants MODIFY COLUMN status ENUM('pending', 'active', 'suspended', 'expired', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            }
            if (Schema::hasColumn('tenants', 'paid_until')) {
                $table->dropColumn('paid_until');
            }
            if (Schema::hasColumn('tenants', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('tenants', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }
            if (Schema::hasColumn('tenants', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });

        DB::statement("ALTER TABLE tenants MODIFY COLUMN status ENUM('active', 'suspended', 'pending', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};
