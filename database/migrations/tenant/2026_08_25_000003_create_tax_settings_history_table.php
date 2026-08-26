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
        if (!Schema::hasTable('tax_settings_history')) {
            Schema::create('tax_settings_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_name')->nullable();
                $table->boolean('previous_tax_enabled')->default(false);
                $table->boolean('new_tax_enabled')->default(false);
                $table->decimal('previous_tax_rate', 5, 2)->default(0.00);
                $table->decimal('new_tax_rate', 5, 2)->default(0.00);
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_settings_history');
    }
};
