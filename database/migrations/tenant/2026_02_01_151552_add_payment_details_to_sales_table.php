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
        Schema::table('sales', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('payment_mode');
            $table->string('check_number')->nullable()->after('bank_name');
            $table->string('check_image')->nullable()->after('check_number');
            $table->string('sender_name')->nullable()->after('check_image');
            $table->string('transaction_id')->nullable()->after('sender_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'check_number', 'check_image', 'sender_name', 'transaction_id']);
        });
    }
};
