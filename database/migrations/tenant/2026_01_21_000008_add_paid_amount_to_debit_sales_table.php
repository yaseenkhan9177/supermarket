<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('debit_sales', function (Blueprint $table) {
            $table->decimal('paid_amount', 15, 2)->default(0)->after('net_total');
        });
    }

    public function down()
    {
        Schema::table('debit_sales', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });
    }
};
