@extends('super_admin.layout')

@section('title', 'Platform Stores')
@section('header', 'Platform Stores')
@section('subheader', 'Manage tenant store onboarding, payment validity, and status')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

    {{-- Toolbar --}}
    <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
        <form method="GET" class="flex items-center gap-3 flex-1 flex-wrap">
            <div class="relative flex-1 max-w-xs min-w-[200px]">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search stores or owners..."
                    class="pl-9 pr-4 py-2 border border-slate-200 rounded-xl text-sm w-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <select name="status" class="border border-slate-200 rounded-xl py-2 pl-3 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Statuses</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors">
                Filter
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('super.tenants') }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>
            @endif
        </form>
        <div class="text-xs text-slate-400 font-medium">{{ $tenants->total() }} stores registered</div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full whitespace-nowrap text-sm">
            <thead>
                <tr class="bg-slate-50 text-left text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <th class="px-6 py-3.5">Store / ID</th>
                    <th class="px-6 py-3.5">Owner Contact</th>
                    <th class="px-6 py-3.5">Status</th>
                    <th class="px-6 py-3.5">Paid Until</th>
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
                                {{ strtoupper(substr($tenant->store_name ?? $tenant->id, 0, 1)) }}
                            </div>
                            <div>
                                <a href="{{ route('super.tenants.show', $tenant->id) }}" class="font-semibold text-slate-800 hover:text-indigo-600">
                                    {{ $tenant->store_name ?? 'Unnamed Store' }}
                                </a>
                                <p class="text-xs text-slate-400 font-mono">{{ substr($tenant->id, 0, 8) }}...</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-medium text-slate-700">{{ $tenant->owner_name ?? $tenant->user->name ?? 'N/A' }}</p>
                        <p class="text-xs text-slate-400">{{ $tenant->owner_email ?? $tenant->user->email ?? 'N/A' }} • {{ $tenant->owner_phone ?? 'No Phone' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @if($tenant->status === 'active' && $tenant->paid_until && $tenant->paid_until->lt(now()->startOfDay()))
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-200 uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Expired
                            </span>
                        @elseif($tenant->status === 'active')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Active
                            </span>
                        @elseif($tenant->status === 'pending')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-blue-50 text-blue-700 border border-blue-100 uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>Pending Approval
                            </span>
                        @elseif($tenant->status === 'suspended')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-100 uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Suspended
                            </span>
                        @elseif($tenant->status === 'expired')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-200 uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Expired
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-slate-100 text-slate-600 border border-slate-200 uppercase">
                                {{ ucfirst($tenant->status) }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-xs font-medium">
                        @if($tenant->paid_until)
                            <span class="{{ $tenant->paid_until->lt(now()->startOfDay()) ? 'text-amber-600 font-bold' : 'text-slate-700' }}">
                                {{ $tenant->paid_until->format('d M Y') }}
                            </span>
                        @else
                            <span class="text-slate-400 italic">Not set</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-400">
                        {{ $tenant->created_at ? $tenant->created_at->format('d M Y') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('super.tenants.show', $tenant->id) }}" 
                            class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                            <i class="fas fa-edit text-xs"></i> Manage
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <i class="fas fa-store text-4xl text-slate-200 mb-3 block"></i>
                        <p class="text-slate-400 font-medium">No stores match your filters.</p>
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