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
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('store_name');
            $table->string('owner_name');
            $table->string('owner_email');
            $table->string('owner_phone');
            $table->enum('status', ['active', 'suspended', 'pending'])->default('pending');
            $table->string('database_name')->unique();
            $table->string('subscription_plan'); // basic, premium, enterprise
            $table->date('valid_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
