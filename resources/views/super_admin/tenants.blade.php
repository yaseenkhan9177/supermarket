@extends('super_admin.layout')

@section('title', 'Active Stores')
@section('header', 'Active Stores')
@section('subheader', 'Manage all registered tenant stores')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    {{-- Toolbar --}}
    <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
        <form method="GET" class="flex items-center gap-3 flex-1">
            <div class="relative flex-1 max-w-xs">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search stores..."
                    class="pl-9 pr-4 py-2 border border-slate-200 rounded-xl text-sm w-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <select name="status" class="border border-slate-200 rounded-xl py-2 pl-3 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors">
                Filter
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('super.tenants') }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>
            @endif
        </form>
        <div class="text-xs text-slate-400">{{ $tenants->total() }} stores total</div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full whitespace-nowrap text-sm">
            <thead>
                <tr class="bg-slate-50 text-left text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <th class="px-6 py-3.5">Store</th>
                    <th class="px-6 py-3.5">Owner</th>
                    <th class="px-6 py-3.5">Plan</th>
                    <th class="px-6 py-3.5">Status</th>
                    <th class="px-6 py-3.5">Joined</th>
                    <th class="px-6 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($tenants as $tenant)
                <tr class="hover:bg-slate-50/70 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center text-indigo-700 font-bold text-sm flex-shrink-0">
                                {{ strtoupper(substr($tenant->store_name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-slate-800">{{ $tenant->store_name }}</p>
                                <p class="text-xs text-slate-400 font-mono">{{ substr($tenant->id, 0, 8) }}...</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-medium text-slate-700">{{ $tenant->owner_name }}</p>
                        <p class="text-xs text-slate-400">{{ $tenant->owner_email }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-purple-50 text-purple-700 border border-purple-100 uppercase">
                            {{ $tenant->subscription_plan ?? 'Free' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($tenant->status === 'active')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>Active
                            </span>
                        @elseif($tenant->status === 'suspended')
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-slate-100 text-slate-600 border border-slate-200 uppercase">Suspended</span>
                        @elseif($tenant->status === 'pending')
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-100 uppercase">Pending</span>
                        @else
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-100 uppercase">{{ $tenant->status }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-400">
                        {{ $tenant->created_at->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-1">
                            {{-- Suspend/Activate --}}
                            <form action="{{ route('super.tenants.suspend', $tenant->id) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Are you sure you want to {{ $tenant->status === 'active' ? 'suspend' : 'activate' }} this store?')">
                                @csrf
                                <button type="submit"
                                    title="{{ $tenant->status === 'active' ? 'Suspend Store' : 'Activate Store' }}"
                                    class="p-2 rounded-lg {{ $tenant->status === 'active' ? 'text-slate-400 hover:text-rose-600 hover:bg-rose-50' : 'text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50' }} transition-colors">
                                    <i class="fas {{ $tenant->status === 'active' ? 'fa-ban' : 'fa-check-circle' }} text-sm"></i>
                                </button>
                            </form>

                            {{-- Login As --}}
                            <form action="{{ route('super.tenants.loginAs', $tenant->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" title="Login as Owner"
                                    class="p-2 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors">
                                    <i class="fas fa-sign-in-alt text-sm"></i>
                                </button>
                            </form>

                            {{-- Backup --}}
                            <form action="{{ route('super.tenants.backup', $tenant->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" title="Backup Database"
                                    class="p-2 rounded-lg text-slate-400 hover:text-sky-600 hover:bg-sky-50 transition-colors">
                                    <i class="fas fa-database text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <i class="fas fa-store text-4xl text-slate-200 mb-3 block"></i>
                        <p class="text-slate-400 font-medium">No stores found.</p>
                        <p class="text-slate-300 text-sm mt-1">Try adjusting your filters.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($tenants->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">
        {{ $tenants->links() }}
    </div>
    @endif
</div>
@endsection