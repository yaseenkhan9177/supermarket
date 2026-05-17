@extends('super_admin.layout')

@section('title', 'Active Stores')
@section('header', 'Active Stores')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full whitespace-nowrap">
            <thead>
                <tr class="text-left font-bold text-gray-500 border-b border-gray-100 bg-gray-50 uppercase text-xs tracking-wider">
                    <th class="px-6 py-4">Store Name</th>
                    <th class="px-6 py-4">Owner</th>
                    <th class="px-6 py-4">Domain</th>
                    <th class="px-6 py-4">Plan</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($tenants as $tenant)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-semibold text-gray-800">{{ $tenant->store_name }}</div>
                        <div class="text-xs text-gray-500">{{ $tenant->id }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-800">{{ $tenant->owner_name }}</div>
                        <div class="text-xs text-gray-500">{{ $tenant->owner_email }}</div>
                    </td>
                    <td class="px-6 py-4">
                        @foreach($tenant->domains as $domain)
                        <span class="block text-sm text-indigo-600">{{ $domain->domain }}</span>
                        @endforeach
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-50 text-purple-600 border border-purple-100 uppercase">
                            {{ $tenant->subscription_plan }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($tenant->status === 'active')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-50 text-green-600 border border-green-100 uppercase">Active</span>
                        @elseif($tenant->status === 'suspended')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-50 text-red-600 border border-red-100 uppercase">Suspended</span>
                        @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-600 border border-amber-100 uppercase">{{ $tenant->status }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right space-x-2 flex justify-end items-center">
                        <!-- Login as Owner -->
                        <form action="{{ route('super.tenants.loginAs', $tenant->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" title="Login as Owner" class="text-gray-400 hover:text-indigo-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                            </button>
                        </form>
                        <!-- Backup -->
                        <form action="{{ route('super.tenants.backup', $tenant->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" title="Backup Database" class="text-gray-400 hover:text-blue-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                                </svg>
                            </button>
                        </form>
                        <!-- Suspend Toggle -->
                        <form action="{{ route('super.tenants.suspend', $tenant->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" title="{{ $tenant->status === 'active' ? 'Suspend Store' : 'Activate Store' }}" class="{{ $tenant->status === 'active' ? 'text-gray-400 hover:text-red-600' : 'text-green-400 hover:text-green-600' }} transition-colors">
                                @if($tenant->status === 'active')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                </svg>
                                @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                @endif
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        No stores found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-100">
        {{ $tenants->links() }}
    </div>
</div>
@endsection