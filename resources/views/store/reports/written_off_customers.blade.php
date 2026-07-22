@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Header & Title --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('customers.index') }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-sm font-semibold transition">
                    <i class="fas fa-arrow-left mr-1"></i> Customers
                </a>
                <span class="text-slate-300">/</span>
                <span class="text-slate-600 dark:text-slate-400 text-sm font-bold">Reports</span>
            </div>
            <h1 class="text-3xl font-black text-slate-800 dark:text-white mt-1 flex items-center gap-3">
                <i class="fas fa-file-invoice-dollar text-red-600"></i> Written Off Customers Report
            </h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                Overview of debt write-offs, reason categories, and user attributions.
            </p>
        </div>

        {{-- Date Filter Form --}}
        <form method="GET" action="{{ route('reports.written-off-customers') }}" class="flex items-center gap-2 flex-wrap bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-2 rounded-2xl shadow-sm">
            <input type="date" name="start_date" value="{{ $startDate }}" placeholder="Start Date"
                   class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500">
            <span class="text-slate-400 text-xs font-bold">to</span>
            <input type="date" name="end_date" value="{{ $endDate }}" placeholder="End Date"
                   class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500">
            <button type="submit" class="px-4 py-1.5 bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if($startDate || $endDate)
                <a href="{{ route('reports.written-off-customers') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        {{-- Total Written Off --}}
        <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-red-600"></div>
            <div class="text-red-600 dark:text-red-400 text-xs font-bold uppercase tracking-wider mb-2">Total Written Off</div>
            <div class="text-3xl font-black text-red-700 dark:text-red-300">
                Rs. {{ number_format($totalWrittenOffAmount, 2) }}
            </div>
            <div class="text-xs text-red-500/80 mt-1 font-semibold">
                {{ $writeOffEntries->count() }} {{ Str::plural('customer account', $writeOffEntries->count()) }}
            </div>
        </div>

        {{-- Category Breakdown Cards --}}
        @php
            $categoryLabels = [
                'absconded'       => ['label' => 'Absconded',       'icon' => 'fa-running',         'color' => 'text-amber-600 dark:text-amber-400'],
                'deceased'        => ['label' => 'Deceased',        'icon' => 'fa-cross',           'color' => 'text-slate-600 dark:text-slate-400'],
                'disputed'        => ['label' => 'Disputed',        'icon' => 'fa-gavel',           'color' => 'text-purple-600 dark:text-purple-400'],
                'business_closed' => ['label' => 'Business Closed', 'icon' => 'fa-store-slash',    'color' => 'text-blue-600 dark:text-blue-400'],
                'other'           => ['label' => 'Other Reasons',   'icon' => 'fa-question-circle', 'color' => 'text-gray-600 dark:text-gray-400'],
            ];
        @endphp

        @foreach(['absconded', 'deceased', 'disputed'] as $catKey)
            @php
                $item = $breakdown->get($catKey, ['count' => 0, 'total' => 0]);
                $meta = $categoryLabels[$catKey];
            @endphp
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400 text-xs font-bold uppercase tracking-wider">{{ $meta['label'] }}</span>
                    <i class="fas {{ $meta['icon'] }} {{ $meta['color'] }}"></i>
                </div>
                <div class="text-2xl font-extrabold text-slate-800 dark:text-white mt-2">
                    Rs. {{ number_format($item['total'], 2) }}
                </div>
                <div class="text-xs text-slate-400 mt-1 font-semibold">
                    {{ $item['count'] }} entries
                </div>
            </div>
        @endforeach
    </div>

    {{-- Detail Table --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-list text-red-600"></i> Written Off Entries Log
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-bold text-xs">
                    <tr>
                        <th class="p-4">Date & Time</th>
                        <th class="p-4">Customer Name</th>
                        <th class="p-4">Reason Category</th>
                        <th class="p-4 text-right">Written Off Amount</th>
                        <th class="p-4">Detailed Note</th>
                        <th class="p-4">Written Off By</th>
                        <th class="p-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($writeOffEntries as $entry)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-4 text-slate-600 dark:text-slate-400 text-xs font-medium whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($entry->created_at)->format('d M Y, h:i A') }}
                            </td>
                            <td class="p-4 font-bold text-slate-800 dark:text-white">
                                <a href="{{ route('customers.show', $entry->customer_id) }}" class="hover:text-red-600 transition">
                                    {{ $entry->customer->name ?? 'Unknown Customer' }}
                                </a>
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800 dark:bg-red-950/40 dark:text-red-300 capitalize">
                                    {{ str_replace('_', ' ', $entry->reason_category ?: 'other') }}
                                </span>
                            </td>
                            <td class="p-4 text-right font-black text-red-600 dark:text-red-400 whitespace-nowrap">
                                Rs. {{ number_format(abs($entry->amount), 2) }}
                            </td>
                            <td class="p-4 text-slate-700 dark:text-slate-300 text-xs max-w-xs">
                                {{ $entry->note }}
                            </td>
                            <td class="p-4 text-slate-500 dark:text-slate-400 text-xs whitespace-nowrap">
                                <i class="fas fa-user-shield text-slate-400 mr-1"></i>
                                {{ $entry->creator->name ?? 'Admin' }}
                            </td>
                            <td class="p-4 text-center whitespace-nowrap">
                                <a href="{{ route('customers.show', $entry->customer_id) }}"
                                   class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                    View Customer <i class="fas fa-arrow-right text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-10 text-center text-slate-400 dark:text-slate-500">
                                <i class="fas fa-check-circle text-3xl mb-2 block opacity-30 text-emerald-500"></i>
                                No written off customer accounts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
