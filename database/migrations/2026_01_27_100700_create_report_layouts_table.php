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
        if (!Schema::hasTable('report_layouts')) {
            Schema::create('report_layouts', function (Blueprint $table) {
                $table->id();
                $table->string('report_type'); // e.g., 'sales_daily', 'purchase_ledger'
                $table->string('layout_name'); // e.g., 'My Tax View'
                $table->json('visible_columns'); // Array of column keys ['date', 'inv_no', 'tax']
                $table->boolean('is_default')->default(false);
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Null = System Default
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_layouts');
    }
};
