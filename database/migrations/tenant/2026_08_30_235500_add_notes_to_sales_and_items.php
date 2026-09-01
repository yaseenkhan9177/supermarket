<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Invoice-level note
        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) {
                if (!Schema::hasColumn('sales', 'note')) {
                    $table->text('note')->nullable();
                }
            });
        }

        // Product-level note for each line item
        if (Schema::hasTable('sale_items')) {
            Schema::table('sale_items', function (Blueprint $table) {
                if (!Schema::hasColumn('sale_items', 'note')) {
                    $table->text('note')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'note')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('note');
            });
        }
        if (Schema::hasTable('sale_items') && Schema::hasColumn('sale_items', 'note')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->dropColumn('note');
            });
        }
    }
};
