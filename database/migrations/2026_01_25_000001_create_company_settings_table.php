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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();

            // --- 1. Identity & Contact (Left Card) ---
            $table->string('business_name')->default('NEW BLANK COMPANY');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();

            // --- 2. Regional & Tax (FBR & Currency) ---
            $table->string('fbr_post_id')->nullable()->comment('FBR POSTID for tax integration');
            $table->string('currency_symbol')->default('Rs.');
            $table->string('currency_code')->default('PKR');

            // --- 3. Printers (The Dropdowns) ---
            $table->string('printer_receipt')->nullable();
            $table->string('printer_barcode')->nullable();
            $table->string('printer_default')->default('Microsoft Print to PDF');

            // --- 4. Hardware Hardware Names (Legacy: Printer Name, Drawer Name, etc.) ---
            $table->string('pos_printer_name')->default('Printer');
            $table->string('pos_drawer_name')->default('Drawer');
            $table->string('pos_display_name')->default('Display');
            $table->integer('comm_port_drawer')->default(0); // Legacy: Drawer attached to COMM
            $table->integer('comm_port_display')->default(0); // Legacy: Display attached to COMM

            // --- 5. POS Configuration (Barcode & Styles) ---
            $table->integer('barcode_labels_per_row')->default(0);
            $table->integer('barcode_labels_per_col')->default(0);
            $table->integer('receipt_width')->default(200); // Legacy: width 200 to 400
            $table->integer('number_of_counters')->default(1);
            $table->boolean('outlook_integration')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
