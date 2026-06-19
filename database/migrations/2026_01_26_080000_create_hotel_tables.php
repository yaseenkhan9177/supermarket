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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_no')->unique(); // e.g., 101, 205
            $table->string('type')->default('Standard'); // Standard, Deluxe, Suite
            $table->integer('floor')->default(1);
            $table->decimal('price_night', 10, 2);
            $table->enum('status', ['Available', 'Occupied', 'Cleaning', 'Maintenance'])->default('Available');
            $table->timestamps();
        });

        Schema::create('kots', function (Blueprint $table) { // Kitchen Order Tickets
            $table->id();
            $table->string('kot_no')->unique();
            $table->string('table_or_room'); // e.g., "Table 5" or "Room 101"
            $table->string('guest_name')->nullable();
            $table->enum('status', ['Active', 'Served', 'Billed'])->default('Active');
            $table->timestamps();
        });

        Schema::create('kot_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kot_id')->constrained('kots')->onDelete('cascade');
            $table->string('item_name');
            $table->integer('qty');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kot_items');
        Schema::dropIfExists('kots');
        Schema::dropIfExists('rooms');
    }
};
