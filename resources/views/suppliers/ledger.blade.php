@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('suppliers.index') }}" class="text-slate-400 hover:text-indigo-600 transition">
                    <i class="fas fa-arrow-left text-sm"></i>
                </a>
                <h1 class="text-2xl font-extrabold text-slate-800 dark:text-white">
                    <span class="bg-indigo-100 text-indigo-700 text-xs font-mono font-bold px-2 py-0.5 rounded-lg mr-2">{{ $supplier->code }}</span>
                    {{ $supplier->name }}
                    @if($supplier->company_name)
                        <span class="text-sm font-normal text-slate-400 ml-2">— {{ $supplier->company_name }}</span>
                    @endif
                </h1>
            </div>
            <div class="flex items-center gap-2 ml-7 mt-1">
                <p class="text-slate-500 text-sm">Supplier Ledger &amp; Transaction History</p>
                @if($supplier->category)
                    <span class="inline-block bg-purple-100 text-purple-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                        {{ $supplier->category->name }}
                    </span>
                @endif
            </div>
        </div>
        <a href="{{ route('supplier-returns.index') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-md text-sm transition">
            <i class="fas fa-rotate-left"></i> Initiate Return
        </a>
    </div>

    {{-- Balance Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Current Balance</p>
            @if($supplier->current_balance > 0)
                <p class="text-2xl font-extrabold text-red-500">Rs. {{ number_format($supplier->current_balance, 2) }}</p>
                <span class="text-xs font-bold text-red-400 bg-red-50 px-2 py-0.5 rounded-full">Payable (We owe them)</span>
            @elseif($supplier->current_balance < 0)
                <p class="text-2xl font-extrabold text-emerald-500">Rs. {{ number_format(abs($supplier->current_balance), 2) }}</p>
                <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Credit (They owe us)</span>
            @else
                <p class="text-2xl font-extrabold text-slate-500">Rs. 0.00</p>
                <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">Settled</span>
            @endif
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Phone</p>
            <p class="text-lg font-bold text-slate-700 dark:text-white">{{ $supplier->phone ?? '—' }}</p>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 shadow-sm">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Total Transactions</p>
            <p class="text-2xl font-extrabold text-indigo-500">{{ $entries->total() }}</p>
        </div>
    </div>

    {{-- Ledger Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="bg-indigo-50 dark:bg-slate-800 border-b border-indigo-100 dark:border-slate-700 px-6 py-4 flex items-center gap-2">
            <i class="fas fa-book text-indigo-500"></i>
            <h2 class="font-bold text-indigo-900 dark:text-white">Transaction History</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 text-xs uppercase font-bold">
                    <tr>
                        <th class="p-4">Date</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Description</th>
                        <th class="p-4 text-right">Debit (Our Gain)</th>
                        <th class="p-4 text-right">Credit (Their Gain)</th>
                        <th class="p-4 text-right">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($entries as $entry)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                        <td class="p-4 text-slate-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($entry->created_at)->format('d M Y') }}</td>
                        <td class="p-4">
                            @php
                                $typeMap = [
                                    'Purchase'      => ['label' => 'Purchase',       'color' => 'bg-blue-100 text-blue-700'],
                                    'CreditApplied' => ['label' => 'Credit Applied', 'color' => 'bg-emerald-100 text-emerald-700'],
                                    'ReturnCash'    => ['label' => 'Return (Cash)',  'color' => 'bg-orange-100 text-orange-700'],
                                    'ReturnCredit'  => ['label' => 'Return Credit',  'color' => 'bg-purple-100 text-purple-700'],
                                    'Payment'       => ['label' => 'Payment',        'color' => 'bg-teal-100 text-teal-700'],
                                ];
                                $type = $typeMap[$entry->reference_type] ?? ['label' => $entry->reference_type, 'color' => 'bg-slate-100 text-slate-600'];
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $type['color'] }}">{{ $type['label'] }}</span>
                        </td>
                        <td class="p-4 text-slate-700 dark:text-slate-300 max-w-xs truncate">{{ $entry->description }}</td>
                        <td class="p-4 text-right font-mono">
                            @if($entry->debit > 0)
                                <span class="text-emerald-600 font-bold">+ {{ number_format($entry->debit, 2) }}</span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="p-4 text-right font-mono">
                            @if($entry->credit > 0)
                                <span class="text-red-500 font-bold">– {{ number_format($entry->credit, 2) }}</span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="p-4 text-right font-mono font-bold
                            {{ $entry->balance > 0 ? 'text-red-500' : ($entry->balance < 0 ? 'text-emerald-500' : 'text-slate-400') }}">
                            Rs. {{ number_format($entry->balance, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center text-slate-400">
                            <i class="fas fa-book-open text-4xl mb-3 block opacity-30"></i>
                            No transactions recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $entries->links() }}
        </div>
    </div>

</div>
@endsection
