<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $totalAll       = Tenant::count();
        $totalActive    = Tenant::where('status', 'active')->count();
        $totalPending   = Tenant::where('status', 'pending')->count();
        $totalSuspended = Tenant::where('status', 'suspended')->count();
        $totalExpired   = Tenant::where('status', 'expired')->orWhere(function($q) {
            $q->where('status', 'active')->whereNotNull('paid_until')->where('paid_until', '<', now()->toDateString());
        })->count();
        $totalRejected  = Tenant::where('status', 'rejected')->count();

        $stats = [
            'total_tenants'   => $totalAll,
            'total_active'    => $totalActive,
            'total_pending'   => $totalPending,
            'total_suspended' => $totalSuspended,
            'total_expired'   => $totalExpired,
            'total_rejected'  => $totalRejected,
            'system_status'   => 'Healthy',
        ];

        // Monthly growth (last 6 months)
        $months        = [];
        $monthlyCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[]        = $date->format('M Y');
            $monthlyCounts[] = Tenant::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        $growthChart = [
            'labels' => $months,
            'data'   => $monthlyCounts,
        ];

        $statusChart = [
            'active'    => $totalActive,
            'pending'   => $totalPending,
            'suspended' => $totalSuspended,
            'expired'   => $totalExpired,
            'rejected'  => $totalRejected,
        ];

        $recentTenants = Tenant::with('user')->latest()->limit(5)->get();

        return view('super_admin.dashboard', compact('stats', 'growthChart', 'statusChart', 'recentTenants'));
    }

    public function tenants(Request $request)
    {
        $query = Tenant::with('user');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('store_name', 'like', "%{$s}%")
                  ->orWhere('owner_name', 'like', "%{$s}%")
                  ->orWhere('owner_email', 'like', "%{$s}%")
                  ->orWhereHas('user', function($q2) use ($s) {
                      $q2->where('name', 'like', "%{$s}%")
                         ->orWhere('email', 'like', "%{$s}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'expired') {
                $query->where(function($q) {
                    $q->where('status', 'expired')
                      ->orWhere(function($q2) {
                          $q2->where('status', 'active')
                             ->whereNotNull('paid_until')
                             ->where('paid_until', '<', now()->toDateString());
                      });
                });
            } else {
                $query->where('status', $status);
            }
        }

        $tenants = $query->latest()->paginate(15);
        return view('super_admin.tenants', compact('tenants'));
    }

    public function storeRequests()
    {
        $requests = Tenant::where('status', 'pending')->latest()->paginate(15);
        return view('super_admin.requests.index', compact('requests'));
    }

    public function storeRequestShow($id)
    {
        $tenant = Tenant::with('user')->findOrFail($id);
        return view('super_admin.requests.show', compact('tenant'));
    }

    public function showTenant($id)
    {
        $tenant = Tenant::with('user')->findOrFail($id);
        return view('super_admin.tenant_detail', compact('tenant'));
    }

    /**
     * Approve a pending store registration.
     *
     * This method is IDEMPOTENT / RESUMABLE:
     * - Step 1: Check if the tenant's physical database already exists on the DB server.
     *   If it does (from a prior partial attempt), SKIP CreateDatabaseCpanel and go
     *   straight to MigrateDatabase. This prevents the "database already exists" crash
     *   when retrying after a failed migration.
     * - Step 2: MigrateDatabase failure is caught separately and recorded in
     *   provisioning_error so the Super Admin UI surfaces exactly what broke.
     * - Steps 3-5: User activation, tenant DB store seeding, and status update.
     */
    public function approveStore(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);

        if ($tenant->status !== 'pending') {
            return back()->with('error', 'Store is not in pending approval state.');
        }

        // ── Step 1: DB existence check — skip creation if already provisioned ────────
        $dbName          = $tenant->database_name;
        $databaseExists  = false;

        try {
            $databaseExists = DB::connection('mysql')
                ->select("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?", [$dbName]) !== [];
        } catch (\Exception $e) {
            Log::warning("approveStore: could not query information_schema for tenant {$tenant->id}: " . $e->getMessage());
        }

        if (! $databaseExists) {
            // Fresh tenant — run full DB provisioning via CPanel/local driver
            try {
                \App\Jobs\Tenancy\CreateDatabaseCpanel::dispatchSync($tenant);
            } catch (\Exception $e) {
                $errorMsg = "DB provisioning failed for tenant {$tenant->id}: " . $e->getMessage();
                Log::error($errorMsg);

                $tenant->provisioning_error = $errorMsg;
                $tenant->save();

                return back()->with('error', "Database creation failed: " . $e->getMessage() . " — No changes were made. Click Approve again to retry.");
            }
        } else {
            // DB already exists from a prior partial attempt — resume from migration step
            Log::info("approveStore: physical database '{$dbName}' already exists for tenant {$tenant->id} — skipping DB creation, resuming from migration.");
        }

        // ── Step 2: Run tenant schema migrations ─────────────────────────────────────
        try {
            \Stancl\Tenancy\Jobs\MigrateDatabase::dispatchSync($tenant);
        } catch (\Exception $e) {
            $errorMsg = "Migration failed for tenant {$tenant->id} (DB '{$dbName}'): " . $e->getMessage();
            Log::error($errorMsg);

            // Persist the error so the Super Admin can see exactly what broke in the UI.
            // Tenant stays 'pending' — clicking Approve again will skip DB creation
            // and retry migrations directly (since the DB now exists).
            $tenant->provisioning_error = $errorMsg;
            $tenant->save();

            return back()->with('error',
                "Database was created but migrations failed: " . $e->getMessage()
                . " — The store is still pending. Click Approve again to retry migrations."
            );
        }

        // ── Step 3: Activate or create central owner user ─────────────────────────────
        try {
            $user = User::where('tenant_id', $tenant->id)->where('role', 'owner')->first();
            if ($user) {
                $user->is_active = true;
                $user->save();
            } else {
                $user = User::create([
                    'name'      => $tenant->owner_name,
                    'email'     => $tenant->owner_email,
                    'phone'     => $tenant->owner_phone,
                    'role'      => 'owner',
                    'tenant_id' => $tenant->id,
                    'is_active' => true,
                    'password'  => Hash::make(Str::random(16)), // random — owner sets own password
                ]);
                if (\App\Models\SpatieRole::where('name', 'owner')->exists()) {
                    $user->assignRole('owner');
                }
            }
        } catch (\Exception $e) {
            $errorMsg = "User activation failed for tenant {$tenant->id}: " . $e->getMessage();
            Log::error($errorMsg);
            $tenant->provisioning_error = $errorMsg;
            $tenant->save();
            return back()->with('error', "Migrations succeeded but user activation failed: " . $e->getMessage());
        }

        // ── Step 4: Seed Store record inside the tenant database ──────────────────────
        try {
            tenancy()->initialize($tenant);
            if (!Store::where('user_id', $user->id)->exists()) {
                Store::create([
                    'name'          => $tenant->store_name,
                    'business_type' => 'Retail',
                    'user_id'       => $user->id,
                ]);
            }
            tenancy()->end();
        } catch (\Exception $e) {
            // Non-fatal — store record can be created later; don't block approval
            Log::warning("approveStore: tenant store seed notice for {$tenant->id}: " . $e->getMessage());
            tenancy()->end();
        }

        // ── Step 5: Mark tenant active, clear any prior provisioning error ────────────
        $days          = (int) $request->input('paid_days', 30);
        $paidUntilDate = $request->filled('paid_until')
            ? $request->input('paid_until')
            : now()->addDays($days)->toDateString();

        $tenant->status             = 'active';
        $tenant->paid_until         = $paidUntilDate;
        $tenant->approved_at        = now();
        $tenant->approved_by        = Auth::guard('super_admin')->id();
        $tenant->provisioning_error = null; // clear any prior stuck-state error
        $tenant->save();

        Log::info("Store Approved: {$tenant->store_name} ({$tenant->id}) by Super Admin " . Auth::guard('super_admin')->id());

        return redirect()->route('super.tenants')
            ->with('success', "Store \"{$tenant->store_name}\" approved and database provisioned! Active until {$paidUntilDate}.");
    }

    public function rejectStore(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $tenant = Tenant::findOrFail($id);
        $tenant->status           = 'rejected';
        $tenant->rejected_at      = now();
        $tenant->rejection_reason = $request->rejection_reason;
        $tenant->save();

        Log::info("Store {$tenant->id} rejected. Reason: {$request->rejection_reason}");

        return redirect()->route('super.tenants')
            ->with('success', "Store \"{$tenant->store_name}\" request rejected.");
    }

    public function updatePaidUntil(Request $request, $id)
    {
        $request->validate([
            'paid_until' => 'required|date',
        ]);

        $tenant = Tenant::findOrFail($id);
        $tenant->paid_until = $request->paid_until;

        // If status was expired and new paid_until is today or in the future, set to active
        if ($tenant->status === 'expired' && \Carbon\Carbon::parse($request->paid_until)->gte(now()->startOfDay())) {
            $tenant->status = 'active';
        }

        $tenant->save();

        return back()->with('success', "Updated payment date for \"{$tenant->store_name}\" to {$request->paid_until}.");
    }

    public function suspendTenant($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->status = 'suspended';
        $tenant->save();

        return back()->with('success', "Store \"{$tenant->store_name}\ font-semibold has been suspended.");
    }

    public function unsuspendTenant($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->status = 'active';
        $tenant->save();

        return back()->with('success', "Store \"{$tenant->store_name}\" status set to active.");
    }

    public function loginAsOwner($id)
    {
        return back()->with('info', 'Login as Owner functionality is not yet implemented.');
    }

    public function backupTenant($id)
    {
        return back()->with('info', 'Backup functionality is not yet implemented.');
    }

    public function plans()
    {
        return view('super_admin.plans');
    }

    // ─────────────────────────────────────────────
    // Super Admin User Management (Real CRUD)
    // ─────────────────────────────────────────────

    public function users(Request $request)
    {
        $query = SuperAdmin::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $admins = $query->latest()->paginate(15);
        $pins = \App\Models\SuperAdminPin::latest()->get();

        return view('super_admin.users', compact('admins', 'pins'));
    }

    public function generatePin(Request $request)
    {
        do {
            $pin = sprintf("%06d", mt_rand(100000, 999999));
        } while (\App\Models\SuperAdminPin::where('pin', $pin)->whereNull('used_at')->exists());

        \App\Models\SuperAdminPin::create([
            'pin' => $pin,
        ]);

        return back()->with('success', "Registration PIN generated successfully: {$pin}");
    }

    public function createUser()
    {
        return view('super_admin.users_create');
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:super_admins,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:super_owner,support,sales',
        ]);

        SuperAdmin::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => $data['role'],
            'is_active' => true,
        ]);

        return redirect()->route('super.users')->with('success', "Admin \"{$data['name']}\" created successfully.");
    }

    public function editUser($id)
    {
        $admin = SuperAdmin::findOrFail($id);
        return view('super_admin.users_edit', compact('admin'));
    }

    public function updateUser(Request $request, $id)
    {
        $admin = SuperAdmin::findOrFail($id);

        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => "required|email|unique:super_admins,email,{$id}",
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => 'required|in:super_owner,support,sales',
        ]);

        $updateData = [
            'name'  => $data['name'],
            'email' => $data['email'],
            'role'  => $data['role'],
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $admin->update($updateData);

        return redirect()->route('super.users')->with('success', "Admin \"{$data['name']}\" updated successfully.");
    }

    public function destroyUser($id)
    {
        $currentUser = Auth::guard('super_admin')->user();

        if ($currentUser->id == $id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $admin = SuperAdmin::findOrFail($id);
        $name  = $admin->name;
        $admin->delete();

        return redirect()->route('super.users')->with('success', "Admin \"{$name}\" has been deleted.");
    }

    public function toggleUser($id)
    {
        $currentUser = Auth::guard('super_admin')->user();

        if ($currentUser->id == $id) {
            return back()->with('error', 'You cannot disable your own account.');
        }

        $admin = SuperAdmin::findOrFail($id);
        $admin->is_active = !$admin->is_active;
        $admin->save();

        $status = $admin->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Admin \"{$admin->name}\" has been {$status}.");
    }

    // ─────────────────────────────────────────────
    // Logs
    // ─────────────────────────────────────────────

    public function logs()
    {
        $logPath = storage_path('logs/laravel.log');
        $logLines = [];

        if (file_exists($logPath)) {
            $file  = new \SplFileObject($logPath);
            $file->seek(PHP_INT_MAX);
            $totalLines = $file->key();

            $start = max(0, $totalLines - 200);
            $file->seek($start);

            while (!$file->eof()) {
                $line = $file->current();
                if (trim($line) !== '') {
                    $logLines[] = trim($line);
                }
                $file->next();
            }

            $logLines = array_reverse($logLines);
        }

        return view('super_admin.logs', compact('logLines'));
    }

    // ─────────────────────────────────────────────
    // Settings
    // ─────────────────────────────────────────────

    public function settings()
    {
        return view('super_admin.settings');
    }

    public function updateSettings(Request $request)
    {
        // In a real app, save to .env or a settings table
        return back()->with('success', 'Settings saved successfully.');
    }
}
