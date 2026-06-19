@extends('super_admin.layout')

@section('title', 'Create Admin User')
@section('header', 'Create Admin User')
@section('subheader', 'Add a new super admin account')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                <i class="fas fa-user-plus text-white text-sm"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-800">New Admin User</h3>
                <p class="text-xs text-slate-400">Fill in the details below to create a new admin account.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('super.users.store') }}" class="p-8 space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Full Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        placeholder="e.g. John Doe"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('name') border-rose-400 @enderror">
                    @error('name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Role <span class="text-rose-500">*</span></label>
                    <select name="role" required
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('role') border-rose-400 @enderror">
                        <option value="">Select role...</option>
                        <option value="super_owner" {{ old('role') === 'super_owner' ? 'selected' : '' }}>👑 Super Owner</option>
                        <option value="support" {{ old('role') === 'support' ? 'selected' : '' }}>🎧 Support</option>
                        <option value="sales" {{ old('role') === 'sales' ? 'selected' : '' }}>📈 Sales</option>
                    </select>
                    @error('role')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email Address <span class="text-rose-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    placeholder="admin@ownstore.com"
                    class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('email') border-rose-400 @enderror">
                @error('email')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" required
                        placeholder="Min. 8 characters"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('password') border-rose-400 @enderror">
                    @error('password')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Confirm Password <span class="text-rose-500">*</span></label>
                    <input type="password" name="password_confirmation" required
                        placeholder="Repeat password"
                        class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('super.users') }}"
                   class="px-5 py-2.5 border border-slate-200 text-slate-600 rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl text-sm font-semibold hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/20">
                    <i class="fas fa-plus mr-2"></i>Create Admin
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
