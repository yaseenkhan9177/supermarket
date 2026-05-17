@extends('super_admin.layout')

@section('title', 'Command Center')
@section('header', 'Command Center')

@section('content')
<div class="space-y-6">
    <!-- Top Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Store Requests -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400">Pending Requests</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['pending_setups'] }}</h3>
            </div>
            <div class="p-3 bg-amber-50 rounded-lg text-amber-600">
                <i class="fas fa-inbox text-xl"></i>
            </div>
        </div>

        <!-- Active Stores -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400">Active Stores</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total_active_stores'] }}</h3>
            </div>
            <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600">
                <i class="fas fa-store text-xl"></i>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400">Total Revenue</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1">${{ number_format($stats['total_revenue'], 2) }}</h3>
            </div>
            <div class="p-3 bg-emerald-50 rounded-lg text-emerald-600">
                <i class="fas fa-dollar-sign text-xl"></i>
            </div>
        </div>

        <!-- System Status -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400">System Status</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['system_status'] }}</h3>
            </div>
            <div class="p-3 bg-blue-50 rounded-lg text-blue-600">
                <i class="fas fa-server text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Growth Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 lg:col-span-2">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Stores Growth (Monthly)</h3>
            <canvas id="growthChart" height="200"></canvas>
        </div>

        <!-- Status Chart -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Approved vs Rejected</h3>
            <canvas id="statusChart" height="200"></canvas>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h3>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('super.requests.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                <i class="fas fa-tasks mr-2"></i> Review Pending Requests
            </a>
            <a href="#" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-server mr-2"></i> System Health Check
            </a>
            <a href="#" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-database mr-2"></i> Backup All DBs
            </a>
        </div>
    </div>
</div>

<!-- Chart JS -->
<!-- Chart JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Data Injection -->
<script id="dashboard-data" type="application/json">
    {
        "growthLabels": {
            !!json_encode($growthChart['labels']) !!
        },
        "growthData": {
            !!json_encode($growthChart['data']) !!
        },
        "statsActive": {
            {
                $statusChart['active']
            }
        },
        "statsRejected": {
            {
                $statusChart['rejected']
            }
        },
        "statsPending": {
            {
                $statusChart['pending']
            }
        }
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Parse Data
        const dashboardData = JSON.parse(document.getElementById('dashboard-data').textContent);

        // Growth Chart
        const ctxGrowth = document.getElementById('growthChart').getContext('2d');
        const growthChart = new Chart(ctxGrowth, {
            type: 'line',
            data: {
                labels: dashboardData.growthLabels,
                datasets: [{
                    label: 'New Stores',
                    data: dashboardData.growthData,
                    borderColor: '#4F46E5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Status Chart
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Rejected', 'Pending'],
                datasets: [{
                    data: [
                        dashboardData.statsActive,
                        dashboardData.statsRejected,
                        dashboardData.statsPending
                    ],
                    backgroundColor: [
                        '#10B981', // Emerald 500
                        '#EF4444', // Red 500
                        '#F59E0B' // Amber 500
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>
@endsection