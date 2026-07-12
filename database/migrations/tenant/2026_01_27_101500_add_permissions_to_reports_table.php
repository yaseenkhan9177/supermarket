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
        Schema::table('reports', function (Blueprint $table) {
            // Visibility Flags
            $table->boolean('is_hidden_global')->default(false); // Hide to All
            $table->boolean('is_owner_only')->default(false);  // Hide to all except me
            $table->boolean('requires_permission')->default(false); // For not permitted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['is_hidden_global', 'is_owner_only', 'requires_permission']);
        });
    }
};
