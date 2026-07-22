<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('supplier_ledgers') && Schema::hasTable('supplier_ledger_entries')) {
            $legacyEntries = DB::table('supplier_ledgers')->orderBy('id', 'asc')->get();

            foreach ($legacyEntries as $legacy) {
                // Avoid duplicating if already migrated
                $exists = DB::table('supplier_ledger_entries')
                    ->where('supplier_id', $legacy->supplier_id)
                    ->where('created_at', $legacy->created_at ?: ($legacy->date . ' 00:00:00'))
                    ->where('note', $legacy->description)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $refType = strtolower((string)$legacy->reference_type);
                $type = 'manual_adjustment';

                if (str_contains($refType, 'purchase')) {
                    $type = 'purchase';
                } elseif (str_contains($refType, 'return')) {
                    $type = 'return_to_supplier';
                } elseif (str_contains($refType, 'payment')) {
                    $type = 'payment_made';
                } elseif ($refType === 'opening') {
                    $type = 'manual_adjustment';
                }

                $credit = (float)($legacy->credit ?? 0);
                $debit  = (float)($legacy->debit ?? 0);
                $amount = $credit - $debit; // Positive = Store owes supplier more; Negative = Store owes less

                DB::table('supplier_ledger_entries')->insert([
                    'supplier_id'   => $legacy->supplier_id,
                    'type'          => $type,
                    'amount'        => $amount,
                    'balance_after' => $legacy->balance ?? 0,
                    'method'        => null,
                    'note'          => $legacy->description ?: ($type . ' (Legacy Entry)'),
                    'created_by'    => null,
                    'created_at'    => $legacy->created_at ?: ($legacy->date . ' ' . now()->format('H:i:s')),
                    'updated_at'    => $legacy->updated_at ?: now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destruct on rollback
    }
};
