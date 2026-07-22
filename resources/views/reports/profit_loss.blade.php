@extends('layouts.admin')

@section('title', 'Profit & Loss Statement')

@section('content')
<div class="max-w-6xl mx-auto pb-16">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="/reports" class="text-slate-400 hover:text-slate-200 transition-colors text-sm flex items-center gap-1.5">
                    <i class="fas fa-arrow-left text-xs"></i> Reports
                </a>
            </div>
            <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">Profit & Loss Statement</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                Financial performance summary showing revenue, COGS, expenses, and net profit
            </p>
        </div>

        <button onclick="window.print()" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl flex items-center gap-2 transition-colors self-start sm:self-center">
            <i class="fas fa-print"></i> Print Statement
        </button>
    </div>

    {{-- Date Range Filter Component --}}
    @include('partials.date_range_picker')

    {{-- Legacy Non-Batch COGS Disclaimer Alert --}}
    @if($hasLegacyNonBatchSales)
    <div class="mb-6 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 rounded-2xl p-4 flex items-start gap-3 text-amber-800 dark:text-amber-300 text-xs">
        <i class="fas fa-exclamation-triangle text-amber-500 text-base shrink-0 mt-0.5"></i>
        <div>
            <strong>Approximate COGS Notice:</strong> Some sales in this date range were recorded before batch cost tracking was implemented. For those items, COGS was calculated using the product's current cost rate.
        </div>
    </div>
    @endif

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

        {{-- Net Revenue --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Net Revenue</span>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-1">Rs. {{ number_format($netRevenue, 2) }}</h3>
            <p class="text-[11px] text-slate-400 mt-1">Gross sales minus returns</p>
        </div>

        {{-- COGS --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Cost of Goods Sold</span>
            <h3 class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">Rs. {{ number_format($cogs, 2) }}</h3>
            <p class="text-[11px] text-slate-400 mt-1">FIFO batch inventory cost</p>
        </div>

        {{-- Gross Profit --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Gross Profit</span>
            <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">Rs. {{ number_format($grossProfit, 2) }}</h3>
            <p class="text-[11px] text-slate-400 mt-1">Revenue minus COGS</p>
        </div>

        {{-- Net Profit --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm relative overflow-hidden">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Net Profit</span>
            <h3 class="text-2xl font-black mt-1 {{ $netProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                Rs. {{ number_format($netProfit, 2) }}
            </h3>
            <p class="text-[11px] text-slate-400 mt-1">After expenses & bad debt</p>
        </div>

    </div>

    {{-- Statement Table --}}
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden mb-8">
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 dark:text-white text-base">
                Income Statement Breakdown
            </h2>
            <span class="text-xs text-slate-400">
                Range: {{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d M Y') : 'Start' }} — {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d M Y') : 'Today' }}
            </span>
        </div>

        <table class="w-full text-left border-collapse">
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-sm">

                {{-- REVENUE SECTION --}}
                <tr class="bg-slate-50/50 dark:bg-slate-900/30">
                    <td class="py-3 px-6 font-bold text-slate-800 dark:text-white uppercase tracking-wider text-xs" colspan="2">
                        1. Revenue
                    </td>
                </tr>
                <tr>
                    <td class="py-3 px-6 pl-10 text-slate-600 dark:text-slate-300">Gross Sales (Cash, Debit & POS)</td>
                    <td class="py-3 px-6 text-right font-semibold text-slate-800 dark:text-slate-200">Rs. {{ number_format($grossSales, 2) }}</td>
                </tr>
                <tr>
                    <td class="py-3 px-6 pl-10 text-slate-500 dark:text-slate-400 text-xs">Less: Refunds & Returns</td>
                    <td class="py-3 px-6 text-right font-semibold text-red-500">(Rs. {{ number_format($totalRefunds, 2) }})</td>
                </tr>
                <tr class="bg-indigo-50/40 dark:bg-indigo-950/20 font-bold">
                    <td class="py-3 px-6 text-slate-800 dark:text-white">Net Operating Revenue</td>
                    <td class="py-3 px-6 text-right text-indigo-600 dark:text-indigo-400">Rs. {{ number_format($netRevenue, 2) }}</td>
                </tr>

                {{-- COGS SECTION --}}
                <tr class="bg-slate-50/50 dark:bg-slate-900/30">
                    <td class="py-3 px-6 font-bold text-slate-800 dark:text-white uppercase tracking-wider text-xs" colspan="2">
                        2. Cost of Goods Sold (COGS)
                    </td>
                </tr>
                <tr>
                    <td class="py-3 px-6 pl-10 text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                        Inventory Batch Cost
                        @if($hasLegacyNonBatchSales)
                        <span class="text-[10px] text-amber-500 font-normal">(Approximate for legacy rows)</span>
                        @endif
                    </td>
                    <td class="py-3 px-6 text-right font-semibold text-amber-600">Rs. {{ number_format($cogs, 2) }}</td>
                </tr>
                <tr class="bg-emerald-50/40 dark:bg-emerald-950/20 font-bold border-t-2 border-slate-200 dark:border-slate-700">
                    <td class="py-3.5 px-6 text-slate-900 dark:text-white">GROSS PROFIT</td>
                    <td class="py-3.5 px-6 text-right text-emerald-600 dark:text-emerald-400 text-base">Rs. {{ number_format($grossProfit, 2) }}</td>
                </tr>

                {{-- OPERATING EXPENSES SECTION --}}
                <tr class="bg-slate-50/50 dark:bg-slate-900/30">
                    <td class="py-3 px-6 font-bold text-slate-800 dark:text-white uppercase tracking-wider text-xs" colspan="2">
                        3. Operating Expenses & Bills
                    </td>
                </tr>
                @forelse($expenseBreakdown as $exp)
                <tr>
                    <td class="py-2.5 px-6 pl-10 text-slate-600 dark:text-slate-300 text-xs">
                        • {{ $exp->paid_to_account }}
                    </td>
                    <td class="py-2.5 px-6 text-right font-semibold text-slate-700 dark:text-slate-300 text-xs">
                        Rs. {{ number_format($exp->total_amount, 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td class="py-2.5 px-6 pl-10 text-slate-400 text-xs italic">No expenses recorded in this period.</td>
                    <td class="py-2.5 px-6 text-right text-slate-400 text-xs">Rs. 0.00</td>
                </tr>
                @endforelse
                <tr class="bg-slate-100/60 dark:bg-slate-800 font-semibold text-xs">
                    <td class="py-2.5 px-6 text-slate-700 dark:text-slate-300">Total Operating Expenses</td>
                    <td class="py-2.5 px-6 text-right text-red-600 dark:text-red-400">Rs. {{ number_format($totalExpenses, 2) }}</td>
                </tr>

                {{-- BAD DEBT LOSSES SECTION --}}
                <tr class="bg-slate-50/50 dark:bg-slate-900/30">
                    <td class="py-3 px-6 font-bold text-slate-800 dark:text-white uppercase tracking-wider text-xs" colspan="2">
                        4. Bad Debt & Written-Off Receivables
                    </td>
                </tr>
                <tr>
                    <td class="py-3 px-6 pl-10 text-slate-600 dark:text-slate-300 text-xs">
                        Customer Write-Offs & Bad Debt Losses
                    </td>
                    <td class="py-3 px-6 text-right font-semibold text-purple-600 dark:text-purple-400">
                        Rs. {{ number_format($totalBadDebt, 2) }}
                    </td>
                </tr>

                {{-- NET PROFIT FINAL TOTAL --}}
                <tr class="{{ $netProfit >= 0 ? 'bg-green-100/70 dark:bg-green-950/40 text-green-900 dark:text-green-300' : 'bg-red-100/70 dark:bg-red-950/40 text-red-900 dark:text-red-300' }} border-t-2 border-slate-300 dark:border-slate-600 font-black text-base">
                    <td class="py-4 px-6">
                        NET {{ $netProfit >= 0 ? 'PROFIT' : 'LOSS' }}
                    </td>
                    <td class="py-4 px-6 text-right text-lg">
                        Rs. {{ number_format($netProfit, 2) }}
                    </td>
                </tr>

            </tbody>
        </table>
    </div>

    {{-- Net Profit Trend Chart --}}
    @if(!empty($trendData['labels']))
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700/60 p-6">
        <h3 class="text-sm font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
            <i class="fas fa-chart-line text-indigo-500"></i> Net Profit Daily Trend
        </h3>
        <div class="h-64">
            <canvas id="profitTrendChart"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('profitTrendChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($trendData['labels']),
                datasets: [{
                    label: 'Net Profit (Rs.)',
                    data: @json($trendData['values']),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.08)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        grid: { color: 'rgba(156, 163, 175, 0.1)' },
                        ticks: { font: { size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });
    });
    </script>
    @endif

</div>
@endsection
