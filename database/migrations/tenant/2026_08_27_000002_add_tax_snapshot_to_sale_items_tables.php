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
        $tables = ['sale_items', 'debit_sale_items', 'cash_sale_items'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'tax_rate')) {
                        $table->decimal('tax_rate', 5, 2)->nullable()->default(null)->after('total');
                    }
                    if (!Schema::hasColumn($tableName, 'tax_amount')) {
                        $table->decimal('tax_amount', 10, 2)->default(0.00)->after('tax_rate');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['sale_items', 'debit_sale_items', 'cash_sale_items'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $columnsToDrop = [];
                    if (Schema::hasColumn($tableName, 'tax_rate')) {
                        $columnsToDrop[] = 'tax_rate';
                    }
                    if (Schema::hasColumn($tableName, 'tax_amount')) {
                        $columnsToDrop[] = 'tax_amount';
                    }
                    if (!empty($columnsToDrop)) {
                        $table->dropColumn($columnsToDrop);
                    }
                });
            }
        }
    }
};
