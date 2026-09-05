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
        if (!Schema::hasTable('customer_balance_conversions')) {
            Schema::create('customer_balance_conversions', function (Blueprint $table) {
                $table->id();
                $table->string('tenant_id')->index();
                $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                $table->unsignedBigInteger('super_admin_id')->nullable();
                $table->string('super_admin_name')->nullable();
                $table->integer('customers_processed')->default(0);
                $table->integer('positive_converted')->default(0);
                $table->integer('negative_converted')->default(0);
                $table->integer('zero_unchanged')->default(0);
                $table->decimal('total_balance_before', 15, 2)->default(0);
                $table->decimal('total_balance_after', 15, 2)->default(0);
                $table->timestamp('converted_at');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_balance_conversions');
    }
};
