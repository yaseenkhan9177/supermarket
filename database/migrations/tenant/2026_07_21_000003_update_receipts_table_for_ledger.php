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
        if (!Schema::hasTable('receipts')) {
            Schema::create('receipts', function (Blueprint $table) {
                $table->id();
                $table->string('receipt_number')->nullable()->unique();
                $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
                $table->unsignedBigInteger('ledger_entry_id')->nullable()->index();
                $table->decimal('amount', 12, 2)->nullable();
                $table->decimal('remaining_balance', 12, 2)->nullable();
                $table->string('payment_method')->nullable();
                $table->unsignedBigInteger('received_by')->nullable()->index();
                $table->string('store_name')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('receipts', function (Blueprint $table) {
                if (!Schema::hasColumn('receipts', 'receipt_number')) {
                    $table->string('receipt_number')->nullable()->unique()->after('id');
                }
                if (!Schema::hasColumn('receipts', 'ledger_entry_id')) {
                    $table->unsignedBigInteger('ledger_entry_id')->nullable()->after('customer_id')->index();
                }
                if (!Schema::hasColumn('receipts', 'amount')) {
                    $table->decimal('amount', 12, 2)->nullable()->after('ledger_entry_id');
                }
                if (!Schema::hasColumn('receipts', 'remaining_balance')) {
                    $table->decimal('remaining_balance', 12, 2)->nullable()->after('amount');
                }
                if (!Schema::hasColumn('receipts', 'payment_method')) {
                    $table->string('payment_method')->nullable()->after('remaining_balance');
                }
                if (!Schema::hasColumn('receipts', 'received_by')) {
                    $table->unsignedBigInteger('received_by')->nullable()->after('payment_method')->index();
                }
                if (!Schema::hasColumn('receipts', 'store_name')) {
                    $table->string('store_name')->nullable()->after('received_by');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $cols = ['receipt_number', 'ledger_entry_id', 'amount', 'remaining_balance', 'payment_method', 'received_by', 'store_name'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('receipts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
