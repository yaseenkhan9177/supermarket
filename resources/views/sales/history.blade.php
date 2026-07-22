@extends('layouts.app')

@section('title', 'Sales History')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white">Transaction History</h1>
            <p class="text-slate-500 text-sm">Monitor your Cash and Debit sales performance.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('dashboard') }}" class="bg-gray-600 hover:bg-gray-500 text-white px-4 py-2 rounded-lg font-bold shadow flex items-center gap-2 text-sm">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="{{ route('cash-sales.create') }}" class="bg-green-600 hover:bg-green-500 text-white px-4 py-2 rounded-lg font-bold shadow flex items-center gap-2 text-sm">
                <i class="fas fa-money-bill-wave"></i> Cash Sale
            </a>
            <a href="{{ route('debit-sales.create') }}" class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-lg font-bold shadow flex items-center gap-2 text-sm">
                <i class="fas fa-hand-holding-usd"></i> Debit Sale
            </a>
        </div>
    </div>

    {{-- Date Range Filter --}}
    @include('partials.date_range_picker')

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <a href="{{ route('sales.history', ['type' => 'all']) }}"
            class="p-6 rounded-2xl border transition transform hover:scale-105 shadow-xl flex flex-col justify-between
           {{ request('type') == 'all' || !request('type') ? 'bg-blue-600 border-blue-500 text-white' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800' }}">

            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold uppercase {{ request('type') == 'all' || !request('type') ? 'text-blue-200' : 'text-slate-500' }}">Total Invoices</p>
                    <h3 class="text-3xl font-extrabold mt-1 {{ request('type') == 'all' || !request('type') ? 'text-white' : 'text-slate-800 dark:text-white' }}">
                        {{ $stats['all_count'] }}
                    </h3>
                </div>
                <div class="p-3 rounded-xl {{ request('type') == 'all' || !request('type') ? 'bg-white/20' : 'bg-blue-50 text-blue-600' }}">
                    <i class="fas fa-chart-pie text-xl"></i>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t {{ request('type') == 'all' || !request('type') ? 'border-white/20' : 'border-slate-100 dark:border-slate-800' }}">
                <span class="text-sm font-bold">Total: Rs. {{ number_format($stats['all_total']) }}</span>
            </div>
        </a>

        <a href="{{ route('sales.history', ['type' => 'Cash']) }}"
            class="p-6 rounded-2xl border transition transform hover:scale-105 shadow-xl flex flex-col justify-between
           {{ request('type') == 'Cash' ? 'bg-green-600 border-green-500 text-white' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800' }}">

            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold uppercase {{ request('type') == 'Cash' ? 'text-green-200' : 'text-slate-500' }}">Cash Invoices</p>
                    <h3 class="text-3xl font-extrabold mt-1 {{ request('type') == 'Cash' ? 'text-white' : 'text-slate-800 dark:text-white' }}">
                        {{ $stats['cash_count'] }}
                    </h3>
                </div>
                <div class="p-3 rounded-xl {{ request('type') == 'Cash' ? 'bg-white/20' : 'bg-green-50 text-green-600' }}">
                    <i class="fas fa-coins text-xl"></i>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t {{ request('type') == 'Cash' ? 'border-white/20' : 'border-slate-100 dark:border-slate-800' }}">
                <span class="text-sm font-bold">Total: Rs. {{ number_format($stats['cash_total']) }}</span>
            </div>
        </a>

        <a href="{{ route('sales.history', ['type' => 'Debit']) }}"
            class="p-6 rounded-2xl border transition transform hover:scale-105 shadow-xl flex flex-col justify-between
           {{ request('type') == 'Debit' ? 'bg-red-600 border-red-500 text-white' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800' }}">

            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold uppercase {{ request('type') == 'Debit' ? 'text-red-200' : 'text-slate-500' }}">Debit Invoices</p>
                    <h3 class="text-3xl font-extrabold mt-1 {{ request('type') == 'Debit' ? 'text-white' : 'text-slate-800 dark:text-white' }}">
                        {{ $stats['debit_count'] }}
                    </h3>
                </div>
                <div class="p-3 rounded-xl {{ request('type') == 'Debit' ? 'bg-white/20' : 'bg-red-50 text-red-600' }}">
                    <i class="fas fa-file-invoice-dollar text-xl"></i>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t {{ request('type') == 'Debit' ? 'border-white/20' : 'border-slate-100 dark:border-slate-800' }}">
                <span class="text-sm font-bold">Due: Rs. {{ number_format($stats['debit_total']) }}</span>
            </div>
        </a>

    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden">

        <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 flex justify-between items-center">
            <h3 class="font-bold text-slate-700 dark:text-white">
                @if(request('type') == 'Cash') Cash Invoices
                @elseif(request('type') == 'Debit') Debit Invoices
                @else All Invoices
                @endif
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 dark:bg-slate-950 text-slate-500 uppercase font-bold text-xs">
                    <tr>
                        <th class="px-6 py-4">Invoice #</th>
                        <th class="px-6 py-4">Date</th>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Cashier</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4 text-right">Amount</th>
                        <th class="px-6 py-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition group">
                        <td class="px-6 py-4 font-mono font-bold text-slate-700 dark:text-slate-300">
                            {{ $sale->invoice_no }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                            {{ $sale->created_at->format('d M, Y') }} <span class="text-xs opacity-50">{{ $sale->created_at->format('h:i A') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($sale->customer)
                            <span class="font-bold text-slate-800 dark:text-white">{{ $sale->customer->name }}</span>
                            @else
                            <span class="text-slate-400 italic">Walk-in Customer</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-slate-700 dark:text-slate-300">
                            {{ $sale->user->name ?? 'Staff' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($sale->payment_mode === 'Cash')
                            <span class="px-2 py-1 rounded text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                                Cash Sale
                            </span>
                            @else
                            <span class="px-2 py-1 rounded text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                                Debit Sale
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-slate-800 dark:text-white">
                            Rs. {{ number_format($sale->grand_total, 2) }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button onclick="printInvoice({{ $sale->id }})" class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition flex items-center justify-center">
                                <i class="fas fa-print text-xs"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <i class="fas fa-folder-open text-4xl mb-3 opacity-30"></i>
                            <p>No invoices found for this category.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $sales->appends(['type' => request('type')])->links() }}
        </div>

    </div>
</div>

<script>
    function printInvoice(id) {
        window.open(`/sales/${id}/print`, '_blank', 'width=400,height=600');
    }
</script>
@endsection