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
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            // --- LEFT COLUMN: Basic Info ---
            $table->string('item_type')->default('Inventory'); //
            $table->string('code')->unique();                  // Barcode/Item Code
            $table->string('description');                     // Item Name (e.g., KURKURE)
            $table->string('short_code')->nullable();
            $table->string('associated_text')->nullable();

            // Checkboxes
            $table->boolean('hide_sale_price')->default(false);
            $table->boolean('parse_bar')->default(false);
            $table->boolean('open_price')->default(false);
            $table->boolean('is_container')->default(false);

            // Categorization
            $table->foreignId('department_id')->nullable();
            $table->foreignId('salesman_id')->nullable();
            $table->foreignId('class_id')->nullable();

            // --- RIGHT COLUMN: Pricing & Stock ---
            $table->decimal('cost_rate', 10, 2)->default(0);
            $table->decimal('purchase_rate', 10, 2)->default(0);
            $table->decimal('sale_rate', 10, 2)->default(0);   // Main Sale Price
            $table->decimal('trade_rate', 10, 2)->default(0);

            // Wholesale Logic
            $table->integer('ctn_qty')->default(0);            // Carton Quantity
            $table->decimal('sale_ctn', 10, 2)->default(0);    // Sale Price per Carton
            $table->integer('wholesale_qty')->default(0);
            $table->decimal('sale_whole', 10, 2)->default(0);

            // Inventory Levels
            $table->integer('min_stock')->default(0);
            $table->integer('max_stock')->default(0);
            $table->decimal('on_hand', 10, 2)->default(0);     // Current Stock (Cached)

            // --- BOTTOM RIGHT: Accounts ---
            $table->foreignId('sales_account_id')->nullable(); // "Sales Income"
            $table->foreignId('cogs_account_id')->nullable();  // "Cost of Goods Sold"
            $table->foreignId('asset_account_id')->nullable(); // "Stock In Hand"

            $table->string('image_path')->nullable();          // For "Add/Change Photo"
            $table->timestamps();
        });

        // Sub-Items Table (For "Sub Items" button functionality)
        Schema::create('item_sub_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('child_item_id')->constrained('items');
            $table->decimal('quantity', 10, 2); // How many children in this parent?
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_sub_items');
        Schema::dropIfExists('items');
    }
};
