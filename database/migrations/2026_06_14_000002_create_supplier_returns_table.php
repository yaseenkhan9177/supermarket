<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the supplier_returns table (header record per return transaction).
     */
    public function up(): void
    {
        if (!Schema::hasTable('supplier_returns')) {
            Schema::create('supplier_returns', function (Blueprint $table) {
                $table->id();
                $table->string('return_no')->unique(); // e.g. SR-2026-0001

                $table->foreignId('supplier_id')->constrained('suppliers');
                $table->date('return_date');
                $table->decimal('total_value', 15, 2)->default(0);

                /**
                 * Resolution determines the financial outcome:
                 *   cash_refund  — supplier pays us back in cash (account is credited)
                 *   store_credit — supplier reduces our debt (supplier balance decremented)
                 */
                $table->enum('resolution', ['cash_refund', 'store_credit'])->default('store_credit');

                // Only used for cash_refund: which account receives the cash
                $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();

                $table->text('notes')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_returns');
    }
};
