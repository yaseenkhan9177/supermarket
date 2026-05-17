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
        // Modify database_name to be nullable
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('database_name')->nullable()->change();
        });

        // Modify status enum using raw statement as Doctrine might not support enum modification easily without extra packages
        // Or if we can't use change(), we just drop and re-add constraint or use raw sql
        DB::statement("ALTER TABLE tenants MODIFY COLUMN status ENUM('active', 'suspended', 'pending', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('database_name')->nullable(false)->change();
        });

        DB::statement("ALTER TABLE tenants MODIFY COLUMN status ENUM('active', 'suspended', 'pending') NOT NULL DEFAULT 'pending'");
    }
};
