@extends('layouts.admin')

@section('title', 'Low Stock Alerts')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Breadcrumb and Action --}}
    <div class="flex items-center justify-between mb-6 no-print">
        <div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white font-semibold transition text-sm">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">Low Stock Alerts</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Items that are out of stock or below their minimum threshold</p>
        </div>
        <button onclick="window.print()" class="bg-white text-black border border-gray-300 hover:bg-gray-100 font-bold px-4 py-2 rounded-lg flex items-center gap-2 shadow-sm transition">
            <i class="fas fa-print"></i> Print Alerts
        </button>
    </div>

    {{-- Print Header (Visible only when printing) --}}
    <div class="hidden print:block text-center mb-6">
        <h1 class="text-3xl font-bold">Mart</h1>
        <h2 class="text-xl font-semibold">Low Stock Alerts Report</h2>
        <p class="text-sm text-gray-500 mt-1">Printed on {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    {{-- Summary Bar --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 no-print">
        {{-- Out of Stock Count --}}
        <div class="bg-red-50 border border-red-200 dark:bg-red-950/20 dark:border-red-800 rounded-xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-600 flex items-center justify-center text-white shadow">
                <i class="fas fa-times-circle text-base"></i>
            </div>
            <div>
                <div class="text-xs font-bold text-red-600 dark:text-red-400 uppercase tracking-wide">Out of Stock</div>
                <div class="text-2xl font-extrabold text-red-700 dark:text-red-300 font-mono">{{ $outOfStockCount }}</div>
            </div>
        </div>

        {{-- Low Stock Count --}}
        <div class="bg-orange-50 border border-orange-200 dark:bg-orange-950/20 dark:border-orange-800 rounded-xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-orange-500 flex items-center justify-center text-white shadow">
                <i class="fas fa-exclamation-triangle text-base"></i>
            </div>
            <div>
                <div class="text-xs font-bold text-orange-600 dark:text-orange-400 uppercase tracking-wide">Low Stock</div>
                <div class="text-2xl font-extrabold text-orange-700 dark:text-orange-300 font-mono">{{ $lowStockOnlyCount }}</div>
            </div>
        </div>

        {{-- Configured Min Level --}}
        <div class="bg-indigo-50 border border-indigo-100 dark:bg-slate-800 dark:border-indigo-900 rounded-xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center text-white shadow">
                <i class="fas fa-sliders-h text-base"></i>
            </div>
            <div>
                <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wide">Min Level Configured</div>
                <div class="text-2xl font-extrabold text-indigo-700 dark:text-indigo-300 font-mono">{{ $totalConfiguredCount }}</div>
            </div>
        </div>
    </div>

    {{-- Content --}}
    @if($items->isEmpty())
        <div class="bg-emerald-50 border border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 rounded-xl p-6 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0 text-xl">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <h3 class="font-bold text-lg">✓ All items are sufficiently stocked</h3>
                <p class="text-sm opacity-90 mt-0.5">Every item is currently stocked above its minimum configured warning threshold.</p>
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-slate-700 border-b border-gray-200 dark:border-slate-600 text-gray-600 dark:text-slate-300 uppercase text-xs font-bold">
                            <th class="p-4 w-12 text-center">#</th>
                            <th class="p-4">Item Name</th>
                            <th class="p-4 text-center">Item Code</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4 text-center">Current Stock</th>
                            <th class="p-4 text-center">Min Level</th>
                            <th class="p-4 text-right">Shortage</th>
                            <th class="p-4 text-right">Shortage %</th>
                            <th class="p-4 text-center">Available in Godam</th>
                            <th class="p-4">Preferred Supplier</th>
                            <th class="p-4 text-center">Last Purchase</th>
                            <th class="p-4 text-center no-print">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                        @foreach($items as $idx => $item)
                            @php
                                $isOutOfStock   = $item->on_hand <= 0;
                                $minLevel       = (float)($item->min_stock_level ?? 0);
                                $shortage       = max(0, $minLevel - (float)$item->on_hand);
                                $shortagePercent = $minLevel > 0 ? round(($shortage / $minLevel) * 100, 1) : ($isOutOfStock ? 100 : 0);

                                if ($isOutOfStock) {
                                    $rowBg = 'bg-red-50 hover:bg-red-100 dark:bg-red-950/20 dark:hover:bg-red-950/30';
                                } else {
                                    // Low stock — use red vs yellow based on how critical
                                    $isHighShortage = $minLevel > 0 && $shortage > ($minLevel * 0.5);
                                    $rowBg = $isHighShortage
                                        ? 'bg-orange-50 hover:bg-orange-100 dark:bg-orange-950/20 dark:hover:bg-orange-950/30'
                                        : 'bg-yellow-50 hover:bg-yellow-100 dark:bg-yellow-950/20 dark:hover:bg-yellow-950/30';
                                }
                            @endphp
                            <tr class="{{ $rowBg }} transition-colors duration-150">
                                <td class="p-4 text-center font-mono font-medium text-gray-500">{{ $idx + 1 }}</td>
                                <td class="p-4">
                                    <div class="text-gray-900 dark:text-white font-bold">{{ $item->description ?? '—' }}</div>
                                    <div class="text-gray-500 dark:text-slate-400 font-mono text-xs mt-0.5">Code: {{ $item->code }}</div>
                                </td>
                                <td class="p-4 text-center font-mono text-xs text-gray-600 dark:text-slate-300">
                                    {{ $item->code ?? '—' }}
                                </td>
                                <td class="p-4 text-center">
                                    @if($isOutOfStock)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-red-600 text-white shadow-sm">
                                            <i class="fas fa-times-circle text-[10px]"></i> OUT OF STOCK
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-orange-500 text-white shadow-sm">
                                            <i class="fas fa-exclamation-triangle text-[10px]"></i> LOW STOCK
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-center font-bold font-mono {{ $isOutOfStock ? 'text-red-600 dark:text-red-400' : 'text-orange-600 dark:text-orange-400' }}">
                                    {{ (float)$item->on_hand }}
                                </td>
                                <td class="p-4 text-center font-bold font-mono text-gray-500 dark:text-slate-400">
                                    {{ $minLevel > 0 ? $minLevel : '—' }}
                                </td>
                                <td class="p-4 text-right font-bold font-mono text-red-600 dark:text-red-400">
                                    @if($minLevel > 0)
                                        -{{ (float)$shortage }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="p-4 text-right font-bold font-mono">
                                    @if($minLevel > 0)
                                        {{ $shortagePercent }}%
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="p-4 text-center font-bold font-mono">
                                    @php
                                        $godamQty = $item->totalWarehouseStock();
                                    @endphp
                                    @if($godamQty > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800 dark:bg-green-950/20 dark:text-green-400">
                                            {{ (float)$godamQty }}
                                        </span>
                                        <a href="{{ route('stock-transfers.create', ['item_id' => $item->id]) }}" 
                                           class="ml-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-2 py-1 rounded text-[10px] transition inline-flex items-center gap-0.5 shadow-sm no-print">
                                            <i class="fas fa-exchange-alt"></i> Transfer
                                        </a>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800 dark:bg-red-950/20 dark:text-red-400">
                                            None
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if($item->preferredSupplier)
                                        <a href="{{ route('suppliers.show', $item->preferredSupplier->id) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-bold">
                                            {{ $item->preferredSupplier->name }}
                                        </a>
                                        @if($item->preferredSupplier->phone)
                                            <div class="text-xs text-gray-500 dark:text-slate-400 font-mono mt-0.5"><i class="fas fa-phone-alt mr-1"></i>{{ $item->preferredSupplier->phone }}</div>
                                        @endif
                                    @else
                                        <span class="text-gray-400 dark:text-slate-500 italic">None Set</span>
                                    @endif
                                </td>
                                <td class="p-4 text-center font-mono text-xs text-gray-500 dark:text-slate-400">
                                    {{ $item->last_purchase_date ? \Carbon\Carbon::parse($item->last_purchase_date)->format('d M Y') : '—' }}
                                </td>
                                <td class="p-4 text-center no-print">
                                    <a href="{{ route('purchases.create', array_filter(['supplier_id' => $item->preferred_supplier_id, 'item_id' => $item->id])) }}" 
                                       class="bg-white text-black border border-gray-300 hover:bg-gray-100 font-bold px-3 py-1.5 rounded-lg text-xs transition duration-150 inline-flex items-center gap-1 shadow-sm">
                                        <i class="fas fa-shopping-cart"></i> Reorder
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

{{-- Print Optimization Styles --}}
<style>
    @media print {
        nav, .no-print, .print\:hidden {
            display: none !important;
        }
        body {
            background-color: white !important;
            color: black !important;
            font-size: 12px;
            padding: 0 !important;
            margin: 0 !important;
        }
        .max-w-7xl, .container {
            max-width: 100% !important;
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        table {
            border-collapse: collapse !important;
            width: 100% !important;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #475569 !important;
            padding: 6px 8px !important;
            text-align: left !important;
        }
        th {
            background-color: #f1f5f9 !important;
            color: black !important;
        }
        tr {
            background-color: transparent !important;
            color: black !important;
            page-break-inside: avoid !important;
        }
        /* Ensure table prints headers on multi-page */
        thead {
            display: table-header-group !important;
        }
    }
</style>
@endsection
