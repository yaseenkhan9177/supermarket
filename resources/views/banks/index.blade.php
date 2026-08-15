@extends('layouts.admin')

@section('title', 'Banks & Cash Accounts - Accounts')
@section('navbar_subtitle', 'Bank & Cash Management')

@section('content')
<div x-data="bankPage()">

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
            <a href="{{ route('banks.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-indigo-600 text-white shadow-md shadow-indigo-600/30">
                <i class="fas fa-university"></i> Banks & Cash
            </a>
            <a href="{{ route('values.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800">
                <i class="fas fa-chart-line"></i> Values
            </a>
        </div>
        <button @click="openModal()" class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-500/20 transition transform active:scale-95 flex items-center gap-2">
            <i class="fas fa-plus-circle"></i> Add Account / Wallet
        </button>
    </div>

    {{-- HEADER & SUMMARY CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl group-hover:bg-emerald-500/20 transition"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-sm">
                    <i class="fas fa-money-check-alt"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Liquid Funds</span>
            </div>
            <div class="text-2xl font-extrabold text-emerald-400 tracking-tight">Rs. {{ number_format($totalFunds, 2) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Combined cash, bank & wallet funds</span>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/10 rounded-full blur-xl group-hover:bg-blue-500/20 transition"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-blue-500/20 text-blue-400 border border-blue-500/30 flex items-center justify-center text-sm">
                    <i class="fas fa-cash-register"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Cash in Hand</span>
            </div>
            <div class="text-2xl font-extrabold text-blue-400 tracking-tight">Rs. {{ number_format($totalCash, 2) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Shop counter & drawer balances</span>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-purple-500/10 rounded-full blur-xl group-hover:bg-purple-500/20 transition"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-purple-500/20 text-purple-400 border border-purple-500/30 flex items-center justify-center text-sm">
                    <i class="fas fa-university"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Bank & Wallets</span>
            </div>
            <div class="text-2xl font-extrabold text-purple-400 tracking-tight">Rs. {{ number_format($totalBank, 2) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Bank accounts & digital wallets</span>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-500/10 rounded-full blur-xl group-hover:bg-indigo-500/20 transition"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center text-sm">
                    <i class="fas fa-toggle-on"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Accounts</span>
            </div>
            <div class="text-2xl font-extrabold text-white tracking-tight">{{ number_format($activeCount) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Active financial channels</span>
        </div>
    </div>

    {{-- SECTION TITLE & CARDS GRID --}}
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-base font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-wallet text-indigo-400"></i> Financial Accounts & Wallets
            </h2>
            <span class="text-xs text-slate-400">Connected Mart payment channels and balances</span>
        </div>
    </div>

    {{-- WALLET & BANK CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">

        {{-- WALLETS LOOP --}}
        @foreach($wallets as $wallet)
            <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xl hover:border-slate-700 transition group flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-lg shadow-inner
                            {{ $wallet->type == 'counter' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : '' }}
                            {{ $wallet->type == 'bank' ? 'bg-purple-500/20 text-purple-400 border border-purple-500/30' : '' }}
                            {{ $wallet->type == 'wallet' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : '' }}">
                            @if($wallet->type == 'counter')
                                <i class="fas fa-cash-register"></i>
                            @elseif($wallet->type == 'bank')
                                <i class="fas fa-university"></i>
                            @else
                                <i class="fas fa-mobile-alt"></i>
                            @endif
                        </div>
                        <span class="bg-slate-950 text-slate-400 text-[10px] font-mono px-2.5 py-1 rounded-full border border-slate-800 font-bold uppercase">
                            {{ $wallet->type }}
                        </span>
                    </div>

                    <h3 class="text-base font-extrabold text-white mb-1 tracking-tight">{{ $wallet->name }}</h3>
                    <p class="text-xs text-slate-400">{{ $wallet->account_number ?: 'Internal Wallet' }}</p>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-800">
                    <div class="flex justify-between items-end">
                        <div>
                            <span class="text-[10px] text-slate-500 uppercase font-bold block">Status</span>
                            @if($wallet->is_active ?? true)
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-400">
                                    <i class="fas fa-circle text-[7px]"></i> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-500">
                                    <i class="fas fa-circle text-[7px]"></i> Inactive
                                </span>
                            @endif
                        </div>

                        <div class="text-right">
                            <span class="text-[10px] text-slate-500 uppercase font-bold block">Current Balance</span>
                            <span class="text-xl font-extrabold font-mono tracking-tight {{ $wallet->balance < 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                                Rs. {{ number_format($wallet->balance, 2) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-800/80 flex justify-between items-center text-xs">
                        <a href="{{ route('general-ledger.index') }}" class="text-indigo-400 hover:text-indigo-300 font-bold flex items-center gap-1">
                            <i class="fas fa-book-open text-xs"></i> View Ledger
                        </a>
                        <span class="text-[10px] text-slate-500 font-mono">Wallet ID: #{{ $wallet->id }}</span>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- BANK ACCOUNTS LOOP --}}
        @foreach($bankAccounts as $bank)
            <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xl hover:border-slate-700 transition group flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-11 h-11 rounded-xl bg-purple-500/20 text-purple-400 border border-purple-500/30 flex items-center justify-center text-lg shadow-inner">
                            <i class="fas fa-landmark"></i>
                        </div>
                        <span class="bg-slate-950 text-slate-400 text-[10px] font-mono px-2 py-1 rounded-full border border-slate-800 font-bold">
                            GL: {{ $bank->gl_code }}
                        </span>
                    </div>

                    <h3 class="text-base font-extrabold text-white mb-1 tracking-tight">{{ $bank->account_title }}</h3>
                    <p class="text-xs text-slate-400 uppercase font-bold">{{ $bank->bank_name }}</p>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-800">
                    <div class="flex justify-between items-end">
                        <div>
                            <span class="text-[10px] text-slate-500 uppercase font-bold block">Account No</span>
                            <span class="text-xs font-mono text-slate-300">{{ $bank->account_number ?: 'N/A' }}</span>
                        </div>

                        <div class="text-right">
                            <span class="text-[10px] text-slate-500 uppercase font-bold block">Current Balance</span>
                            <span class="text-xl font-extrabold font-mono tracking-tight {{ $bank->current_balance < 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                                Rs. {{ number_format($bank->current_balance, 2) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-800/80 flex justify-between items-center text-xs">
                        <button @click="openModal({{ json_encode($bank) }})" class="text-slate-400 hover:text-white font-bold">
                            <i class="fas fa-edit mr-1"></i> Edit Info
                        </button>
                        <a href="{{ route('banks.show', $bank->id) }}" class="text-emerald-400 hover:text-emerald-300 font-bold flex items-center gap-1">
                            Statement <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

    {{-- ACCOUNTS & WALLETS DIRECTORY TABLE --}}
    <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden mb-12">
        <div class="bg-slate-950 px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="font-bold text-sm text-slate-200 flex items-center gap-2">
                <i class="fas fa-list-alt text-indigo-400"></i> Financial Channels Directory
            </h3>
            <span class="text-xs text-slate-500 font-mono">Total {{ count($wallets) + count($bankAccounts) }} Channels</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="p-4">Account Name</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Account No / Code</th>
                        <th class="p-4 text-right">Current Balance (Rs)</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach($wallets as $w)
                        <tr class="hover:bg-slate-800/50 transition">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-indigo-400">
                                        @if($w->type == 'counter')
                                            <i class="fas fa-cash-register text-xs text-blue-400"></i>
                                        @elseif($w->type == 'bank')
                                            <i class="fas fa-university text-xs text-purple-400"></i>
                                        @else
                                            <i class="fas fa-mobile-alt text-xs text-emerald-400"></i>
                                        @endif
                                    </div>
                                    <span class="font-bold text-white text-xs">{{ $w->name }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-xs font-mono uppercase text-slate-400">{{ $w->type }}</td>
                            <td class="p-4 text-xs font-mono text-slate-400">{{ $w->account_number ?: 'Internal Wallet' }}</td>
                            <td class="p-4 text-right font-mono font-bold text-xs {{ $w->balance < 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                                Rs. {{ number_format($w->balance, 2) }}
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    Active
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <a href="{{ route('general-ledger.index') }}" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-indigo-300 border border-slate-700 text-xs font-bold transition inline-flex items-center gap-1">
                                    <i class="fas fa-book-open text-xs"></i> Ledger
                                </a>
                            </td>
                        </tr>
                    @endforeach

                    @foreach($bankAccounts as $b)
                        <tr class="hover:bg-slate-800/50 transition">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-purple-400">
                                        <i class="fas fa-landmark text-xs"></i>
                                    </div>
                                    <span class="font-bold text-white text-xs">{{ $b->account_title }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-xs font-mono uppercase text-slate-400">Bank ({{ $b->bank_name }})</td>
                            <td class="p-4 text-xs font-mono text-slate-400">{{ $b->account_number ?: $b->gl_code }}</td>
                            <td class="p-4 text-right font-mono font-bold text-xs {{ $b->current_balance < 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                                Rs. {{ number_format($b->current_balance, 2) }}
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                    Active
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <a href="{{ route('banks.show', $b->id) }}" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-emerald-300 border border-slate-700 text-xs font-bold transition inline-flex items-center gap-1">
                                    Statement <i class="fas fa-arrow-right text-xs"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ADD / EDIT BANK ACCOUNT MODAL --}}
    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-zoomIn">
            
            <form action="{{ route('banks.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id" x-model="form.id">

                <div class="bg-slate-950 px-6 py-4 border-b border-slate-800 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center">
                            <i class="fas fa-university"></i>
                        </div>
                        <h3 class="font-bold text-white text-sm" x-text="form.id ? 'Edit Bank Account' : 'New Bank Account'"></h3>
                    </div>
                    <button type="button" @click="closeModal()" class="text-slate-500 hover:text-white transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4 text-xs text-slate-300">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Account Type *</label>
                        <select name="bank_name" x-model="form.bank_name" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:border-indigo-500 outline-none">
                            <option value="Internal">Internal Cash (Safe/Drawer)</option>
                            <option value="Meezan Bank">Meezan Bank</option>
                            <option value="HBL">HBL</option>
                            <option value="EasyPaisa">EasyPaisa</option>
                            <option value="Other">Other Bank / Wallet</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Account Title (Name) *</label>
                        <input type="text" name="account_title" x-model="form.account_title" placeholder="e.g. Main Safe Cash" required class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:border-indigo-500 outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">GL Code *</label>
                            <input type="text" name="gl_code" x-model="form.gl_code" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2.5 text-xs font-mono font-bold text-white focus:border-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Opening Balance (Rs)</label>
                            <input type="number" step="0.01" name="opening_balance" x-model="form.opening_balance" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2.5 text-xs font-mono text-emerald-400 focus:border-indigo-500 outline-none">
                        </div>
                    </div>

                    <div x-show="form.bank_name !== 'Internal'" class="bg-slate-950/60 p-4 rounded-xl border border-slate-800 space-y-3">
                        <h4 class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Bank Details</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Account No</label>
                                <input type="text" name="account_number" x-model="form.account_number" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-xs text-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Branch Code</label>
                                <input type="text" name="branch_code" x-model="form.branch_code" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-2 text-xs text-white">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-950 border-t border-slate-800 flex justify-end gap-3">
                    <button type="button" @click="closeModal()" class="px-5 py-2.5 rounded-xl border border-slate-700 text-slate-300 font-bold hover:bg-slate-800 text-xs">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-bold text-xs shadow-lg hover:from-indigo-600 hover:to-purple-700 transition flex items-center gap-2">
                        <i class="fas fa-save"></i> Save Account
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

    function bankPage() {
        return {
            isModalOpen: false,
            form: {
                id: '',
                account_title: '',
                bank_name: 'Internal',
                gl_code: '',
                opening_balance: 0,
                account_number: '',
                branch_code: ''
            },

            openModal(bank = null) {
                if (bank) {
                    this.form = { ...bank };
                } else {
                    let count = Number('{{ count($bankAccounts) }}');
                    this.form = {
                        id: '',
                        account_title: '',
                        bank_name: 'Internal',
                        gl_code: '01-00' + (count + 1),
                        opening_balance: 0,
                        account_number: '',
                        branch_code: ''
                    };
                }
                this.isModalOpen = true;
            },

            closeModal() {
                this.isModalOpen = false;
            }
        }
    }
</script>
@endsection