<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Create Receipt Allocations Table
        if (!Schema::hasTable('receipt_allocations')) {
            Schema::create('receipt_allocations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('receipt_id')->constrained('receipts')->onDelete('restrict'); // Strict: Cannot delete receipt if allocations exist
                $table->foreignId('debit_sale_id')->constrained('debit_sales');
                $table->decimal('allocated_amount', 15, 2);
                $table->timestamps();
            });
        }

        // 2. Enhance Receipts Table
        Schema::table('receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('receipts', 'account_id')) {
                // Determine a default account ID if possible, or make nullable temporarily
                // For strict safety, let's make it nullable then user can migrate data if needed
                // But this is a new feature so nullable is fine for old records
                $table->foreignId('account_id')->nullable()->constrained('accounts');
            }
            if (!Schema::hasColumn('receipts', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->index() /* central user ref */;
            }
            if (!Schema::hasColumn('receipts', 'net_amount')) {
                $table->decimal('net_amount', 15, 2)->default(0)->after('amount_received');
            }
        });

        // 3. Modify Debit Sales Status
        // ENUM modification is tricky in valid SQL. 
        // We will try to modify the column if possible, or just raw logic.
        // DB::statement("ALTER TABLE debit_sales MODIFY COLUMN status ENUM('open', 'partial', 'paid', 'overdue') NOT NULL DEFAULT 'open'");
        // Since sqlite/mysql differences, for standard MySQL this works.
        try {
            DB::statement("ALTER TABLE debit_sales MODIFY COLUMN status ENUM('open', 'partial', 'paid', 'overdue') NOT NULL DEFAULT 'open'");
        } catch (\Exception $e) {
            // If failed (e.g. SQLite testing), ignore or log. 
            // In production MySQL this is needed.
        }
    }

    public function down()
    {
        Schema::dropIfExists('receipt_allocations');

        Schema::table('receipts', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn(['account_id', 'created_by', 'net_amount']);
        });
    }
};
