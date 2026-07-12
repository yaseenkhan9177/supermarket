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
        if (!Schema::hasColumn('purchases', 'paid_from_account')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->string('paid_from_account')->nullable();
            });
        } else {
            DB::statement("ALTER TABLE purchases MODIFY COLUMN paid_from_account VARCHAR(255) NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('purchases', 'paid_from_account')) {
            DB::statement("ALTER TABLE purchases MODIFY COLUMN paid_from_account VARCHAR(255) NOT NULL DEFAULT 'Cash Drawer'");
        }
    }
};
