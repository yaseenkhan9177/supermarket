@extends('layouts.admin')

@section('title', 'Expense Report')

@section('content')
<div class="max-w-6xl mx-auto pb-16">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('reports.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors text-sm flex items-center gap-1.5">
                    <i class="fas fa-arrow-left text-xs"></i> Reports
                </a>
            </div>
            <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-red-100 dark:bg-red-900/30">
                    <i class="fas fa-receipt text-red-500 text-lg"></i>
                </span>
                Expense Report
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                Breakdown of operational expenses by category, payment method, and time period
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('expenses.index', ['from_date' => $fromDate, 'to_date' => $toDate]) }}"
               class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl flex items-center gap-2 transition-colors">
                <i class="fas fa-list"></i> View Details
            </a>
            <button onclick="window.print()"
                    class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl flex items-center gap-2 transition-colors">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="{{ route('expenses.export', array_merge(request()->query(), ['format' => 'xlsx'])) }}"
               class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl flex items-center gap-2 transition-colors shadow-sm">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
        </div>
    </div>

    {{-- Date Range Filter --}}
    @include('partials.date_range_picker', [
        'defaultPreset' => $preset,
        'initialFrom'   => $fromDate,
        'initialTo'     => $toDate,
        'showAllTime'   => false,
        'actionUrl'     => route('reports.expenses'),
    ])

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Expenses</span>
            <h3 class="text-2xl font-black text-red-600 dark:text-red-400 mt-1">Rs. {{ number_format($totalExpenses, 2) }}</h3>
            <p class="text-[11px] text-slate-400 mt-1">For selected period</p>
        </div>
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Transactions</span>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $totalCount }}</h3>
            <p class="text-[11px] text-slate-400 mt-1">Expense records</p>
        </div>
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm col-span-2 lg:col-span-1">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Average per Transaction</span>
            <h3 class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">
                Rs. {{ $totalCount > 0 ? number_format($totalExpenses / $totalCount, 2) : '0.00' }}
            </h3>
            <p class="text-[11px] text-slate-400 mt-1">Mean expense value</p>
        </div>
    </div>

    {{-- Charts Row --}}
    @if($totalCount > 0)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <i class="fas fa-chart-pie text-red-500"></i> By Category
            </h3>
            <canvas id="categoryPieChart" height="220"></canvas>
        </div>
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4 flex items-center gap-2">
                <i class="fas fa-chart-bar text-blue-500"></i> Monthly Trend
            </h3>
            <canvas id="monthlyChart" height="220"></canvas>
        </div>
    </div>
    @endif

    {{-- Main Report Tables --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- By Category --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/60 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/60 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/10 dark:to-orange-900/10">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                    <i class="fas fa-tags text-red-500"></i> Expenses by Category
                </h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <th class="text-left px-5 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Category</th>
                        <th class="text-center px-5 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">#</th>
                        <th class="text-right px-5 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Amount</th>
                        <th class="text-right px-5 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/40">
                    @forelse($byCategory as $row)
                    @php $pct = $totalExpenses > 0 ? round(($row->total_amount / $totalExpenses) * 100, 1) : 0; @endphp
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/20 transition-colors">
                        <td class="px-5 py-3 text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $row->category_name }}</td>
                        <td class="px-5 py-3 text-center text-xs text-slate-500">{{ $row->count }}</td>
                        <td class="px-5 py-3 text-right text-xs font-bold text-red-600 dark:text-red-400">Rs. {{ number_format($row->total_amount, 2) }}</td>
                        <td class="px-5 py-3 text-right text-[10px] text-slate-400">{{ $pct }}%</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-6 text-center text-xs text-slate-400">No expense data for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($byCategory->isNotEmpty())
                <tfoot class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <td colspan="2" class="px-5 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400">Total</td>
                        <td class="px-5 py-2.5 text-right text-sm font-black text-red-600 dark:text-red-400">Rs. {{ number_format($totalExpenses, 2) }}</td>
                        <td class="px-5 py-2.5 text-right text-[10px] text-slate-400">100%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        {{-- By Payment Method --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/60 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/60 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                    <i class="fas fa-credit-card text-blue-500"></i> Expenses by Payment Method
                </h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <th class="text-left px-5 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Method</th>
                        <th class="text-center px-5 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">#</th>
                        <th class="text-right px-5 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Amount</th>
                        <th class="text-right px-5 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/40">
                    @forelse($byPaymentMethod as $row)
                    @php $pct = $totalExpenses > 0 ? round(($row->total_amount / $totalExpenses) * 100, 1) : 0; @endphp
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/20 transition-colors">
                        <td class="px-5 py-3 text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $row->payment_method }}</td>
                        <td class="px-5 py-3 text-center text-xs text-slate-500">{{ $row->count }}</td>
                        <td class="px-5 py-3 text-right text-xs font-bold text-slate-800 dark:text-white">Rs. {{ number_format($row->total_amount, 2) }}</td>
                        <td class="px-5 py-3 text-right text-[10px] text-slate-400">{{ $pct }}%</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-6 text-center text-xs text-slate-400">No data for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if($byPaymentMethod->isNotEmpty())
                <tfoot class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <td colspan="2" class="px-5 py-2.5 text-xs font-bold text-slate-600 dark:text-slate-400">Total</td>
                        <td class="px-5 py-2.5 text-right text-sm font-black text-slate-800 dark:text-white">Rs. {{ number_format($totalExpenses, 2) }}</td>
                        <td class="px-5 py-2.5 text-right text-[10px] text-slate-400">100%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Monthly Trend Table --}}
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/60 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/60 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/10 dark:to-indigo-900/10">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                <i class="fas fa-chart-line text-purple-500"></i> Monthly Expense Summary (Last 12 Months)
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <th class="text-left px-5 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Month</th>
                        <th class="text-center px-5 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Transactions</th>
                        <th class="text-right px-5 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Amount</th>
                        <th class="text-right px-5 py-2.5 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Avg / Txn</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/40">
                    @forelse($byMonth as $month)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/20 transition-colors">
                        <td class="px-5 py-3 text-xs font-semibold text-slate-700 dark:text-slate-300">
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $month->month_year)->format('F Y') }}
                        </td>
                        <td class="px-5 py-3 text-center text-xs text-slate-500">{{ $month->count }}</td>
                        <td class="px-5 py-3 text-right text-xs font-bold text-red-600 dark:text-red-400">Rs. {{ number_format($month->total_amount, 2) }}</td>
                        <td class="px-5 py-3 text-right text-xs text-slate-500">
                            Rs. {{ $month->count > 0 ? number_format($month->total_amount / $month->count, 2) : '0.00' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-6 text-center text-xs text-slate-400">No monthly data available yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($totalCount > 0)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.06)';
    const labelColor = isDark ? '#94a3b8' : '#64748b';

    // Category Pie Chart
    const catData = @json($byCategory);
    const colors = ['#ef4444','#f97316','#eab308','#22c55e','#06b6d4','#8b5cf6','#ec4899','#64748b','#0ea5e9','#a855f7','#f43f5e'];
    new Chart(document.getElementById('categoryPieChart'), {
        type: 'doughnut',
        data: {
            labels: catData.map(c => c.category_name),
            datasets: [{
                data: catData.map(c => parseFloat(c.total_amount)),
                backgroundColor: colors.slice(0, catData.length),
                borderWidth: 2,
                borderColor: isDark ? '#1e293b' : '#ffffff',
            }]
        },
        options: {
            responsive: true,
            cutout: '60%',
            plugins: {
                legend: { position: 'bottom', labels: { color: labelColor, boxWidth: 10, padding: 8, font: { size: 10 } } },
                tooltip: { callbacks: { label: ctx => ` Rs. ${parseFloat(ctx.parsed).toLocaleString('en', {minimumFractionDigits:2})}` } }
            }
        }
    });

    // Monthly Bar Chart
    const monthData = @json($byMonth->sortBy('month_year')->values());
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: monthData.map(m => {
                const [y, mo] = m.month_year.split('-');
                return new Date(y, mo - 1).toLocaleString('default', { month: 'short', year: '2-digit' });
            }),
            datasets: [{
                label: 'Expenses',
                data: monthData.map(m => parseFloat(m.total_amount)),
                backgroundColor: 'rgba(239,68,68,0.75)',
                borderRadius: 5,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: labelColor, font: { size: 10 } } },
                y: { grid: { color: gridColor }, ticks: { color: labelColor, font: { size: 10 }, callback: v => 'Rs.' + (v/1000).toFixed(0) + 'k' } }
            }
        }
    });
});
</script>
@endpush
@endif

@endsection
