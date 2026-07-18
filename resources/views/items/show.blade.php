@extends('layouts.admin')

@section('title', 'Item Details: ' . $item->description)

@section('content')
<div class="max-w-7xl mx-auto pb-12">
    
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('items.index') }}" class="text-slate-400 hover:text-slate-200 transition-colors">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <h1 class="text-3xl font-bold text-slate-800 dark:text-white tracking-tight">{{ $item->description }}</h1>
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $item->item_type === 'Service' ? 'bg-purple-500/20 text-purple-400 border border-purple-500/30' : 'bg-blue-500/20 text-blue-400 border border-blue-500/30' }}">
                    {{ $item->item_type }}
                </span>
            </div>
            <p class="text-slate-500 dark:text-slate-400 flex items-center gap-4 text-sm">
                <span><i class="fas fa-barcode mr-1"></i> {{ $item->code }}</span>
                <span><i class="fas fa-folder mr-1"></i> {{ $item->department->name ?? 'Uncategorized' }}</span>
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('items.edit', $item->id) }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-semibold shadow-lg shadow-indigo-500/20 transition-all flex items-center gap-2">
                <i class="fas fa-edit"></i> Edit Item
            </a>
        </div>
    </div>

    {{-- KPIs Row --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        {{-- Current Average Cost --}}
        <div class="bg-white dark:bg-slate-800/80 rounded-2xl shadow-xl shadow-black/5 border border-slate-100 dark:border-slate-700/50 p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-semibold uppercase tracking-wider">Avg Cost (Active)</h3>
                <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-700/50 flex items-center justify-center text-slate-500 dark:text-slate-300">
                    <i class="fas fa-calculator"></i>
                </div>
            </div>
            <div>
                <div class="text-3xl font-bold text-slate-800 dark:text-white">
                    Rs. {{ number_format($averageCost, 2) }}
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                    Fallback Cost: Rs. {{ number_format($item->cost_rate, 2) }}
                </div>
            </div>
        </div>

        {{-- Total Qty Sold --}}
        <div class="bg-white dark:bg-slate-800/80 rounded-2xl shadow-xl shadow-black/5 border border-slate-100 dark:border-slate-700/50 p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-semibold uppercase tracking-wider">Total Sold</h3>
                <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-400">
                    <i class="fas fa-box-open"></i>
                </div>
            </div>
            <div>
                <div class="text-3xl font-bold text-slate-800 dark:text-white">
                    {{ number_format($totalQtySold, 2) }}
                </div>
            </div>
        </div>

        {{-- Total Revenue --}}
        <div class="bg-white dark:bg-slate-800/80 rounded-2xl shadow-xl shadow-black/5 border border-slate-100 dark:border-slate-700/50 p-6 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-slate-500 dark:text-slate-400 text-sm font-semibold uppercase tracking-wider">Total Revenue</h3>
                <div class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
            <div>
                <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">
                    Rs. {{ number_format($totalRevenue, 2) }}
                </div>
            </div>
        </div>

        {{-- Total Profit --}}
        <div class="bg-white dark:bg-slate-800/80 rounded-2xl shadow-xl shadow-black/5 border border-slate-100 dark:border-slate-700/50 p-6 flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 to-purple-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative z-10 flex items-center justify-between mb-4">
                <h3 class="text-slate-500 dark:text-slate-400 group-hover:text-indigo-100 text-sm font-semibold uppercase tracking-wider transition-colors">Total Profit</h3>
                <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-500/10 group-hover:bg-white/20 flex items-center justify-center text-indigo-600 dark:text-indigo-300 group-hover:text-white transition-colors">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <div class="relative z-10">
                <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 group-hover:text-white transition-colors">
                    Rs. {{ number_format($totalProfit, 2) }}
                </div>
            </div>
        </div>

    </div>

    {{-- Tabs Layout for History --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        
        {{-- Purchase History / Batches --}}
        <div class="bg-white dark:bg-slate-800/80 rounded-2xl shadow-xl shadow-black/5 border border-slate-100 dark:border-slate-700/50 overflow-hidden flex flex-col h-[600px]">
            <div class="p-6 border-b border-slate-100 dark:border-slate-700/50 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-truck-loading text-indigo-500"></i> Purchase History & Batches
                </h2>
                <span class="text-sm text-slate-500 dark:text-slate-400">{{ $batches->count() }} Batches</span>
            </div>
            
            <div class="overflow-y-auto flex-1 custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 dark:bg-slate-800/80 sticky top-0 z-10">
                        <tr>
                            <th class="py-3 px-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">Date</th>
                            <th class="py-3 px-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">Batch / Supplier</th>
                            <th class="py-3 px-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">Cost/Sale</th>
                            <th class="py-3 px-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700 text-right">Remaining</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        @forelse($batches as $batch)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors {{ $batch->quantity_available <= 0 ? 'opacity-60' : '' }}">
                                <td class="py-3 px-4 text-sm">
                                    <div class="font-medium text-slate-800 dark:text-slate-200">{{ $batch->received_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-slate-500">{{ $batch->received_at->format('h:i A') }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="text-sm font-mono font-bold text-slate-700 dark:text-slate-300">{{ $batch->batch_no }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[150px]" title="{{ $batch->supplier_name ?? 'N/A' }}">
                                        {{ $batch->supplier_name ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-sm">
                                    <div class="font-bold text-slate-700 dark:text-slate-300">C: {{ number_format($batch->cost_price, 2) }}</div>
                                    <div class="text-xs text-emerald-600 dark:text-emerald-400">S: {{ number_format($batch->sale_price, 2) }}</div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $batch->quantity_available > 0 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400' : 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-400' }}">
                                        {{ number_format($batch->quantity_available, 2) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 px-4 text-center text-slate-500 dark:text-slate-400">
                                    No purchase history available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sales History --}}
        <div class="bg-white dark:bg-slate-800/80 rounded-2xl shadow-xl shadow-black/5 border border-slate-100 dark:border-slate-700/50 overflow-hidden flex flex-col h-[600px]">
            <div class="p-6 border-b border-slate-100 dark:border-slate-700/50 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/50">
                <h2 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-shopping-cart text-emerald-500"></i> Sales History
                </h2>
                <span class="text-sm text-slate-500 dark:text-slate-400">{{ $salesHistory->count() }} Sales</span>
            </div>
            
            <div class="overflow-y-auto flex-1 custom-scrollbar">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 dark:bg-slate-800/80 sticky top-0 z-10">
                        <tr>
                            <th class="py-3 px-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">Date / Inv</th>
                            <th class="py-3 px-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">Batch Drawn</th>
                            <th class="py-3 px-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">Qty / Rate</th>
                            <th class="py-3 px-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700 text-right">Profit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        @forelse($salesHistory as $sale)
                            @php
                                $costUsed = $sale->batch ? $sale->batch->cost_price : $item->cost_rate;
                                $lineProfit = $sale->total - ($sale->qty * $costUsed);
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="py-3 px-4 text-sm">
                                    <div class="font-bold text-slate-800 dark:text-slate-200">
                                        <a href="{{ route('sales.print', $sale->sale_id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                            {{ $sale->invoice_no }}
                                        </a>
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ \Carbon\Carbon::parse($sale->sale_date)->format('M d, Y') }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    @if($sale->batch_id)
                                        <span class="text-xs font-mono bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 px-2 py-1 rounded">
                                            {{ $sale->batch->batch_no ?? 'Unknown Batch' }}
                                        </span>
                                    @else
                                        <span class="text-xs font-medium text-amber-600 dark:text-amber-500 italic">
                                            Legacy Sale
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-sm">
                                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ number_format($sale->qty, 2) }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">@ {{ number_format($sale->rate, 2) }}</div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="font-bold {{ $lineProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">
                                        {{ $lineProfit >= 0 ? '+' : '' }}{{ number_format($lineProfit, 2) }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 px-4 text-center text-slate-500 dark:text-slate-400">
                                    No sales history available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<style>
    /* Custom scrollbar for history tables */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 20px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #475569;
    }
</style>
@endsection
