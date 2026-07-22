@extends('layouts.admin')

@section('title', 'Bills & Expenses List')

@section('content')
<div class="max-w-7xl mx-auto pb-12">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="/dashboard" class="text-slate-400 hover:text-slate-200 transition-colors text-sm flex items-center gap-1.5">
                    <i class="fas fa-arrow-left text-xs"></i> Dashboard
                </a>
            </div>
            <h1 class="text-3xl font-bold text-slate-800 dark:text-white tracking-tight">Bills & Expenses</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                Track all outgoing payments and business expenses
            </p>
        </div>
        <a href="{{ route('payments.create') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-500/20 flex items-center gap-2 transition-transform hover:scale-105">
            <i class="fas fa-plus"></i> Record Expense
        </a>
    </div>

    {{-- Date Range Filter --}}
    @include('partials.date_range_picker')

    {{-- Total Expenses Summary Card --}}
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm mb-6 flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Expenses (Selected Range)</span>
            <h2 class="text-3xl font-black text-red-600 dark:text-red-400 mt-0.5">Rs. {{ number_format($totalAmount, 2) }}</h2>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex items-center justify-center text-xl">
            <i class="fas fa-wallet"></i>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
        @if($payments->isEmpty())
            <div class="p-12 text-center text-slate-400 dark:text-slate-500">
                <i class="fas fa-receipt text-5xl mb-4 opacity-30 block"></i>
                <p class="text-base font-semibold">No expense payments found for the selected filter.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3.5 px-5">Voucher #</th>
                            <th class="py-3.5 px-5">Date</th>
                            <th class="py-3.5 px-5">Paid To (Account)</th>
                            <th class="py-3.5 px-5">Source</th>
                            <th class="py-3.5 px-5">Memo / Description</th>
                            <th class="py-3.5 px-5 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-sm">
                        @foreach($payments as $payment)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="py-3.5 px-5 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                {{ $payment->payment_no }}
                            </td>
                            <td class="py-3.5 px-5 text-slate-600 dark:text-slate-300">
                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}
                            </td>
                            <td class="py-3.5 px-5 font-semibold text-slate-800 dark:text-white">
                                {{ $payment->paid_to_account }}
                            </td>
                            <td class="py-3.5 px-5 text-slate-500 dark:text-slate-400 text-xs">
                                {{ $payment->paid_from_account }}
                            </td>
                            <td class="py-3.5 px-5 text-slate-500 dark:text-slate-400 text-xs">
                                {{ $payment->memo ?: '—' }}
                            </td>
                            <td class="py-3.5 px-5 text-right font-black text-red-600 dark:text-red-400">
                                Rs. {{ number_format($payment->amount_paid, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-700/60">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
