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
        Schema::create('waste_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->decimal('quantity', 10, 2);
            $table->string('reason'); // Refund reason or other waste reason
            $table->foreignId('refund_id')->nullable()->constrained('refunds')->onDelete('set null');
            $table->unsignedBigInteger('user_id')->nullable()->index() /* central user ref */;
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_logs');
    }
};
