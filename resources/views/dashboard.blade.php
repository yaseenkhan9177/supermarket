@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center space-x-3">
        <h1 class="text-xl font-bold text-gray-800">My Store</h1>
        <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-xs font-bold rounded-full uppercase tracking-wide">Retail</span>
    </div>
    <button class="text-gray-400 hover:text-indigo-600 transition">
        <i class="fas fa-sync-alt"></i>
    </button>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">

    <!-- Sales (Green) -->
    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-green-500 flex justify-between items-center relative overflow-hidden group">
        <div class="relative z-10">
            <p class="text-gray-500 text-xs font-semibold mb-1">Today's Net Sales</p>
            <h3 class="text-xl font-bold text-gray-800">Rs. {{ number_format($kpis['daily_sales']) }}</h3>
        </div>
        <div class="bg-green-100 p-2 rounded-lg text-green-600 group-hover:scale-110 transition-transform">
            <i class="fas fa-chart-line text-lg"></i>
        </div>
    </div>

    <!-- Cash Hand (Blue) -->
    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-blue-500 flex justify-between items-center relative overflow-hidden group">
        <div>
            <p class="text-gray-500 text-xs font-semibold mb-1">Cash in Hand</p>
            <h3 class="text-xl font-bold text-gray-800">Rs. {{ number_format($kpis['cash_in_hand']) }}</h3>
        </div>
        <div class="bg-blue-100 p-2 rounded-lg text-blue-600 group-hover:scale-110 transition-transform">
            <i class="fas fa-wallet text-lg"></i>
        </div>
    </div>

    <!-- Receivables (Red) -->
    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-red-500 flex justify-between items-center relative overflow-hidden group">
        <div>
            <p class="text-gray-500 text-xs font-semibold mb-1">Receivables</p>
            <h3 class="text-xl font-bold text-red-600">Rs. {{ number_format($kpis['receivables']) }}</h3>
        </div>
        <div class="bg-red-100 p-2 rounded-lg text-red-600 group-hover:scale-110 transition-transform">
            <i class="fas fa-hand-holding-usd text-lg"></i>
        </div>
    </div>

    <!-- Low Stock (Orange) -->
    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-orange-400 flex justify-between items-center relative overflow-hidden group">
        <div>
            <p class="text-gray-500 text-xs font-semibold mb-1">Low Stock Items</p>
            <h3 class="text-xl font-bold text-gray-800">{{ $kpis['low_stock_count'] }} Products</h3>
        </div>
        <div class="bg-orange-100 p-2 rounded-lg text-orange-600 group-hover:scale-110 transition-transform">
            <i class="fas fa-exclamation-triangle text-lg"></i>
        </div>
    </div>

    <!-- Near Expiry (Purple) -->
    <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-purple-500 flex justify-between items-center relative overflow-hidden group">
        <div>
            <p class="text-gray-500 text-xs font-semibold mb-1">Near Expiry</p>
            <h3 class="text-xl font-bold text-gray-800">{{ $kpis['expiring_count'] }} Items</h3>
        </div>
        <div class="bg-purple-100 p-2 rounded-lg text-purple-600 group-hover:scale-110 transition-transform">
            <i class="fas fa-hourglass-half text-lg"></i>
        </div>
    </div>

</div>

