@extends('layouts.app')

@section('title', 'Daily Cash Closing & Reconciliation')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white flex items-center gap-3">
                <i class="fas fa-cash-register text-emerald-600"></i> Daily Cash Closing
            </h1>
            <p class="text-slate-500 text-sm mt-1">Reconcile physical cash counted against expected register balances.</p>
        </div>
        <form method="GET" action="{{ route('reports.daily-closing') }}" class="flex items-center gap-2">
            <input type="date" name="date" value="{{ $selectedDate }}" 
                   class="px-4 py-2 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm font-semibold focus:ring-2 focus:ring-emerald-500">
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold text-sm shadow transition">
                Load Date
            </button>
        </form>
    </div>

    <!-- Active Closing Form / Closed Banner -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-950">
            <h2 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i class="far fa-calendar-alt text-slate-500"></i> Closing Summary for {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y (l)') }}
            </h2>
            @if($existingClosing)
                <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800">
                    <i class="fas fa-lock mr-1"></i> Closed & Verified
                </span>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-300 dark:border-amber-800">
                    <i class="fas fa-clock mr-1"></i> Open For Closing
                </span>
            @endif
        </div>

        <div class="p-6">
            <!-- Financial Breakdown Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Total Cash Sales</span>
                    <span class="text-2xl font-black text-slate-900 dark:text-white mt-1 block font-mono">
                        Rs. {{ number_format($cashSalesTotal, 2) }}
                    </span>
                </div>
                <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Total Cash Refunds</span>
                    <span class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1 block font-mono">
                        - Rs. {{ number_format($cashRefundsTotal, 2) }}
                    </span>
                </div>
                <div class="p-5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800/50">
                    <span class="text-xs font-bold text-indigo-700 dark:text-indigo-400 uppercase tracking-wider block">Expected Drawer Cash</span>
                    <span class="text-2xl font-black text-indigo-600 dark:text-indigo-300 mt-1 block font-mono">
                        Rs. {{ number_format($expectedCash, 2) }}
                    </span>
                </div>
            </div>

            @if($existingClosing)
                <!-- CLOSED VIEW -->
                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-800/30 border border-slate-200 dark:border-slate-700 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                        <div>
                            <span class="text-xs text-slate-500 dark:text-slate-400 block font-semibold">Physical Counted Cash</span>
                            <span class="text-xl font-bold font-mono text-slate-900 dark:text-white">
                                Rs. {{ number_format($existingClosing->counted_cash, 2) }}
                            </span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 dark:text-slate-400 block font-semibold">Discrepancy / Difference</span>
                            @if($existingClosing->difference == 0)
                                <span class="inline-flex items-center gap-1 text-lg font-bold text-emerald-600">
                                    <i class="fas fa-check-circle"></i> Balanced (Rs. 0.00)
                                </span>
                            @elseif($existingClosing->difference > 0)
                                <span class="inline-flex items-center gap-1 text-lg font-bold text-amber-600">
                                    <i class="fas fa-arrow-up"></i> Rs. {{ number_format(abs($existingClosing->difference), 2) }} Over
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-lg font-bold text-rose-600">
                                    <i class="fas fa-arrow-down"></i> Rs. {{ number_format(abs($existingClosing->difference), 2) }} Short
                                </span>
                            @endif
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 dark:text-slate-400 block font-semibold">Closed By</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">
                                {{ $existingClosing->closedBy->name ?? 'System' }}
                            </span>
                            <span class="text-xs text-slate-400 block">{{ $existingClosing->created_at->format('h:i A') }}</span>
                        </div>
                    </div>

                    @if($existingClosing->note)
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Closing Note / Explanation</span>
                            <p class="text-sm text-slate-700 dark:text-slate-300 mt-1 italic bg-white dark:bg-slate-900 p-3 rounded-xl border border-slate-200 dark:border-slate-800">
                                "{{ $existingClosing->note }}"
                            </p>
                        </div>
                    @endif
                </div>
            @else
                <!-- RECONCILIATION SUBMIT FORM -->
                <form action="{{ route('reports.daily-closing.store') }}" method="POST" x-data="{
                    expected: {{ $expectedCash }},
                    counted: '',
                    get difference() {
                        const c = parseFloat(this.counted) || 0;
                        return c - this.expected;
                    },
                    get isMismatch() {
                        return Math.abs(this.difference) > 0.001;
                    }
                }" class="space-y-6">
                    @csrf
                    <input type="hidden" name="date" value="{{ $selectedDate }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                Physical Cash Counted (Rs.) *
                            </label>
                            <input type="number" step="0.01" min="0" name="counted_cash" x-model="counted" required
                                   placeholder="Enter counted physical cash..."
                                   class="w-full px-4 py-3 border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-mono font-bold text-lg focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">
                                Live Difference Preview
                            </label>
                            <div class="px-4 py-3 border rounded-xl font-mono font-bold text-lg flex items-center justify-between"
                                 :class="{
                                     'bg-slate-50 text-slate-400 border-slate-200 dark:bg-slate-800 dark:border-slate-700': counted === '',
                                     'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800': counted !== '' && Math.abs(difference) <= 0.001,
                                     'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800': counted !== '' && Math.abs(difference) > 0.001
                                 }">
                                <span>
                                    <template x-if="counted === ''"><span>Enter count above</span></template>
                                    <template x-if="counted !== '' && Math.abs(difference) <= 0.001"><span>✓ Exact Match</span></template>
                                    <template x-if="counted !== '' && difference < -0.001"><span>⚠ Cash Short</span></template>
                                    <template x-if="counted !== '' && difference > 0.001"><span>ℹ Cash Over</span></template>
                                </span>
                                <span>
                                    <template x-if="counted !== ''">
                                        <span x-text="'Rs. ' + Math.abs(difference).toFixed(2)"></span>
                                    </template>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Note field (Shown/Required if mismatch) -->
                    <div x-show="isMismatch" x-cloak class="p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-xl space-y-2">
                        <label class="block text-sm font-bold text-amber-900 dark:text-amber-300">
                            Explanatory Note Required for Cash Mismatch *
                        </label>
                        <textarea name="note" rows="2" placeholder="Describe the reason for cash difference (e.g. change error, missing voucher, etc.)..."
                                  class="w-full px-3 py-2 border border-amber-300 dark:border-amber-700 rounded-lg bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 text-sm focus:ring-2 focus:ring-amber-500"></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-8 py-3.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg transition flex items-center gap-2">
                            <i class="fas fa-lock"></i> Close & Save Reconciliation
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- Reconciliation History Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 dark:text-white text-lg">Past Closing History</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-semibold text-xs border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="p-4">Date</th>
                        <th class="p-4 text-right">Expected</th>
                        <th class="p-4 text-right">Counted</th>
                        <th class="p-4 text-center">Difference</th>
                        <th class="p-4">Closed By</th>
                        <th class="p-4">Note</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($history as $row)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                            <td class="p-4 font-mono font-bold text-slate-800 dark:text-slate-200">
                                {{ $row->date->format('Y-m-d (D)') }}
                            </td>
                            <td class="p-4 text-right font-mono text-slate-600 dark:text-slate-400">
                                Rs. {{ number_format($row->expected_cash, 2) }}
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-slate-900 dark:text-white">
                                Rs. {{ number_format($row->counted_cash, 2) }}
                            </td>
                            <td class="p-4 text-center">
                                @if($row->difference == 0)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400">
                                        Balanced
                                    </span>
                                @elseif($row->difference > 0)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400">
                                        + Rs. {{ number_format(abs($row->difference), 2) }} (Over)
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-400">
                                        - Rs. {{ number_format(abs($row->difference), 2) }} (Short)
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                {{ $row->closedBy->name ?? 'System' }}
                            </td>
                            <td class="p-4 text-xs text-slate-500 dark:text-slate-400 max-w-xs truncate" title="{{ $row->note }}">
                                {{ $row->note ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-400">
                                No past daily closing records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($history->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $history->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
