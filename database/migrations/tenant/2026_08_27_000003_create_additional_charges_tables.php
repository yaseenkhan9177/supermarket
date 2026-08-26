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
        if (!Schema::hasTable('additional_charges')) {
            Schema::create('additional_charges', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('type', ['fixed', 'percentage'])->default('fixed');
                $table->decimal('value', 10, 2)->default(0.00);
                $table->boolean('is_enabled')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sale_additional_charges')) {
            Schema::create('sale_additional_charges', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sale_id');
                $table->unsignedBigInteger('additional_charge_id')->nullable();
                $table->string('name');
                $table->enum('type', ['fixed', 'percentage']);
                $table->decimal('value', 10, 2)->default(0.00);
                $table->decimal('amount', 10, 2)->default(0.00);
                $table->timestamps();

                $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            });
        }

        $salesTables = ['sales', 'debit_sales', 'cash_sales'];
        foreach ($salesTables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'additional_charges_total')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->decimal('additional_charges_total', 10, 2)->default(0.00);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $salesTables = ['sales', 'debit_sales', 'cash_sales'];
        foreach ($salesTables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'additional_charges_total')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('additional_charges_total');
                });
            }
        }

        Schema::dropIfExists('sale_additional_charges');
        Schema::dropIfExists('additional_charges');
    }
};
