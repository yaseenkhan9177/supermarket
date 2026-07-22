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
        if (!Schema::hasTable('customer_ledger_entries')) {
            Schema::create('customer_ledger_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
                $table->enum('type', [
                    'sale',
                    'return',
                    'payment_received',
                    'payment_made',
                    'manual_adjustment'
                ]);
                $table->decimal('amount', 12, 2); // Positive = customer owes more; Negative = customer owes less / store owes customer
                $table->decimal('balance_after', 12, 2); // Running debit balance snapshot after transaction
                $table->string('method')->nullable(); // cash, bank, easypaisa, jazzcash, etc.
                $table->text('note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index(); // Central user ID reference across DBs
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_ledger_entries');
    }
};
