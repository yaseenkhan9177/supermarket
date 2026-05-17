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
        // Using raw SQL to avoid doctrine/dbal dependency issues usually required for ->change()
        DB::statement("ALTER TABLE purchases MODIFY COLUMN paid_from_account VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE purchases MODIFY COLUMN paid_from_account VARCHAR(255) NOT NULL DEFAULT 'Cash Drawer'");
    }
};
