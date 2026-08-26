@extends('layouts.admin')

@section('title', 'Version History — Invoice #' . $sale->invoice_no)

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('sales.today') }}" class="hover:text-blue-600 transition">Sales</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="{{ route('sales.today') }}" class="hover:text-blue-600 transition">Invoices</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-slate-700 dark:text-slate-300 font-semibold">Audit & Version History</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white flex items-center gap-3">
                <i class="fas fa-history text-indigo-600"></i>
                History for Invoice #{{ $sale->invoice_no }}
                <span class="text-xs px-2.5 py-1 rounded-full font-bold uppercase tracking-wider
                    {{ $sale->status === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400' : 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400' }}">
                    {{ $sale->status ?? 'Active' }}
                </span>
            </h1>
        </div>

        <div class="flex items-center gap-2">
            @if($sale->status !== 'cancelled')
            <a href="{{ route('sales.edit', $sale->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-md transition flex items-center gap-2">
                <i class="fas fa-edit"></i> Edit Invoice
            </a>
            @endif
            <a href="{{ route('sales.print', $sale->id) }}" target="_blank" class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition flex items-center gap-2">
                <i class="fas fa-print text-blue-500"></i> Print Current
            </a>
        </div>
    </div>

    {{-- Current Invoice Snapshot Card --}}
    <div class="bg-white dark:bg-slate-800/80 rounded-2xl shadow-xl shadow-black/5 border border-slate-100 dark:border-slate-700/50 p-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-xs text-slate-400 uppercase font-semibold">Customer</p>
                <p class="font-bold text-slate-800 dark:text-white mt-0.5">{{ $sale->customer->name ?? $sale->customer_name ?? 'Walk-in Customer' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 uppercase font-semibold">Payment Mode</p>
                <p class="font-bold text-slate-800 dark:text-white mt-0.5">{{ $sale->payment_mode }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 uppercase font-semibold">Current Grand Total</p>
                <p class="font-bold text-emerald-600 dark:text-emerald-400 text-lg mt-0.5">Rs. {{ number_format($sale->grand_total, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400 uppercase font-semibold">Total Versions Recorded</p>
                <p class="font-bold text-indigo-600 dark:text-indigo-400 text-lg mt-0.5">{{ $versions->count() }} Version(s)</p>
            </div>
        </div>
    </div>

    {{-- Versions Timeline --}}
    <div class="space-y-6">
        <h2 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <i class="fas fa-code-branch text-indigo-500"></i> Version Changelog & Audit Trail
        </h2>

        @forelse($versions as $v)
        <div class="bg-white dark:bg-slate-800/80 rounded-2xl shadow-xl shadow-black/5 border border-slate-100 dark:border-slate-700/50 overflow-hidden">

            {{-- Version Header --}}
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-900/60 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-indigo-600 text-white font-black text-sm flex items-center justify-center shadow-md shadow-indigo-500/30">
                        V{{ $v->version_number }}
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                Version {{ $v->version_number }}
                                @if($loop->first)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400">Current</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300">View Only</span>
                                @endif
                            </h3>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider
                                {{ $v->action_type === 'created' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400' : ($v->action_type === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400' : 'bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-400') }}">
                                {{ $v->action_type }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            By <strong class="text-slate-700 dark:text-slate-300">{{ $v->user_name ?? ($v->user->name ?? 'Staff') }}</strong>
                            • {{ $v->created_at->format('d M, Y \a\t h:i A') }}
                            @if($v->ip_address)
                                <span class="opacity-60">(IP: {{ $v->ip_address }})</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if($loop->first && $sale->status !== 'cancelled')
                        <a href="{{ route('sales.edit', $sale->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                            <i class="fas fa-edit"></i> Edit Current Invoice
                        </a>
                    @endif
                    <a href="{{ route('sales.version.show', [$sale->id, $v->version_number]) }}" target="_blank"
                        class="bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                        <i class="fas fa-eye text-indigo-500"></i> View Snapshot (Read-Only)
                    </a>
                </div>
            </div>

            {{-- Version Body & Diff --}}
            <div class="p-6 space-y-4">
                {{-- Reason Box --}}
                @if($v->reason)
                <div class="p-3 bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40 rounded-xl text-xs text-amber-900 dark:text-amber-300 flex items-start gap-2">
                    <i class="fas fa-comment-dots text-amber-600 mt-0.5"></i>
                    <div>
                        <strong class="font-bold">Audit Reason / Note:</strong>
                        <span>{{ $v->reason }}</span>
                    </div>
                </div>
                @endif

                {{-- Detailed Changes Summary if edited --}}
                @if($v->action_type === 'edited' && !empty($v->changes_summary))
                    @php
                        $summary = $v->changes_summary;
                        $financial = $summary['financial'] ?? null;
                    @endphp

                    {{-- Financial Totals Delta --}}
                    @if($financial)
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 dark:bg-slate-900/50 p-4 rounded-xl text-xs">
                        <div>
                            <span class="text-slate-400">Subtotal:</span>
                            <p class="font-bold text-slate-700 dark:text-slate-300">
                                Rs. {{ number_format($financial['subtotal_before'] ?? 0, 2) }} →
                                <strong class="text-slate-900 dark:text-white">Rs. {{ number_format($financial['subtotal_after'] ?? 0, 2) }}</strong>
                            </p>
                        </div>
                        <div>
                            <span class="text-slate-400">Discount:</span>
                            <p class="font-bold text-slate-700 dark:text-slate-300">
                                Rs. {{ number_format($financial['discount_before'] ?? 0, 2) }} →
                                <strong class="text-slate-900 dark:text-white">Rs. {{ number_format($financial['discount_after'] ?? 0, 2) }}</strong>
                            </p>
                        </div>
                        <div>
                            <span class="text-slate-400">Grand Total:</span>
                            <p class="font-bold text-slate-700 dark:text-slate-300">
                                Rs. {{ number_format($financial['grand_total_before'] ?? 0, 2) }} →
                                <strong class="text-emerald-600 font-black">Rs. {{ number_format($financial['grand_total_after'] ?? 0, 2) }}</strong>
                            </p>
                        </div>
                        <div>
                            <span class="text-slate-400">Net Difference:</span>
                            <p class="font-black text-sm {{ ($financial['total_difference'] ?? 0) >= 0 ? 'text-blue-600' : 'text-amber-600' }}">
                                {{ ($financial['total_difference'] ?? 0) >= 0 ? '+' : '' }}Rs. {{ number_format($financial['total_difference'] ?? 0, 2) }}
                            </p>
                        </div>
                    </div>
                    @endif

                    {{-- Product Line Item Changes --}}
                    <div class="space-y-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Product & Quantity Changes</h4>

                        {{-- Added --}}
                        @if(!empty($summary['items_added']))
                            @foreach($summary['items_added'] as $add)
                            <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/30 rounded-lg text-xs flex justify-between items-center">
                                <span class="font-bold text-emerald-800 dark:text-emerald-300">
                                    <i class="fas fa-plus-circle mr-1"></i> Added: {{ $add['item_name'] }}
                                </span>
                                <span class="font-mono text-emerald-700 dark:text-emerald-400">
                                    {{ $add['qty'] }} pcs @ Rs. {{ number_format($add['rate'], 2) }} = Rs. {{ number_format($add['total'], 2) }}
                                </span>
                            </div>
                            @endforeach
                        @endif

                        {{-- Modified --}}
                        @if(!empty($summary['items_modified']))
                            @foreach($summary['items_modified'] as $mod)
                            <div class="p-2.5 bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-800/30 rounded-lg text-xs flex justify-between items-center">
                                <span class="font-bold text-blue-800 dark:text-blue-300">
                                    <i class="fas fa-exchange-alt mr-1"></i> Modified: {{ $mod['item_name'] }}
                                </span>
                                <span class="font-mono text-blue-700 dark:text-blue-400">
                                    Qty: <strong>{{ $mod['old_qty'] }} → {{ $mod['new_qty'] }}</strong> |
                                    Rate: Rs. {{ number_format($mod['old_rate'], 2) }} → Rs. {{ number_format($mod['new_rate'], 2) }}
                                </span>
                            </div>
                            @endforeach
                        @endif

                        {{-- Removed --}}
                        @if(!empty($summary['items_removed']))
                            @foreach($summary['items_removed'] as $rem)
                            <div class="p-2.5 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800/30 rounded-lg text-xs flex justify-between items-center">
                                <span class="font-bold text-red-800 dark:text-red-300">
                                    <i class="fas fa-minus-circle mr-1"></i> Removed: {{ $rem['item_name'] }}
                                </span>
                                <span class="font-mono text-red-700 dark:text-red-400">
                                    Returned {{ $rem['qty'] }} pcs to stock
                                </span>
                            </div>
                            @endforeach
                        @endif
                    </div>

                    {{-- Stock Movements Recorded --}}
                    @if(!empty($summary['stock_movements']))
                    <div class="space-y-1 pt-2 border-t border-slate-100 dark:border-slate-700">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Stock Ledger Movements</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                            @foreach($summary['stock_movements'] as $sm)
                            <div class="p-2 bg-slate-50 dark:bg-slate-900 rounded border border-slate-200 dark:border-slate-800 flex justify-between">
                                <span class="text-slate-700 dark:text-slate-300">{{ $sm['item_name'] }}</span>
                                <strong class="font-mono {{ $sm['net_change'] >= 0 ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ $sm['net_change'] >= 0 ? '+' : '' }}{{ $sm['net_change'] }} units
                                </strong>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                @elseif($v->action_type === 'created')
                    {{-- Created Snapshot Lines Table --}}
                    @php $items = $v->new_values['items'] ?? []; @endphp
                    <div class="overflow-x-auto rounded-xl border border-slate-100 dark:border-slate-800">
                        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                            <thead class="bg-slate-50 dark:bg-slate-900 font-bold uppercase text-slate-400">
                                <tr>
                                    <th class="py-2 px-3">Item</th>
                                    <th class="py-2 px-3 text-center">Qty</th>
                                    <th class="py-2 px-3 text-right">Rate</th>
                                    <th class="py-2 px-3 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($items as $it)
                                <tr>
                                    <td class="py-2 px-3 font-semibold text-slate-800 dark:text-slate-200">{{ $it['item_name'] }}</td>
                                    <td class="py-2 px-3 text-center">{{ $it['qty'] }}</td>
                                    <td class="py-2 px-3 text-right">Rs. {{ number_format($it['rate'], 2) }}</td>
                                    <td class="py-2 px-3 text-right font-bold">Rs. {{ number_format($it['total'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
        @empty
        <div class="p-8 text-center bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 text-slate-400">
            <i class="fas fa-history text-3xl mb-2 opacity-40"></i>
            <p>No historical versions recorded for this invoice yet.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
