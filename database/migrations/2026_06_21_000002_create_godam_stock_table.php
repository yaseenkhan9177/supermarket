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
        Schema::create('godam_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('godam_id')->constrained('godams')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->decimal('quantity', 12, 2)->default(0.00);
            $table->timestamp('last_received_at')->nullable();
            $table->timestamps();

            $table->unique(['godam_id', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('godam_stock');
    }
};
