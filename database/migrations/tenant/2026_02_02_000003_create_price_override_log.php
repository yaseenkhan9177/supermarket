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
        Schema::create('price_override_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('sale_item_id')->nullable()->constrained('sale_items')->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->decimal('original_price', 15, 2);
            $table->decimal('override_price', 15, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->unsignedBigInteger('user_id')->nullable()->index(); // Central user reference
            $table->string('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index(['sale_id', 'item_id']);
            // user_id index already created inline above
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_override_logs');
    }
};
