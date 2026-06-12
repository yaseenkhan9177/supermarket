@extends('super_admin.layout')

@section('title', 'Edit Admin User')
@section('header', 'Edit Admin User')
@section('subheader', 'Update admin account details')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-lg">
                {{ strtoupper(substr($admin->name, 0, 1)) }}
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-800">{{ $admin->name }}</h3>
                <p class="text-xs text-slate-400">{{ $admin->email }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('super.users.update', $admin->id) }}" class="p-8 space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Full Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $admin->name) }}" required
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-rose-400 @enderror">
                    @error('name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Role <span class="text-rose-500">*</span></label>
                    <select name="role" required
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('role') border-rose-400 @enderror">
                        <option value="super_owner" {{ old('role', $admin->role) === 'super_owner' ? 'selected' : '' }}>👑 Super Owner</option>
                        <option value="support" {{ old('role', $admin->role) === 'support' ? 'selected' : '' }}>🎧 Support</option>
                        <option value="sales" {{ old('role', $admin->role) === 'sales' ? 'selected' : '' }}>📈 Sales</option>
                    </select>
                    @error('role')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email Address <span class="text-rose-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $admin->email) }}" required
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-rose-400 @enderror">
                @error('email')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Change Password (optional)</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">New Password</label>
                        <input type="password" name="password"
                            placeholder="Leave blank to keep current"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white @error('password') border-rose-400 @enderror">
                        @error('password')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Confirm Password</label>
                        <input type="password" name="password_confirmation"
                            placeholder="Repeat new password"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('super.users') }}"
                   class="px-5 py-2.5 border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors">
                    ← Back
                </a>
                <button type="submit"
                    class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/20">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
