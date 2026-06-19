@extends('layouts.app')

@section('title', 'Stock Adjustment Details')

@section('content')
<div class="min-h-screen p-6 bg-slate-100 dark:bg-slate-950">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Stock Adjustment Details</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1">{{ $adjustment->adjustment_number }}</p>
            </div>
            <a href="{{ route('adjustments.index') }}" class="px-6 py-3 bg-slate-600 hover:bg-slate-700 text-white font-bold rounded-xl shadow-lg transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to List
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Panel - Adjustment Info -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Adjustment Details Card -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-2 mb-6 border-b border-slate-200 dark:border-slate-800 pb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Adjustment Info</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Adjustment #</label>
                            <p class="text-lg font-mono font-bold text-blue-600 dark:text-blue-400">{{ $adjustment->adjustment_number }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Date</label>
                                <p class="text-slate-900 dark:text-white font-medium">{{ \Carbon\Carbon::parse($adjustment->date)->format('d M Y') }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Type</label>
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold
                                    @if($adjustment->type == 'correction') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                                    @elseif($adjustment->type == 'damage') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
                                    @elseif($adjustment->type == 'loss') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300
                                    @elseif($adjustment->type == 'transfer') bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300
                                    @else bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $adjustment->type)) }}
                                </span>
                            </div>
                        </div>

                        @if($adjustment->reference)
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Reference</label>
                            <p class="text-slate-700 dark:text-slate-300">{{ $adjustment->reference }}</p>
                        </div>
                        @endif

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Created By</label>
                            <p class="text-slate-900 dark:text-white font-medium">{{ $adjustment->user->name ?? 'System' }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Created At</label>
                            <p class="text-slate-700 dark:text-slate-300">{{ $adjustment->created_at->format('d M Y, h:i A') }}</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Total Items</label>
                            <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $adjustment->items->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Items Table -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-2 mb-6 border-b border-slate-200 dark:border-slate-800 pb-4">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Adjusted Items</h3>
                </div>

                <!-- Items Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-bold text-slate-500 uppercase bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800">
                                <th class="p-3 w-10 text-center">#</th>
                                <th class="p-3">Product</th>
                                <th class="p-3 w-32 text-center">System Qty</th>
                                <th class="p-3 w-32 text-center bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400">Physical Qty</th>
                                <th class="p-3 w-24 text-center">Difference</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($adjustment->items as $index => $item)
                            <tr class="border-b border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                <td class="p-3 text-center text-slate-400">{{ $index + 1 }}</td>
                                <td class="p-3">
                                    <div>
                                        <p class="font-medium text-slate-900 dark:text-white">{{ $item->product->name ?? 'N/A' }}</p>
                                        @if($item->product->barcode)
                                        <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $item->product->barcode }}</p>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono text-sm">
                                        {{ $item->system_quantity }}
                                    </span>
                                </td>
                                <td class="p-3 text-center bg-blue-50 dark:bg-blue-900/10">
                                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-mono text-sm font-bold">
                                        {{ $item->physical_quantity }}
                                    </span>
                                </td>
                                <td class="p-3 text-center">
                                    <span class="inline-flex items-center justify-center px-3 py-2 rounded-lg font-bold text-sm
                                        @if($item->difference < 0) bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                        @elseif($item->difference > 0) bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
                                        @else bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400
                                        @endif">
                                        {{ $item->difference > 0 ? '+' . $item->difference : $item->difference }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Summary Stats -->
                <div class="mt-6 p-4 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase mb-1">Total Items</p>
                            <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $adjustment->items->count() }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase mb-1">Total Increase</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                +{{ $adjustment->items->where('difference', '>', 0)->sum('difference') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-500 uppercase mb-1">Total Decrease</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                                {{ $adjustment->items->where('difference', '<', 0)->sum('difference') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection