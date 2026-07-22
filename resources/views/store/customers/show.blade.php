@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="customerLedger()">

    {{-- ── TOAST NOTIFICATION ────────────────────────────────────────────── --}}
    <div x-show="toast.show" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2"
         class="fixed bottom-5 right-5 z-50 px-5 py-3 rounded-xl shadow-xl flex items-center gap-3 text-sm font-bold text-white"
         :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-red-600'" style="display: none;">
        <i class="fas" :class="toast.type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'"></i>
        <span x-text="toast.message"></span>
    </div>

    {{-- ── BREADCRUMB ─────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-8">
        <a href="{{ route('customers.index') }}"
           class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white font-semibold transition text-sm">
            <i class="fas fa-arrow-left"></i> Back to Customers
        </a>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 text-xs font-bold uppercase rounded-full
                {{ $customer->balance > 0 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300'
                : ($customer->balance < 0 ? 'bg-red-100 text-red-800 dark:bg-red-950/40 dark:text-red-300'
                : 'bg-gray-100 text-gray-700 dark:bg-slate-800 dark:text-slate-400') }}">
                {{ $customer->balance > 0 ? 'Has Debit Balance'
                   : ($customer->balance < 0 ? 'Overpaid' : 'Settled') }}
            </span>
            <a href="{{ route('customers.edit', $customer->id) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400 hover:dark:bg-indigo-900/60 text-xs font-bold rounded-lg transition">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this customer?');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-950/40 dark:text-red-400 hover:dark:bg-red-900/60 text-xs font-bold rounded-lg transition">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>

    {{-- ── PROFILE HEADER CARD & MONEY ACTIONS ───────────────────────────── --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm mb-8 relative overflow-hidden">
        {{-- accent bar --}}
        <div class="absolute top-0 left-0 h-full w-2 bg-indigo-600 rounded-l-3xl"></div>

        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 pl-2">
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white">{{ $customer->name }}</h1>
                    @if($customer->phone)
                        <span class="px-2.5 py-1 bg-purple-100 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 text-xs font-semibold rounded-full">
                            <i class="fas fa-phone mr-1"></i>{{ $customer->phone }}
                        </span>
                    @endif
                    {{-- Status badge --}}
                    @if($customer->status === 'written_off')
                        <span class="px-3 py-1 bg-red-600 text-white text-xs font-black uppercase rounded-full tracking-wider flex items-center gap-1.5 shadow">
                            <i class="fas fa-ban"></i> Written Off
                        </span>
                    @elseif($customer->status === 'deactivated')
                        <span class="px-3 py-1 bg-slate-400 text-white text-xs font-black uppercase rounded-full tracking-wider flex items-center gap-1.5">
                            <i class="fas fa-user-slash"></i> Deactivated
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
                        <h2 class="text-2xl font-black text-red-600 dark:text-red-400 mt-0.5" id="lbl-customer-balance">Rs. {{ number_format($customer->balance, 2) }}</h2>
                        <span class="text-xs text-red-500/80 mt-0.5">Customer owes us</span>
                    @elseif($customer->balance < 0)
                        <h2 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5" id="lbl-customer-balance">Rs. {{ number_format(abs($customer->balance), 2) }}</h2>
                        <span class="text-xs text-emerald-500/80 mt-0.5">We owe customer</span>
                    @else
                        <h2 class="text-2xl font-black text-slate-400 mt-0.5" id="lbl-customer-balance">Rs. 0.00</h2>
                        <span class="text-xs text-slate-400 mt-0.5">No balance outstanding</span>
                    @endif
                </div>

                {{-- Store Credit --}}
                <div class="flex flex-col">
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Store Credit</p>
                    <h2 class="text-2xl font-black {{ ($customer->store_credit ?? 0) > 0 ? 'text-violet-600 dark:text-violet-400' : 'text-slate-400' }} mt-0.5" id="lbl-customer-store-credit">
                        Rs. {{ number_format($customer->store_credit ?? 0, 2) }}
                    </h2>
                    <span class="text-xs text-slate-400 mt-0.5">Available credit</span>
                </div>
            </div>
        </div>

        {{-- Money Action Buttons (Gated to Admin / Owner) --}}
        @if($isAdmin)
            <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800 flex flex-wrap gap-3 items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500">
                    <i class="fas fa-user-shield text-indigo-500 mr-1"></i> Admin Financial Actions:
                </span>
                <div class="flex flex-wrap gap-2.5">
                    @if($customer->status !== 'written_off')
                        @if(($customer->balance ?? 0) > 0)
                            <button @click="openReceiveModal = true"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold rounded-xl shadow-sm transition transform active:scale-95">
                                <i class="fas fa-hand-holding-usd text-sm"></i> Receive Payment
                            </button>
                        @endif

                        @if(($customer->store_credit ?? 0) > 0)
                            <button @click="openPayModal = true"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white text-xs font-extrabold rounded-xl shadow-sm transition transform active:scale-95">
                                <i class="fas fa-paper-plane text-sm"></i> Pay Customer
                            </button>
                        @endif

                        <button @click="openAdjustModal = true"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white dark:bg-slate-700 dark:hover:bg-slate-600 text-xs font-extrabold rounded-xl shadow-sm transition transform active:scale-95">
                            <i class="fas fa-sliders-h text-sm"></i> Adjust Balance
                        </button>
                    @endif

                    {{-- Write Off / Reinstate --}}
                    @if($customer->status === 'written_off')
                        <button @click="openReinstateModal = true"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold rounded-xl shadow-sm transition transform active:scale-95">
                            <i class="fas fa-undo text-sm"></i> Reinstate Customer
                        </button>
                    @elseif($customer->status !== 'deactivated' && ($customer->balance ?? 0) > 0)
                        <button @click="openWriteOffModal = true"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-extrabold rounded-xl shadow-sm transition transform active:scale-95">
                            <i class="fas fa-ban text-sm"></i> Write Off Balance
                        </button>
                    @endif

                    {{-- Deactivate / Reactivate --}}
                    @if($customer->status !== 'written_off')
                        <form action="{{ route('customers.deactivate', $customer->id) }}" method="POST" class="inline"
                              onsubmit="return confirm('{{ $customer->status === 'deactivated' ? 'Reactivate this customer?' : 'Deactivate this customer? They will be hidden from the active list.' }}')">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2 {{ $customer->status === 'deactivated' ? 'bg-emerald-700 hover:bg-emerald-800' : 'bg-amber-600 hover:bg-amber-700' }} text-white text-xs font-extrabold rounded-xl shadow-sm transition transform active:scale-95">
                                <i class="fas {{ $customer->status === 'deactivated' ? 'fa-user-check' : 'fa-user-slash' }} text-sm"></i>
                                {{ $customer->status === 'deactivated' ? 'Reactivate' : 'Deactivate' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @else
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-right">
                <span class="text-xs italic text-slate-400">Balance is read-only (Admin privileges required to edit balance).</span>
            </div>
        @endif
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
    <div>

        {{-- Tab Navigation --}}
        <div class="flex items-center gap-1 mb-4 border-b border-slate-200 dark:border-slate-800 overflow-x-auto">
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
            {{-- 4th Tab: Customer Ledger --}}
            <button @click="activeTab = 'ledger'"
                :class="activeTab === 'ledger'
                    ? 'border-b-2 border-purple-600 text-purple-600 dark:text-purple-400'
                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white'"
                class="px-5 py-3 text-sm font-bold transition-colors whitespace-nowrap">
                <i class="fas fa-book-open mr-1.5"></i>
                Customer Ledger
                <span class="ml-1.5 text-xs px-1.5 py-0.5 rounded-full bg-purple-100 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 font-bold">
                    {{ $ledgerEntries->total() }}
                </span>
            </button>
        </div>

        {{-- ── TAB 1: CASH SALES ──────────────────────────────────────────── --}}
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

        {{-- ── TAB 2: DEBIT SALES ─────────────────────────────────────────── --}}
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

        {{-- ── TAB 3: RETURNS ─────────────────────────────────────────────── --}}
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

        {{-- ── TAB 4: CUSTOMER LEDGER (AUDIT TRAIL) ────────────────────────── --}}
        <div x-show="activeTab === 'ledger'" x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
                    <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-book-open text-purple-600"></i> Customer Ledger & Audit Trail
                    </h3>
                    <span class="text-xs font-medium text-slate-400">
                        Single Source of Truth for Balance History
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-bold text-xs">
                            <tr>
                                <th class="p-4">Date & Time</th>
                                <th class="p-4">Type</th>
                                <th class="p-4 text-right">Amount</th>
                                <th class="p-4 text-right">Running Balance</th>
                                <th class="p-4">Method</th>
                                <th class="p-4">Note / Reference</th>
                                <th class="p-4">Performed By</th>
                                @if($isAdmin)
                                    <th class="p-4 text-center">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($ledgerEntries as $entry)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="p-4 text-slate-600 dark:text-slate-400 text-xs font-medium whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($entry->created_at)->format('d M Y, h:i A') }}
                                </td>
                                <td class="p-4 whitespace-nowrap">
                                    @php
                                        $typeBadges = [
                                            'sale'              => ['label' => 'Sale',              'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300'],
                                            'return'            => ['label' => 'Return',            'class' => 'bg-orange-100 text-orange-800 dark:bg-orange-950/40 dark:text-orange-300'],
                                            'payment_received'  => ['label' => 'Payment Received',  'class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300'],
                                            'payment_made'      => ['label' => 'Payout (Made)',     'class' => 'bg-teal-100 text-teal-800 dark:bg-teal-950/40 dark:text-teal-300'],
                                            'manual_adjustment' => ['label' => 'Adjustment',        'class' => 'bg-purple-100 text-purple-800 dark:bg-purple-950/40 dark:text-purple-300'],
                                            'write_off'         => ['label' => 'Write Off',         'class' => 'bg-red-100 text-red-800 dark:bg-red-950/40 dark:text-red-400'],
                                            'write_off_reversal'=> ['label' => 'Write Off Reversed','class' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/40 dark:text-indigo-300'],
                                            'payment_reversal'  => ['label' => 'Reversed',          'class' => 'bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-400'],
                                        ];
                                        $badge = $typeBadges[$entry->type] ?? ['label' => $entry->type, 'class' => 'bg-slate-100 text-slate-700'];
                                        $isReversible = in_array($entry->type, ['payment_received', 'payment_made']);
                                        $hasReversal  = $entry->reversal !== null;
                                    @endphp
                                    <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $badge['class'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                    @if($hasReversal)
                                        <span class="ml-1 px-2 py-0.5 text-[10px] font-bold rounded-full bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-500">reversed</span>
                                    @endif
                                </td>
                                <td class="p-4 text-right font-black whitespace-nowrap
                                    {{ $entry->amount > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                    {{ $entry->amount > 0 ? '+ Rs. ' . number_format($entry->amount, 2) : '- Rs. ' . number_format(abs($entry->amount), 2) }}
                                </td>
                                <td class="p-4 text-right font-mono font-bold text-slate-800 dark:text-white whitespace-nowrap">
                                    Rs. {{ number_format($entry->balance_after, 2) }}
                                </td>
                                <td class="p-4 text-slate-600 dark:text-slate-400 text-xs font-semibold capitalize whitespace-nowrap">
                                    {{ $entry->method ? str_replace('_', ' ', $entry->method) : '—' }}
                                </td>
                                <td class="p-4 text-slate-700 dark:text-slate-300 text-xs max-w-xs truncate">
                                    {{ $entry->note ?: '—' }}
                                </td>
                                <td class="p-4 text-slate-500 dark:text-slate-400 text-xs whitespace-nowrap">
                                    <i class="fas fa-user-circle text-slate-400 mr-1"></i>
                                    {{ $entry->creator->name ?? 'System' }}
                                </td>
                                {{-- Actions: View Receipt + Reverse Button --}}
                                <td class="p-4 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1 justify-center">
                                        @if($entry->type === 'payment_received' && $entry->receipt)
                                            <a href="{{ route('customer.receipts.show', $entry->receipt->id) }}" target="_blank"
                                               class="inline-flex items-center gap-1 text-[10px] px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-600 hover:text-emerald-800 dark:bg-emerald-950/40 dark:hover:bg-emerald-950/60 dark:text-emerald-400 font-bold rounded-lg transition"
                                               title="View / Print Payment Receipt">
                                                <i class="fas fa-receipt"></i> View Receipt
                                            </a>
                                        @endif

                                        @if($isAdmin && $isReversible && !$hasReversal)
                                            <button @click="openReverseModal({{ $entry->id }})" title="Reverse this entry"
                                                    class="inline-flex items-center gap-1 text-[10px] px-2.5 py-1 bg-gray-100 hover:bg-red-100 text-gray-500 hover:text-red-700 dark:bg-slate-800 dark:hover:bg-red-950/40 dark:text-slate-400 dark:hover:text-red-400 font-bold rounded-lg transition">
                                                <i class="fas fa-undo"></i> Reverse
                                            </button>
                                        @elseif(!($entry->type === 'payment_received' && $entry->receipt))
                                            <span class="text-slate-300 dark:text-slate-700 text-xs">—</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 8 : 7 }}" class="p-10 text-center text-slate-400 dark:text-slate-500">
                                    <i class="fas fa-book-open text-3xl mb-2 block opacity-30"></i>
                                    No ledger entries recorded for this customer yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($ledgerEntries->hasPages())
                    <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                        {{ $ledgerEntries->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>{{-- /x-data tabs --}}

    {{-- ── ADMIN MODALS ────────────────────────────────────────────────────── --}}
    @if($isAdmin)

        {{-- 1. RECEIVE PAYMENT MODAL --}}
        <div x-show="openReceiveModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="openReceiveModal = false"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 max-w-md w-full p-6 md:p-8 relative z-10 transform transition-all" @click.stop>
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-xl font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="fas fa-hand-holding-usd text-emerald-600"></i> Receive Payment
                        </h3>
                        <button @click="openReceiveModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form @submit.prevent="submitReceive">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Payment Amount (Rs.)</label>
                                <input type="number" step="0.01" min="0.01" x-model="receiveData.amount" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white font-bold text-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                <span class="text-xs text-slate-400 mt-1 block">Current Debit Balance: Rs. {{ number_format($customer->balance, 2) }}</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Payment Method</label>
                                <select x-model="receiveData.method" required
                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white font-semibold text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                                    <option value="cash">Cash</option>
                                    <option value="bank">Bank Transfer</option>
                                    <option value="easypaisa">EasyPaisa</option>
                                    <option value="jazzcash">JazzCash</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Date</label>
                                <input type="date" x-model="receiveData.date"
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white font-semibold text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Note (Optional)</label>
                                <textarea x-model="receiveData.note" rows="2" placeholder="e.g. Received via cash counter"
                                          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white font-normal text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3">
                            <button type="button" @click="openReceiveModal = false"
                                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition">
                                Cancel
                            </button>
                            <button type="submit" :disabled="loading"
                                    class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin" x-show="loading" style="display: none;"></i>
                                Confirm Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 2. PAY CUSTOMER MODAL --}}
        <div x-show="openPayModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="openPayModal = false"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 max-w-md w-full p-6 md:p-8 relative z-10 transform transition-all" @click.stop>
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-xl font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="fas fa-paper-plane text-violet-600"></i> Pay Customer (Store Credit)
                        </h3>
                        <button @click="openPayModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form @submit.prevent="submitPay">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Payout Amount (Rs.)</label>
                                <input type="number" step="0.01" min="0.01" x-model="payData.amount" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white font-bold text-lg focus:ring-2 focus:ring-violet-500 focus:outline-none">
                                <span class="text-xs text-slate-400 mt-1 block">Available Store Credit: Rs. {{ number_format($customer->store_credit ?? 0, 2) }}</span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Payout Method</label>
                                <select x-model="payData.method" required
                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white font-semibold text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                                    <option value="cash">Cash</option>
                                    <option value="bank">Bank Transfer</option>
                                    <option value="easypaisa">EasyPaisa</option>
                                    <option value="jazzcash">JazzCash</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Date</label>
                                <input type="date" x-model="payData.date"
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white font-semibold text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Note (Optional)</label>
                                <textarea x-model="payData.note" rows="2" placeholder="e.g. Refund payout to customer"
                                          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white font-normal text-sm focus:ring-2 focus:ring-violet-500 focus:outline-none"></textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3">
                            <button type="button" @click="openPayModal = false"
                                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition">
                                Cancel
                            </button>
                            <button type="submit" :disabled="loading"
                                    class="px-5 py-2 bg-violet-600 hover:bg-violet-700 text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin" x-show="loading" style="display: none;"></i>
                                Confirm Payout
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 3. ADJUST BALANCE MODAL --}}
        <div x-show="openAdjustModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="openAdjustModal = false"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 max-w-md w-full p-6 md:p-8 relative z-10 transform transition-all" @click.stop>
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-xl font-extrabold text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="fas fa-sliders-h text-purple-600"></i> Adjust Customer Balance
                        </h3>
                        <button @click="openAdjustModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form @submit.prevent="submitAdjust">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Adjustment Action</label>
                                <select x-model="adjustData.action" required
                                        class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white font-semibold text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                                    <option value="reduce_debt">Decrease Customer Debt (-) [Discount / Waiver]</option>
                                    <option value="add_debt">Increase Customer Debt (+) [Manual Charge]</option>
                                    <option value="set_balance">Set Fixed Debit Balance (=)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Amount (Rs.)</label>
                                <input type="number" step="0.01" min="0" x-model="adjustData.amount" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white font-bold text-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">
                                    Reason / Note <span class="text-red-500">* (Required)</span>
                                </label>
                                <textarea x-model="adjustData.note" rows="3" required placeholder="State reason for manual adjustment (e.g. Approved discount / bad debt waiver)"
                                          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white font-normal text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none"></textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3">
                            <button type="button" @click="openAdjustModal = false"
                                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition">
                                Cancel
                            </button>
                            <button type="submit" :disabled="loading"
                                    class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin" x-show="loading" style="display: none;"></i>
                                Save Adjustment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 4. WRITE OFF MODAL --}}
        <div x-show="openWriteOffModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="openWriteOffModal = false"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-red-200 dark:border-red-900 max-w-md w-full p-6 md:p-8 relative z-10" @click.stop>
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-xl font-extrabold text-red-700 dark:text-red-400 flex items-center gap-2">
                            <i class="fas fa-ban"></i> Write Off Balance
                        </h3>
                        <button @click="openWriteOffModal = false" class="text-slate-400 hover:text-slate-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-5 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 rounded-xl p-3">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-1"></i>
                        This will set the customer balance to <strong>Rs. 0</strong> and mark them as <strong>Written Off</strong>. This action is logged and can be reversed later.
                    </p>
                    <form @submit.prevent="submitWriteOff">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Reason Category <span class="text-red-500">*</span></label>
                                <select x-model="writeOffData.reason_category" required
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500">
                                    <option value="">— Select Reason —</option>
                                    <option value="absconded">Absconded</option>
                                    <option value="deceased">Deceased</option>
                                    <option value="disputed">Disputed Debt</option>
                                    <option value="business_closed">Business Closed</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Note (min 10 characters) <span class="text-red-500">*</span></label>
                                <textarea x-model="writeOffData.note" rows="3" required minlength="10"
                                          placeholder="Provide a clear reason for writing off this balance..."
                                          class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                            </div>
                        </div>
                        <div class="mt-6 flex items-center justify-end gap-3">
                            <button type="button" @click="openWriteOffModal = false"
                                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition">
                                Cancel
                            </button>
                            <button type="submit" :disabled="loading"
                                    class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin" x-show="loading" style="display: none;"></i>
                                Confirm Write Off
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 5. REINSTATE MODAL --}}
        <div x-show="openReinstateModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="openReinstateModal = false"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-indigo-200 dark:border-indigo-900 max-w-md w-full p-6 md:p-8 relative z-10" @click.stop>
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-xl font-extrabold text-indigo-700 dark:text-indigo-400 flex items-center gap-2">
                            <i class="fas fa-undo"></i> Reinstate Customer
                        </h3>
                        <button @click="openReinstateModal = false" class="text-slate-400 hover:text-slate-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-5 bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-900 rounded-xl p-3">
                        <i class="fas fa-info-circle text-indigo-500 mr-1"></i>
                        This will restore the customer to <strong>Active</strong> status. The previous balance is not automatically restored — you may manually adjust it after reinstatement.
                    </p>
                    <form @submit.prevent="submitReinstate">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Reinstatement Note <span class="text-red-500">*</span></label>
                            <textarea x-model="reinstateData.note" rows="3" required minlength="3"
                                      placeholder="Reason for reinstatement..."
                                      class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                        <div class="mt-6 flex items-center justify-end gap-3">
                            <button type="button" @click="openReinstateModal = false"
                                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition">
                                Cancel
                            </button>
                            <button type="submit" :disabled="loading"
                                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin" x-show="loading" style="display: none;"></i>
                                Reinstate Customer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 6. REVERSE ENTRY MODAL --}}
        <div x-show="openReverseEntryModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="openReverseEntryModal = false"></div>
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-amber-200 dark:border-amber-900 max-w-md w-full p-6 md:p-8 relative z-10" @click.stop>
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-xl font-extrabold text-amber-700 dark:text-amber-400 flex items-center gap-2">
                            <i class="fas fa-undo"></i> Reverse Ledger Entry
                        </h3>
                        <button @click="openReverseEntryModal = false" class="text-slate-400 hover:text-slate-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-5 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900 rounded-xl p-3">
                        <i class="fas fa-exclamation-triangle text-amber-500 mr-1"></i>
                        An offsetting entry will be created and the customer balance adjusted accordingly. This cannot be undone.
                    </p>
                    <form @submit.prevent="submitReverse">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Reason for Reversal <span class="text-red-500">*</span></label>
                            <textarea x-model="reverseData.note" rows="3" required minlength="3"
                                      placeholder="Why is this entry being reversed?"
                                      class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500"></textarea>
                        </div>
                        <div class="mt-6 flex items-center justify-end gap-3">
                            <button type="button" @click="openReverseEntryModal = false"
                                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl transition">
                                Cancel
                            </button>
                            <button type="submit" :disabled="loading"
                                    class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin" x-show="loading" style="display: none;"></i>
                                Confirm Reversal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @endif

