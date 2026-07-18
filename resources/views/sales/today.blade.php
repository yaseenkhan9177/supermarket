@extends('layouts.admin')

@section('title', "Today's Sales — " . now()->format('d M Y'))

@section('content')
<div class="max-w-5xl mx-auto pb-12">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="/dashboard" class="text-slate-400 hover:text-slate-200 transition-colors text-sm flex items-center gap-1.5">
                    <i class="fas fa-arrow-left text-xs"></i> Dashboard
                </a>
            </div>
            <h1 class="text-3xl font-bold text-slate-800 dark:text-white tracking-tight">Today's Sales</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                <i class="fas fa-calendar-day mr-1"></i>
                {{ now()->format('l, d F Y') }}
            </p>
        </div>
        <a href="{{ route('sales.history') }}"
           class="self-start sm:self-center px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-semibold flex items-center gap-2 transition-colors">
            <i class="fas fa-history"></i> All Sales History
        </a>
    </div>

    {{-- KPI Summary Bar --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">

        {{-- Total Transactions --}}
        <div class="bg-white dark:bg-slate-800/80 rounded-2xl shadow-lg shadow-black/5 border border-slate-100 dark:border-slate-700/50 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-500/15 flex items-center justify-center text-green-600 dark:text-green-400 shrink-0">
                <i class="fas fa-receipt text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">Transactions</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ $todaySales->count() }}</p>
            </div>
        </div>

        {{-- Total Revenue --}}
        <div class="bg-white dark:bg-slate-800/80 rounded-2xl shadow-lg shadow-black/5 border border-slate-100 dark:border-slate-700/50 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-500/15 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                <i class="fas fa-money-bill-wave text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">Total Revenue</p>
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($totalRevenue) }}</p>
            </div>
        </div>

        {{-- Cash vs Debit --}}
        @php
            $cashTotal = $todaySales->where('payment_mode', 'Cash')->sum('grand_total');
            $debitTotal = $todaySales->where('payment_mode', 'Debit')->sum('grand_total');
        @endphp
        <div class="bg-white dark:bg-slate-800/80 rounded-2xl shadow-lg shadow-black/5 border border-slate-100 dark:border-slate-700/50 p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-500/15 flex items-center justify-center text-blue-600 dark:text-blue-400 shrink-0">
                <i class="fas fa-exchange-alt text-xl"></i>
            </div>
            <div class="min-w-0">
                <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold uppercase tracking-wider">Cash / Debit</p>
                <p class="text-lg font-bold text-slate-800 dark:text-white">
                    <span class="text-green-600 dark:text-green-400">Rs. {{ number_format($cashTotal) }}</span>
                    <span class="text-slate-300 dark:text-slate-600 mx-1">/</span>
                    <span class="text-blue-600 dark:text-blue-400">Rs. {{ number_format($debitTotal) }}</span>
                </p>
            </div>
        </div>

    </div>

    {{-- Sales Table --}}
    <div class="bg-white dark:bg-slate-800/80 rounded-2xl shadow-xl shadow-black/5 border border-slate-100 dark:border-slate-700/50 overflow-hidden">

        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/50 bg-slate-50/60 dark:bg-slate-800/60 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-list text-slate-400"></i> Sales Records
            </h2>
            <span class="text-xs text-slate-500 dark:text-slate-400">
                Click any row to view its receipt
            </span>
        </div>

        @if($todaySales->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 text-slate-400 dark:text-slate-500">
                <i class="fas fa-receipt text-5xl mb-4 opacity-25"></i>
                <p class="text-lg font-semibold">No sales recorded today yet.</p>
                <p class="text-sm mt-1">Sales will appear here as they are completed.</p>
                <a href="{{ route('sales.pos') }}" class="mt-6 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-semibold transition-colors flex items-center gap-2">
                    <i class="fas fa-cash-register"></i> Open POS
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/80">
                            <th class="py-3 px-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">Invoice</th>
                            <th class="py-3 px-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">Payment</th>
                            <th class="py-3 px-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">Customer</th>
                            <th class="py-3 px-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700">Time</th>
                            <th class="py-3 px-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700 text-center">Items</th>
                            <th class="py-3 px-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-700 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        @foreach($todaySales as $sale)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors cursor-pointer group"
                            onclick="window.open('{{ route('sales.print', $sale->id) }}', '_blank')">

                            {{-- Invoice No --}}
                            <td class="py-3.5 px-5">
                                <span class="font-mono font-bold text-sm text-slate-800 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                    {{ $sale->invoice_no ?? '#' . $sale->id }}
                                </span>
                            </td>

                            {{-- Payment Badge --}}
                            <td class="py-3.5 px-5">
                                @if($sale->payment_mode)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide
                                    {{ $sale->payment_mode === 'Cash'
                                        ? 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400'
                                        : 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-400' }}">
                                    <i class="fas {{ $sale->payment_mode === 'Cash' ? 'fa-money-bill-wave' : 'fa-credit-card' }} mr-1 text-[10px]"></i>
                                    {{ $sale->payment_mode }}
                                </span>
                                @else
                                <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Customer --}}
                            <td class="py-3.5 px-5">
                                <span class="text-sm text-slate-700 dark:text-slate-300">
                                    <i class="fas fa-user text-slate-300 dark:text-slate-600 mr-1 text-xs"></i>
                                    {{ $sale->customer->name ?? $sale->customer_name ?? 'Walk-in Customer' }}
                                </span>
                            </td>

                            {{-- Time --}}
                            <td class="py-3.5 px-5">
                                <span class="text-sm text-slate-600 dark:text-slate-400">
                                    {{ \Carbon\Carbon::parse($sale->sale_date)->format('g:i A') }}
                                </span>
                            </td>

                            {{-- Items count --}}
                            <td class="py-3.5 px-5 text-center">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300">
                                    {{ $sale->items_count }}
                                </span>
                            </td>

                            {{-- Grand Total --}}
                            <td class="py-3.5 px-5 text-right">
                                <span class="font-bold text-sm text-slate-800 dark:text-white">
                                    Rs. {{ number_format($sale->grand_total) }}
                                </span>
                                <i class="fas fa-external-link-alt ml-2 text-slate-300 dark:text-slate-600 group-hover:text-indigo-500 text-xs transition-colors"></i>
                            </td>

                        </tr>
                        @endforeach
                    </tbody>

                    {{-- Footer totals row --}}
                    <tfoot>
                        <tr class="bg-slate-50 dark:bg-slate-800/80 border-t-2 border-slate-200 dark:border-slate-700">
                            <td colspan="4" class="py-3 px-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">
                                {{ $todaySales->count() }} Transaction{{ $todaySales->count() === 1 ? '' : 's' }}
                            </td>
                            <td class="py-3 px-5 text-center">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-300">
                                    {{ $todaySales->sum('items_count') }}
                                </span>
                            </td>
                            <td class="py-3 px-5 text-right">
                                <span class="font-extrabold text-emerald-600 dark:text-emerald-400">
                                    Rs. {{ number_format($totalRevenue) }}
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection
