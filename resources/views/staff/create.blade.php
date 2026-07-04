@extends('layouts.admin')

@section('title', 'Add Staff Member')

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('staff.index') }}"
               class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-slate-300 hover:bg-indigo-100 hover:text-indigo-600 transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                    Add Staff Member
                </h1>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5">Create a new login account with a specific role.</p>
            </div>
        </div>
        <a href="{{ route('godams.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-xl shadow-sm transition transform hover:scale-105">
            <i class="fas fa-warehouse"></i> Godams
        </a>
    </div>

    {{-- Errors --}}
    @if($errors->any())
        <div class="mb-6 px-5 py-4 rounded-xl bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-700">
            <p class="text-sm font-bold text-red-700 dark:text-red-400 mb-2">Please fix the following errors:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li class="text-sm text-red-600 dark:text-red-300">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 p-8">
        <form method="POST" action="{{ route('staff.store') }}" id="form-create-staff">
            @csrf

            {{-- Name --}}
            <div class="mb-5">
                <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name') }}"
                       placeholder="e.g. Ali Hassan"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-slate-600 rounded-xl
                              bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                              focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm transition
                              @error('name') border-red-400 @enderror">
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-5">
                <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                    Email Address <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       placeholder="e.g. ali@store.com"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-slate-600 rounded-xl
                              bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                              focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm transition
                              @error('email') border-red-400 @enderror">
                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Phone (optional) --}}
            <div class="mb-5">
                <label for="phone" class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                    Phone <span class="text-gray-400 font-normal text-xs">(optional)</span>
                </label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                       placeholder="e.g. 0300-1234567"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-slate-600 rounded-xl
                              bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                              focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm transition">
            </div>

            {{-- Role --}}
            <div class="mb-5">
                <label for="role" class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                    Role <span class="text-red-500">*</span>
                </label>
                <select id="role" name="role"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-slate-600 rounded-xl
                               bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                               focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm transition
                               @error('role') border-red-400 @enderror">
                    <option value="">— Select a role —</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>
                @error('role')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                <div class="mt-2 grid grid-cols-2 gap-2 text-xs text-gray-500 dark:text-slate-400">
                    <div class="flex items-start gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-purple-500 mt-0.5 flex-shrink-0"></span>
                        <span><b>Owner</b> — Full access to everything</span>
                    </div>
                    <div class="flex items-start gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500 mt-0.5 flex-shrink-0"></span>
                        <span><b>Manager</b> — All except settings &amp; staff</span>
                    </div>
                    <div class="flex items-start gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-green-500 mt-0.5 flex-shrink-0"></span>
                        <span><b>Cashier</b> — POS, Sales, Items only</span>
                    </div>
                    <div class="flex items-start gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-amber-500 mt-0.5 flex-shrink-0"></span>
                        <span><b>Warehouse</b> — Godams, Stock, Items</span>
                    </div>
                </div>
            </div>

            {{-- Password --}}
            <div class="mb-5">
                <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                    Password <span class="text-red-500">*</span>
                </label>
                <div x-data="{ show: false }" class="relative">
                    <input :type="show ? 'text' : 'password'"
                           id="password" name="password"
                           placeholder="Minimum 8 characters"
                           class="w-full px-4 py-2.5 pr-10 border border-gray-300 dark:border-slate-600 rounded-xl
                                  bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                                  focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm transition
                                  @error('password') border-red-400 @enderror">
                    <button type="button" @click="show = !show"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                        <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 dark:text-slate-300 mb-1.5">
                    Confirm Password <span class="text-red-500">*</span>
                </label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       placeholder="Re-enter password"
                       class="w-full px-4 py-2.5 border border-gray-300 dark:border-slate-600 rounded-xl
                              bg-white dark:bg-slate-700 text-gray-900 dark:text-white
                              focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-sm transition">
            </div>

            {{-- Active Toggle --}}
            <div class="mb-8 flex items-center justify-between p-4 bg-gray-50 dark:bg-slate-700/50 rounded-xl border border-gray-200 dark:border-slate-600">
                <div>
                    <p class="text-sm font-semibold text-gray-700 dark:text-slate-300">Account Active</p>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Inactive accounts cannot log in.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer" x-data="{ active: true }">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           x-model="active" @change="" checked
                           class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-400
                                rounded-full peer dark:bg-gray-600
                                peer-checked:after:translate-x-full peer-checked:after:border-white
                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                after:bg-white after:border-gray-300 after:border after:rounded-full
                                after:h-5 after:w-5 after:transition-all
                                peer-checked:bg-indigo-600"></div>
                </label>
            </div>

            {{-- Submit --}}
            <div class="flex items-center gap-3">
                <button type="submit" id="btn-submit-staff"
                        class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl
                               shadow-sm transition-all hover:shadow-indigo-300/50 hover:shadow-lg">
                    <i class="fas fa-user-plus mr-2"></i> Create Staff Member
                </button>
                <a href="{{ route('staff.index') }}"
                   class="px-5 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-slate-700 dark:hover:bg-slate-600
                          text-gray-600 dark:text-slate-300 font-semibold rounded-xl transition text-sm">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