</div>

<script>
function customerLedger() {
    return {
        activeTab: '{{ request("ledger_page") ? "ledger" : "cash" }}',
        openReceiveModal: false,
        openPayModal: false,
        openAdjustModal: false,
        openWriteOffModal: false,
        openReinstateModal: false,
        openReverseEntryModal: false,

        receiveData: {
            amount: '{{ $customer->balance > 0 ? number_format($customer->balance, 2, ".", "") : 0 }}',
            method: 'cash',
            date: '{{ date("Y-m-d") }}',
            note: ''
        },
        payData: {
            amount: '{{ ($customer->store_credit ?? 0) > 0 ? number_format($customer->store_credit, 2, ".", "") : 0 }}',
            method: 'cash',
            date: '{{ date("Y-m-d") }}',
            note: ''
        },
        adjustData: {
            action: 'reduce_debt',
            amount: '',
            note: ''
        },
        writeOffData: {
            reason_category: '',
            note: ''
        },
        reinstateData: {
            note: ''
        },
        reverseData: {
            entryId: null,
            note: ''
        },

        loading: false,
        toast: { show: false, message: '', type: 'success' },

        showToast(msg, type = 'success') {
            this.toast.message = msg;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => { this.toast.show = false; }, 4000);
        },

        async submitReceive() {
            if (!this.receiveData.amount || this.receiveData.amount <= 0) {
                alert('Please enter a valid amount.');
                return;
            }
            this.loading = true;
            try {
                const res = await fetch('{{ route("customers.payments.receive", $customer->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.receiveData)
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.openReceiveModal = false;
                    this.showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    alert(data.message || 'Error processing payment.');
                }
            } catch (e) {
                alert('Network or server error.');
            } finally {
                this.loading = false;
            }
        },

        async submitPay() {
            if (!this.payData.amount || this.payData.amount <= 0) {
                alert('Please enter a valid amount.');
                return;
            }
            this.loading = true;
            try {
                const res = await fetch('{{ route("customers.payments.pay", $customer->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.payData)
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.openPayModal = false;
                    this.showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    alert(data.message || 'Error processing payout.');
                }
            } catch (e) {
                alert('Network or server error.');
            } finally {
                this.loading = false;
            }
        },

        async submitAdjust() {
            if (this.adjustData.amount === '' || this.adjustData.amount < 0) {
                alert('Please enter a valid non-negative amount.');
                return;
            }
            if (!this.adjustData.note || this.adjustData.note.trim().length < 3) {
                alert('A note explaining the adjustment is required.');
                return;
            }
            this.loading = true;
            try {
                const res = await fetch('{{ route("customers.adjust-balance", $customer->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.adjustData)
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.openAdjustModal = false;
                    this.showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    alert(data.message || 'Error adjusting balance.');
                }
            } catch (e) {
                alert('Network or server error.');
            } finally {
                this.loading = false;
            }
        },

        openReverseModal(entryId) {
            this.reverseData.entryId = entryId;
            this.reverseData.note = '';
            this.openReverseEntryModal = true;
        },

        async submitWriteOff() {
            if (!this.writeOffData.reason_category) {
                alert('Please select a reason category.');
                return;
            }
            if (!this.writeOffData.note || this.writeOffData.note.trim().length < 10) {
                alert('Please provide a note with at least 10 characters.');
                return;
            }
            this.loading = true;
            try {
                const res = await fetch('{{ route("customers.write-off", $customer->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.writeOffData)
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.openWriteOffModal = false;
                    this.showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    alert(data.message || 'Error processing write off.');
                }
            } catch (e) {
                alert('Network or server error.');
            } finally {
                this.loading = false;
            }
        },

        async submitReinstate() {
            if (!this.reinstateData.note || this.reinstateData.note.trim().length < 3) {
                alert('Please provide a reinstatement note.');
                return;
            }
            this.loading = true;
            try {
                const res = await fetch('{{ route("customers.reinstate", $customer->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.reinstateData)
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.openReinstateModal = false;
                    this.showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    alert(data.message || 'Error reinstating customer.');
                }
            } catch (e) {
                alert('Network or server error.');
            } finally {
                this.loading = false;
            }
        },

        async submitReverse() {
            if (!this.reverseData.note || this.reverseData.note.trim().length < 3) {
                alert('Please provide a reason for the reversal.');
                return;
            }
            this.loading = true;
            try {
                const reverseUrl = '{{ route("customers.ledger.reverse", [$customer->id, "__ENTRY_ID__"]) }}'.replace('__ENTRY_ID__', this.reverseData.entryId);
                const res = await fetch(reverseUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ note: this.reverseData.note })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.openReverseEntryModal = false;
                    this.showToast(data.message, 'success');
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    alert(data.message || 'Error reversing entry.');
                }
            } catch (e) {
                alert('Network or server error.');
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection
