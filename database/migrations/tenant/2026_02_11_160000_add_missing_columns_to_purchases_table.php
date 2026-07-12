<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Check and add columns if they don't exist
            if (!Schema::hasColumn('purchases', 'supplier_id')) {
                $table->unsignedBigInteger('supplier_id')->nullable()->after('id');
                // Index normally for FK, but avoid constraint for now to be safe with existing data
                $table->index('supplier_id');
            }
            if (!Schema::hasColumn('purchases', 'purchase_no')) {
                $table->string('purchase_no')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('purchases', 'vendor_bill_no')) {
                $table->string('vendor_bill_no')->nullable()->after('purchase_no');
            }
            if (!Schema::hasColumn('purchases', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0)->after('gross_total');
            }
            if (!Schema::hasColumn('purchases', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('updated_at');
            }
            // Add 'memo' alias for notes if strictly needed, or just map in controller
            // We'll stick to 'notes' column in DB and map 'memo' input to 'notes' in Controller
        });
    }

    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['supplier_id', 'purchase_no', 'vendor_bill_no', 'tax_amount', 'user_id']);
        });
    }
};
