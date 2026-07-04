<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Show the profile edit page for the authenticated user.
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the authenticated user's profile.
     * - Name can always be changed.
     * - Password requires current password confirmation.
     * - Email and Role cannot be changed here (owner-only via Staff Management).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'             => 'required|string|max:255',
            'current_password' => 'nullable|string',
            'password'         => 'nullable|string|min:8|confirmed',
        ]);

        // Update name
        $user->name = $request->name;

        // Handle password change
        if ($request->filled('password')) {
            if (! $request->filled('current_password')) {
                return back()->withErrors(['current_password' => 'Current password is required to set a new password.'])->withInput();
            }
            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'The current password is incorrect.'])->withInput();
            }
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('profile.edit')
            ->with('success', 'Profile updated successfully!');
    }
}
