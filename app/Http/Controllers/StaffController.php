<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    /**
     * List all staff members (everyone except the owner themselves).
     */
    public function index()
    {
        $staff = User::with('roles')
            ->orderByDesc('id')
            ->get();

        return view('staff.index', compact('staff'));
    }

    /**
     * Show the form to create a new staff member.
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('staff.create', compact('roles'));
    }

    /**
     * Store a new staff member.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|max:255|unique:users,email',
            'password'              => 'required|string|min:8|confirmed',
            'role'                  => 'required|string|exists:spatie_roles,name',
            'is_active'             => 'nullable|boolean',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'phone'     => $request->phone ?? null,
            'role'      => $request->role,   // legacy string column kept in sync
            'is_active' => $request->boolean('is_active', true),
            'avatar'    => null,
        ]);

        $user->assignRole($request->role);

        return redirect()->route('staff.index')
            ->with('success', "Staff member \"{$user->name}\" created successfully.");
    }

    /**
     * Show the edit form for an existing staff member.
     */
    public function edit($id)
    {
        $member = User::with('roles')->findOrFail($id);
        $roles  = Role::orderBy('name')->get();

        return view('staff.edit', compact('member', 'roles'));
    }

    /**
     * Update an existing staff member.
     */
    public function update(Request $request, $id)
    {
        $member = User::with('roles')->findOrFail($id);
        $currentUser = Auth::user();

        // Prevent non-self editing of owner accounts
        if ($member->hasRole('owner') && $currentUser->id !== $member->id) {
            return redirect()->route('staff.index')
                ->with('error', 'The owner account can only be edited by the owner themselves.');
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', 'max:255', Rule::unique('users')->ignore($member->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => 'required|string|exists:spatie_roles,name',
            'is_active' => 'nullable|boolean',
        ]);

        $member->name      = $request->name;
        $member->email     = $request->email;
        $member->role      = $request->role;   // keep legacy column in sync
        $member->is_active = $request->boolean('is_active', true);

        if ($request->filled('password')) {
            $member->password = Hash::make($request->password);
        }

        $member->save();

        // Sync Spatie role (remove all roles, re-assign the selected one)
        $member->syncRoles([$request->role]);

        return redirect()->route('staff.index')
            ->with('success', "Staff member \"{$member->name}\" updated successfully.");
    }

    /**
     * Deactivate (soft-disable) a staff member.
     * We never hard-delete users — we set is_active = false.
     * Owner accounts and the currently logged-in user cannot be deleted.
     */
    public function destroy($id)
    {
        $member      = User::with('roles')->findOrFail($id);
        $currentUser = Auth::user();

        if ($member->hasRole('owner')) {
            return redirect()->route('staff.index')
                ->with('error', 'The owner account cannot be deleted.');
        }

        if ($currentUser->id === $member->id) {
            return redirect()->route('staff.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $member->is_active = false;
        $member->save();

        return redirect()->route('staff.index')
            ->with('success', "\"{$member->name}\" has been deactivated.");
    }
}
