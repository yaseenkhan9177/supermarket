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
        try {
            Schema::table('sale_versions', function (Blueprint $table) {
                $table->dropForeign('sale_versions_user_id_foreign');
            });
        } catch (\Throwable $e) {
            // Already unconstrained or foreign key does not exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
