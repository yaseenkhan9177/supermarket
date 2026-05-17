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
        if (!Schema::hasTable('transfers')) {
            Schema::create('transfers', function (Blueprint $table) {
                $table->id();
                $table->string('transfer_no')->unique(); // e.g., TRF-2026-001
                $table->date('transfer_date');

                // The Movement
                $table->string('from_account'); // Source (Withdraw From)
                $table->string('to_account');   // Destination (Deposit To)
                $table->decimal('amount', 10, 2);

                // Details
                $table->text('purpose')->nullable();
                $table->text('purpose')->nullable();
                $table->foreignId('user_id'); // Operator

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
