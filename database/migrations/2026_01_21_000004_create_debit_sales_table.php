<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('debit_sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('salesman_id')->nullable()->constrained('employees'); // Salesman might satisfy user_id or be separate
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('pricing_type')->default('Retail'); // Retail, Wholesale
            $table->decimal('gross_total', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('net_total', 15, 2)->default(0);
            $table->decimal('adjusted_amount', 15, 2)->nullable();
            $table->enum('status', ['open', 'paid', 'overdue'])->default('open');
            $table->timestamps();
        });

        Schema::create('debit_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debit_sale_id')->constrained('debit_sales')->onDelete('cascade');
            $table->unsignedBigInteger('product_id')->nullable(); // Nullable if custom item or if product deleted? Best to be constrained if products table exists. Assuming products exists.
            $table->string('item_name'); // Snapshot of name
            $table->integer('quantity')->default(1);
            $table->decimal('rate', 15, 2);
            $table->decimal('total', 15, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2);
            $table->string('department')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('debit_sale_items');
        Schema::dropIfExists('debit_sales');
    }
};
