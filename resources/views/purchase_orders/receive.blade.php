@extends('layouts.admin')

@section('title', "Receive Stock — PO {$po->po_number}")

@section('content')
<div class="max-w-5xl mx-auto pb-16" x-data="receiveForm()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-200 flex items-center gap-1">
                    <i class="fas fa-home text-xs"></i> Dashboard
                </a>
                <span>/</span>
                <a href="{{ route('purchase-orders.show', $po->id) }}" class="hover:text-slate-200 flex items-center gap-1">
                    PO Details
                </a>
            </div>
            <h1 class="text-3xl font-bold text-slate-800 dark:text-white">Receive Stock — {{ $po->po_number }}</h1>
            <p class="text-slate-500 text-sm mt-1">
                Supplier: <strong>{{ $po->supplier->name }}</strong> | Unallocated Expenses Pool: <strong class="text-amber-600">Rs. {{ number_format($unallocatedExpenses, 2) }}</strong>
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-xl flex items-center gap-1.5 transition-transform hover:scale-105">
            <i class="fas fa-home"></i> Dashboard
        </a>
    </div>

    <form method="POST" action="{{ route('purchase-orders.receive.process', $po->id) }}" class="space-y-6">
        @csrf

        {{-- Line Items Receiving Table Card --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-6 border border-slate-200 dark:border-slate-700/60 shadow-sm">
            <h3 class="font-bold text-slate-800 dark:text-white text-base mb-4">Stock Receiving & Sale Price Setting</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-400 uppercase">
                            <th class="py-3 px-3">Item / Product</th>
                            <th class="py-3 px-3 text-center w-28">Pending</th>
                            <th class="py-3 px-3 w-32">Receiving Qty</th>
                            <th class="py-3 px-3 w-32 text-right">Supplier Unit Cost</th>
                            <th class="py-3 px-3 w-36 text-right">Landed Unit Cost</th>
                            <th class="py-3 px-3 w-36 text-right">Batch Sale Price <span class="text-red-500">*</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-xs">
                        @foreach($po->items as $poItem)
                        @if($poItem->pending_quantity > 0)
                        <tr>
                            <td class="py-3.5 px-3 font-bold text-slate-800 dark:text-white">
                                {{ $poItem->item->description ?? $poItem->item->name }}
                                <div class="text-[10px] font-normal text-slate-400">Current Cost: Rs.{{ $poItem->item->cost_rate }} | Sale Rate: Rs.{{ $poItem->item->sale_rate }}</div>
                            </td>
                            <td class="py-3.5 px-3 text-center font-bold text-amber-600">
                                {{ number_format($poItem->pending_quantity, 2) }}
                            </td>
                            <td class="py-3.5 px-3">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    max="{{ $poItem->pending_quantity }}"
                                    name="items[{{ $poItem->id }}][qty_received]"
                                    value="{{ $poItem->pending_quantity }}"
                                    class="w-full text-xs font-bold p-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-white"
                                >
                            </td>
                            <td class="py-3.5 px-3 text-right font-semibold text-slate-700 dark:text-slate-300">
                                Rs. {{ number_format($poItem->unit_cost, 2) }}
                            </td>
                            <td class="py-3.5 px-3 text-right font-black text-indigo-600 dark:text-indigo-400">
                                @php
                                    $share = ($po->subtotal > 0) ? ($poItem->line_total / $po->subtotal) * $unallocatedExpenses : 0;
                                    $landedUnit = $poItem->unit_cost + ($poItem->pending_quantity > 0 ? ($share / $poItem->pending_quantity) : 0);
                                @endphp
                                Rs. {{ number_format($landedUnit, 2) }}
                            </td>
                            <td class="py-3.5 px-3 text-right">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="items[{{ $poItem->id }}][sale_price]"
                                    value="{{ $poItem->item->sale_rate }}"
                                    required
                                    class="w-full text-xs font-bold p-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-emerald-600 dark:text-emerald-400 text-right"
                                >
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Receiving Memo / Note</label>
                <input type="text" name="note" placeholder="Driver name, delivery note #..." class="w-full text-xs p-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-white">
            </div>
        </div>

        {{-- Action buttons --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('purchase-orders.show', $po->id) }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-200 rounded-xl text-sm font-semibold">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-emerald-500/20">
                <i class="fas fa-check-circle mr-1"></i> Confirm & Create Inventory Batches
            </button>
        </div>
    </form>
</div>
@endsection