<!-- Quick Access Shortcuts -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-8">

    <a href="{{ route('reports.index') }}"
       id="shortcut-reports"
       class="group flex flex-col items-center justify-center gap-2 bg-white dark:bg-slate-800
              border border-indigo-100 dark:border-indigo-900 rounded-xl p-4 shadow-sm
              hover:shadow-md hover:border-indigo-400 dark:hover:border-indigo-500
              hover:-translate-y-0.5 transition-all duration-200">
        <div class="w-10 h-10 rounded-lg bg-indigo-600 flex items-center justify-center
                    shadow-md shadow-indigo-300 dark:shadow-indigo-900
                    group-hover:scale-110 transition-transform">
            <i class="fas fa-chart-pie text-white text-base"></i>
        </div>
        <span class="text-xs font-bold text-gray-700 dark:text-slate-300 text-center leading-tight">Reports</span>
    </a>

    <a href="{{ route('sales.pos') }}"
       id="shortcut-pos"
       class="group flex flex-col items-center justify-center gap-2 bg-white dark:bg-slate-800
              border border-green-100 dark:border-green-900 rounded-xl p-4 shadow-sm
              hover:shadow-md hover:border-green-400 dark:hover:border-green-500
              hover:-translate-y-0.5 transition-all duration-200">
        <div class="w-10 h-10 rounded-lg bg-green-500 flex items-center justify-center
                    shadow-md shadow-green-200 dark:shadow-green-900
                    group-hover:scale-110 transition-transform">
            <i class="fas fa-cash-register text-white text-base"></i>
        </div>
        <span class="text-xs font-bold text-gray-700 dark:text-slate-300 text-center leading-tight">POS Sale</span>
    </a>

    <a href="{{ route('purchases.create') }}"
       id="shortcut-purchase"
       class="group flex flex-col items-center justify-center gap-2 bg-white dark:bg-slate-800
              border border-blue-100 dark:border-blue-900 rounded-xl p-4 shadow-sm
              hover:shadow-md hover:border-blue-400 dark:hover:border-blue-500
              hover:-translate-y-0.5 transition-all duration-200">
        <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center
                    shadow-md shadow-blue-200 dark:shadow-blue-900
                    group-hover:scale-110 transition-transform">
            <i class="fas fa-shopping-cart text-white text-base"></i>
        </div>
        <span class="text-xs font-bold text-gray-700 dark:text-slate-300 text-center leading-tight">Purchase</span>
    </a>

    <a href="{{ route('general-ledger.index') }}"
       id="shortcut-accounts"
       class="group flex flex-col items-center justify-center gap-2 bg-white dark:bg-slate-800
              border border-purple-100 dark:border-purple-900 rounded-xl p-4 shadow-sm
              hover:shadow-md hover:border-purple-400 dark:hover:border-purple-500
              hover:-translate-y-0.5 transition-all duration-200">
        <div class="w-10 h-10 rounded-lg bg-purple-600 flex items-center justify-center
                    shadow-md shadow-purple-200 dark:shadow-purple-900
                    group-hover:scale-110 transition-transform">
            <i class="fas fa-sitemap text-white text-base"></i>
        </div>
        <span class="text-xs font-bold text-gray-700 dark:text-slate-300 text-center leading-tight">Accounts</span>
    </a>

    <a href="{{ route('items.index') }}"
       id="shortcut-items"
       class="group flex flex-col items-center justify-center gap-2 bg-white dark:bg-slate-800
              border border-amber-100 dark:border-amber-900 rounded-xl p-4 shadow-sm
              hover:shadow-md hover:border-amber-400 dark:hover:border-amber-500
              hover:-translate-y-0.5 transition-all duration-200">
        <div class="w-10 h-10 rounded-lg bg-amber-500 flex items-center justify-center
                    shadow-md shadow-amber-200 dark:shadow-amber-900
                    group-hover:scale-110 transition-transform">
            <i class="fas fa-boxes text-white text-base"></i>
        </div>
        <span class="text-xs font-bold text-gray-700 dark:text-slate-300 text-center leading-tight">Items</span>
    </a>

    <a href="{{ route('customers.index') }}"
       id="shortcut-customers"
       class="group flex flex-col items-center justify-center gap-2 bg-white dark:bg-slate-800
              border border-teal-100 dark:border-teal-900 rounded-xl p-4 shadow-sm
              hover:shadow-md hover:border-teal-400 dark:hover:border-teal-500
              hover:-translate-y-0.5 transition-all duration-200">
        <div class="w-10 h-10 rounded-lg bg-teal-500 flex items-center justify-center
                    shadow-md shadow-teal-200 dark:shadow-teal-900
                    group-hover:scale-110 transition-transform">
            <i class="fas fa-users text-white text-base"></i>
        </div>
        <span class="text-xs font-bold text-gray-700 dark:text-slate-300 text-center leading-tight">Customers</span>
    </a>

</div>

