@extends('super_admin.layout')

@section('title', 'System Logs')
@section('header', 'System Logs')
@section('subheader', 'Real-time application log viewer')

@section('content')
<div class="space-y-5">

    {{-- Toolbar --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs font-semibold text-slate-600">Live — Last 200 lines</span>
            </div>
            <span class="text-xs text-slate-400">{{ count($logLines) }} entries loaded</span>
        </div>
        <a href="{{ route('super.logs') }}"
           class="px-4 py-2 bg-slate-700 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition-colors">
            <i class="fas fa-sync-alt mr-1.5"></i>Refresh
        </a>
    </div>

    {{-- Log Terminal --}}
    <div class="bg-slate-900 rounded-2xl shadow-xl overflow-hidden border border-slate-800">
        {{-- Terminal Bar --}}
        <div class="px-5 py-3 bg-slate-800 border-b border-slate-700 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                <span class="ml-3 text-xs text-slate-400 font-mono">storage/logs/laravel.log</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex gap-1">
                    <button onclick="filterLogs('all')" id="btn-all" class="log-filter-btn active-filter px-2 py-0.5 text-[10px] rounded font-bold uppercase">All</button>
                    <button onclick="filterLogs('error')" id="btn-error" class="log-filter-btn px-2 py-0.5 text-[10px] rounded font-bold uppercase text-slate-400 hover:text-rose-400">Error</button>
                    <button onclick="filterLogs('warning')" id="btn-warning" class="log-filter-btn px-2 py-0.5 text-[10px] rounded font-bold uppercase text-slate-400 hover:text-amber-400">Warning</button>
                    <button onclick="filterLogs('info')" id="btn-info" class="log-filter-btn px-2 py-0.5 text-[10px] rounded font-bold uppercase text-slate-400 hover:text-sky-400">Info</button>
                </div>
            </div>
        </div>

        {{-- Log Lines --}}
        <div class="p-5 font-mono text-xs overflow-y-auto max-h-[600px] space-y-1" id="log-container">
            @forelse($logLines as $line)
                @php
                    $isError   = stripos($line, '.ERROR') !== false || stripos($line, 'error') !== false;
                    $isWarning = stripos($line, '.WARNING') !== false || stripos($line, 'warning') !== false;
                    $isInfo    = stripos($line, '.INFO') !== false || stripos($line, 'info') !== false;
                    $isDebug   = stripos($line, '.DEBUG') !== false || stripos($line, 'debug') !== false;

                    $colorClass = 'text-slate-400';
                    $tagClass   = 'bg-slate-700 text-slate-400';
                    $tag        = 'DEBUG';
                    $dataType   = 'debug';

                    if ($isError) {
                        $colorClass = 'text-rose-300';
                        $tagClass   = 'bg-rose-900/60 text-rose-400';
                        $tag        = 'ERROR';
                        $dataType   = 'error';
                    } elseif ($isWarning) {
                        $colorClass = 'text-amber-300';
                        $tagClass   = 'bg-amber-900/40 text-amber-400';
                        $tag        = 'WARN';
                        $dataType   = 'warning';
                    } elseif ($isInfo) {
                        $colorClass = 'text-sky-300';
                        $tagClass   = 'bg-sky-900/40 text-sky-400';
                        $tag        = 'INFO';
                        $dataType   = 'info';
                    }
                @endphp
                <div class="log-line flex items-start gap-3 hover:bg-white/5 px-2 py-1 rounded transition-colors" data-type="{{ $dataType }}">
                    <span class="flex-shrink-0 px-1.5 py-0.5 rounded text-[10px] font-bold {{ $tagClass }}">{{ $tag }}</span>
                    <span class="{{ $colorClass }} leading-relaxed break-all">{{ $line }}</span>
                </div>
            @empty
                <div class="text-center py-16 text-slate-500">
                    <i class="fas fa-file-alt text-3xl mb-3 block text-slate-700"></i>
                    <p>No log entries found.</p>
                    <p class="text-[10px] mt-1 text-slate-600">Log file: storage/logs/laravel.log</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
.active-filter { background: rgba(99,102,241,0.3); color: #a5b4fc; }
</style>

<script>
function filterLogs(type) {
    const lines = document.querySelectorAll('.log-line');
    lines.forEach(line => {
        if (type === 'all' || line.dataset.type === type) {
            line.style.display = 'flex';
        } else {
            line.style.display = 'none';
        }
    });

    // Update button styles
    document.querySelectorAll('.log-filter-btn').forEach(btn => {
        btn.classList.remove('active-filter');
        btn.style.color = '';
    });
    const activeBtn = document.getElementById('btn-' + type);
    if (activeBtn) activeBtn.classList.add('active-filter');
}

// Auto-scroll to bottom on load
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('log-container');
    container.scrollTop = container.scrollHeight;
});
</script>
@endsection