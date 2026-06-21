@extends('layouts.admin')

@section('title', $godam->name . ' Inventory')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Breadcrumb and Summary Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('godams.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-850 dark:text-slate-400 dark:hover:text-white font-semibold transition text-sm">
                <i class="fas fa-arrow-left"></i> Back to Godams
            </a>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ $godam->name }} Inventory</h1>
            @if($godam->location)
                <p class="text-xs text-gray-500 dark:text-slate-400 font-medium mt-0.5"><i class="fas fa-map-marker-alt mr-1 text-slate-400"></i>{{ $godam->location }}</p>
            @endif
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('stock-transfers.create', ['from_godam_id' => $godam->id]) }}" 
               class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2.5 rounded-lg flex items-center gap-2 shadow-md transition">
                <i class="fas fa-exchange-alt"></i> Transfer Stock Out
            </a>
        </div>
    </div>

    {{-- Stats Bar --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl shadow-sm border border-gray-250 dark:border-slate-700 flex justify-between items-center">
            <div>
                <p class="text-gray-500 dark:text-slate-400 text-xs font-bold uppercase">Distinct Products</p>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-0.5">{{ $stocks->count() }}</h3>
            </div>
            <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-slate-700 flex items-center justify-center text-indigo-650 dark:text-indigo-400 text-lg">
                <i class="fas fa-boxes"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl shadow-sm border border-gray-250 dark:border-slate-700 flex justify-between items-center">
            <div>
                <p class="text-gray-500 dark:text-slate-400 text-xs font-bold uppercase">Total Quantity</p>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-0.5">{{ (float)$stocks->sum('quantity') }}</h3>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-slate-700 flex items-center justify-center text-emerald-650 dark:text-emerald-400 text-lg">
                <i class="fas fa-cubes"></i>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-5 rounded-xl shadow-sm border border-gray-250 dark:border-slate-700 flex justify-between items-center">
            <div>
                <p class="text-gray-500 dark:text-slate-400 text-xs font-bold uppercase">Total Stock Value</p>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-0.5">Rs. {{ number_format($totalValue, 2) }}</h3>
            </div>
            <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-slate-700 flex items-center justify-center text-purple-650 dark:text-purple-400 text-lg">
                <i class="fas fa-dollar-sign"></i>
            </div>
        </div>
    </div>

    {{-- Search and Table card --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
        
        {{-- Search filter header --}}
        <div class="p-4 bg-slate-50 dark:bg-slate-750/30 border-b border-gray-200 dark:border-slate-700 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h2 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-clipboard-list text-slate-400"></i> Stock Records
            </h2>
            <form action="{{ route('godams.inventory', $godam->id) }}" method="GET" class="flex items-center gap-2 w-full md:w-auto">
                <div class="relative w-full md:w-64">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                        <i class="fas fa-search text-xs"></i>
                    </span>
                    <input type="text" 
                           name="search" 
                           id="inventorySearch"
                           placeholder="Search by name or code..." 
                           value="{{ request('search') }}"
                           class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg text-sm text-gray-950 dark:text-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>
                <button type="submit" class="bg-gray-800 hover:bg-black dark:bg-slate-700 dark:hover:bg-slate-600 text-white font-bold px-4 py-2 rounded-lg text-sm transition">
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('godams.inventory', $godam->id) }}" class="text-xs text-gray-500 hover:text-indigo-650 dark:text-slate-400 dark:hover:text-white font-semibold whitespace-nowrap pl-1">
                        Clear Filters
                    </a>
                @endif
            </form>
        </div>

        @if($stocks->isEmpty())
            <div class="p-12 text-center text-gray-400">
                <i class="fas fa-box-open text-4xl mb-4"></i>
                <p class="font-semibold text-lg">No stock records found.</p>
                <p class="text-sm text-gray-500 dark:text-slate-450 mt-1">This warehouse does not currently hold any available inventory.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm" id="inventoryTable">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-slate-700/50 border-b border-gray-200 dark:border-slate-650 text-gray-600 dark:text-slate-300 uppercase text-xs font-bold">
                            <th class="p-4 w-12 text-center">#</th>
                            <th class="p-4">Item Name</th>
                            <th class="p-4">Category (Dept)</th>
                            <th class="p-4 text-center">Current Qty</th>
                            <th class="p-4 text-right">Unit Cost</th>
                            <th class="p-4 text-right">Total Value</th>
                            <th class="p-4 text-center">Last Received Date</th>
                            <th class="p-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        @foreach($stocks as $idx => $s)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-750/30 transition-colors duration-150 text-gray-700 dark:text-slate-350 inventory-row">
                                <td class="p-4 text-center font-mono font-medium text-gray-400">{{ $idx + 1 }}</td>
                                <td class="p-4">
                                    <div class="font-bold text-gray-950 dark:text-white item-name">{{ $s->item->description }}</div>
                                    <div class="text-xs text-gray-400 dark:text-slate-400 font-mono item-code">Code: {{ $s->item->code }}</div>
                                </td>
                                <td class="p-4 font-semibold">
                                    {{ $s->item->department->name ?? 'Uncategorized' }}
                                </td>
                                <td class="p-4 text-center font-bold font-mono text-gray-900 dark:text-white bg-slate-50/50 dark:bg-slate-900/10">
                                    {{ (float)$s->quantity }}
                                </td>
                                <td class="p-4 text-right font-mono font-semibold">
                                    Rs. {{ number_format($s->item->cost_rate ?? 0, 2) }}
                                </td>
                                <td class="p-4 text-right font-bold font-mono text-indigo-600 dark:text-indigo-400">
                                    Rs. {{ number_format($s->quantity * ($s->item->cost_rate ?? 0), 2) }}
                                </td>
                                <td class="p-4 text-center font-mono text-xs text-gray-500 dark:text-slate-400">
                                    {{ $s->last_received_at ? $s->last_received_at->format('d M Y, h:i A') : '—' }}
                                </td>
                                <td class="p-4 text-center">
                                    <a href="{{ route('stock-transfers.create', ['item_id' => $s->item_id, 'from_godam_id' => $godam->id]) }}" 
                                       class="bg-indigo-50 text-indigo-650 hover:bg-indigo-600 hover:text-white dark:bg-indigo-950/20 dark:text-indigo-400 dark:hover:bg-indigo-600 dark:hover:text-white font-bold px-3 py-1.5 rounded-lg text-xs transition duration-150 inline-flex items-center gap-1 shadow-sm">
                                        <i class="fas fa-exchange-alt"></i> Transfer
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Summary card footer --}}
            <div class="p-4 bg-slate-50 dark:bg-slate-750/30 border-t border-gray-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <span class="text-xs text-gray-500 dark:text-slate-400 uppercase font-bold tracking-wide">Summary (Filtered Listings)</span>
                <div class="text-right">
                    <span class="text-xs font-bold text-gray-400 dark:text-slate-400 uppercase">Warehouse Value</span>
                    <span class="block text-xl font-extrabold text-indigo-650 dark:text-indigo-400">Rs. {{ number_format($totalValue, 2) }}</span>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    // Live JS text filtering for even faster desktop updates
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('inventorySearch');
        if (searchInput) {
            searchInput.addEventListener('input', function (e) {
                const query = e.target.value.toLowerCase().trim();
                const rows = document.querySelectorAll('.inventory-row');
                
                rows.forEach(row => {
                    const name = row.querySelector('.item-name').textContent.toLowerCase();
                    const code = row.querySelector('.item-code').textContent.toLowerCase();
                    
                    if (name.includes(query) || code.includes(query)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
@endsection
