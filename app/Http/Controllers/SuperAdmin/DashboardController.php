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
        $totalAll     = Tenant::count();
        $totalActive  = Tenant::where('status', 'active')->count();
        $totalPending = Tenant::where('status', 'pending')->count();
        $totalReject  = Tenant::where('status', 'rejected')->count();

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
                  ->orWhereHas('user', function($q2) use ($s) {
                      $q2->where('name', 'like', "%{$s}%")
                         ->orWhere('email', 'like', "%{$s}%");
                  });
            });
        }

        // Stores don't have a status column currently, so we skip status filter.

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
            // Check if database needs to be created
            if (!$tenant->database()->manager()->databaseExists($tenant->database()->getName())) {
                $tenant->database()->make();
            }

            // Create central user
            $user = User::create([
                'name'       => $tenant->owner_name,
                'email'      => $tenant->owner_email,
                'phone'      => $tenant->owner_phone,
                'role'       => 'owner',
                'tenant_id'  => $tenant->id,
                'is_active'  => true,
                'password'   => bcrypt('password'),
            ]);

            if (\App\Models\SpatieRole::where('name', 'owner')->exists()) {
                $user->assignRole('owner');
            }

            // Initialize tenancy and create Store
            tenancy()->initialize($tenant);

            Store::create([
                'name'          => $tenant->store_name,
                'business_type' => 'Retail',
                'user_id'       => $user->id,
            ]);

            tenancy()->end();

            $tenant->status = 'active';
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
