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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 50)->unique()->comment('Auto-generated: ADJ-YYYYMMDD-XX');
            $table->date('date');
            $table->enum('type', ['correction', 'damage', 'loss', 'transfer', 'stock_take'])
                ->comment('Type of stock adjustment');
            $table->text('reason')->nullable()->comment('Explanation for the adjustment');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Staff member who performed the audit');
            $table->integer('total_items')->default(0)->comment('Count of products adjusted');
            $table->timestamps();

            // Indexes for faster lookups
            $table->index('date');
            $table->index('type');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
