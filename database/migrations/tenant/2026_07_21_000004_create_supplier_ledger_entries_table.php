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
        if (!Schema::hasTable('supplier_ledger_entries')) {
            Schema::create('supplier_ledger_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
                $table->enum('type', [
                    'purchase',
                    'return_to_supplier',
                    'payment_made',
                    'payment_reversal',
                    'manual_adjustment'
                ]);
                $table->decimal('amount', 12, 2); // Positive = Store owes supplier more (payable increase); Negative = Store owes less (payment made)
                $table->decimal('balance_after', 12, 2); // Running payable balance after transaction
                $table->string('method')->nullable(); // cash, bank_transfer, cheque, etc.
                $table->text('note')->nullable();
                $table->unsignedBigInteger('reversed_entry_id')->nullable()->index();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_ledger_entries');
    }
};
