@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- ── BREADCRUMB ─────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-8">
        <a href="{{ route('customers.index') }}"
           class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white font-semibold transition text-sm">
            <i class="fas fa-arrow-left"></i> Back to Customers
        </a>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 text-xs font-bold uppercase rounded-full
                {{ $customer->balance > 0 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300'
                : ($customer->balance < 0 ? 'bg-red-100 text-red-800 dark:bg-red-950/40 dark:text-red-300'
                : 'bg-gray-100 text-gray-700 dark:bg-slate-800 dark:text-slate-400') }}">
                {{ $customer->balance > 0 ? 'Has Debit Balance'
                   : ($customer->balance < 0 ? 'Overpaid' : 'Settled') }}
            </span>
        </div>
    </div>

    {{-- ── PROFILE HEADER CARD ────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden">
        {{-- accent bar --}}
        <div class="absolute top-0 left-0 h-full w-2 bg-indigo-600 rounded-l-3xl"></div>
        <div class="pl-2">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white">{{ $customer->name }}</h1>
                @if($customer->phone)
                    <span class="px-2.5 py-1 bg-purple-100 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 text-xs font-semibold rounded-full">
                        <i class="fas fa-phone mr-1"></i>{{ $customer->phone }}
                    </span>
                @endif
            </div>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                <i class="fas fa-map-marker-alt mr-1"></i>{{ $customer->address ?: 'No address provided' }}
            </p>
            <p class="text-slate-400 dark:text-slate-500 text-xs mt-2">
                Member since {{ $customer->created_at->format('d M Y') }}
            </p>
        </div>

        {{-- Right side — balance + store credit --}}
        <div class="flex flex-col sm:flex-row gap-6 text-left sm:text-right">
            {{-- Debit Balance --}}
            <div class="flex flex-col">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Debit Balance</p>
                @if($customer->balance > 0)
                    <h2 class="text-2xl font-black text-red-600 dark:text-red-400 mt-0.5">Rs. {{ number_format($customer->balance, 2) }}</h2>
                    <span class="text-xs text-red-500/80 mt-0.5">Customer owes us</span>
                @elseif($customer->balance < 0)
                    <h2 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">Rs. {{ number_format(abs($customer->balance), 2) }}</h2>
                    <span class="text-xs text-emerald-500/80 mt-0.5">We owe customer</span>
                @else
                    <h2 class="text-2xl font-black text-slate-400 mt-0.5">Rs. 0.00</h2>
                    <span class="text-xs text-slate-400 mt-0.5">No balance outstanding</span>
                @endif
            </div>
            {{-- Store Credit --}}
            <div class="flex flex-col">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Store Credit</p>
                <h2 class="text-2xl font-black {{ ($customer->store_credit ?? 0) > 0 ? 'text-violet-600 dark:text-violet-400' : 'text-slate-400' }} mt-0.5">
                    Rs. {{ number_format($customer->store_credit ?? 0, 2) }}
                </h2>
                <span class="text-xs text-slate-400 mt-0.5">Available credit</span>
            </div>
        </div>
    </div>

    {{-- ── KPI DASHBOARD ───────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">

        {{-- Items Sold --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col justify-between">
            <div class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider mb-2">Items Sold</div>
            <div class="flex items-baseline justify-between">
                <span class="text-xl font-extrabold text-slate-800 dark:text-white">{{ number_format($totalItemsSold) }}</span>
                <span class="text-slate-300 dark:text-slate-700"><i class="fas fa-boxes text-base"></i></span>
            </div>
            <div class="text-slate-400 text-[10px] mt-1">Total units across all sales</div>
        </div>

        {{-- Cash Sales --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col justify-between">
            <div class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider mb-2">Cash Sales</div>
            <span class="text-xl font-extrabold text-slate-800 dark:text-white">Rs. {{ number_format($totalCashAmount) }}</span>
            <div class="text-slate-400 text-[10px] mt-1">{{ $totalCashCount }} {{ Str::plural('bill', $totalCashCount) }}</div>
        </div>

        {{-- Debit Sales --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col justify-between">
            <div class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider mb-2">Debit Sales</div>
            <span class="text-xl font-extrabold text-slate-800 dark:text-white">Rs. {{ number_format($totalDebitAmount) }}</span>
            <div class="text-slate-400 text-[10px] mt-1">{{ $totalDebitCount }} {{ Str::plural('invoice', $totalDebitCount) }}</div>
        </div>

        {{-- Total Returned --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col justify-between relative overflow-hidden">
            @if($totalRefundAmount > 0)
                <div class="absolute top-0 left-0 w-full h-0.5 bg-orange-400"></div>
            @endif
            <div class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider mb-2">Total Returned</div>
            <span class="text-xl font-extrabold {{ $totalRefundAmount > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-slate-400' }}">
                Rs. {{ number_format($totalRefundAmount) }}
            </span>
            <div class="text-slate-400 text-[10px] mt-1">{{ $totalRefundCount }} {{ Str::plural('return', $totalRefundCount) }}</div>
        </div>

        {{-- Outstanding Due --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm flex flex-col justify-between relative overflow-hidden">
            @if($outstandingAmount > 0)
                <div class="absolute top-0 left-0 w-full h-0.5 bg-red-500"></div>
            @endif
            <div class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider mb-2">Outstanding Due</div>
            <span class="text-xl font-extrabold {{ $outstandingAmount > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-400' }}">
                Rs. {{ number_format($outstandingAmount) }}
            </span>
            <div class="text-slate-400 text-[10px] mt-1">Unpaid debit balance</div>
        </div>

        {{-- Net Lifetime Value --}}
        <div class="bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-900 rounded-2xl p-4 shadow-sm flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-0.5 bg-indigo-500"></div>
            <div class="text-indigo-400 dark:text-indigo-500 text-[10px] font-bold uppercase tracking-wider mb-2">Net Lifetime Value</div>
            <span class="text-xl font-extrabold text-indigo-700 dark:text-indigo-300">
                Rs. {{ number_format($netLifetimeValue) }}
            </span>
            <div class="text-indigo-400 dark:text-indigo-600 text-[10px] mt-1">Sales minus returns</div>
        </div>

    </div>

    {{-- ── TABBED TRANSACTION HISTORY ──────────────────────────────────────── --}}
    <div x-data="{ activeTab: 'cash' }">

        {{-- Tab Navigation --}}
        <div class="flex items-center gap-1 mb-4 border-b border-slate-200 dark:border-slate-800">
            <button @click="activeTab = 'cash'"
                :class="activeTab === 'cash'
                    ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white'"
                class="px-5 py-3 text-sm font-bold transition-colors whitespace-nowrap">
                <i class="fas fa-money-bill-wave mr-1.5"></i>
                Cash Sales
                <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                    {{ $totalCashCount }}
                </span>
            </button>
            <button @click="activeTab = 'debit'"
                :class="activeTab === 'debit'
                    ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white'"
                class="px-5 py-3 text-sm font-bold transition-colors whitespace-nowrap">
                <i class="fas fa-file-invoice-dollar mr-1.5"></i>
                Debit Sales
                <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                    {{ $totalDebitCount }}
                </span>
            </button>
            <button @click="activeTab = 'returns'"
                :class="activeTab === 'returns'
                    ? 'border-b-2 border-orange-500 text-orange-600 dark:text-orange-400'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white'"
                class="px-5 py-3 text-sm font-bold transition-colors whitespace-nowrap">
                <i class="fas fa-undo mr-1.5"></i>
                Returns
                <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                    {{ $totalRefundCount }}
                </span>
            </button>
        </div>

        {{-- ── TAB: CASH SALES ──────────────────────────────────────────── --}}
        <div x-show="activeTab === 'cash'" x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-money-bill-wave text-green-500"></i> Cash Sale Invoices
                    </h3>
                    <span class="text-sm font-bold text-green-600 dark:text-green-400">
                        Total: Rs. {{ number_format($totalCashAmount, 2) }}
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-bold text-xs">
                            <tr>
                                <th class="p-4">Invoice No</th>
                                <th class="p-4">Date</th>
                                <th class="p-4 text-center">Items</th>
                                <th class="p-4 text-right">Total</th>
                                <th class="p-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($cashSales as $sale)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="p-4 font-mono font-bold text-slate-800 dark:text-white text-sm">
                                    {{ $sale->invoice_no ?: '—' }}
                                </td>
                                <td class="p-4 text-slate-600 dark:text-slate-400">
                                    {{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') }}
                                </td>
                                <td class="p-4 text-center font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $sale->items_count }}
                                </td>
                                <td class="p-4 text-right font-black text-slate-800 dark:text-white">
                                    Rs. {{ number_format($sale->grand_total, 2) }}
                                </td>
                                <td class="p-4 text-center">
                                    <a href="{{ route('cash-sales.show', $sale->id) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-indigo-500 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/30 dark:hover:bg-indigo-950/50 px-2.5 py-1.5 rounded-lg text-xs font-bold transition">
                                        <i class="fas fa-print"></i> Print
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-10 text-center text-slate-400 dark:text-slate-500">
                                    <i class="fas fa-receipt text-3xl mb-2 block opacity-30"></i>
                                    No cash sales recorded for this customer.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ── TAB: DEBIT SALES ─────────────────────────────────────────── --}}
        <div x-show="activeTab === 'debit'" x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-file-invoice-dollar text-amber-500"></i> Debit Sale Invoices
                    </h3>
                    <span class="text-sm font-bold text-amber-600 dark:text-amber-400">
                        Total: Rs. {{ number_format($totalDebitAmount, 2) }}
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-bold text-xs">
                            <tr>
                                <th class="p-4">Invoice No</th>
                                <th class="p-4">Date</th>
                                <th class="p-4">Due Date</th>
                                <th class="p-4 text-center">Items</th>
                                <th class="p-4 text-right">Net Total</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($debitSales as $sale)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="p-4 font-mono font-bold text-slate-800 dark:text-white text-sm">
                                    {{ $sale->invoice_no ?: '—' }}
                                </td>
                                <td class="p-4 text-slate-600 dark:text-slate-400">
                                    {{ \Carbon\Carbon::parse($sale->invoice_date)->format('d M Y') }}
                                </td>
                                <td class="p-4 text-slate-600 dark:text-slate-400">
                                    {{ $sale->due_date ? \Carbon\Carbon::parse($sale->due_date)->format('d M Y') : '—' }}
                                </td>
                                <td class="p-4 text-center font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $sale->items_count }}
                                </td>
                                <td class="p-4 text-right font-black text-slate-800 dark:text-white">
                                    Rs. {{ number_format($sale->net_total, 2) }}
                                </td>
                                <td class="p-4">
                                    @php
                                        $statusMap = [
                                            'open'    => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-950/30 dark:text-yellow-400',
                                            'paid'    => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
                                            'overdue' => 'bg-red-100 text-red-800 dark:bg-red-950/30 dark:text-red-400',
                                        ];
                                    @endphp
                                    <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $statusMap[$sale->status] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($sale->status) }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <a href="{{ route('debit-sales.show', $sale->id) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-indigo-500 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/30 dark:hover:bg-indigo-950/50 px-2.5 py-1.5 rounded-lg text-xs font-bold transition">
                                        <i class="fas fa-print"></i> Print
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="p-10 text-center text-slate-400 dark:text-slate-500">
                                    <i class="fas fa-file-invoice text-3xl mb-2 block opacity-30"></i>
                                    No debit sales recorded for this customer.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ── TAB: RETURNS ─────────────────────────────────────────────── --}}
        <div x-show="activeTab === 'returns'" x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-undo text-orange-500"></i> Return / Credit Notes
                    </h3>
                    <span class="text-sm font-bold text-orange-600 dark:text-orange-400">
                        Total Refunded: Rs. {{ number_format($totalRefundAmount, 2) }}
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-bold text-xs">
                            <tr>
                                <th class="p-4">Credit Note No</th>
                                <th class="p-4">Date</th>
                                <th class="p-4 text-center">Items</th>
                                <th class="p-4">Refund Method</th>
                                <th class="p-4 text-right">Amount</th>
                                <th class="p-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($refunds as $refund)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="p-4 font-mono font-bold text-slate-800 dark:text-white text-sm">
                                    {{ $refund->credit_no ?: '—' }}
                                </td>
                                <td class="p-4 text-slate-600 dark:text-slate-400">
                                    {{ \Carbon\Carbon::parse($refund->refund_date)->format('d M Y') }}
                                </td>
                                <td class="p-4 text-center font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $refund->items_count }}
                                </td>
                                <td class="p-4">
                                    @php
                                        $modeMap = [
                                            'CASH'            => ['label' => 'Cash',           'class' => 'bg-green-100 text-green-800 dark:bg-green-950/30 dark:text-green-400'],
                                            'STORE_CREDIT'    => ['label' => 'Store Credit',   'class' => 'bg-violet-100 text-violet-800 dark:bg-violet-950/30 dark:text-violet-400'],
                                            'REDUCE_DEBIT'    => ['label' => 'Reduce Debit',   'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/30 dark:text-blue-400'],
                                            'ORIGINAL_METHOD' => ['label' => 'Original Method','class' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300'],
                                        ];
                                        $mode = $modeMap[$refund->refund_mode] ?? ['label' => $refund->refund_mode, 'class' => 'bg-slate-100 text-slate-600'];
                                    @endphp
                                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full {{ $mode['class'] }}">
                                        {{ $mode['label'] }}
                                    </span>
                                </td>
                                <td class="p-4 text-right font-black text-orange-600 dark:text-orange-400">
                                    Rs. {{ number_format($refund->total_amount, 2) }}
                                </td>
                                <td class="p-4 text-center">
                                    <a href="{{ route('refunds.print', $refund->id) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-orange-500 hover:text-orange-700 bg-orange-50 hover:bg-orange-100 dark:bg-orange-950/20 dark:hover:bg-orange-950/40 px-2.5 py-1.5 rounded-lg text-xs font-bold transition">
                                        <i class="fas fa-print"></i> Print
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="p-10 text-center text-slate-400 dark:text-slate-500">
                                    <i class="fas fa-undo text-3xl mb-2 block opacity-30"></i>
                                    No returns recorded for this customer.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>{{-- /x-data tabs --}}

</div>
@endsection
