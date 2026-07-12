<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // 'wallet', 'bank', 'counter', 'other'
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // Seed default wallets
        DB::table('wallets')->insert([
            [
                'name' => 'Shop Counter',
                'type' => 'counter',
                'bank_account_id' => null,
                'balance' => 0.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Admin Wallet',
                'type' => 'wallet',
                'bank_account_id' => null,
                'balance' => 0.00,
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
