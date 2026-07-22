@extends('layouts.app')

@section('title', 'Centralized Audit Log')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white flex items-center gap-3">
                <i class="fas fa-user-shield text-indigo-600"></i> Centralized Audit Log
            </h1>
            <p class="text-slate-500 text-sm mt-1">Cross-entity activity stream for store owner & administrative oversight.</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-lg">
        <form method="GET" action="{{ route('reports.audit-log') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Action Type</label>
                <select name="action_type" class="w-full text-sm border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                    <option value="">All Actions</option>
                    @foreach($actionTypes as $at)
                        <option value="{{ $at }}" {{ request('action_type') == $at ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $at)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Performed By</label>
                <select name="performed_by" class="w-full text-sm border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
                    <option value="">All Users</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('performed_by') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">From Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" 
                       class="w-full text-sm border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">To Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" 
                       class="w-full text-sm border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl text-sm shadow transition">
                    Filter
                </button>
                @if(request()->hasAny(['action_type', 'performed_by', 'start_date', 'end_date']))
                    <a href="{{ route('reports.audit-log') }}" class="px-3 py-2.5 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl text-sm font-bold hover:bg-slate-300 transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Audit Log Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-semibold text-xs border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="p-4 w-44">Date & Time</th>
                        <th class="p-4 w-40">Action Type</th>
                        <th class="p-4 w-48">Subject</th>
                        <th class="p-4 w-40">Performed By</th>
                        <th class="p-4">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($logs as $log)
                        @php
                            $badgeClass = match($log->action_type) {
                                'customer_write_off'     => 'bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-400',
                                'customer_reinstate'     => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400',
                                'customer_reversal',
                                'supplier_reversal'      => 'bg-purple-100 text-purple-800 dark:bg-purple-950/40 dark:text-purple-400',
                                'customer_adjustment',
                                'supplier_adjustment'    => 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400',
                                'daily_closing'          => 'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-400',
                                default                  => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-300',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                            <td class="p-4 text-xs font-mono text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                {{ $log->created_at->format('d M Y, h:i:s A') }}
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-black uppercase tracking-wider inline-block {{ $badgeClass }}">
                                    {{ str_replace('_', ' ', $log->action_type) }}
                                </span>
                            </td>
                            <td class="p-4 font-medium text-slate-800 dark:text-slate-200">
                                @if($log->subject_type === 'Customer' && $log->subject_id)
                                    <a href="{{ route('customers.show', $log->subject_id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold">
                                        <i class="fas fa-user text-xs mr-1"></i> Customer #{{ $log->subject_id }}
                                    </a>
                                @elseif($log->subject_type === 'Supplier' && $log->subject_id)
                                    <a href="{{ route('suppliers.show', $log->subject_id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold">
                                        <i class="fas fa-truck text-xs mr-1"></i> Supplier #{{ $log->subject_id }}
                                    </a>
                                @elseif($log->subject_type)
                                    <span class="text-slate-600 dark:text-slate-400">{{ $log->subject_type }} #{{ $log->subject_id }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="p-4 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                {{ $log->performer->name ?? 'System / Admin' }}
                            </td>
                            <td class="p-4 text-slate-800 dark:text-slate-200 text-xs">
                                {{ $log->description }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-400">
                                No audit log records found matching the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
