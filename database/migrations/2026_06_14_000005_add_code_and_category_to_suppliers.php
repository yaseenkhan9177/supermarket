<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('code')->nullable()->after('id');
            $table->unsignedBigInteger('category_id')->nullable()->after('code');
            $table->foreign('category_id')->references('id')->on('supplier_categories')->onDelete('set null');
        });

        // Backfill existing suppliers with a unique code based on their ID
        $suppliers = DB::table('suppliers')->whereNull('code')->orWhere('code', '')->get();
        foreach ($suppliers as $supplier) {
            DB::table('suppliers')
                ->where('id', $supplier->id)
                ->update(['code' => 'SUP-' . str_pad($supplier->id, 4, '0', STR_PAD_LEFT)]);
        }

        // Now add the unique index
        Schema::table('suppliers', function (Blueprint $table) {
            $table->unique('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropUnique(['code']);
            $table->dropColumn(['code', 'category_id']);
        });
    }
};
