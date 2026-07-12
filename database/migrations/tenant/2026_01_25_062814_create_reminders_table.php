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
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable(); // Optional detail

            // Timing
            $table->date('due_date');
            $table->time('due_time');

            // Logic
            $table->enum('priority', ['high', 'normal'])->default('normal');
            $table->boolean('is_completed')->default(false); // To mark "Done" like legacy app

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
