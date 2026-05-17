<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_active_stores' => Tenant::where('status', 'active')->count(),
            'total_revenue' => 12500.00, // Mock revenue
            'pending_setups' => Tenant::where('status', 'pending')->count(),
            'system_status' => 'Healthy',
        ];

        // Chart Data (Mock)
        $growthChart = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'data' => [5, 12, 19, 25, 32, 45],
        ];

        $statusChart = [
            'active' => Tenant::where('status', 'active')->count(),
            'rejected' => Tenant::where('status', 'rejected')->count(),
            'pending' => Tenant::where('status', 'pending')->count(),
        ];

        return view('super_admin.dashboard', compact('stats', 'growthChart', 'statusChart'));
    }

    public function tenants()
    {
        $tenants = Tenant::where('status', 'active')->latest()->paginate(10);
        return view('super_admin.tenants', compact('tenants'));
    }

    public function storeRequests()
    {
        $requests = Tenant::where('status', 'pending')->latest()->paginate(10);
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
            // 1. Generate DB Name
            $dbName = 'store_' . strtolower(Str::random(8)) . '_' . $tenant->id;
            // Sanitize db name just in case (though uuids are safe-ish, random string is safer)
            $dbName = preg_replace('/[^a-z0-9_]/', '', $dbName);

            // 2. Create Database
            DB::statement("CREATE DATABASE {$dbName}");

            // 3. Configure Connection
            // We clone the 'mysql' connection config and change the database name
            $connectionName = 'tenant_deploy';
            $config = Config::get('database.connections.mysql');
            $config['database'] = $dbName;
            Config::set("database.connections.{$connectionName}", $config);
            DB::purge($connectionName);

            // 4. Run Migrations
            // We run all migrations for simplicity. In a real app, you might check paths.
            Artisan::call('migrate', [
                '--database' => $connectionName,
                '--force' => true,
            ]);

            // 5. Create Owner User in Tenant DB
            // Using DB facade on the new connection to avoid Model connection issues
            DB::connection($connectionName)->table('users')->insert([
                'name' => $tenant->owner_name,
                'email' => $tenant->owner_email,
                'password' => bcrypt('password'), // Default password, should be emailed or set by user
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Also create the 'Store' record in the tenant DB (if that's where settings live)
            DB::connection($connectionName)->table('stores')->insert([
                'name' => $tenant->store_name,
                'business_type' => 'Retail', // Default or from tenant data
                'user_id' => 1, // The user we just created
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 6. Update Tenant Record
            $tenant->database_name = $dbName;
            $tenant->status = 'active';
            $tenant->save();

            // 7. Send Email (Stub)
            Log::info("Store Approved: Email sent to {$tenant->owner_email} with credentials.");

            return redirect()->route('super.requests.index')->with('success', 'Store approved and database created successfully!');
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

        // Stub: Log rejection reason if provided
        $reason = $request->input('reason', 'No reason provided');
        Log::info("Store {$tenant->id} rejected. Reason: {$reason}");

        return redirect()->route('super.requests.index')->with('success', 'Store request rejected.');
    }

    public function suspendTenant($id)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->status = $tenant->status === 'active' ? 'suspended' : 'active';
        $tenant->save();

        return back()->with('success', 'Store status updated successfully.');
    }

    public function loginAsOwner($id)
    {
        // Stub
        return back()->with('info', 'Login as Owner functionality is not yet implemented.');
    }

    public function backupTenant($id)
    {
        // Stub
        return back()->with('info', 'Backup functionality is not yet implemented.');
    }

    public function plans()
    {
        return view('super_admin.plans');
    }

    public function users()
    {
        return view('super_admin.users');
    }

    public function logs()
    {
        return view('super_admin.logs');
    }

    public function settings()
    {
        return view('super_admin.settings');
    }
}
