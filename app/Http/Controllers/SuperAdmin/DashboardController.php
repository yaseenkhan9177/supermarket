<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use App\Models\Tenant;
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
        $totalActive  = Tenant::where('status', 'active')->count();
        $totalPending = Tenant::where('status', 'pending')->count();
        $totalReject  = Tenant::where('status', 'rejected')->count();
        $totalAll     = Tenant::count();

        $stats = [
            'total_active_stores' => $totalActive,
            'total_revenue'       => 0, // extend as needed
            'pending_setups'      => $totalPending,
            'total_tenants'       => $totalAll,
            'system_status'       => 'Healthy',
        ];

        // Monthly growth (last 6 months)
        $months      = [];
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
            'active'   => $totalActive,
            'rejected' => $totalReject,
            'pending'  => $totalPending,
        ];

        $recentTenants = Tenant::latest()->limit(5)->get();

        return view('super_admin.dashboard', compact('stats', 'growthChart', 'statusChart', 'recentTenants'));
    }

    public function tenants(Request $request)
    {
        $query = Tenant::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('store_name', 'like', "%{$s}%")
                  ->orWhere('owner_name', 'like', "%{$s}%")
                  ->orWhere('owner_email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
        $tenant = Tenant::findOrFail($id);
        return view('super_admin.requests.show', compact('tenant'));
    }

    public function approveStore($id)
    {
        $tenant = Tenant::findOrFail($id);

        if ($tenant->status !== 'pending') {
            return back()->with('error', 'Store is not pending approval.');
        }

        try {
            $dbName = 'store_' . strtolower(Str::random(8)) . '_' . substr($tenant->id, 0, 8);
            $dbName = preg_replace('/[^a-z0-9_]/', '', $dbName);

            DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}`");

            $connectionName = 'tenant_deploy_' . Str::random(4);
            $config = Config::get('database.connections.mysql');
            $config['database'] = $dbName;
            Config::set("database.connections.{$connectionName}", $config);
            DB::purge($connectionName);

            Artisan::call('migrate', [
                '--database' => $connectionName,
                '--force'    => true,
            ]);

            DB::connection($connectionName)->table('users')->insert([
                'name'       => $tenant->owner_name,
                'email'      => $tenant->owner_email,
                'password'   => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::connection($connectionName)->table('stores')->insert([
                'name'          => $tenant->store_name,
                'business_type' => 'Retail',
                'user_id'       => 1,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            $tenant->database_name = $dbName;
            $tenant->status        = 'active';
            $tenant->save();

            Log::info("Store Approved: Email sent to {$tenant->owner_email} with credentials.");

            return redirect()->route('super.requests.index')
                ->with('success', "Store \"{$tenant->store_name}\" approved and database created successfully!");
        } catch (\Exception $e) {
            Log::error("Store Approval Failed: " . $e->getMessage());
            return back()->with('error', 'Failed to approve store: ' . $e->getMessage());
        }
    }

    public function rejectStore(Request $request, $id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->status = 'rejected';
        $tenant->save();

        $reason = $request->input('reason', 'No reason provided');
        Log::info("Store {$tenant->id} rejected. Reason: {$reason}");

        return redirect()->route('super.requests.index')
            ->with('success', "Store \"{$tenant->store_name}\" request rejected.");
    }

    public function suspendTenant($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->status = $tenant->status === 'active' ? 'suspended' : 'active';
        $tenant->save();

        $msg = $tenant->status === 'active' ? 'activated' : 'suspended';
        return back()->with('success', "Store \"{$tenant->store_name}\" has been {$msg}.");
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
        return view('super_admin.users', compact('admins'));
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
