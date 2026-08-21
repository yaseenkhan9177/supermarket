@extends('layouts.admin')

@section('title', 'Journal Vouchers - Accounts')
@section('navbar_subtitle', 'General Journal (JV)')

@section('content')
<div x-data="journalPage()">

    {{-- ACCOUNTS MODULE SUB-NAVBAR --}}
    <div class="mb-6 bg-slate-900/80 backdrop-blur-md p-2 rounded-2xl border border-slate-800 shadow-lg flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-1 overflow-x-auto no-scrollbar py-1">
            <a href="{{ route('journals.create') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-indigo-600 text-white shadow-md shadow-indigo-600/30">
                <i class="fas fa-book"></i> Journal
            </a>
            <a href="{{ route('general-ledger.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800">
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
        <button @click="showEntryModal = true" class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-500/20 transition transform active:scale-95 flex items-center gap-2">
            <i class="fas fa-plus-circle"></i> Post New Journal
        </button>
    </div>

    {{-- HEADER & METRICS --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-500/10 rounded-full blur-xl group-hover:bg-indigo-500/20 transition"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center text-sm">
                    <i class="fas fa-book"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Vouchers</span>
            </div>
            <div class="text-2xl font-extrabold text-white tracking-tight">{{ number_format($totalTransactions) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Recorded Journal Entries</span>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl group-hover:bg-emerald-500/20 transition"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-sm">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Debit</span>
            </div>
            <div class="text-2xl font-extrabold text-emerald-400 tracking-tight">Rs. {{ number_format($totalDebit, 2) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Debits across journals</span>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/10 rounded-full blur-xl group-hover:bg-blue-500/20 transition"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-blue-500/20 text-blue-400 border border-blue-500/30 flex items-center justify-center text-sm">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Credit</span>
            </div>
            <div class="text-2xl font-extrabold text-blue-400 tracking-tight">Rs. {{ number_format($totalCredit, 2) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Credits across journals</span>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-purple-500/10 rounded-full blur-xl group-hover:bg-purple-500/20 transition"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-purple-500/20 text-purple-400 border border-purple-500/30 flex items-center justify-center text-sm">
                    <i class="fas fa-balance-scale"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Ledger Integrity</span>
            </div>
            <div class="flex items-center gap-2 mt-1">
                @if(abs($totalDebit - $totalCredit) < 0.01)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-bold">
                        <i class="fas fa-check-circle"></i> Balanced
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 text-xs font-bold animate-pulse">
                        <i class="fas fa-exclamation-triangle"></i> Diff: {{ number_format(abs($totalDebit - $totalCredit), 2) }}
                    </span>
                @endif
            </div>
            <span class="text-[11px] text-slate-500 mt-2 block">Double-entry status</span>
        </div>
    </div>

    {{-- SEARCH & FILTER CONTROLS --}}
    <div class="bg-slate-900 rounded-2xl border border-slate-800 p-4 shadow-xl mb-6">
        <form method="GET" action="{{ route('journals.create') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <div class="lg:col-span-4 relative">
                <i class="fas fa-search absolute left-3.5 top-3 text-slate-500 text-sm"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search JV#, Memo..." class="w-full bg-slate-950 border border-slate-700 rounded-xl pl-9 pr-3 py-2 text-xs text-white placeholder-slate-500 focus:border-indigo-500 outline-none">
            </div>

            <div class="lg:col-span-2">
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:border-indigo-500 outline-none">
            </div>

            <div class="lg:col-span-2">
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:border-indigo-500 outline-none">
            </div>

            <div class="lg:col-span-3">
                <select name="account_code" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:border-indigo-500 outline-none">
                    <option value="">All Accounts</option>
                    @if(count($accounts) > 0)
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->gl_code }}" {{ request('account_code') == $acc->gl_code ? 'selected' : '' }}>
                                {{ $acc->gl_code }} - {{ $acc->name }} ({{ $acc->account_type }})
                            </option>
                        @endforeach
                    @else
                        <option value="1010" {{ request('account_code') == '1010' ? 'selected' : '' }}>1010 - Cash on Hand</option>
                        <option value="1020" {{ request('account_code') == '1020' ? 'selected' : '' }}>1020 - Bank - Meezan</option>
                        <option value="1200" {{ request('account_code') == '1200' ? 'selected' : '' }}>1200 - Accounts Receivable</option>
                        <option value="2010" {{ request('account_code') == '2010' ? 'selected' : '' }}>2010 - Accounts Payable</option>
                        <option value="5010" {{ request('account_code') == '5010' ? 'selected' : '' }}>5010 - Rent Expense</option>
                        <option value="5020" {{ request('account_code') == '5020' ? 'selected' : '' }}>5020 - Electricity Expense</option>
                    @endif
                </select>
            </div>

            <div class="lg:col-span-1 flex items-center gap-1">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2 rounded-xl text-xs shadow-md transition flex items-center justify-center" title="Apply Filter">
                    <i class="fas fa-filter"></i>
                </button>
                <a href="{{ route('journals.create') }}" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold py-2 rounded-xl text-xs transition text-center flex items-center justify-center" title="Reset Filters">
                    <i class="fas fa-redo-alt"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- JOURNAL TABLE --}}
    <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden mb-12">
        <div class="bg-slate-950 px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="font-bold text-sm text-slate-200 flex items-center gap-2">
                <i class="fas fa-list-alt text-indigo-400"></i> Journal History
            </h3>
            <span class="text-xs text-slate-500 font-mono">Showing {{ $journals->firstItem() ?? 0 }}-{{ $journals->lastItem() ?? 0 }} of {{ $journals->total() }}</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="p-4">Date</th>
                        <th class="p-4">Voucher No</th>
                        <th class="p-4">Memo / Description</th>
                        <th class="p-4">Postings</th>
                        <th class="p-4 text-right">Debit (Rs)</th>
                        <th class="p-4 text-right">Credit (Rs)</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($journals as $j)
                        <tr class="hover:bg-slate-800/50 transition group">
                            <td class="p-4 whitespace-nowrap text-xs text-slate-300 font-mono">
                                {{ \Carbon\Carbon::parse($j->date)->format('M d, Y') }}
                            </td>
                            <td class="p-4 whitespace-nowrap font-mono text-xs font-bold text-indigo-400">
                                {{ $j->journal_no }}
                            </td>
                            <td class="p-4 text-xs text-slate-200">
                                <span class="font-medium text-white">{{ $j->memo ?: 'General Journal Entry' }}</span>
                                @if($j->user)
                                    <span class="block text-[10px] text-slate-500 mt-0.5"><i class="fas fa-user text-[9px] mr-1"></i>By: {{ $j->user->name }}</span>
                                @endif
                            </td>
                            <td class="p-4 text-xs">
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @foreach($j->entries as $line)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono border {{ $line->debit > 0 ? 'bg-emerald-950/50 text-emerald-300 border-emerald-800/50' : 'bg-blue-950/50 text-blue-300 border-blue-800/50' }}">
                                            {{ $line->account_code }}: {{ $line->debit > 0 ? 'Dr' : 'Cr' }} {{ number_format($line->debit > 0 ? $line->debit : $line->credit, 2) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-emerald-400">
                                {{ number_format($j->total_debit, 2) }}
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-blue-400">
                                {{ number_format($j->total_credit, 2) }}
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    Posted
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <button @click="viewJournal({{ json_encode($j) }})" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-indigo-300 border border-slate-700 text-xs font-bold transition flex items-center gap-1 mx-auto">
                                    <i class="fas fa-eye text-xs"></i> View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-12 text-center">
                                <div class="flex flex-col items-center justify-center opacity-60">
                                    <i class="fas fa-book-open text-4xl text-slate-600 mb-3"></i>
                                    <span class="text-slate-400 font-bold text-sm">No journal vouchers found</span>
                                    <span class="text-slate-500 text-xs mt-1">Click "Post New Journal" to create your first voucher entry.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($journals->hasPages())
            <div class="px-6 py-4 border-t border-slate-800 bg-slate-950">
                {{ $journals->links() }}
            </div>
        @endif
    </div>

    {{-- POST NEW JOURNAL ENTRY MODAL --}}
    <div x-show="showEntryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display: none;">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden animate-zoomIn">
            
            <div class="bg-slate-950 px-6 py-4 border-b border-slate-800 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center">
                        <i class="fas fa-pen-alt"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">New Journal Voucher</h3>
                        <span class="text-xs text-slate-400">Record a double-entry journal transaction</span>
                    </div>
                </div>
                <button @click="showEntryModal = false" class="text-slate-500 hover:text-white transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <div class="p-6 overflow-y-auto flex-1 space-y-6 custom-scrollbar">
                <form id="journal-form" action="{{ route('journals.store') }}" method="POST" @submit.prevent="validateAndSubmit">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-950/60 p-4 rounded-xl border border-slate-800">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Journal Date *</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:border-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Reference / Memo</label>
                            <input type="text" name="memo" placeholder="e.g. Monthly Depreciation Adjustment" class="w-full bg-slate-900 border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:border-indigo-500 outline-none">
                        </div>
                    </div>

                    <div class="bg-slate-950/60 rounded-xl border border-slate-800 overflow-hidden">
                        <div class="p-3 bg-slate-950 border-b border-slate-800 flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-300 uppercase tracking-wider"><i class="fas fa-list mr-1 text-indigo-400"></i> Journal Lines</span>
                            <button type="button" @click="addRow()" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-bold transition flex items-center gap-1">
                                <i class="fas fa-plus"></i> Add Line
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-300">
                                <thead>
                                    <tr class="bg-slate-950 text-slate-400 uppercase text-[10px] font-bold border-b border-slate-800">
                                        <th class="p-3 w-10 text-center">#</th>
                                        <th class="p-3 w-64">Account *</th>
                                        <th class="p-3">Line Description</th>
                                        <th class="p-3 w-32 text-right">Debit (Rs)</th>
                                        <th class="p-3 w-32 text-right">Credit (Rs)</th>
                                        <th class="p-3 w-10 text-center"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800">
                                    <template x-for="(row, index) in rows" :key="index">
                                        <tr class="hover:bg-slate-900/50 transition">
                                            <td class="p-3 text-center text-slate-500 font-mono" x-text="index + 1"></td>
                                            
                                            <td class="p-3">
                                                <select :name="`entries[${index}][account_code]`" x-model="row.account_code" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-xs text-white focus:border-indigo-500 outline-none">
                                                    <option value="">-- Select Account --</option>
                                                    @if(count($accounts) > 0)
                                                        @foreach($accounts as $acc)
                                                            <option value="{{ $acc->gl_code }}">{{ $acc->gl_code }} - {{ $acc->name }} ({{ $acc->account_type }})</option>
                                                        @endforeach
                                                    @else
                                                        <optgroup label="Assets">
                                                            <option value="1010">1010 - Cash on Hand</option>
                                                            <option value="1020">1020 - Bank - Meezan</option>
                                                            <option value="1200">1200 - Accounts Receivable</option>
                                                        </optgroup>
                                                        <optgroup label="Liabilities">
                                                            <option value="2010">2010 - Accounts Payable</option>
                                                        </optgroup>
                                                        <optgroup label="Expenses">
                                                            <option value="5010">5010 - Rent Expense</option>
                                                            <option value="5020">5020 - Electricity Expense</option>
                                                        </optgroup>
                                                    @endif
                                                </select>
                                                <input type="hidden" :name="`entries[${index}][account_name]`" :value="getAccountName(row.account_code)">
                                            </td>

                                            <td class="p-3">
                                                <input type="text" :name="`entries[${index}][description]`" x-model="row.description" placeholder="Optional line memo..." class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-xs text-white focus:border-indigo-500 outline-none">
                                            </td>

                                            <td class="p-3">
                                                <input type="number" step="0.01" :name="`entries[${index}][debit]`" x-model="row.debit" @input="if(row.debit > 0) row.credit = 0" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-right text-xs font-mono text-emerald-400 focus:border-emerald-500 outline-none">
                                            </td>

                                            <td class="p-3">
                                                <input type="number" step="0.01" :name="`entries[${index}][credit]`" x-model="row.credit" @input="if(row.credit > 0) row.debit = 0" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-right text-xs font-mono text-blue-400 focus:border-blue-500 outline-none">
                                            </td>

                                            <td class="p-3 text-center">
                                                <button type="button" @click="removeRow(index)" class="text-slate-500 hover:text-rose-400 transition">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot class="bg-slate-950 font-bold border-t border-slate-800">
                                    <tr>
                                        <td colspan="3" class="p-3 text-right uppercase text-slate-400 text-[10px]">Total</td>
                                        <td class="p-3 text-right font-mono text-emerald-400 text-xs" x-text="totalDebit.toFixed(2)"></td>
                                        <td class="p-3 text-right font-mono text-blue-400 text-xs" x-text="totalCredit.toFixed(2)"></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div x-show="Math.abs(totalDebit - totalCredit) > 0.01" class="bg-rose-500/10 border border-rose-500/30 text-rose-400 px-4 py-3 rounded-xl text-xs flex items-center justify-between animate-pulse">
                        <span><i class="fas fa-exclamation-triangle mr-1"></i> Journal Unbalanced! Debit and Credit totals must match.</span>
                        <span class="font-mono font-bold">Diff: <span x-text="Math.abs(totalDebit - totalCredit).toFixed(2)"></span></span>
                    </div>
                </form>
            </div>

            <div class="px-6 py-4 bg-slate-950 border-t border-slate-800 flex justify-end gap-3">
                <button type="button" @click="showEntryModal = false" class="px-5 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold hover:bg-slate-800 text-xs">
                    Cancel
                </button>
                <button type="submit" form="journal-form"
                    :disabled="Math.abs(totalDebit - totalCredit) > 0.01 || totalDebit === 0"
                    :class="(Math.abs(totalDebit - totalCredit) > 0.01 || totalDebit === 0) ? 'bg-slate-800 text-slate-500 cursor-not-allowed' : 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg hover:from-indigo-600 hover:to-purple-700'"
                    class="px-6 py-2.5 rounded-xl font-bold text-xs transition flex items-center gap-2">
                    <i class="fas fa-check-circle"></i> Post Journal Entry
                </button>
            </div>
        </div>
    </div>

    {{-- VIEW JOURNAL DETAILS MODAL --}}
    <div x-show="showViewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" style="display: none;">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">
            <div class="bg-slate-950 px-6 py-4 border-b border-slate-800 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <i class="fas fa-file-invoice text-indigo-400"></i>
                    <h3 class="font-bold text-white text-sm" x-text="'Journal Voucher: ' + (activeJournal ? activeJournal.journal_no : '')"></h3>
                </div>
                <button @click="showViewModal = false" class="text-slate-500 hover:text-white transition"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="p-6 space-y-4 text-xs text-slate-300">
                <div class="grid grid-cols-2 gap-4 bg-slate-950/60 p-4 rounded-xl border border-slate-800">
                    <div>
                        <span class="text-slate-500 block text-[10px] uppercase font-bold">Date</span>
                        <span class="font-bold text-white" x-text="activeJournal ? activeJournal.date : ''"></span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px] uppercase font-bold">Memo / Reference</span>
                        <span class="font-bold text-white" x-text="activeJournal ? (activeJournal.memo || 'N/A') : ''"></span>
                    </div>
                </div>

                <div class="border border-slate-800 rounded-xl overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-950 text-slate-400 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-3">Account</th>
                                <th class="p-3">Description</th>
                                <th class="p-3 text-right">Debit</th>
                                <th class="p-3 text-right">Credit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            <template x-for="line in (activeJournal ? activeJournal.entries : [])" :key="line.id">
                                <tr class="hover:bg-slate-800/40">
                                    <td class="p-3">
                                        <span class="font-bold text-white font-mono" x-text="line.account_code"></span> - <span x-text="line.account_name"></span>
                                    </td>
                                    <td class="p-3 text-slate-400" x-text="line.description || '-'"></td>
                                    <td class="p-3 text-right font-mono font-bold text-emerald-400" x-text="parseFloat(line.debit) > 0 ? parseFloat(line.debit).toFixed(2) : '-'"></td>
                                    <td class="p-3 text-right font-mono font-bold text-blue-400" x-text="parseFloat(line.credit) > 0 ? parseFloat(line.credit).toFixed(2) : '-'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="px-6 py-3 bg-slate-950 border-t border-slate-800 text-right">
                <button @click="showViewModal = false" class="px-5 py-2 bg-slate-800 text-slate-300 rounded-xl font-bold text-xs hover:bg-slate-700">Close</button>
            </div>
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

    function journalPage() {
        return {
            showEntryModal: false,
            showViewModal: false,
            activeJournal: null,
            rows: [
                { account_code: '', description: '', debit: 0, credit: 0 },
                { account_code: '', description: '', debit: 0, credit: 0 }
            ],

            viewJournal(journal) {
                this.activeJournal = journal;
                this.showViewModal = true;
            },

            addRow() {
                this.rows.push({ account_code: '', description: '', debit: 0, credit: 0 });
            },

            removeRow(index) {
                if (this.rows.length > 1) {
                    this.rows.splice(index, 1);
                }
            },

            get totalDebit() {
                return this.rows.reduce((sum, row) => sum + (parseFloat(row.debit) || 0), 0);
            },

            get totalCredit() {
                return this.rows.reduce((sum, row) => sum + (parseFloat(row.credit) || 0), 0);
            },

            accountsMap: @json($accounts->pluck('name', 'gl_code')),
            getAccountName(code) {
                return this.accountsMap[code] || 'Ledger Account';
            },

            validateAndSubmit(e) {
                if (Math.abs(this.totalDebit - this.totalCredit) > 0.01) {
                    Swal.fire('Error', 'Journal is not balanced!', 'error');
                    return;
                }
                if (this.totalDebit === 0) {
                    Swal.fire('Error', 'Amounts cannot be zero', 'error');
                    return;
                }
                e.target.submit();
            }
        }
    }
</script>
@endsection