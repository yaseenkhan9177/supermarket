@extends('layouts.admin')

@section('title', 'Values & Financial Overview - Accounts')
@section('navbar_subtitle', 'Financial Overview & Audit')

@section('content')
<div x-data="valueSearch()">

    {{-- ACCOUNTS MODULE SUB-NAVBAR --}}
    <div class="mb-6 bg-slate-900/80 backdrop-blur-md p-2 rounded-2xl border border-slate-800 shadow-lg flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-1 overflow-x-auto no-scrollbar py-1">
            <a href="{{ route('journals.create') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800">
                <i class="fas fa-book"></i> Journal
            </a>
            <a href="{{ route('general-ledger.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800">
                <i class="fas fa-book-open"></i> General Ledger
            </a>
            <a href="{{ route('accounts.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800">
                <i class="fas fa-wallet"></i> Accounts
            </a>
            <a href="{{ route('banks.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800">
                <i class="fas fa-university"></i> Banks & Cash
            </a>
            <a href="{{ route('values.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-indigo-600 text-white shadow-md shadow-indigo-600/30">
                <i class="fas fa-chart-line"></i> Values
            </a>
        </div>
        <span class="text-xs font-bold text-slate-400 px-3 py-1 bg-slate-950 rounded-xl border border-slate-800 flex items-center gap-1.5">
            <i class="fas fa-search-dollar text-indigo-400"></i> Financial Values Dashboard
        </span>
    </div>

    {{-- HEADER & FINANCIAL OVERVIEW METRICS --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-4 shadow-xl relative overflow-hidden group">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Total Assets</span>
            <div class="text-lg font-extrabold text-emerald-400 tracking-tight font-mono">Rs. {{ number_format($totalAssets, 2) }}</div>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-4 shadow-xl relative overflow-hidden group">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Cash in Hand</span>
            <div class="text-lg font-extrabold text-blue-400 tracking-tight font-mono">Rs. {{ number_format($totalCash, 2) }}</div>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-4 shadow-xl relative overflow-hidden group">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Bank & Wallets</span>
            <div class="text-lg font-extrabold text-purple-400 tracking-tight font-mono">Rs. {{ number_format($totalBank, 2) }}</div>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-4 shadow-xl relative overflow-hidden group">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Receivables</span>
            <div class="text-lg font-extrabold text-amber-400 tracking-tight font-mono">Rs. {{ number_format($totalReceivables, 2) }}</div>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-4 shadow-xl relative overflow-hidden group">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Sales Income</span>
            <div class="text-lg font-extrabold text-indigo-400 tracking-tight font-mono">Rs. {{ number_format($totalSalesIncome, 2) }}</div>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-4 shadow-xl relative overflow-hidden group">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Expenses</span>
            <div class="text-lg font-extrabold text-rose-400 tracking-tight font-mono">Rs. {{ number_format($totalExpenses, 2) }}</div>
        </div>
    </div>

    {{-- VALUE SEARCH & AUDIT CONTROLS --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- LEFT COLUMN: SEARCH FORM --}}
        <div class="lg:col-span-4 space-y-6">
            <form @submit.prevent="performSearch" class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
                <div class="bg-slate-950 px-5 py-4 border-b border-slate-800 flex items-center gap-2">
                    <i class="fas fa-filter text-indigo-400"></i>
                    <h3 class="font-bold text-sm text-white">Value Search Criteria</h3>
                </div>

                <div class="p-5 space-y-5 text-xs text-slate-300">

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-3">Transaction Type</label>
                        <div class="space-y-1.5 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                            <label class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-950/60 hover:bg-slate-800/60 border border-slate-800 cursor-pointer transition">
                                <input type="radio" name="type" value="all" x-model="filters.type" class="text-indigo-600 focus:ring-indigo-500">
                                <span class="font-bold text-white">X. All Above (Global Search)</span>
                            </label>
                            <label class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-950/60 hover:bg-slate-800/60 border border-slate-800 cursor-pointer transition">
                                <input type="radio" name="type" value="sales" x-model="filters.type" class="text-indigo-600 focus:ring-indigo-500">
                                <span>1. Sales (Cash & Credit)</span>
                            </label>
                            <label class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-950/60 hover:bg-slate-800/60 border border-slate-800 cursor-pointer transition">
                                <input type="radio" name="type" value="purchases" x-model="filters.type" class="text-indigo-600 focus:ring-indigo-500">
                                <span>4. Purchases (Bills)</span>
                            </label>
                            <label class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-950/60 hover:bg-slate-800/60 border border-slate-800 cursor-pointer transition">
                                <input type="radio" name="type" value="payments" x-model="filters.type" class="text-indigo-600 focus:ring-indigo-500">
                                <span>9. Payment (Expenses)</span>
                            </label>
                            <label class="flex items-center gap-3 p-2.5 rounded-xl bg-slate-950/60 hover:bg-slate-800/60 border border-slate-800 cursor-pointer transition">
                                <input type="radio" name="type" value="receipts" x-model="filters.type" class="text-indigo-600 focus:ring-indigo-500">
                                <span>8. Receipt (Income)</span>
                            </label>
                        </div>
                    </div>

                    <hr class="border-slate-800">

                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <input type="checkbox" id="useDate" x-model="filters.useDate" class="rounded text-indigo-600">
                            <label for="useDate" class="text-xs font-bold text-slate-400 uppercase">Date Range Filter</label>
                        </div>
                        <div class="grid grid-cols-2 gap-3" x-show="filters.useDate" x-transition>
                            <div>
                                <label class="text-[10px] text-slate-500 font-bold block mb-1">From Date</label>
                                <input type="date" x-model="filters.dateFrom" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2 text-xs text-white outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-500 font-bold block mb-1">To Date</label>
                                <input type="date" x-model="filters.dateTo" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2 text-xs text-white outline-none focus:border-indigo-500">
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <input type="checkbox" id="useValue" x-model="filters.useValue" class="rounded text-indigo-600">
                            <label for="useValue" class="text-xs font-bold text-slate-400 uppercase">Value / Amount Range</label>
                        </div>
                        <div class="grid grid-cols-2 gap-3" x-show="filters.useValue" x-transition>
                            <div>
                                <label class="text-[10px] text-slate-500 font-bold block mb-1">Min Amount (Rs)</label>
                                <input type="number" step="0.01" x-model="filters.valLower" placeholder="0.00" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2 text-xs font-mono text-white outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-500 font-bold block mb-1">Max Amount (Rs)</label>
                                <input type="number" step="0.01" x-model="filters.valUpper" placeholder="Max" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2 text-xs font-mono text-white outline-none focus:border-indigo-500">
                            </div>
                        </div>
                    </div>

                    <button type="submit" :disabled="loading" class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-bold py-3 rounded-xl shadow-lg transition transform active:scale-95 flex justify-center items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed text-xs">
                        <i class="fas fa-search" x-show="!loading"></i>
                        <i class="fas fa-spinner fa-spin" x-show="loading" style="display: none;"></i>
                        <span x-text="loading ? 'Searching Transactions...' : 'Get Audit List'"></span>
                    </button>
                </div>
            </form>
        </div>

        {{-- RIGHT COLUMN: SEARCH RESULTS TABLE --}}
        <div class="lg:col-span-8">
            <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden min-h-[500px] flex flex-col">

                <div class="bg-slate-950 px-6 py-4 border-b border-slate-800 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-list text-indigo-400"></i>
                        <h3 class="font-bold text-sm text-white">Search Audit Results</h3>
                    </div>
                    <span class="bg-indigo-500/10 text-indigo-400 border border-indigo-500/30 text-xs font-mono font-bold px-3 py-1 rounded-full" x-text="results.length + ' Records Found'"></span>
                </div>

                <div class="flex-1 overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="bg-slate-950/80 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-800 sticky top-0">
                            <tr>
                                <th class="p-4 w-28">Date</th>
                                <th class="p-4 w-32">Ref #</th>
                                <th class="p-4 w-28">Type</th>
                                <th class="p-4">Description / Account</th>
                                <th class="p-4 w-36 text-right">Amount (Rs)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <template x-for="row in results" :key="row.ref + row.id">
                                <tr class="hover:bg-slate-800/50 transition">
                                    <td class="p-4 text-xs font-mono text-slate-300" x-text="formatDate(row.date)"></td>

                                    <td class="p-4 text-xs font-mono font-bold text-indigo-400" x-text="row.ref"></td>

                                    <td class="p-4">
                                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full uppercase border"
                                            :class="{
                                                'bg-emerald-500/10 text-emerald-400 border-emerald-500/20': row.type.includes('Sale') || row.type === 'Receipt',
                                                'bg-rose-500/10 text-rose-400 border-rose-500/20': row.type === 'Purchase' || row.type === 'Payment'
                                            }"
                                            x-text="row.type">
                                        </span>
                                    </td>

                                    <td class="p-4 text-xs font-medium text-white" x-text="row.description"></td>

                                    <td class="p-4 text-right font-mono font-bold text-xs" :class="row.type.includes('Sale') || row.type === 'Receipt' ? 'text-emerald-400' : 'text-rose-400'" x-text="'Rs. ' + Number(row.amount).toLocaleString('en-US', {minimumFractionDigits: 2})"></td>
                                </tr>
                            </template>

                            <tr x-show="!hasSearched">
                                <td colspan="5" class="p-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center opacity-60">
                                        <i class="fas fa-search-dollar text-4xl text-slate-600 mb-3"></i>
                                        <span class="text-slate-400 font-bold text-sm">Transaction Value Search</span>
                                        <span class="text-slate-500 text-xs mt-1">Select criteria and click "Get Audit List" to perform a multi-table search.</span>
                                    </div>
                                </td>
                            </tr>

                            <tr x-show="hasSearched && results.length === 0" style="display: none;">
                                <td colspan="5" class="p-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center opacity-60">
                                        <i class="fas fa-ghost text-4xl text-slate-600 mb-3"></i>
                                        <span class="text-slate-400 font-bold text-sm">No Records Found</span>
                                        <span class="text-slate-500 text-xs mt-1">No transactions match your specified filters and value limit criteria.</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="bg-slate-950 p-4 border-t border-slate-800 flex justify-between items-center">
                    <span class="text-xs text-slate-500">Value Audit Summary</span>
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-slate-500 uppercase block">Sum of Matching Transactions</span>
                        <span class="text-base font-extrabold font-mono text-indigo-400" x-text="'Rs. ' + totalSum"></span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
    function valueSearch() {
        return {
            loading: false,
            hasSearched: false,
            filters: {
                type: 'all',
                useDate: false,
                dateFrom: new Date().toISOString().slice(0, 10),
                dateTo: new Date().toISOString().slice(0, 10),
                useValue: false,
                valLower: '',
                valUpper: ''
            },
            results: [],

            get totalSum() {
                const sum = this.results.reduce((s, row) => s + (parseFloat(row.amount) || 0), 0);
                return sum.toLocaleString('en-US', { minimumFractionDigits: 2 });
            },

            formatDate(dateStr) {
                if (!dateStr) return '';
                return dateStr.substring(0, 10);
            },

            async performSearch() {
                this.loading = true;
                this.hasSearched = true;
                this.results = [];

                try {
                    const response = await fetch('/values/search', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.filters)
                    });

                    if (!response.ok) throw new Error('Search failed');

                    this.results = await response.json();
                } catch (error) {
                    console.error(error);
                    alert('Error fetching results. Please try again.');
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endsection