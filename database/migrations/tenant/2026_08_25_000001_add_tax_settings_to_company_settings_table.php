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
        Schema::table('company_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('company_settings', 'tax_enabled')) {
                $table->boolean('tax_enabled')->default(false)->after('currency_code');
            }
            if (!Schema::hasColumn('company_settings', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0.00)->after('tax_enabled');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            if (Schema::hasColumn('company_settings', 'tax_rate')) {
                $table->dropColumn('tax_rate');
            }
            if (Schema::hasColumn('company_settings', 'tax_enabled')) {
                $table->dropColumn('tax_enabled');
            }
        });
    }
};
