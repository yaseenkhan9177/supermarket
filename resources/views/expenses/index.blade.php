@extends('layouts.admin')

@section('title', 'Expense Management')

@section('content')
<div class="max-w-7xl mx-auto pb-16">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-red-100 dark:bg-red-900/30">
                    <i class="fas fa-receipt text-red-500 text-lg"></i>
                </span>
                Expense Management
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                Track, manage, and analyse all operational expenses
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @can('expense_categories.manage')
            <a href="{{ route('expense-categories.index') }}"
               class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl flex items-center gap-2 transition-colors">
                <i class="fas fa-tags"></i> Categories
            </a>
            @endcan

            @can('expenses.export')
            <a href="{{ route('expenses.export', array_merge(request()->query(), ['format' => 'csv'])) }}"
               class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl flex items-center gap-2 transition-colors">
                <i class="fas fa-file-csv"></i> CSV
            </a>
            <a href="{{ route('expenses.export', array_merge(request()->query(), ['format' => 'xlsx'])) }}"
               class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl flex items-center gap-2 transition-colors shadow-sm">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            @endcan

            @can('expenses.create')
            <a href="{{ route('expenses.create') }}"
               id="btn-new-expense"
               class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl flex items-center gap-2 transition-colors shadow-sm">
                <i class="fas fa-plus"></i> New Expense
            </a>
            @endcan
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="mb-5 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl flex items-start gap-3">
        <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
        <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-5 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800/60 rounded-2xl flex items-start gap-3">
        <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
        <p class="text-sm font-semibold text-red-800 dark:text-red-300">{{ session('error') }}</p>
    </div>
    @endif

    @if($errors->any())
    <div class="mb-5 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800/60 rounded-2xl">
        <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-exclamation-triangle text-red-500"></i>
            <p class="text-sm font-bold text-red-700 dark:text-red-400">Please fix the following errors:</p>
        </div>
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
            <li class="text-xs text-red-600 dark:text-red-400">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Date Range Filter --}}
    @include('partials.date_range_picker', [
        'defaultPreset' => $preset,
        'initialFrom'   => $fromDate,
        'initialTo'     => $toDate,
        'showAllTime'   => true,
    ])

    {{-- Additional Filters --}}
    <form method="GET" action="{{ route('expenses.index') }}" class="bg-white dark:bg-slate-800/90 px-4 py-3 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/60 mb-6">
        @foreach(request()->only(['preset','from_date','to_date']) as $key => $val)
            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
        @endforeach
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 text-slate-700 dark:text-slate-200 text-xs font-bold uppercase tracking-wider">
                <i class="fas fa-sliders-h text-indigo-500"></i> More Filters:
            </div>

            {{-- Category --}}
            <select name="category_id" class="text-xs font-semibold px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="all">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
                @endforeach
            </select>

            {{-- Payment Method --}}
            <select name="payment_method" class="text-xs font-semibold px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="all">All Methods</option>
                @foreach(['Cash','Bank','Cheque','Card','Other'] as $m)
                <option value="{{ $m }}" {{ request('payment_method') == $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>

            {{-- Search --}}
            <div class="flex-1 min-w-48">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search expense #, description, ref..."
                    class="w-full text-xs font-medium px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <div class="flex items-center gap-2 ml-auto">
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors shadow-sm">
                    <i class="fas fa-search text-xs"></i> Filter
                </button>
                @if(request()->hasAny(['category_id','payment_method','search']))
                <a href="{{ route('expenses.index', request()->only(['preset','from_date','to_date'])) }}"
                   class="px-3 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold transition-colors">
                    Clear
                </a>
                @endif
            </div>
        </div>
    </form>

    {{-- KPI Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Expenses</span>
            <h3 class="text-2xl font-black text-red-600 dark:text-red-400 mt-1">Rs. {{ number_format($totalExpenses, 2) }}</h3>
            <p class="text-[11px] text-slate-400 mt-1">For selected period</p>
        </div>
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">No. of Expenses</span>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $expenseCount }}</h3>
            <p class="text-[11px] text-slate-400 mt-1">Transactions recorded</p>
        </div>
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Average Expense</span>
            <h3 class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">Rs. {{ number_format($avgExpense, 2) }}</h3>
            <p class="text-[11px] text-slate-400 mt-1">Per transaction</p>
        </div>
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Largest Expense</span>
            <h3 class="text-2xl font-black text-purple-600 dark:text-purple-400 mt-1">Rs. {{ number_format($maxExpense, 2) }}</h3>
            <p class="text-[11px] text-slate-400 mt-1">Single transaction</p>
        </div>
    </div>

    {{-- Analytics Charts --}}
    @if($expenseCount > 0)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        {{-- Daily Trend Chart --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <i class="fas fa-chart-line text-indigo-500"></i> Daily Expense Trend
            </h3>
            <canvas id="dailyTrendChart" height="200"></canvas>
        </div>

        {{-- Category Breakdown Chart --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <i class="fas fa-chart-pie text-red-500"></i> By Category
            </h3>
            <canvas id="categoryChart" height="200"></canvas>
        </div>
    </div>

    {{-- Category & Payment Method Breakdown Tables --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Category Breakdown Table --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/60 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/60">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                    <i class="fas fa-tags text-amber-500"></i> Expense by Category
                </h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700/40">
                @forelse($categoryBreakdown as $cat)
                @php $pct = $totalExpenses > 0 ? round(($cat->total_amount / $totalExpenses) * 100, 1) : 0; @endphp
                <div class="px-5 py-3">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $cat->category_name }}</span>
                        <div class="text-right">
                            <span class="text-xs font-black text-slate-800 dark:text-white">Rs. {{ number_format($cat->total_amount, 2) }}</span>
                            <span class="text-[10px] text-slate-400 ml-1">({{ $pct }}%)</span>
                        </div>
                    </div>
                    <div class="h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-red-500 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">{{ $cat->count }} transaction(s)</p>
                </div>
                @empty
                <p class="px-5 py-4 text-xs text-slate-400">No data available.</p>
                @endforelse
            </div>
        </div>

        {{-- Payment Method Breakdown Table --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/60 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/60">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                    <i class="fas fa-credit-card text-blue-500"></i> By Payment Method
                </h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-700/40">
                @forelse($paymentBreakdown as $pm)
                @php
                    $pmPct = $totalExpenses > 0 ? round(($pm->total_amount / $totalExpenses) * 100, 1) : 0;
                    $pmColor = match($pm->payment_method) {
                        'Cash'   => 'bg-emerald-500',
                        'Bank'   => 'bg-blue-500',
                        'Cheque' => 'bg-purple-500',
                        'Card'   => 'bg-amber-500',
                        default  => 'bg-slate-400',
                    };
                @endphp
                <div class="px-5 py-3">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                            <span class="inline-block w-2 h-2 rounded-full {{ $pmColor }}"></span>
                            {{ $pm->payment_method }}
                        </span>
                        <div class="text-right">
                            <span class="text-xs font-black text-slate-800 dark:text-white">Rs. {{ number_format($pm->total_amount, 2) }}</span>
                            <span class="text-[10px] text-slate-400 ml-1">({{ $pmPct }}%)</span>
                        </div>
                    </div>
                    <div class="h-1.5 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full {{ $pmColor }} rounded-full" style="width: {{ $pmPct }}%"></div>
                    </div>
                </div>
                @empty
                <p class="px-5 py-4 text-xs text-slate-400">No data available.</p>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    {{-- Expenses Table --}}
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/60 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                <i class="fas fa-list text-slate-500"></i>
                Expense Records
                <span class="ml-1 text-xs font-normal text-slate-400">({{ $expenses->total() }} total)</span>
            </h3>
        </div>

        @if($expenses->isEmpty())
        <div class="py-16 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-700 mb-4">
                <i class="fas fa-receipt text-3xl text-slate-300 dark:text-slate-500"></i>
            </div>
            <h3 class="text-base font-bold text-slate-600 dark:text-slate-400">No expenses found</h3>
            <p class="text-sm text-slate-400 mt-1">Try adjusting your filters or record a new expense.</p>
            @can('expenses.create')
            <a href="{{ route('expenses.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl transition-colors">
                <i class="fas fa-plus"></i> Record Expense
            </a>
            @endcan
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <th class="text-left px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Expense #</th>
                        <th class="text-left px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Date</th>
                        <th class="text-left px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Category</th>
                        <th class="text-left px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Description</th>
                        <th class="text-left px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Method</th>
                        <th class="text-right px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Amount</th>
                        <th class="text-left px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">By</th>
                        <th class="text-right px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/40">
                    @foreach($expenses as $expense)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/20 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ $expense->expense_no }}</span>
                                @if($expense->attachment_path)
                                <i class="fas fa-paperclip text-slate-400 text-[10px]" title="Has attachment"></i>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-600 dark:text-slate-400 whitespace-nowrap">
                            {{ $expense->expense_date->format('d M Y') }}
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                                {{ $expense->category_name }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="text-xs font-medium text-slate-700 dark:text-slate-300 max-w-48 truncate" title="{{ $expense->description }}">
                                {{ $expense->description }}
                            </div>
                            @if($expense->reference_no)
                            <div class="text-[10px] text-slate-400 mt-0.5">Ref: {{ $expense->reference_no }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            @php
                                $badge = match($expense->payment_method) {
                                    'Cash'   => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
                                    'Bank'   => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                                    'Cheque' => 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
                                    'Card'   => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                                    default  => 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-semibold {{ $badge }}">
                                {{ $expense->payment_method }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <span class="text-sm font-black text-red-600 dark:text-red-400">Rs. {{ number_format($expense->amount, 2) }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400">
                            {{ $expense->user->name ?? 'Staff' }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('expenses.show', $expense->id) }}"
                                   class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500 dark:text-slate-400 transition-colors"
                                   title="View">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                <a href="{{ route('expenses.print', $expense->id) }}" target="_blank"
                                   class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500 dark:text-slate-400 transition-colors"
                                   title="Print Voucher">
                                    <i class="fas fa-print text-xs"></i>
                                </a>
                                @can('expenses.edit')
                                <a href="{{ route('expenses.edit', $expense->id) }}"
                                   class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-900/30 hover:bg-amber-100 text-amber-600 dark:text-amber-400 transition-colors"
                                   title="Edit">
                                    <i class="fas fa-pencil text-xs"></i>
                                </a>
                                @endcan
                                @can('expenses.delete')
                                <form method="POST" action="{{ route('expenses.destroy', $expense->id) }}"
                                      onsubmit="return confirm('Delete expense {{ $expense->expense_no }}? This will restore the wallet balance.')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="p-1.5 rounded-lg bg-red-50 dark:bg-red-900/30 hover:bg-red-100 text-red-600 dark:text-red-400 transition-colors"
                                            title="Delete">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                {{-- Totals Row --}}
                <tfoot class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <td colspan="5" class="px-5 py-3 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase">
                            Page Total ({{ $expenses->count() }} records)
                        </td>
                        <td class="px-5 py-3 text-right text-sm font-black text-red-600 dark:text-red-400">
                            Rs. {{ number_format($expenses->sum('amount'), 2) }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Pagination --}}
        @if($expenses->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-700/60">
            {{ $expenses->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

@if($expenseCount > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.06)';
    const labelColor = isDark ? '#94a3b8' : '#64748b';

    // Daily Trend Chart
    const dailyData = @json($dailyTrend);
    new Chart(document.getElementById('dailyTrendChart'), {
        type: 'bar',
        data: {
            labels: dailyData.map(d => d.expense_date),
            datasets: [{
                label: 'Expenses (Rs.)',
                data: dailyData.map(d => parseFloat(d.daily_amount)),
                backgroundColor: 'rgba(239,68,68,0.75)',
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: labelColor, font: { size: 10 } } },
                y: { grid: { color: gridColor }, ticks: { color: labelColor, font: { size: 10 },
                    callback: v => 'Rs.' + v.toLocaleString() } }
            }
        }
    });

    // Category Pie Chart
    const catData = @json($categoryBreakdown);
    const catColors = ['#ef4444','#f97316','#eab308','#22c55e','#06b6d4','#8b5cf6','#ec4899','#64748b','#0ea5e9','#a855f7','#f43f5e'];
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: catData.map(c => c.category_name),
            datasets: [{
                data: catData.map(c => parseFloat(c.total_amount)),
                backgroundColor: catColors.slice(0, catData.length),
                borderWidth: 2,
                borderColor: isDark ? '#1e293b' : '#ffffff',
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: labelColor, boxWidth: 10, padding: 10, font: { size: 10 } }
                },
                tooltip: {
                    callbacks: {
                        label: ctx => ` Rs. ${parseFloat(ctx.parsed).toLocaleString('en', {minimumFractionDigits:2})}`
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endif

@endsection
