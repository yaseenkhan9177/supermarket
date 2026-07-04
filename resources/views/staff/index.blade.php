@extends('layouts.admin')

@section('title', 'Staff Management')

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                <i class="fas fa-users-cog text-indigo-500 mr-2"></i> Staff Management
            </h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                Manage web-login accounts — Owner, Manager, Cashier, Warehouse.
            </p>
        </div>
        <a href="{{ route('staff.create') }}"
           id="btn-add-staff"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700
                  text-white text-sm font-bold rounded-xl shadow-sm transition-all transform hover:scale-105">
            <i class="fas fa-plus"></i> Add Staff Member
        </a>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div id="alert-success" class="flex items-center gap-3 mb-6 px-5 py-4 rounded-xl bg-green-50 border border-green-200 text-green-800 dark:bg-green-900/20 dark:border-green-700 dark:text-green-300">
            <i class="fas fa-check-circle text-green-500 text-lg flex-shrink-0"></i>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div id="alert-error" class="flex items-center gap-3 mb-6 px-5 py-4 rounded-xl bg-red-50 border border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-700 dark:text-red-300">
            <i class="fas fa-exclamation-circle text-red-500 text-lg flex-shrink-0"></i>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Staff Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-indigo-50 dark:bg-slate-700/60 text-left">
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-slate-300 uppercase text-xs tracking-wider">#</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-slate-300 uppercase text-xs tracking-wider">Name</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-slate-300 uppercase text-xs tracking-wider">Email</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-slate-300 uppercase text-xs tracking-wider">Role</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-slate-300 uppercase text-xs tracking-wider">Status</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-slate-300 uppercase text-xs tracking-wider">Joined</th>
                        <th class="px-6 py-4 font-bold text-gray-600 dark:text-slate-300 uppercase text-xs tracking-wider text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @forelse($staff as $index => $member)
                    <tr class="hover:bg-indigo-50/40 dark:hover:bg-slate-700/30 transition-colors group">
                        <td class="px-6 py-4 text-gray-400 dark:text-slate-500 font-mono text-xs">{{ $member->id }}</td>

                        {{-- Avatar + Name --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($member->avatar)
                                    <img src="{{ asset('storage/'.$member->avatar) }}"
                                         class="w-9 h-9 rounded-full object-cover ring-2 ring-indigo-200" alt="{{ $member->name }}">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-bold text-sm flex-shrink-0">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $member->name }}</div>
                                    @if($member->phone)
                                        <div class="text-xs text-gray-400">{{ $member->phone }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-gray-600 dark:text-slate-300">{{ $member->email }}</td>

                        {{-- Role Badge --}}
                        <td class="px-6 py-4">
                            @php
                                $roleName = $member->roles->first()?->name ?? $member->role ?? 'none';
                                $badgeColors = [
                                    'owner'     => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
                                    'manager'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                    'cashier'   => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                                    'warehouse' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                ];
                                $badgeClass = $badgeColors[$roleName] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300';
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide {{ $badgeClass }}">
                                {{ ucfirst($roleName) }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4">
                            @if($member->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 text-xs font-semibold rounded-full">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 text-xs font-semibold rounded-full">
                                    <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span> Inactive
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-gray-500 dark:text-slate-400 text-xs">
                            {{ $member->created_at->format('d M Y') }}
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('staff.edit', $member->id) }}"
                                   id="btn-edit-{{ $member->id }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700
                                          text-white text-xs font-semibold rounded-lg transition-all hover:scale-105">
                                    <i class="fas fa-edit"></i> Edit
                                </a>

                                @if(!$member->hasRole('owner') && $member->id !== Auth::id())
                                    <form method="POST" action="{{ route('staff.destroy', $member->id) }}"
                                          id="form-delete-{{ $member->id }}"
                                          onsubmit="return confirm('Deactivate {{ addslashes($member->name) }}? They will no longer be able to log in.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-100 hover:bg-red-600
                                                       text-red-600 hover:text-white text-xs font-semibold rounded-lg transition-all hover:scale-105">
                                            <i class="fas fa-ban"></i> Deactivate
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3 text-gray-400">
                                <i class="fas fa-users text-4xl opacity-30"></i>
                                <p class="font-medium">No staff members found.</p>
                                <a href="{{ route('staff.create') }}" class="text-indigo-500 hover:text-indigo-700 text-sm font-semibold">
                                    Add your first staff member →
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer count --}}
        @if($staff->isNotEmpty())
        <div class="px-6 py-3 bg-gray-50 dark:bg-slate-700/30 border-t border-gray-100 dark:border-slate-700 text-xs text-gray-500 dark:text-slate-400">
            Showing {{ $staff->count() }} account(s)
        </div>
        @endif
    </div>
</div>
@endsection
