@extends('super_admin.layout')

@section('title', 'Dashboard')
@section('header', 'Dashboard')
@section('subheader', 'Welcome back! Here\'s what\'s happening with your stores.')

@section('content')
<div class="space-y-6">

    {{-- ══════════════════════════════ --}}
    {{-- Stat Cards                    --}}
    {{-- ══════════════════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        {{-- Total Stores --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/25 flex-shrink-0">
                <i class="fas fa-store text-white text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Stores</p>
                <h3 class="text-2xl font-extrabold text-slate-800 mt-0.5">{{ $stats['total_tenants'] }}</h3>
                <p class="text-xs text-indigo-500 font-medium mt-0.5">All registered</p>
            </div>
        </div>

        {{-- Active Stores --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/25 flex-shrink-0">
                <i class="fas fa-check-circle text-white text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Stores</p>
                <h3 class="text-2xl font-extrabold text-slate-800 mt-0.5">{{ $stats['total_active_stores'] }}</h3>
                <p class="text-xs text-emerald-500 font-medium mt-0.5">Live &amp; running</p>
            </div>
        </div>

        {{-- Pending Requests --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-amber-500 flex items-center justify-center shadow-lg shadow-amber-400/25 flex-shrink-0">
                <i class="fas fa-inbox text-white text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pending</p>
                <h3 class="text-2xl font-extrabold text-slate-800 mt-0.5">{{ $stats['pending_setups'] }}</h3>
                <p class="text-xs text-amber-500 font-medium mt-0.5">Awaiting approval</p>
            </div>
        </div>

        {{-- System Status --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sky-500 to-sky-600 flex items-center justify-center shadow-lg shadow-sky-500/25 flex-shrink-0">
                <i class="fas fa-server text-white text-lg"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">System Status</p>
                <h3 class="text-xl font-extrabold text-emerald-600 mt-0.5">{{ $stats['system_status'] }}</h3>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <p class="text-xs text-slate-400 font-medium">All systems go</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════ --}}
    {{-- Charts                        --}}
    {{-- ══════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Growth Chart --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 lg:col-span-2">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Store Growth</h3>
                    <p class="text-xs text-slate-400 mt-0.5">New registrations per month</p>
                </div>
                <span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">Last 6 months</span>
            </div>
            <canvas id="growthChart" height="140"></canvas>
        </div>

        {{-- Status Donut --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <div class="mb-6">
                <h3 class="text-base font-bold text-slate-800">Store Status</h3>
                <p class="text-xs text-slate-400 mt-0.5">Breakdown by status</p>
            </div>
            <canvas id="statusChart" height="180"></canvas>
            <div class="mt-4 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span><span class="text-slate-600">Active</span></div>
                    <span class="font-bold text-slate-800">{{ $statusChart['active'] }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span><span class="text-slate-600">Pending</span></div>
                    <span class="font-bold text-slate-800">{{ $statusChart['pending'] }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span><span class="text-slate-600">Rejected</span></div>
                    <span class="font-bold text-slate-800">{{ $statusChart['rejected'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════ --}}
    {{-- Recent Tenants + Quick Actions --}}
    {{-- ══════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Recent Tenants --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 lg:col-span-2 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-800">Recent Stores</h3>
                <a href="{{ route('super.tenants') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">View All →</a>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($recentTenants as $tenant)
                <div class="px-6 py-3.5 flex items-center justify-between hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-100 to-purple-100 flex items-center justify-center text-indigo-600 font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr($tenant->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ $tenant->name }}</p>
                            <p class="text-xs text-slate-400">{{ $tenant->user->email ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-slate-400">{{ $tenant->created_at->diffForHumans() }}</span>
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-700 uppercase">Active</span>
                    </div>
                </div>
                @empty
                <div class="px-6 py-10 text-center text-slate-400 text-sm">
                    <i class="fas fa-store text-2xl mb-2 block text-slate-300"></i>
                    No stores registered yet.
                </div>
                @endforelse
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-base font-bold text-slate-800 mb-4">Quick Actions</h3>
            <div class="space-y-3">
                <a href="{{ route('super.requests.index') }}"
                   class="flex items-center gap-3 p-3.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center flex-shrink-0 shadow group-hover:scale-105 transition-transform">
                        <i class="fas fa-tasks text-white text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">Review Requests</p>
                        <p class="text-xs text-indigo-500">{{ $stats['pending_setups'] }} pending</p>
                    </div>
                </a>

                <a href="{{ route('super.tenants') }}"
                   class="flex items-center gap-3 p-3.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center flex-shrink-0 shadow group-hover:scale-105 transition-transform">
                        <i class="fas fa-store text-white text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">Manage Stores</p>
                        <p class="text-xs text-emerald-500">{{ $stats['total_active_stores'] }} active</p>
                    </div>
                </a>

                <a href="{{ route('super.users.create') }}"
                   class="flex items-center gap-3 p-3.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-purple-600 flex items-center justify-center flex-shrink-0 shadow group-hover:scale-105 transition-transform">
                        <i class="fas fa-user-plus text-white text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">Add Admin User</p>
                        <p class="text-xs text-purple-500">Create new admin</p>
                    </div>
                </a>

                <a href="{{ route('super.logs') }}"
                   class="flex items-center gap-3 p-3.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-rose-600 flex items-center justify-center flex-shrink-0 shadow group-hover:scale-105 transition-transform">
                        <i class="fas fa-terminal text-white text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">View Logs</p>
                        <p class="text-xs text-rose-500">System events</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Growth Chart
    const ctxGrowth = document.getElementById('growthChart').getContext('2d');
    new Chart(ctxGrowth, {
        type: 'line',
        data: {
            labels: {!! json_encode($growthChart['labels']) !!},
            datasets: [{
                label: 'New Stores',
                data: {!! json_encode($growthChart['data']) !!},
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.08)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#6366f1',
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#94a3b8',
                    bodyColor: '#f8fafc',
                    borderColor: '#334155',
                    borderWidth: 1,
                    cornerRadius: 8,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0, color: '#94a3b8', font: { size: 11 } },
                    grid: { color: 'rgba(148,163,184,0.12)' }
                },
                x: {
                    ticks: { color: '#94a3b8', font: { size: 11 } },
                    grid: { display: false }
                }
            }
        }
    });

    // Status Donut
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Pending', 'Rejected'],
            datasets: [{
                data: [
                    {{ $statusChart['active'] }},
                    {{ $statusChart['pending'] }},
                    {{ $statusChart['rejected'] }}
                ],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            cutout: '72%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#94a3b8',
                    bodyColor: '#f8fafc',
                    borderColor: '#334155',
                    borderWidth: 1,
                    cornerRadius: 8,
                }
            }
        }
    });
});
</script>
@endsection