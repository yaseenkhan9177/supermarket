@extends('layouts.admin')

@section('title', 'Godam Setup')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Breadcrumb and Action --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-850 dark:text-slate-400 dark:hover:text-white font-semibold transition text-sm">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">Godam (Warehouse) Management</h1>
        </div>
        <a href="{{ route('godams.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2.5 rounded-lg flex items-center gap-2 shadow-md transition">
            <i class="fas fa-plus"></i> Add New Godam
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

    {{-- Warehouses Grid / Table --}}
    @if($godams->isEmpty())
        <div class="bg-white dark:bg-slate-800 rounded-xl p-12 text-center shadow-sm border border-gray-200 dark:border-slate-700">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-750 flex items-center justify-center text-slate-400 dark:text-slate-500 mx-auto text-2xl mb-4">
                <i class="fas fa-warehouse"></i>
            </div>
            <h3 class="font-bold text-lg text-gray-900 dark:text-white">No Godams Configured</h3>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1 mb-6">Create warehouse locations to track separate inventories (e.g. Main Godam, Cold Storage).</p>
            <a href="{{ route('godams.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-5 py-2.5 rounded-lg inline-flex items-center gap-2 shadow transition">
                <i class="fas fa-plus"></i> Add First Godam
            </a>
        </div>
    @else
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-slate-700 border-b border-gray-200 dark:border-slate-600 text-gray-600 dark:text-slate-300 uppercase text-xs font-bold">
                            <th class="p-4 w-12 text-center">#</th>
                            <th class="p-4">Godam Name</th>
                            <th class="p-4">Location</th>
                            <th class="p-4 text-center">Total Items</th>
                            <th class="p-4 text-center">Total Qty</th>
                            <th class="p-4 text-right">Total Stock Value</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        @foreach($godams as $idx => $g)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-750/30 transition-colors duration-150 text-gray-700 dark:text-slate-350">
                                <td class="p-4 text-center font-mono font-medium text-gray-400">{{ $idx + 1 }}</td>
                                <td class="p-4">
                                    <div class="font-bold text-gray-950 dark:text-white">{{ $g->name }}</div>
                                    @if($g->notes)
                                        <div class="text-xs text-gray-400 dark:text-slate-400 mt-0.5 truncate max-w-xs">{{ $g->notes }}</div>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <span class="font-medium">{{ $g->location ?: '—' }}</span>
                                </td>
                                <td class="p-4 text-center font-semibold font-mono">
                                    {{ $g->total_items }}
                                </td>
                                <td class="p-4 text-center font-semibold font-mono">
                                    {{ (float)$g->total_qty }}
                                </td>
                                <td class="p-4 text-right font-bold font-mono text-indigo-600 dark:text-indigo-400">
                                    Rs. {{ number_format($g->total_value, 2) }}
                                </td>
                                <td class="p-4 text-center">
                                    @if($g->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-950/20 dark:text-green-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800 dark:bg-slate-700 dark:text-slate-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('godams.inventory', $g->id) }}" 
                                           class="bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700 font-bold px-3 py-1.5 rounded-lg text-xs transition duration-150 inline-flex items-center gap-1">
                                            <i class="fas fa-boxes"></i> View Inventory
                                        </a>
                                        <a href="{{ route('godams.show', $g->id) }}" 
                                           class="bg-white text-blue-600 border border-blue-200 hover:bg-blue-50 dark:bg-slate-800 dark:text-blue-400 dark:border-blue-900/50 dark:hover:bg-blue-950/20 font-bold px-3 py-1.5 rounded-lg text-xs transition duration-150 inline-flex items-center gap-1">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form action="{{ route('godams.destroy', $g->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this warehouse?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="bg-white text-red-650 border border-red-200 hover:bg-red-50 dark:bg-slate-800 dark:text-red-400 dark:border-red-950/50 dark:hover:bg-red-950/20 font-bold px-3 py-1.5 rounded-lg text-xs transition duration-150 inline-flex items-center gap-1">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                        </form>
                                    </div>
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
