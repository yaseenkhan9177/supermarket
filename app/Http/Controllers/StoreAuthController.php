<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone'         => ['required', 'string', 'max:20'],
            'store_name'    => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string'],
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'      => $request->owner_name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'role'      => 'owner',
            'is_active' => true,
            'password'  => Hash::make($request->password),
        ]);

        // Assign Spatie owner role if the role exists
        if (\Spatie\Permission\Models\Role::where('name', 'owner')->exists()) {
            $user->assignRole('owner');
        }

        $store = Store::create([
            'name'          => $request->store_name,
            'business_type' => $request->business_type,
            'user_id'       => $user->id,
        ]);

        Auth::login($user);

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

            // ----------------------------------------------------------------
            // Check if account is active
            // ----------------------------------------------------------------
            if (isset($user->is_active) && ! $user->is_active) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Your account has been deactivated. Please contact the owner.',
                ])->onlyInput('email');
            }

            // ----------------------------------------------------------------
            // Role-based redirect after login
            // ----------------------------------------------------------------
            if ($user->hasRole('cashier')) {
                return redirect()->intended(route('sales.pos'));
            }

            if ($user->hasRole('warehouse')) {
                return redirect()->intended(route('godams.index'));
            }

            // owner, manager, or any other role → dashboard
            return redirect()->intended('/dashboard');
        }

        // 3. Try Employee Login
        if (Auth::guard('employee')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
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
