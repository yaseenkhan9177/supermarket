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
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'company_name')) {
                $table->string('company_name')->nullable();
            }
            if (!Schema::hasColumn('suppliers', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (Schema::hasColumn('suppliers', 'balance')) {
                // Modifying existing column to increase precision
                $table->decimal('balance', 15, 2)->default(0)->change();
            } else {
                $table->decimal('balance', 15, 2)->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'is_active']);
            // Not reverting balance change to avoid data loss or complexity, or can revert to 12,2
            $table->decimal('balance', 12, 2)->default(0)->change();
        });
    }
};
