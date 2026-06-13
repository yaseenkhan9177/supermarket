<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use App\Models\SuperAdminPin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('super_admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('super_admin')->attempt($credentials, $request->remember)) {
            $request->session()->regenerate();

            // Update last login
            /** @var \App\Models\SuperAdmin $user */
            $user = Auth::guard('super_admin')->user();
            $user->update(['last_login_at' => now()]);

            return redirect()->intended(route('super.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegistrationForm()
    {
        return view('super_admin.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:super_admins,email',
            'password' => 'required|string|min:8|confirmed',
            'pin' => 'required|string',
        ]);

        // Verify the dynamic one-time PIN
        $pinRecord = SuperAdminPin::where('pin', $request->pin)
            ->whereNull('used_at')
            ->first();

        if (!$pinRecord) {
            return back()->withErrors([
                'pin' => 'The provided registration PIN is invalid or has already been used.',
            ])->withInput();
        }

        // Create Super Admin user
        $superAdmin = SuperAdmin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'super_owner', // default dynamic registration role
            'is_active' => true,
        ]);

        // Mark PIN as used
        $pinRecord->update([
            'used_at' => now(),
            'used_by_email' => $request->email,
        ]);

        // Log in the user
        Auth::guard('super_admin')->login($superAdmin);

        return redirect()->route('super.dashboard')->with('success', 'Super Admin registration completed successfully!');
    }

    public function logout(Request $request)
    {
        Auth::guard('super_admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('super.login');
    }
}
