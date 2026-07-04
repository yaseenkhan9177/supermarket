@extends('layouts.admin')

@section('title', 'My Profile')

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">
            <i class="fas fa-user-circle text-indigo-500 mr-2"></i> My Profile
        </h1>
        <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
            Manage your display name and password.
        </p>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div id="alert-success" class="flex items-center gap-3 mb-6 px-5 py-4 rounded-xl bg-green-50 border border-green-200 text-green-800 dark:bg-green-900/20 dark:border-green-700 dark:text-green-300">
            <i class="fas fa-check-circle text-green-500 text-lg flex-shrink-0"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Profile Info Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-8 mb-6">

        {{-- Avatar + Identity --}}
        <div class="flex items-center gap-5 mb-8 pb-8 border-b border-gray-100 dark:border-slate-700">
            <div class="w-16 h-16 rounded-2xl bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-extrabold text-2xl flex-shrink-0 shadow-inner">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div>
                <div class="text-xl font-bold text-gray-900 dark:text-white">{{ Auth::user()->name }}</div>
                <div class="text-sm text-gray-500 dark:text-slate-400">{{ Auth::user()->email }}</div>
                <div class="mt-1.5">
                    @php
                        $roleName = Auth::user()->roles->first()?->name ?? Auth::user()->role ?? 'user';
                        $badgeColors = [
                            'owner'     => 'bg-purple-100 text-purple-700',
                            'manager'   => 'bg-blue-100 text-blue-700',
                            'cashier'   => 'bg-green-100 text-green-700',
                            'warehouse' => 'bg-amber-100 text-amber-700',
                        ];
                        $badgeClass = $badgeColors[$roleName] ?? 'bg-gray-100 text-gray-600';
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide {{ $badgeClass }}">
                        {{ ucfirst($roleName) }}
                    </span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" id="form-profile">
            @csrf
            @method('PUT')

            {{-- Errors --}}
            @if($errors->any())
                <div class="mb-5 px-4 py-3 rounded-xl bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-700">
                    @foreach($errors->all() as $error)
                        <p class="text-sm text-red-600 dark:text-red-300">• {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Name --}}
            <div class="mb-5">
                <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                    Display Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name"
                       value="{{ old('name', Auth::user()->name) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-slate-600 rounded-xl
                              bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                              focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm transition
                              @error('name') border-red-400 @enderror">
            </div>

            {{-- Read-only fields --}}
            <div class="mb-5">
                <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Email Address</label>
                <input type="email" value="{{ Auth::user()->email }}" disabled
                       class="w-full px-4 py-2.5 border border-gray-200 dark:border-slate-600 rounded-xl
                              bg-gray-50 dark:bg-slate-700/50 text-gray-400 dark:text-slate-500
                              cursor-not-allowed text-sm">
                <p class="mt-1 text-xs text-gray-400 dark:text-slate-500">
                    <i class="fas fa-lock mr-1"></i>Email can only be changed by an owner via Staff Management.
                </p>
            </div>

            <div class="mb-8">
                <label class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">Your Role</label>
                <input type="text" value="{{ ucfirst($roleName) }}" disabled
                       class="w-full px-4 py-2.5 border border-gray-200 dark:border-slate-600 rounded-xl
                              bg-gray-50 dark:bg-slate-700/50 text-gray-400 dark:text-slate-500
                              cursor-not-allowed text-sm">
                <p class="mt-1 text-xs text-gray-400 dark:text-slate-500">
                    <i class="fas fa-lock mr-1"></i>Roles can only be changed by an owner via Staff Management.
                </p>
            </div>

            {{-- Password Change Section --}}
            <div class="border-t border-gray-100 dark:border-slate-700 pt-6 mb-6">
                <h3 class="text-sm font-bold text-gray-700 dark:text-slate-300 mb-4">
                    <i class="fas fa-key text-indigo-400 mr-1.5"></i> Change Password
                    <span class="text-gray-400 font-normal text-xs ml-1">(leave blank to keep current)</span>
                </h3>

                <div class="mb-4">
                    <label for="current_password" class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                        Current Password
                    </label>
                    <div x-data="{ show: false }" class="relative">
                        <input :type="show ? 'text' : 'password'"
                               id="current_password" name="current_password"
                               placeholder="Enter your current password"
                               class="w-full px-4 py-2.5 pr-10 border border-gray-300 dark:border-slate-600 rounded-xl
                                      bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                                      focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm transition
                                      @error('current_password') border-red-400 @enderror">
                        <button type="button" @click="show = !show"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                            <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                        New Password
                    </label>
                    <input type="password" id="password" name="password"
                           placeholder="Minimum 8 characters"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-slate-600 rounded-xl
                                  bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                                  focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm transition
                                  @error('password') border-red-400 @enderror">
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                        Confirm New Password
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           placeholder="Re-enter new password"
                           class="w-full px-4 py-2.5 border border-gray-300 dark:border-slate-600 rounded-xl
                                  bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                                  focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm transition">
                </div>
            </div>

            {{-- Submit --}}
            <button type="submit" id="btn-save-profile"
                    class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl
                           shadow-sm transition-all hover:shadow-indigo-300/50 hover:shadow-lg">
                <i class="fas fa-save mr-2"></i> Save Profile
            </button>
        </form>
    </div>
</div>
@endsection
