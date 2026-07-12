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
        if (!Schema::hasColumn('items', 'min_stock_level')) {
            Schema::table('items', function (Blueprint $table) {
                $table->integer('min_stock_level')->default(0)->after('min_stock');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('items', 'min_stock_level')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('min_stock_level');
            });
        }
    }
};
