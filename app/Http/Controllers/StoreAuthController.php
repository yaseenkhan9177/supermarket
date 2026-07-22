<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class StoreAuthController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register_store');
    }

    public function register(Request $request)
    {
        $request->validate([
            'owner_name'    => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:mysql.users,email'],
            'phone'         => ['required', 'string', 'max:20'],
            'store_name'    => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string'],
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 1. Create the tenant
        $tenantId = (string) Str::uuid();
        $databaseName = 'vectabyte_tenant_' . str_replace('-', '_', $tenantId);
        $tenant = Tenant::create([
            'id' => $tenantId,
            'store_name' => $request->store_name,
            'owner_name' => $request->owner_name,
            'owner_email' => $request->email,
            'owner_phone' => $request->phone,
            'status' => 'active',
            'database_name' => $databaseName,
            'subscription_plan' => 'basic',
        ]);

        // Create a dummy domain mapped to the tenant as required by the package structure
        $tenant->domains()->create([
            'domain' => $tenantId . '.localhost'
        ]);

        // 2. Create the central user
        $user = User::create([
            'name'      => $request->owner_name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'role'      => 'owner',
            'tenant_id' => $tenant->id,
            'is_active' => true,
            'password'  => Hash::make($request->password),
        ]);

        // Assign Spatie owner role centrally
        if (\App\Models\SpatieRole::where('name', 'owner')->exists()) {
            $user->assignRole('owner');
        }

        // 3. Initialize tenancy manually to create the store record in the tenant DB
        tenancy()->initialize($tenant);

        Store::create([
            'name'          => $request->store_name,
            'business_type' => $request->business_type,
            'user_id'       => $user->id,
        ]);

        // 4. Log in the user and save the tenant_id in the session
        Auth::login($user);
        session(['tenant_id' => $tenant->id]);

        return redirect('/dashboard');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 1. Try Super Admin Login
        if (Auth::guard('super_admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('super.dashboard'));
        }

        // 2. Try Store Owner / User Login (web guard)
        if (Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::guard('web')->user();

            // Check if account is active
            if (isset($user->is_active) && ! $user->is_active) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact the owner.',
                ])->onlyInput('email');
            }

            // Set the tenant ID in session & initialize tenancy
            if (isset($user->tenant_id) && $user->tenant_id) {
                $tenant = Tenant::find($user->tenant_id);
                if ($tenant && $tenant->status === 'suspended') {
                    Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return back()->withErrors([
                        'email' => 'Your store has been suspended. Please contact support.',
                    ])->onlyInput('email');
                }
                
                session(['tenant_id' => $user->tenant_id]);
                if ($tenant) {
                    tenancy()->initialize($tenant);
                }
            } else {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'This account is not associated with any store.',
                ])->onlyInput('email');
            }

            // Role-based redirect after login
            if ($user->hasRole('cashier')) {
                return redirect()->intended(route('sales.pos'));
            }

            if ($user->hasRole('warehouse')) {
                return redirect()->intended(route('godams.index'));
            }

            return redirect()->intended('/dashboard');
        }

        // 3. Try Employee Login
        if (Auth::guard('employee')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            $employee = Auth::guard('employee')->user();

            if (isset($employee->tenant_id) && $employee->tenant_id) {
                $tenant = Tenant::find($employee->tenant_id);
                if ($tenant && $tenant->status === 'suspended') {
                    Auth::guard('employee')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return back()->withErrors([
                        'email' => 'Your store has been suspended. Please contact support.',
                    ])->onlyInput('email');
                }
                
                session(['tenant_id' => $employee->tenant_id]);
                if ($tenant) {
                    tenancy()->initialize($tenant);
                }
            } else {
                Auth::guard('employee')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'This employee account is not associated with any store.',
                ])->onlyInput('email');
            }

            return redirect()->intended(route('employee.dashboard'));
        }

        // 4. Fail
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        Auth::guard('employee')->logout();
        Auth::guard('super_admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
