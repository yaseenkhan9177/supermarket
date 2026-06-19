@extends('super_admin.layout')

@section('title', 'Admin Users')
@section('header', 'Admin Users')
@section('subheader', 'Manage all super admin accounts')

@section('content')
<div class="space-y-5">

    {{-- Header Actions --}}
    <div class="flex items-center justify-between">
        <form method="GET" class="flex items-center gap-3">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search admins..."
                    class="pl-9 pr-4 py-2 border border-slate-200 rounded-xl text-sm w-60 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit" class="bg-slate-700 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-slate-800 transition-colors">
                <i class="fas fa-search mr-1.5"></i>Search
            </button>
            @if(request('search'))
                <a href="{{ route('super.users') }}" class="text-sm text-slate-500 hover:text-slate-700">Clear</a>
            @endif
        </form>
        <a href="{{ route('super.users.create') }}"
           class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/20">
            <i class="fas fa-plus"></i>
            Add New Admin
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="bg-slate-50 text-left text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <th class="px-6 py-3.5">Admin</th>
                    <th class="px-6 py-3.5">Role</th>
                    <th class="px-6 py-3.5">Status</th>
                    <th class="px-6 py-3.5">Last Login</th>
                    <th class="px-6 py-3.5">Joined</th>
                    <th class="px-6 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($admins as $admin)
                <tr class="hover:bg-slate-50/70 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-sm">
                                {{ strtoupper(substr($admin->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-slate-800 flex items-center gap-2">
                                    {{ $admin->name }}
                                    @if($admin->id === Auth::guard('super_admin')->user()->id)
                                        <span class="text-[10px] bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded font-bold">YOU</span>
                                    @endif
                                </p>
                                <p class="text-xs text-slate-400">{{ $admin->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($admin->role === 'super_owner')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
                                <i class="fas fa-crown text-[10px]"></i>Super Owner
                            </span>
                        @elseif($admin->role === 'support')
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-sky-50 text-sky-700 border border-sky-100">
                                <i class="fas fa-headset text-[10px] mr-1"></i>Support
                            </span>
                        @elseif($admin->role === 'sales')
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                                <i class="fas fa-chart-line text-[10px] mr-1"></i>Sales
                            </span>
                        @else
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-slate-100 text-slate-600">{{ $admin->role }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($admin->is_active)
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400">
                                <span class="w-2 h-2 rounded-full bg-slate-300"></span>Inactive
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-400">
                        {{ $admin->last_login_at ? $admin->last_login_at->diffForHumans() : 'Never' }}
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-400">
                        {{ $admin->created_at->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-1">

                            {{-- Edit --}}
                            <a href="{{ route('super.users.edit', $admin->id) }}"
                               class="p-2 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Edit">
                                <i class="fas fa-edit text-sm"></i>
                            </a>

                            @if($admin->id !== Auth::guard('super_admin')->user()->id)
                                {{-- Toggle Status --}}
                                <form action="{{ route('super.users.toggle', $admin->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit"
                                        title="{{ $admin->is_active ? 'Deactivate' : 'Activate' }}"
                                        class="p-2 rounded-lg {{ $admin->is_active ? 'text-slate-400 hover:text-amber-600 hover:bg-amber-50' : 'text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50' }} transition-colors">
                                        <i class="fas {{ $admin->is_active ? 'fa-toggle-off' : 'fa-toggle-on' }} text-sm"></i>
                                    </button>
                                </form>

                                {{-- Delete --}}
                                <form action="{{ route('super.users.destroy', $admin->id) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Are you sure you want to permanently delete {{ addslashes($admin->name) }}?')">
                                    @csrf
                                    <button type="submit"
                                        title="Delete Admin"
                                        class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <i class="fas fa-users text-4xl text-slate-200 mb-3 block"></i>
                        <p class="text-slate-400 font-medium">No admin users found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($admins->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $admins->links() }}
        </div>
        @endif
    </div>

    {{-- Registration PINs section --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800">Dynamic Registration PINs</h3>
                <p class="text-xs text-slate-400 mt-0.5">One-time PINs generated for registering new super admin users.</p>
            </div>
            <form action="{{ route('super.pins.generate') }}" method="POST">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:from-emerald-700 hover:to-teal-700 transition-all shadow-lg shadow-emerald-500/10">
                    <i class="fas fa-key"></i>
                    Generate Registration PIN
                </button>
            </form>
        </div>

        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="bg-slate-50 text-left text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <th class="px-6 py-3.5">PIN Code</th>
                    <th class="px-6 py-3.5">Status</th>
                    <th class="px-6 py-3.5">Used By</th>
                    <th class="px-6 py-3.5">Used At</th>
                    <th class="px-6 py-3.5">Generated At</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($pins as $pin)
                <tr class="hover:bg-slate-50/70 transition-colors">
                    <td class="px-6 py-4">
                        <span class="font-mono text-sm bg-slate-100 text-slate-700 px-2 py-1 rounded font-bold tracking-widest">
                            {{ $pin->pin }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($pin->used_at)
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-slate-100 text-slate-400 border border-slate-200">
                                Used
                            </span>
                        @else
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100 animate-pulse">
                                Active / Unused
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-600">
                        {{ $pin->used_by_email ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-400">
                        {{ $pin->used_at ? $pin->used_at->format('d M Y, H:i') : '—' }}
                    </td>
                    <td class="px-6 py-4 text-xs text-slate-400">
                        {{ $pin->created_at->format('d M Y, H:i') }} ({{ $pin->created_at->diffForHumans() }})
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                        <i class="fas fa-key text-3xl text-slate-200 mb-2 block"></i>
                        No registration PINs generated yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection