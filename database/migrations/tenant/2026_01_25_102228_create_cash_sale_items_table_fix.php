<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('cash_sale_items')) {
            Schema::create('cash_sale_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cash_sale_id')->constrained('cash_sales')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('items'); // Adding explicit table name just in case
                $table->string('item_name');
                $table->integer('quantity');
                $table->decimal('rate', 10, 2);
                $table->decimal('total', 10, 2);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('cash_sale_items');
    }
};
