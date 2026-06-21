@extends('layouts.admin')

@section('title', 'Stock Transfers')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Breadcrumb and Action --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-850 dark:text-slate-400 dark:hover:text-white font-semibold transition text-sm">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">Stock Transfer Audits</h1>
        </div>
        <a href="{{ route('stock-transfers.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2.5 rounded-lg flex items-center gap-2 shadow-md transition">
            <i class="fas fa-exchange-alt"></i> New Stock Transfer
        </a>
    </div>

    {{-- Session Alerts --}}
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-250 text-emerald-800 rounded-xl p-4 mb-6 shadow-sm flex items-center gap-3">
        <i class="fas fa-check-circle text-lg text-emerald-600"></i>
        <div class="font-semibold">{{ session('success') }}</div>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-250 text-red-800 rounded-xl p-4 mb-6 shadow-sm flex items-center gap-3">
        <i class="fas fa-exclamation-circle text-lg text-red-600"></i>
        <div class="font-semibold">{{ session('error') }}</div>
    </div>
    @endif

    {{-- Stats Banner --}}
    <div class="bg-indigo-50 border border-indigo-100 dark:bg-slate-800 dark:border-indigo-900 rounded-xl p-4 mb-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-650 flex items-center justify-center text-white shadow-md">
                <i class="fas fa-exchange-alt text-lg"></i>
            </div>
            <div>
                <span class="text-xs text-gray-500 dark:text-slate-450 uppercase font-bold tracking-wide">Monthly Summary (Current Month)</span>
                <div class="flex items-center gap-4 mt-0.5 text-sm font-semibold text-gray-700 dark:text-slate-300">
                    <div>
                        Transfers logged: 
                        <span class="text-indigo-600 dark:text-indigo-400 font-bold font-mono">{{ $totalTransfersCount }}</span>
                    </div>
                    <div class="h-4 w-px bg-gray-300 dark:bg-slate-700"></div>
                    <div>
                        Units Transferred: 
                        <span class="text-indigo-600 dark:text-indigo-400 font-bold font-mono">{{ $totalTransfersQty }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-gray-200 dark:border-slate-700 mb-6">
        <form action="{{ route('stock-transfers.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            
            {{-- Date Range --}}
            <div>
                <label class="block text-xs font-bold text-gray-550 dark:text-slate-400 uppercase mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                       class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg p-2 text-sm text-gray-900 dark:text-white outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-550 dark:text-slate-400 uppercase mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg p-2 text-sm text-gray-900 dark:text-white outline-none">
            </div>

            {{-- Godam selection --}}
            <div>
                <label class="block text-xs font-bold text-gray-550 dark:text-slate-400 uppercase mb-1">Filter by Godam</label>
                <select name="godam_id" class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg p-2 text-sm text-gray-900 dark:text-white outline-none">
                    <option value="">— All Godams —</option>
                    @foreach($godams as $g)
                        <option value="{{ $g->id }}" @selected(request('godam_id') == $g->id)>{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Action buttons --}}
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition">
                    <i class="fas fa-filter mr-1"></i> Apply Filters
                </button>
                @if(request()->anyFilled(['start_date', 'end_date', 'godam_id']))
                    <a href="{{ route('stock-transfers.index') }}" class="w-full border border-gray-300 dark:border-slate-600 text-gray-700 dark:text-slate-350 hover:bg-gray-100 dark:hover:bg-slate-700 font-bold py-2 px-4 rounded-lg text-sm text-center transition">
                        Clear
                    </a>
                @endif
            </div>

        </form>
    </div>

    {{-- Transfers Log Table --}}
    @if($transfers->isEmpty())
        <div class="bg-white dark:bg-slate-800 rounded-xl p-12 text-center shadow-sm border border-gray-200 dark:border-slate-700">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-750 flex items-center justify-center text-slate-400 dark:text-slate-505 mx-auto text-2xl mb-4">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <h3 class="font-bold text-lg text-gray-900 dark:text-white">No Transfers Found</h3>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1 mb-6">No stock transfers have been logged matching the select criteria.</p>
        </div>
    @else
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-slate-700 border-b border-gray-200 dark:border-slate-600 text-gray-600 dark:text-slate-300 uppercase text-xs font-bold">
                            <th class="p-4 w-12 text-center">#</th>
                            <th class="p-4">Transfer Date</th>
                            <th class="p-4">Item Description</th>
                            <th class="p-4">From Location</th>
                            <th class="p-4">To Location</th>
                            <th class="p-4 text-center">Quantity</th>
                            <th class="p-4">Transferred By</th>
                            <th class="p-4">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        @foreach($transfers as $idx => $t)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-750/30 transition-colors duration-150 text-gray-700 dark:text-slate-350">
                                <td class="p-4 text-center font-mono text-gray-400">{{ $idx + 1 }}</td>
                                <td class="p-4 font-mono font-semibold text-gray-800 dark:text-white">
                                    {{ $t->transfer_date->format('d M Y') }}
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-gray-950 dark:text-white">{{ $t->item->description }}</div>
                                    <div class="text-xs text-gray-400 font-mono">Code: {{ $t->item->code }}</div>
                                </td>
                                <td class="p-4">
                                    @if($t->fromGodam)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800 dark:bg-purple-950/20 dark:text-purple-400">
                                            <i class="fas fa-warehouse text-[10px]"></i> {{ $t->fromGodam->name }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/20 dark:text-blue-400">
                                            <i class="fas fa-store text-[10px]"></i> Shop Floor
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if($t->toGodam)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800 dark:bg-purple-950/20 dark:text-purple-400">
                                            <i class="fas fa-warehouse text-[10px]"></i> {{ $t->toGodam->name }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/20 dark:text-blue-400">
                                            <i class="fas fa-store text-[10px]"></i> Shop Floor
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-center font-bold font-mono text-gray-900 dark:text-white">
                                    {{ (float)$t->quantity }}
                                </td>
                                <td class="p-4 font-semibold text-gray-900 dark:text-slate-300">
                                    {{ $t->user->name ?? 'System' }}
                                </td>
                                <td class="p-4 text-xs text-gray-550 dark:text-slate-400 italic">
                                    {{ $t->notes ?: '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
