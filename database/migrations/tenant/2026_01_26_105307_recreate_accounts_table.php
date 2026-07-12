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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('accounts');
        Schema::enableForeignKeyConstraints();

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g., 010000, 502000
            $table->string('name'); // e.g., Cash Drawer, Electricity Expense

            // Financial Classification
            $table->enum('type', ['Asset', 'Liability', 'Equity', 'Income', 'Expense']);
            $table->string('category')->nullable(); // e.g., "Current Assets", "Operating Expenses"

            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);

            $table->boolean('is_system')->default(false); // Protect core accounts from deletion
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
