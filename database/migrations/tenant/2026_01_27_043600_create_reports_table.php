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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Daily Sales"
            $table->string('type')->default('report'); // 'folder' or 'report'
            $table->string('icon')->nullable(); // e.g., "fas fa-file"

            // The Parent-Child Relationship
            $table->foreignId('parent_id')->nullable()->constrained('reports')->onDelete('cascade');

            // For actual reports:
            $table->string('route_name')->nullable(); // e.g., "reports.sales.daily"
            $table->text('description')->nullable();

            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
