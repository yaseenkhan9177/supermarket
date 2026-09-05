<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerBalanceConversion;
use App\Models\CustomerLedgerEntry;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerBalanceConversionController extends Controller
{
    /**
     * Enforce Super Admin authorization for all actions.
     */
    protected function ensureSuperAdmin(): void
    {
        if (!Auth::guard('super_admin')->check()) {
            abort(403, 'Unauthorized. Super Admin access only.');
        }
    }

    /**
     * Tenant list with conversion status.
     */
    public function index(Request $request)
    {
        $this->ensureSuperAdmin();

        $search = trim($request->get('search', ''));
        $tenantsQuery = Tenant::query();

        if ($search !== '') {
            $tenantsQuery->where(function ($q) use ($search) {
                $q->where('store_name', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhere('owner_email', 'like', "%{$search}%");
            });
        }

        $tenants = $tenantsQuery->orderBy('store_name', 'asc')->paginate(15);

        // Fetch conversion records for all listed tenants
        $tenantIds = $tenants->pluck('id')->toArray();
        $conversions = CustomerBalanceConversion::whereIn('tenant_id', $tenantIds)
            ->get()
            ->keyBy('tenant_id');

        return view('super_admin.balance_conversion.index', compact('tenants', 'conversions', 'search'));
    }

    /**
     * Show warning, preview summary, and per-customer comparison for a specific tenant.
     */
    public function preview(Request $request, $tenantId)
    {
        $this->ensureSuperAdmin();

        $tenant = Tenant::findOrFail($tenantId);

        // Check if conversion has already been completed for this tenant
        $existingConversion = CustomerBalanceConversion::where('tenant_id', $tenant->id)->first();

        // Initialize tenancy to inspect the tenant's actual customer records
        $customersCount = 0;
        $positiveCount  = 0;
        $negativeCount  = 0;
        $zeroCount      = 0;
        $totalBalance   = 0.0;
        $previewRows    = collect();

        try {
            tenancy()->initialize($tenant);

            $customersCount = Customer::count();
            $positiveCount  = Customer::where('balance', '>', 0)->count();
            $negativeCount  = Customer::where('balance', '<', 0)->count();
            $zeroCount      = Customer::where('balance', 0)->count();
            $totalBalance   = (float) Customer::sum('balance');

            // Preview sample customers (first 50 for fast rendering)
            $previewRows = Customer::orderBy('name', 'asc')->take(50)->get(['id', 'name', 'phone', 'balance'])->map(function ($c) {
                $currentBal = (float) $c->balance;
                $newBal     = round($currentBal * -1, 2);

                if ($currentBal == 0) {
                    $newBal  = 0.00;
                    $meaning = 'No Balance';
                    $class   = 'slate';
                } elseif ($newBal > 0) {
                    $meaning = 'Pay to Store';
                    $class   = 'red';
                } else {
                    $meaning = 'Pay to Customer';
                    $class   = 'green';
                }

                return [
                    'id'              => $c->id,
                    'name'            => $c->name,
                    'phone'           => $c->phone,
                    'current_balance' => $currentBal,
                    'new_balance'     => $newBal,
                    'meaning'         => $meaning,
                    'class'           => $class,
                ];
            });

        } finally {
            if (function_exists('tenancy') && tenancy()->initialized) {
                tenancy()->end();
            }
        }

        return view('super_admin.balance_conversion.preview', compact(
            'tenant',
            'existingConversion',
            'customersCount',
            'positiveCount',
            'negativeCount',
            'zeroCount',
            'totalBalance',
            'previewRows'
        ));
    }

    /**
     * Execute the atomic, audited, one-time customer balance conversion.
     */
    public function convert(Request $request, $tenantId)
    {
        $this->ensureSuperAdmin();

        $tenant = Tenant::findOrFail($tenantId);
        $superAdmin = Auth::guard('super_admin')->user();

        // ── STRICT ONE-TIME CHECK ──────────────────────────────────────────
        if (CustomerBalanceConversion::where('tenant_id', $tenant->id)->exists()) {
            return redirect()->route('super.balance-conversion.preview', $tenant->id)
                ->with('error', 'Customer balance conversion has already been completed.');
        }

        $processed     = 0;
        $posConverted  = 0;
        $negConverted  = 0;
        $zeroUnchanged = 0;
        $totalBefore   = 0.0;
        $totalAfter    = 0.0;
        $now           = now();

        try {
            tenancy()->initialize($tenant);

            // Cross-connection transaction safety: begin transaction on central mysql DB as well
            DB::connection('mysql')->beginTransaction();
            DB::connection()->beginTransaction(); // Tenant DB

            try {
                // Lock customer records to prevent concurrent modifications
                $customers = Customer::lockForUpdate()->get();

                foreach ($customers as $customer) {
                    $oldBalance = (float) $customer->balance;
                    $totalBefore += $oldBalance;

                    if ($oldBalance == 0) {
                        $newBalance = 0.00;
                        $zeroUnchanged++;
                    } elseif ($oldBalance > 0) {
                        $newBalance = round($oldBalance * -1, 2);
                        $posConverted++;
                    } else {
                        $newBalance = round($oldBalance * -1, 2);
                        $negConverted++;
                    }

                    $totalAfter += $newBalance;

                    // Update ONLY the customer balance field used by the application
                    $customer->balance = $newBalance;
                    $customer->save();

                    // Accounting safety: For non-zero adjustments, create audit record in CustomerLedgerEntry
                    if ($oldBalance != 0) {
                        $delta = round($newBalance - $oldBalance, 2);

                        CustomerLedgerEntry::create([
                            'customer_id'     => $customer->id,
                            'type'            => 'manual_adjustment',
                            'reason_category' => 'opening_balance_conversion',
                            'amount'          => $delta,
                            'balance_after'   => $newBalance,
                            'note'            => 'Opening Balance Convention Conversion (Old: ' . number_format($oldBalance, 2) . ', New: ' . number_format($newBalance, 2) . ') by ' . ($superAdmin->name ?? 'Super Admin'),
                            'created_by'      => $superAdmin->id ?? null,
                            'created_at'      => $now,
                        ]);
                    }

                    $processed++;
                }

                // Record the completed conversion permanently on the central database
                CustomerBalanceConversion::create([
                    'tenant_id'            => $tenant->id,
                    'super_admin_id'       => $superAdmin->id ?? null,
                    'super_admin_name'     => $superAdmin->name ?? 'Super Admin',
                    'customers_processed'  => $processed,
                    'positive_converted'   => $posConverted,
                    'negative_converted'   => $negConverted,
                    'zero_unchanged'       => $zeroUnchanged,
                    'total_balance_before' => $totalBefore,
                    'total_balance_after'  => $totalAfter,
                    'converted_at'         => $now,
                ]);

                // Commit both database connections
                DB::connection()->commit();
                DB::connection('mysql')->commit();

            } catch (\Exception $e) {
                DB::connection()->rollBack();
                DB::connection('mysql')->rollBack();
                throw $e;
            }

            Log::info("Customer balance conversion completed for tenant {$tenant->id} by Super Admin {$superAdmin->id}. Processed: {$processed}");

        } catch (\Exception $e) {
            Log::error("Customer balance conversion FAILED for tenant {$tenant->id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('super.balance-conversion.preview', $tenant->id)
                ->with('error', 'Balance conversion failed and all changes were rolled back: ' . $e->getMessage());

        } finally {
            if (function_exists('tenancy') && tenancy()->initialized) {
                tenancy()->end();
            }
        }

        return redirect()->route('super.balance-conversion.preview', $tenant->id)
            ->with('conversion_success', [
                'message'              => 'Customer balance conversion completed successfully.',
                'customers_processed'  => $processed,
                'positive_converted'   => $posConverted,
                'negative_converted'   => $negConverted,
                'zero_unchanged'       => $zeroUnchanged,
                'failed_records'       => 0,
            ]);
    }
}
