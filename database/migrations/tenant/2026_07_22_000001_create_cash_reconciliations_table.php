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
        if (!Schema::hasTable('cash_reconciliations')) {
            Schema::create('cash_reconciliations', function (Blueprint $table) {
                $table->id();
                $table->date('date')->index();
                $table->decimal('opening_cash', 12, 2)->default(0.00);
                $table->decimal('expected_cash', 12, 2)->default(0.00);
                $table->decimal('counted_cash', 12, 2)->default(0.00);
                $table->decimal('difference', 12, 2)->default(0.00);
                $table->text('note')->nullable();
                $table->unsignedBigInteger('closed_by')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_reconciliations');
    }
};
