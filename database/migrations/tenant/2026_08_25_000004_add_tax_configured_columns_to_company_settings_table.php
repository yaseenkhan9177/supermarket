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
        Schema::table('company_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('company_settings', 'tax_configured_at')) {
                $table->timestamp('tax_configured_at')->nullable()->after('tax_rate');
            }
            if (!Schema::hasColumn('company_settings', 'tax_configured_by')) {
                $table->unsignedBigInteger('tax_configured_by')->nullable()->after('tax_configured_at');
            }
        });

        // Backfill: If tenant has already interacted with the tax feature (history records exist OR tax_enabled is true OR tax_rate > 0)
        try {
            $hasHistory = Schema::hasTable('tax_settings_history') && DB::table('tax_settings_history')->count() > 0;
            
            $settings = DB::table('company_settings')->first();
            if ($settings) {
                $isAlreadySet = $hasHistory || (bool)($settings->tax_enabled ?? false) || (float)($settings->tax_rate ?? 0) > 0;
                
                if ($isAlreadySet) {
                    $firstHistory = $hasHistory ? DB::table('tax_settings_history')->orderBy('id', 'asc')->first() : null;
                    
                    DB::table('company_settings')
                        ->where('id', $settings->id)
                        ->update([
                            'tax_configured_at' => $firstHistory?->created_at ?? $settings->updated_at ?? $settings->created_at ?? now(),
                            'tax_configured_by' => $firstHistory?->user_id ?? null,
                        ]);
                }
            }
        } catch (\Throwable $e) {
            // Ignore backfill errors if tables/columns are in transition
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            if (Schema::hasColumn('company_settings', 'tax_configured_by')) {
                $table->dropColumn('tax_configured_by');
            }
            if (Schema::hasColumn('company_settings', 'tax_configured_at')) {
                $table->dropColumn('tax_configured_at');
            }
        });
    }
};
