@extends('super_admin.layout')

@section('title', 'Dashboard')
@section('header', 'Dashboard')
@section('subheader', 'Welcome back! Here\'s what\'s happening with your stores.')

@section('content')
<div class="space-y-6">

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        {{-- Active Stores --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/25 flex-shrink-0">
                <i class="fas fa-check-circle text-white text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Stores</p>
                <h3 class="text-2xl font-extrabold text-slate-800 mt-0.5">{{ $stats['total_active'] }}</h3>
                <p class="text-xs text-emerald-500 font-medium mt-0.5">Live &amp; operational</p>
            </div>
        </div>

        {{-- Pending Requests --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/25 flex-shrink-0">
                <i class="fas fa-inbox text-white text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pending Signups</p>
                <h3 class="text-2xl font-extrabold text-slate-800 mt-0.5">{{ $stats['total_pending'] }}</h3>
                <p class="text-xs text-blue-500 font-medium mt-0.5">Awaiting approval</p>
            </div>
        </div>

        {{-- Suspended Stores --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-500 to-rose-600 flex items-center justify-center shadow-lg shadow-rose-500/25 flex-shrink-0">
                <i class="fas fa-ban text-white text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Suspended Stores</p>
                <h3 class="text-2xl font-extrabold text-slate-800 mt-0.5">{{ $stats['total_suspended'] }}</h3>
                <p class="text-xs text-rose-500 font-medium mt-0.5">Access blocked</p>
            </div>
        </div>

        {{-- Expired Stores --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center shadow-lg shadow-amber-500/25 flex-shrink-0">
                <i class="fas fa-clock text-white text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Expired Stores</p>
                <h3 class="text-2xl font-extrabold text-slate-800 mt-0.5">{{ $stats['total_expired'] }}</h3>
                <p class="text-xs text-amber-500 font-medium mt-0.5">Payment due</p>
            </div>
        </div>
    </div>

    {{-- Charts & Recent --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Growth Chart --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 lg:col-span-2">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Store Registrations</h3>
                    <p class="text-xs text-slate-400 mt-0.5">New signups per month</p>
                </div>
                <span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">Last 6 months</span>
            </div>
            <canvas id="growthChart" height="140"></canvas>
        </div>

        {{-- Status Donut --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <div class="mb-6">
                <h3 class="text-base font-bold text-slate-800">Status Distribution</h3>
                <p class="text-xs text-slate-400 mt-0.5">All {{ $stats['total_tenants'] }} tenants</p>
            </div>
            <canvas id="statusChart" height="180"></canvas>
            <div class="mt-4 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span><span class="text-slate-600">Active</span></div>
                    <span class="font-bold text-slate-800">{{ $statusChart['active'] }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span><span class="text-slate-600">Pending</span></div>
                    <span class="font-bold text-slate-800">{{ $statusChart['pending'] }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span><span class="text-slate-600">Suspended</span></div>
                    <span class="font-bold text-slate-800">{{ $statusChart['suspended'] }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span><span class="text-slate-600">Expired</span></div>
                    <span class="font-bold text-slate-800">{{ $statusChart['expired'] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctxGrowth = document.getElementById('growthChart').getContext('2d');
    new Chart(ctxGrowth, {
        type: 'line',
        data: {
            labels: {!! json_encode($growthChart['labels']) !!},
            datasets: [{
                label: 'Signups',
                data: {!! json_encode($growthChart['data']) !!},
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.08)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });

    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Pending', 'Suspended', 'Expired'],
            datasets: [{
                data: [
                    {{ $statusChart['active'] }},
                    {{ $statusChart['pending'] }},
                    {{ $statusChart['suspended'] }},
                    {{ $statusChart['expired'] }}
                ],
                backgroundColor: ['#10b981', '#3b82f6', '#ef4444', '#f59e0b'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: { legend: { display: false } }
        }
    });
});
</script>
@endsection