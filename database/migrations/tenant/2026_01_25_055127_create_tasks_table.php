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
        if (!Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();

                // The columns on your board
                $table->enum('status', ['todo', 'in_progress', 'completed'])->default('todo');

                // Visual Tags
                $table->enum('priority', ['high', 'medium', 'low'])->default('medium');

                // For the "In Progress" bar
                $table->integer('progress_percent')->default(0);

                // Assignments & Dates
                $table->unsignedBigInteger('assigned_to')->nullable()->index() /* central user ref */; // Who is "AD"?
                $table->date('due_date')->nullable();

                $table->integer('order')->default(0); // To save the order within the column
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
