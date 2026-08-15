@extends('layouts.admin')

@section('title', 'General Ledger - Accounts')
@section('navbar_subtitle', 'General Ledger (GL)')

@section('content')
<div x-data="glPage(@json($accounts))">

    {{-- ACCOUNTS MODULE SUB-NAVBAR --}}
    <div class="mb-6 bg-slate-900/80 backdrop-blur-md p-2 rounded-2xl border border-slate-800 shadow-lg flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-1 overflow-x-auto no-scrollbar py-1">
            <a href="{{ route('journals.create') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800">
                <i class="fas fa-book"></i> Journal
            </a>
            <a href="{{ route('general-ledger.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-indigo-600 text-white shadow-md shadow-indigo-600/30">
                <i class="fas fa-book-open"></i> General Ledger
            </a>
            <a href="{{ route('reports.accounts') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800">
                <i class="fas fa-wallet"></i> Accounts
            </a>
            <a href="{{ route('banks.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800">
                <i class="fas fa-university"></i> Banks & Cash
            </a>
            <a href="{{ route('values.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800">
                <i class="fas fa-chart-line"></i> Values
            </a>
        </div>
        <button @click="openModal()" class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-500/20 transition transform active:scale-95 flex items-center gap-2">
            <i class="fas fa-plus-circle"></i> Add GL Account
        </button>
    </div>

    {{-- HEADER & SUMMARY CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-500/10 rounded-full blur-xl group-hover:bg-indigo-500/20 transition"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center text-sm">
                    <i class="fas fa-user-tag"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Selected View</span>
            </div>
            <div class="text-lg font-extrabold text-white truncate tracking-tight">
                {{ $selectedAccount ? $selectedAccount->name : 'All Accounts' }}
            </div>
            <span class="text-[11px] text-slate-500 mt-1 block">
                {{ $selectedAccount ? 'Code: ' . $selectedAccount->gl_code : 'Showing full chart of accounts' }}
            </span>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl group-hover:bg-emerald-500/20 transition"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-sm">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Cash & Banks (01)</span>
            </div>
            <div class="text-2xl font-extrabold text-emerald-400 tracking-tight">Rs. {{ number_format($totalCash, 2) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Liquid cash & bank assets</span>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/10 rounded-full blur-xl group-hover:bg-blue-500/20 transition"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-blue-500/20 text-blue-400 border border-blue-500/30 flex items-center justify-center text-sm">
                    <i class="fas fa-boxes"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Inventory Assets (02)</span>
            </div>
            <div class="text-2xl font-extrabold text-blue-400 tracking-tight">Rs. {{ number_format($totalInventory, 2) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Total stock value balance</span>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-rose-500/10 rounded-full blur-xl group-hover:bg-rose-500/20 transition"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-rose-500/20 text-rose-400 border border-rose-500/30 flex items-center justify-center text-sm">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Expenses (50)</span>
            </div>
            <div class="text-2xl font-extrabold text-rose-400 tracking-tight">Rs. {{ number_format($totalExpenses, 2) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Operating expense balances</span>
        </div>
    </div>

    {{-- PROMINENT ACCOUNT SELECTOR & FILTERS --}}
    <div class="bg-slate-900 rounded-2xl border border-slate-800 p-4 shadow-xl mb-6">
        <form method="GET" action="{{ route('general-ledger.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <div class="lg:col-span-4">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Select Account</label>
                <select name="account_code" onchange="this.form.submit()" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white font-bold focus:border-indigo-500 outline-none">
                    <option value="">-- All Ledger Accounts --</option>
                    @if(count($accounts) > 0)
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->gl_code }}" {{ request('account_code') == $acc->gl_code ? 'selected' : '' }}>
                                {{ $acc->gl_code }} - {{ $acc->name }} (Rs. {{ number_format($acc->current_balance, 2) }})
                            </option>
                        @endforeach
                    @else
                        <option value="010000" {{ request('account_code') == '010000' ? 'selected' : '' }}>010000: CASH ACCOUNT / DRAWER</option>
                        <option value="010001" {{ request('account_code') == '010001' ? 'selected' : '' }}>010001: MAIN SAFE</option>
                        <option value="020001" {{ request('account_code') == '020001' ? 'selected' : '' }}>020001: MEEZAN BANK</option>
                    @endif
                </select>
            </div>

            <div class="lg:col-span-3 relative">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Search</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-2.5 text-slate-500 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search description, memo..." class="w-full bg-slate-950 border border-slate-700 rounded-xl pl-8 pr-3 py-2 text-xs text-white placeholder-slate-500 focus:border-indigo-500 outline-none">
                </div>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:border-indigo-500 outline-none">
            </div>

            <div class="lg:col-span-2">
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:border-indigo-500 outline-none">
            </div>

            <div class="lg:col-span-1 flex items-end gap-1 mt-4 sm:mt-0">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2 rounded-xl text-xs transition flex items-center justify-center" title="Apply Filters">
                    <i class="fas fa-filter"></i>
                </button>
                <a href="{{ route('general-ledger.index') }}" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold py-2 rounded-xl text-xs transition text-center flex items-center justify-center" title="Reset">
                    <i class="fas fa-redo-alt"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- MAIN SECTION: CHART OF ACCOUNTS & TRANSACTION LEDGER --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- LEFT COLUMN: CHART OF ACCOUNTS GROUPINGS --}}
        <div class="lg:col-span-4 space-y-4">
            <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
                <div class="bg-slate-950 px-5 py-4 border-b border-slate-800 flex justify-between items-center">
                    <h3 class="font-bold text-sm text-slate-200 flex items-center gap-2">
                        <i class="fas fa-sitemap text-indigo-400"></i> Chart of Accounts
                    </h3>
                    <span class="text-[10px] text-slate-500 font-mono">{{ count($accounts) }} Accounts</span>
                </div>

                <div class="p-4 space-y-5 max-h-[600px] overflow-y-auto custom-scrollbar">

                    {{-- 01: CASH/BANKS --}}
                    <div class="border-l-2 border-emerald-500 pl-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-xs text-white flex items-center gap-1.5">
                                <i class="fas fa-wallet text-emerald-400 text-xs"></i> 01: CASH/BANKS
                            </span>
                            <span class="text-[9px] bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded font-bold uppercase border border-emerald-500/20">ASSETS</span>
                        </div>
                        <div class="space-y-1.5">
                            <template x-for="acc in getAccounts('01')" :key="acc.gl_code">
                                <div @click="selectAccount(acc.gl_code)" 
                                    :class="selectedCode === acc.gl_code ? 'bg-indigo-600/20 border-indigo-500/50 text-white' : 'bg-slate-950/60 border-slate-800/60 text-slate-300 hover:bg-slate-800/50'"
                                    class="flex justify-between items-center p-2.5 rounded-xl cursor-pointer transition border group">
                                    <div class="flex items-center gap-2 truncate">
                                        <span class="font-mono text-[10px] text-slate-400 bg-slate-900 border border-slate-700 px-1.5 py-0.5 rounded" x-text="acc.gl_code"></span>
                                        <span class="font-medium text-xs truncate" x-text="acc.name"></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs font-bold text-emerald-400" x-text="'Rs. ' + parseFloat(acc.current_balance || 0).toLocaleString('en-US', {minimumFractionDigits: 2})"></span>
                                        <button type="button" @click.stop="editAccount(acc)" class="text-slate-500 hover:text-indigo-300 text-xs px-1" title="Edit Account">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <div x-show="getAccounts('01').length === 0" class="text-xs text-slate-500 italic p-2">No accounts in group.</div>
                        </div>
                    </div>

                    {{-- 02: INVENTORY ASSETS --}}
                    <div class="border-l-2 border-blue-500 pl-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-xs text-white flex items-center gap-1.5">
                                <i class="fas fa-boxes text-blue-400 text-xs"></i> 02: INVENTORY ASSETS
                            </span>
                            <span class="text-[9px] bg-blue-500/10 text-blue-400 px-2 py-0.5 rounded font-bold uppercase border border-blue-500/20">ASSETS</span>
                        </div>
                        <div class="space-y-1.5">
                            <template x-for="acc in getAccounts('02')" :key="acc.gl_code">
                                <div @click="selectAccount(acc.gl_code)" 
                                    :class="selectedCode === acc.gl_code ? 'bg-indigo-600/20 border-indigo-500/50 text-white' : 'bg-slate-950/60 border-slate-800/60 text-slate-300 hover:bg-slate-800/50'"
                                    class="flex justify-between items-center p-2.5 rounded-xl cursor-pointer transition border group">
                                    <div class="flex items-center gap-2 truncate">
                                        <span class="font-mono text-[10px] text-slate-400 bg-slate-900 border border-slate-700 px-1.5 py-0.5 rounded" x-text="acc.gl_code"></span>
                                        <span class="font-medium text-xs truncate" x-text="acc.name"></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs font-bold text-blue-400" x-text="'Rs. ' + parseFloat(acc.current_balance || 0).toLocaleString('en-US', {minimumFractionDigits: 2})"></span>
                                        <button type="button" @click.stop="editAccount(acc)" class="text-slate-500 hover:text-indigo-300 text-xs px-1" title="Edit Account">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <div x-show="getAccounts('02').length === 0" class="text-xs text-slate-500 italic p-2">No accounts in group.</div>
                        </div>
                    </div>

                    {{-- 50: OPERATING EXPENSES --}}
                    <div class="border-l-2 border-rose-500 pl-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-xs text-white flex items-center gap-1.5">
                                <i class="fas fa-file-invoice-dollar text-rose-400 text-xs"></i> 50: OPERATING EXPENSES
                            </span>
                            <span class="text-[9px] bg-rose-500/10 text-rose-400 px-2 py-0.5 rounded font-bold uppercase border border-rose-500/20">EXPENSES</span>
                        </div>
                        <div class="space-y-1.5">
                            <template x-for="acc in getAccounts('50')" :key="acc.gl_code">
                                <div @click="selectAccount(acc.gl_code)" 
                                    :class="selectedCode === acc.gl_code ? 'bg-indigo-600/20 border-indigo-500/50 text-white' : 'bg-slate-950/60 border-slate-800/60 text-slate-300 hover:bg-slate-800/50'"
                                    class="flex justify-between items-center p-2.5 rounded-xl cursor-pointer transition border group">
                                    <div class="flex items-center gap-2 truncate">
                                        <span class="font-mono text-[10px] text-slate-400 bg-slate-900 border border-slate-700 px-1.5 py-0.5 rounded" x-text="acc.gl_code"></span>
                                        <span class="font-medium text-xs truncate" x-text="acc.name"></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs font-bold text-rose-400" x-text="'Rs. ' + parseFloat(acc.current_balance || 0).toLocaleString('en-US', {minimumFractionDigits: 2})"></span>
                                        <button type="button" @click.stop="editAccount(acc)" class="text-slate-500 hover:text-indigo-300 text-xs px-1" title="Edit Account">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <div x-show="getAccounts('50').length === 0" class="text-xs text-slate-500 italic p-2">No accounts in group.</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: DETAILED LEDGER TABLE --}}
        <div class="lg:col-span-8">
            <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden">
                
                <div class="bg-slate-950 px-6 py-4 border-b border-slate-800 flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-sm text-white flex items-center gap-2">
                            <i class="fas fa-list-ol text-indigo-400"></i>
                            {{ $selectedAccount ? $selectedAccount->gl_code . ' - ' . $selectedAccount->name : 'Transaction Ledger' }}
                        </h3>
                        <span class="text-[11px] text-slate-400 mt-0.5 block">
                            {{ $selectedAccount ? 'Opening Balance: Rs. ' . number_format($selectedAccount->opening_balance ?? 0, 2) : 'Select an account to isolate ledger transactions' }}
                        </span>
                    </div>
                    @if($selectedAccount)
                        <span class="px-3 py-1 bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 text-xs font-bold rounded-full">
                            Current Balance: Rs. {{ number_format($selectedAccount->current_balance, 2) }}
                        </span>
                    @endif
                </div>

                {{-- OPENING BALANCE BANNER --}}
                @if($selectedAccount)
                    <div class="bg-slate-950/70 border-b border-slate-800/80 px-6 py-2.5 flex justify-between items-center text-xs font-bold text-slate-300">
                        <span class="flex items-center gap-2"><i class="fas fa-flag text-indigo-400"></i> OPENING BALANCE</span>
                        <span class="font-mono text-emerald-400">Rs. {{ number_format($selectedAccount->opening_balance ?? 0, 2) }}</span>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-300">
                        <thead class="bg-slate-950/80 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-800">
                            <tr>
                                <th class="p-4">Date</th>
                                <th class="p-4">Reference</th>
                                <th class="p-4">Account / Description</th>
                                <th class="p-4 text-right">Debit (Rs)</th>
                                <th class="p-4 text-right">Credit (Rs)</th>
                                <th class="p-4 text-right">Running Balance (Rs)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @php
                                $runningBalance = $selectedAccount ? (float)($selectedAccount->opening_balance ?? 0) : 0;
                            @endphp

                            @forelse($entries as $entry)
                                @php
                                    $dr = (float)($entry->debit ?? 0);
                                    $cr = (float)($entry->credit ?? 0);
                                    $runningBalance += ($dr - $cr);
                                @endphp
                                <tr class="hover:bg-slate-800/50 transition">
                                    <td class="p-4 whitespace-nowrap text-xs text-slate-300 font-mono">
                                        {{ $entry->journal ? \Carbon\Carbon::parse($entry->journal->date)->format('M d, Y') : \Carbon\Carbon::parse($entry->created_at)->format('M d, Y') }}
                                    </td>
                                    <td class="p-4 whitespace-nowrap font-mono text-xs font-bold text-indigo-400">
                                        {{ $entry->journal ? $entry->journal->journal_no : 'JV-' . $entry->id }}
                                    </td>
                                    <td class="p-4 text-xs text-slate-200">
                                        <span class="font-bold text-white block">{{ $entry->account_name ?: $entry->account_code }}</span>
                                        <span class="text-slate-400 text-[11px]">{{ $entry->description ?: ($entry->journal ? $entry->journal->memo : '-') }}</span>
                                    </td>
                                    <td class="p-4 text-right font-mono font-bold text-emerald-400">
                                        {{ $dr > 0 ? number_format($dr, 2) : '-' }}
                                    </td>
                                    <td class="p-4 text-right font-mono font-bold text-blue-400">
                                        {{ $cr > 0 ? number_format($cr, 2) : '-' }}
                                    </td>
                                    <td class="p-4 text-right font-mono font-bold text-white">
                                        {{ number_format($runningBalance, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-12 text-center">
                                        <div class="flex flex-col items-center justify-center opacity-60">
                                            <i class="fas fa-book-open text-4xl text-slate-600 mb-3"></i>
                                            <span class="text-slate-400 font-bold text-sm">No ledger transactions found</span>
                                            <span class="text-slate-500 text-xs mt-1">Select an account or adjust your date range/search filters.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        @if(count($entries) > 0)
                            <tfoot class="bg-slate-950 font-bold border-t border-slate-800 text-xs">
                                <tr>
                                    <td colspan="3" class="p-4 text-right uppercase text-slate-400 text-[10px]">Closing Balance</td>
                                    <td class="p-4 text-right font-mono text-emerald-400">Rs. {{ number_format($entries->sum('debit'), 2) }}</td>
                                    <td class="p-4 text-right font-mono text-blue-400">Rs. {{ number_format($entries->sum('credit'), 2) }}</td>
                                    <td class="p-4 text-right font-mono text-white text-sm">Rs. {{ number_format($runningBalance, 2) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                @if($entries->hasPages())
                    <div class="px-6 py-4 border-t border-slate-800 bg-slate-950">
                        {{ $entries->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ADD / EDIT GL ACCOUNT MODAL --}}
    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-zoomIn">
            
            <form action="{{ route('general-ledger.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id" x-model="form.id">

                <div class="bg-slate-950 px-6 py-4 border-b border-slate-800 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center">
                            <i class="fas fa-folder-plus"></i>
                        </div>
                        <h3 class="font-bold text-white text-sm" x-text="form.id ? 'Edit GL Account' : 'New GL Account'"></h3>
                    </div>
                    <button type="button" @click="closeModal()" class="text-slate-500 hover:text-white transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4 text-xs text-slate-300">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">GL Type / Group *</label>
                        <select name="gl_type" x-model="form.gl_type" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:border-indigo-500 outline-none">
                            <option value="01">01: CASH/BANKS (Asset)</option>
                            <option value="02">02: INVENTORY ASSETS (Asset)</option>
                            <option value="03">03: FIXED ASSETS (Asset)</option>
                            <option value="50">50: OPERATING EXPENSES (Expense)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-1">
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">GL Code *</label>
                            <input type="text" name="gl_code" x-model="form.gl_code" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2.5 text-xs font-mono font-bold text-white focus:border-indigo-500 outline-none">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Account Name *</label>
                            <input type="text" name="name" x-model="form.name" placeholder="e.g. Petty Cash" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:border-indigo-500 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Opening Balance (Rs)</label>
                        <input type="number" step="0.01" name="opening_balance" x-model="form.opening_balance" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2.5 text-xs font-mono text-emerald-400 focus:border-indigo-500 outline-none">
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-950 border-t border-slate-800 flex justify-end gap-3">
                    <button type="button" @click="closeModal()" class="px-5 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold hover:bg-slate-800 text-xs">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-bold text-xs shadow-lg hover:from-indigo-600 hover:to-purple-700 transition flex items-center gap-2">
                        <i class="fas fa-save"></i> Save GL Account
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sessionSuccess = "{{ session('success') }}";
        const sessionError = "{{ session('error') }}";

        if (sessionSuccess) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: sessionSuccess,
                confirmButtonColor: '#4f46e5',
                background: '#0f172a',
                color: '#fff'
            });
        }
        if (sessionError) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: sessionError,
                confirmButtonColor: '#4f46e5',
                background: '#0f172a',
                color: '#fff'
            });
        }
    });

    function glPage(initialAccounts) {
        return {
            isModalOpen: false,
            accounts: initialAccounts,
            selectedCode: "{{ request('account_code') }}",
            form: {
                id: '',
                gl_code: '',
                name: '',
                gl_type: '01',
                opening_balance: 0
            },

            getAccounts(type) {
                return this.accounts.filter(a => a.gl_type === type);
            },

            selectAccount(code) {
                window.location.href = "{{ route('general-ledger.index') }}?account_code=" + code;
            },

            editAccount(acc) {
                this.form = { ...acc };
                this.isModalOpen = true;
            },

            openModal() {
                this.form = {
                    id: '',
                    gl_code: '01-' + Math.floor(Math.random() * 900),
                    name: '',
                    gl_type: '01',
                    opening_balance: 0
                };
                this.isModalOpen = true;
            },

            closeModal() {
                this.isModalOpen = false;
            }
        }
    }
</script>
@endsection