<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PublicSignupController extends Controller
{
    public function showForm()
    {
        return view('signup');
    }

    public function submit(Request $request)
    {
        $request->validate([
            'store_name'  => ['required', 'string', 'max:255'],
            'owner_name'  => ['required', 'string', 'max:255'],
            'owner_phone' => ['required', 'string', 'max:20'],
            // Explicitly target the central DB connection — the unique check must never
            // run against a tenant DB, even if a stale tenant_id exists in the session.
            'owner_email' => ['required', 'string', 'email', 'max:255', 'unique:mysql.users,email', 'unique:mysql.tenants,owner_email'],
            'subdomain'   => ['required', 'string', 'alpha_dash', 'max:50'],
            'password'    => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $subdomain = Str::slug($request->subdomain);
        $tenantId = (string) Str::uuid();
        $databaseName = 'vectabyte_tenant_' . str_replace('-', '_', $tenantId);

        // 1. Create tenant record with status = 'pending'
        // Database provisioning is deferred until Super Admin approval
        $tenant = Tenant::create([
            'id'                => $tenantId,
            'store_name'        => $request->store_name,
            'owner_name'        => $request->owner_name,
            'owner_email'       => $request->owner_email,
            'owner_phone'       => $request->owner_phone,
            'status'            => 'pending',
            'database_name'     => $databaseName,
            'subscription_plan' => 'basic',
            'paid_until'        => null,
        ]);

        // Create domain mapping
        $domainName = $subdomain . '.' . parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST);
        $tenant->domains()->create([
            'domain' => $domainName,
        ]);

        // 2. Create central owner user (inactive until approved)
        $user = User::create([
            'name'      => $request->owner_name,
            'email'     => $request->owner_email,
            'phone'     => $request->owner_phone,
            'role'      => 'owner',
            'tenant_id' => $tenant->id,
            'is_active' => false,
            'password'  => Hash::make($request->password),
        ]);

        if (\App\Models\SpatieRole::where('name', 'owner')->exists()) {
            $user->assignRole('owner');
        }

        return redirect()->route('signup.success')->with('store_name', $request->store_name);
    }

    public function success()
    {
        return view('signup_success');
    }
}
