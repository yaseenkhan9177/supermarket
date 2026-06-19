<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Rename 'name' to 'full_name' for consistency
            $table->renameColumn('name', 'full_name');

            // Add new fields
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('designation')->default('Salesman')->after('role');
            $table->string('employee_code')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(0.00);
            $table->string('avatar_path')->nullable();
            $table->boolean('is_active')->default(true);
        });

        // Copy data from 'role' to 'designation' then drop 'role'
        DB::statement('UPDATE employees SET designation = role');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->renameColumn('full_name', 'name');
            $table->string('role')->default('Salesman');
            $table->dropColumn([
                'address',
                'city',
                'designation',
                'employee_code',
                'commission_rate',
                'avatar_path',
                'is_active'
            ]);
        });
    }
};
