<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0)->after('grand_total');
            }
            if (!Schema::hasColumn('sales', 'change_amount')) {
                $table->decimal('change_amount', 15, 2)->default(0)->after('paid_amount');
            }
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'change_amount']);
        });
    }
};
