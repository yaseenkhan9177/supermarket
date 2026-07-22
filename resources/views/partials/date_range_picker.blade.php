@php
    $currentPreset = request('preset', 'all_time');
    $fromDate = request('from_date');
    $toDate = request('to_date');
@endphp

<form method="GET" action="{{ $actionUrl ?? request()->url() }}" class="bg-white dark:bg-slate-800/90 p-4 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/60 mb-6" x-data="dateRangePicker('{{ $currentPreset }}', '{{ $fromDate }}', '{{ $toDate }}')">
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2 text-slate-700 dark:text-slate-200 text-xs font-bold uppercase tracking-wider">
            <i class="fas fa-calendar-alt text-indigo-500"></i> Date Filter:
        </div>

        {{-- Preset Select --}}
        <div class="w-40">
            <select
                name="preset"
                x-model="preset"
                @change="applyPreset()"
                class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            >
                <option value="all_time">All Time</option>
                <option value="today">Today</option>
                <option value="this_week">This Week</option>
                <option value="this_month">This Month</option>
                <option value="last_month">Last Month</option>
                <option value="custom">Custom Range</option>
            </select>
        </div>

        {{-- From Date --}}
        <div class="flex items-center gap-1.5" x-show="preset !== 'all_time'">
            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">From:</label>
            <input
                type="date"
                name="from_date"
                x-model="fromDate"
                @change="preset = 'custom'"
                class="text-xs font-medium px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500"
            />
        </div>

        {{-- To Date --}}
        <div class="flex items-center gap-1.5" x-show="preset !== 'all_time'">
            <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">To:</label>
            <input
                type="date"
                name="to_date"
                x-model="toDate"
                @change="preset = 'custom'"
                class="text-xs font-medium px-3 py-1.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500"
            />
        </div>

        {{-- Buttons --}}
        <div class="flex items-center gap-2 ml-auto">
            <button
                type="submit"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors shadow-sm"
            >
                <i class="fas fa-filter text-xs"></i> Apply Filter
            </button>

            @if(request()->hasAny(['preset', 'from_date', 'to_date']))
            <a
                href="{{ $actionUrl ?? request()->url() }}"
                class="px-3 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold transition-colors"
            >
                Reset
            </a>
            @endif
        </div>
    </div>
</form>

<script>
function dateRangePicker(initialPreset, initialFrom, initialTo) {
    return {
        preset: initialPreset || 'all_time',
        fromDate: initialFrom || '',
        toDate: initialTo || '',
        applyPreset() {
            const today = new Date();
            const formatDate = (d) => d.toISOString().split('T')[0];

            if (this.preset === 'today') {
                this.fromDate = formatDate(today);
                this.toDate = formatDate(today);
            } else if (this.preset === 'this_week') {
                const day = today.getDay();
                const diffToMonday = today.getDate() - day + (day === 0 ? -6 : 1);
                const monday = new Date(today.setDate(diffToMonday));
                const sunday = new Date(monday);
                sunday.setDate(monday.getDate() + 6);
                this.fromDate = formatDate(monday);
                this.toDate = formatDate(sunday);
            } else if (this.preset === 'this_month') {
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                this.fromDate = formatDate(firstDay);
                this.toDate = formatDate(lastDay);
            } else if (this.preset === 'last_month') {
                const firstDay = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                const lastDay = new Date(today.getFullYear(), today.getMonth(), 0);
                this.fromDate = formatDate(firstDay);
                this.toDate = formatDate(lastDay);
            } else if (this.preset === 'all_time') {
                this.fromDate = '';
                this.toDate = '';
            }
        }
    }
}
</script>