<!-- Charts & Activity Grid -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">

    <!-- Charts Column -->
    <div class="lg:col-span-3 space-y-6">

        <!-- Row 1: Paid vs Unpaid & Customer Balance -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Paid vs Unpaid (Doughnut) -->
            <div class="bg-white rounded-xl shadow-sm p-6 flex flex-col">
                <h3 class="text-gray-700 font-bold mb-4">Paid vs Unpaid Invoices</h3>
                <div class="relative flex-grow flex items-center justify-center h-64">
                    <canvas id="paidVsUnpaidChart"></canvas>
                </div>
            </div>

            <!-- Customer Balance (Horizontal Bar) -->
            <div class="bg-white rounded-xl shadow-sm p-6 flex flex-col">
                <h3 class="text-gray-700 font-bold mb-4">Top Customer Balances</h3>
                <div class="relative flex-grow h-64">
                    <canvas id="customerBalanceChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Row 2: Daily Debit & Cash Flow -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Daily Debit (Line) -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-gray-700 font-bold mb-4">Daily Debit Trend</h3>
                <div class="h-64">
                    <canvas id="dailyDebitChart"></canvas>
                </div>
            </div>

            <!-- Cash Flow (Line) -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-gray-700 font-bold mb-4">Cash Flow (In/Out)</h3>
                <div class="h-64">
                    <canvas id="cashFlowChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Row 3: Monthly Sales (Bar) -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-gray-700 font-bold mb-4">Monthly Sales Performance</h3>
            <div class="h-80 w-full">
                <canvas id="monthlySalesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Activity Column -->
    <div class="lg:col-span-1">
        <!-- Recent Activity Card -->
        <div class="bg-white rounded-xl shadow-sm p-6 h-full sticky top-24">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-gray-700 font-bold">Recent Activity</h3>
                <a href="#" class="text-xs text-indigo-600 font-semibold hover:underline">View All</a>
            </div>

            <div class="space-y-6 relative border-l border-gray-200 ml-3 pl-6">
                @foreach($recentActivities as $activity)
                <div class="relative">
                    <div class="absolute -left-[31px] bg-white border-2 border-white rounded-full">
                        <div class="h-8 w-8 rounded-full {{ $activity['bg'] }} flex items-center justify-center ring-4 ring-white">
                            <i class="fas {{ $activity['icon'] }} {{ $activity['color'] }} text-xs"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $activity['action'] }}</p>
                        <p class="text-xs text-gray-500 mb-1">{{ $activity['description'] }}</p>
                        <span class="text-[10px] text-gray-400">{{ $activity['time'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>

            <button class="w-full mt-6 py-2 rounded-lg border border-gray-200 text-gray-600 text-sm font-semibold hover:bg-gray-50 transition-colors">
                Load More
            </button>
        </div>
    </div>

</div>

<!-- Data Injection -->
<div id="new-chart-data" class="hidden"
    data-paid="{{ json_encode($chartData['paid_vs_unpaid']) }}"
    data-debit="{{ json_encode($chartData['daily_debit']) }}"
    data-sales="{{ json_encode($chartData['monthly_sales']) }}"
    data-balance="{{ json_encode($chartData['customer_balance']) }}"
    data-cashflow="{{ json_encode($chartData['cash_flow']) }}"></div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const dataElement = document.getElementById('new-chart-data');
    const paidData = JSON.parse(dataElement.dataset.paid);
    const debitData = JSON.parse(dataElement.dataset.debit);
    const salesData = JSON.parse(dataElement.dataset.sales);
    const balanceData = JSON.parse(dataElement.dataset.balance);
    const cashFlowData = JSON.parse(dataElement.dataset.cashflow);

    Chart.defaults.font.family = "'Roboto', sans-serif";
    Chart.defaults.color = '#6B7280';

    // 1. Paid vs Unpaid (Doughnut)
    new Chart(document.getElementById('paidVsUnpaidChart'), {
        type: 'doughnut',
        data: {
            labels: paidData.labels,
            datasets: [{
                data: paidData.data,
                backgroundColor: ['#10B981', '#EF4444'], // Green, Red
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 8
                    }
                }
            }
        }
    });

    // 2. Customer Balance (Horizontal Bar)
    new Chart(document.getElementById('customerBalanceChart'), {
        type: 'bar',
        data: {
            labels: balanceData.labels,
            datasets: [{
                label: 'Balance (Rs)',
                data: balanceData.data,
                backgroundColor: '#6366F1', // Indigo
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y', // Horizontal
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // 3. Daily Debit (Line)
    new Chart(document.getElementById('dailyDebitChart'), {
        type: 'line',
        data: {
            labels: debitData.labels,
            datasets: [{
                label: 'Debit',
                data: debitData.data,
                borderColor: '#F59E0B', // Orange
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        borderDash: [2, 4]
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // 4. Cash Flow (Line - Multi-axis/Multi-dataset)
    new Chart(document.getElementById('cashFlowChart'), {
        type: 'line',
        data: {
            labels: cashFlowData.labels,
            datasets: [{
                label: 'Inflow',
                data: cashFlowData.inflow,
                borderColor: '#10B981', // Green
                tension: 0.3,
                pointRadius: 0
            }, {
                label: 'Outflow',
                data: cashFlowData.outflow,
                borderColor: '#EF4444', // Red
                tension: 0.3,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        boxWidth: 6
                    }
                }
            },
            scales: {
                y: {
                    grid: {
                        borderDash: [2, 4]
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // 5. Monthly Sales (Bar)
    new Chart(document.getElementById('monthlySalesChart'), {
        type: 'bar',
        data: {
            labels: salesData.labels,
            datasets: [{
                label: 'Sales',
                data: salesData.data,
                backgroundColor: '#3B82F6', // Blue
                borderRadius: 4,
                barPercentage: 0.5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        borderDash: [2, 4]
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>
@endsection