<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tax_charge_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_custom')->default(false);
            $table->timestamps();
        });

        // Seed default tax / charge types
        $defaults = [
            'Paracha Traders (Karachi Port Clearing Person)',
            'TP PSID (Karachi Port PSID FBR)',
            'Custom Duty + Sales Tax PSID',
            'GST',
            'Bilty',
            'Detention Charges (Shipping Line Bill)',
            'Department (FBR, Consultant etc.)',
            'ARP (Broker Commission Bill)',
        ];

        foreach ($defaults as $name) {
            DB::table('tax_charge_types')->insert([
                'name' => $name,
                'is_custom' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::create('purchase_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade');
            $table->foreignId('tax_charge_type_id')->constrained('tax_charge_types')->onDelete('cascade');
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_charges');
        Schema::dropIfExists('tax_charge_types');
    }
};
