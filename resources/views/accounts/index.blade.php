@extends('layouts.admin')

@section('title', 'Accounts - Chart of Accounts')
@section('navbar_subtitle', 'Chart of Accounts')

@section('content')
<div x-data="accountsPage(@json($accounts))">

    {{-- ACCOUNTS MODULE SUB-NAVBAR --}}
    <div class="mb-6 bg-slate-900/80 backdrop-blur-md p-2 rounded-2xl border border-slate-800 shadow-lg flex flex-wrap items-center justify-between gap-2">
        <div class="flex items-center gap-1 overflow-x-auto no-scrollbar py-1">
            <a href="{{ route('journals.create') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800">
                <i class="fas fa-book"></i> Journal
            </a>
            <a href="{{ route('general-ledger.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800">
                <i class="fas fa-book-open"></i> General Ledger
            </a>
            <a href="{{ route('accounts.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-indigo-600 text-white shadow-md shadow-indigo-600/30">
                <i class="fas fa-wallet"></i> Accounts
            </a>
            <a href="{{ route('banks.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800">
                <i class="fas fa-university"></i> Banks & Cash
            </a>
            <a href="{{ route('values.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 text-slate-400 hover:text-white hover:bg-slate-800">
                <i class="fas fa-chart-line"></i> Values
            </a>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('accounts.import.show') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-xl border border-slate-700 transition flex items-center gap-2">
                <i class="fas fa-file-import"></i> Import CSV
            </a>
            <button @click="openModal()" class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-500/20 transition transform active:scale-95 flex items-center gap-2">
                <i class="fas fa-plus-circle"></i> Add Account
            </button>
        </div>
    </div>

    {{-- HEADER & METRICS --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-500/10 rounded-full blur-xl group-hover:bg-indigo-500/20 transition"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center text-sm">
                    <i class="fas fa-list-ol"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Accounts</span>
            </div>
            <div class="text-2xl font-extrabold text-white tracking-tight">{{ number_format($totalAccounts) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Chart of Accounts list</span>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl group-hover:bg-emerald-500/20 transition"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-sm">
                    <i class="fas fa-wallet"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Assets</span>
            </div>
            <div class="text-2xl font-extrabold text-emerald-400 tracking-tight">Rs. {{ number_format($totalAssets, 2) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Sum of Asset balances</span>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-rose-500/10 rounded-full blur-xl group-hover:bg-rose-500/20 transition"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-rose-500/20 text-rose-400 border border-rose-500/30 flex items-center justify-center text-sm">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Liabilities</span>
            </div>
            <div class="text-2xl font-extrabold text-rose-400 tracking-tight">Rs. {{ number_format($totalLiabilities, 2) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Sum of Liability balances</span>
        </div>

        <div class="bg-slate-900 rounded-2xl border border-slate-800 p-5 shadow-xl relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/10 rounded-full blur-xl group-hover:bg-blue-500/20 transition"></div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-xl bg-blue-500/20 text-blue-400 border border-blue-500/30 flex items-center justify-center text-sm">
                    <i class="fas fa-chart-line"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Net Value</span>
            </div>
            <div class="text-2xl font-extrabold text-blue-400 tracking-tight">Rs. {{ number_format($netEquity, 2) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Assets minus Liabilities</span>
        </div>
    </div>

    {{-- ACCOUNT TYPE CATEGORY CARDS --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
        <div @click="filterType('Asset')" class="bg-slate-900/90 border border-slate-800 hover:border-emerald-500/50 p-3.5 rounded-2xl cursor-pointer transition group">
            <div class="flex justify-between items-center mb-1">
                <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider">Asset</span>
                <span class="text-[10px] font-mono bg-slate-950 text-slate-400 px-2 py-0.5 rounded border border-slate-800" x-text="getTypeCount('Asset')"></span>
            </div>
            <div class="text-sm font-extrabold text-white font-mono" x-text="'Rs. ' + getTypeTotal('Asset')"></div>
        </div>

        <div @click="filterType('Liability')" class="bg-slate-900/90 border border-slate-800 hover:border-rose-500/50 p-3.5 rounded-2xl cursor-pointer transition group">
            <div class="flex justify-between items-center mb-1">
                <span class="text-[10px] font-bold text-rose-400 uppercase tracking-wider">Liability</span>
                <span class="text-[10px] font-mono bg-slate-950 text-slate-400 px-2 py-0.5 rounded border border-slate-800" x-text="getTypeCount('Liability')"></span>
            </div>
            <div class="text-sm font-extrabold text-white font-mono" x-text="'Rs. ' + getTypeTotal('Liability')"></div>
        </div>

        <div @click="filterType('Equity')" class="bg-slate-900/90 border border-slate-800 hover:border-blue-500/50 p-3.5 rounded-2xl cursor-pointer transition group">
            <div class="flex justify-between items-center mb-1">
                <span class="text-[10px] font-bold text-blue-400 uppercase tracking-wider">Equity</span>
                <span class="text-[10px] font-mono bg-slate-950 text-slate-400 px-2 py-0.5 rounded border border-slate-800" x-text="getTypeCount('Equity')"></span>
            </div>
            <div class="text-sm font-extrabold text-white font-mono" x-text="'Rs. ' + getTypeTotal('Equity')"></div>
        </div>

        <div @click="filterType('Income')" class="bg-slate-900/90 border border-slate-800 hover:border-purple-500/50 p-3.5 rounded-2xl cursor-pointer transition group">
            <div class="flex justify-between items-center mb-1">
                <span class="text-[10px] font-bold text-purple-400 uppercase tracking-wider">Income</span>
                <span class="text-[10px] font-mono bg-slate-950 text-slate-400 px-2 py-0.5 rounded border border-slate-800" x-text="getTypeCount('Income')"></span>
            </div>
            <div class="text-sm font-extrabold text-white font-mono" x-text="'Rs. ' + getTypeTotal('Income')"></div>
        </div>

        <div @click="filterType('Expense')" class="bg-slate-900/90 border border-slate-800 hover:border-amber-500/50 p-3.5 rounded-2xl cursor-pointer transition group">
            <div class="flex justify-between items-center mb-1">
                <span class="text-[10px] font-bold text-amber-400 uppercase tracking-wider">Expense</span>
                <span class="text-[10px] font-mono bg-slate-950 text-slate-400 px-2 py-0.5 rounded border border-slate-800" x-text="getTypeCount('Expense')"></span>
            </div>
            <div class="text-sm font-extrabold text-white font-mono" x-text="'Rs. ' + getTypeTotal('Expense')"></div>
        </div>
    </div>

    {{-- SEARCH & FILTER CONTROLS --}}
    <div class="bg-slate-900 rounded-2xl border border-slate-800 p-4 shadow-xl mb-6">
        <form method="GET" action="{{ route('accounts.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <div class="lg:col-span-6 relative">
                <i class="fas fa-search absolute left-3.5 top-3 text-slate-500 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search account name, GL code, category..." class="w-full bg-slate-950 border border-slate-700 rounded-xl pl-9 pr-3 py-2 text-xs text-white placeholder-slate-500 focus:border-indigo-500 outline-none">
            </div>

            <div class="lg:col-span-4">
                <select name="type" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white font-bold focus:border-indigo-500 outline-none">
                    <option value="">All Account Types</option>
                    <option value="Asset" {{ request('type') == 'Asset' ? 'selected' : '' }}>Asset</option>
                    <option value="Liability" {{ request('type') == 'Liability' ? 'selected' : '' }}>Liability</option>
                    <option value="Equity" {{ request('type') == 'Equity' ? 'selected' : '' }}>Equity</option>
                    <option value="Income" {{ request('type') == 'Income' ? 'selected' : '' }}>Income</option>
                    <option value="Expense" {{ request('type') == 'Expense' ? 'selected' : '' }}>Expense</option>
                </select>
            </div>

            <div class="lg:col-span-2 flex items-center gap-1">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2 rounded-xl text-xs transition flex items-center justify-center gap-1 shadow-md">
                    <i class="fas fa-filter text-xs"></i> Filter
                </button>
                <a href="{{ route('accounts.index') }}" class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold py-2 rounded-xl text-xs transition text-center flex items-center justify-center" title="Reset">
                    <i class="fas fa-redo-alt"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- ACCOUNTS TABLE --}}
    <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden mb-12">
        <div class="bg-slate-950 px-6 py-4 border-b border-slate-800 flex justify-between items-center">
            <h3 class="font-bold text-sm text-slate-200 flex items-center gap-2">
                <i class="fas fa-sitemap text-indigo-400"></i> Chart of Accounts Directory
            </h3>
            <span class="text-xs text-slate-500 font-mono">Showing {{ count($accounts) }} Accounts</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="p-4">Account Name</th>
                        <th class="p-4">GL Code</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Category / Group</th>
                        <th class="p-4 text-right">Current Balance (Rs)</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($accounts as $acc)
                        <tr class="hover:bg-slate-800/50 transition group">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 group-hover:text-indigo-400 group-hover:border-indigo-500/50 transition">
                                        @if($acc->type == 'Asset')
                                            <i class="fas fa-wallet text-emerald-400 text-xs"></i>
                                        @elseif($acc->type == 'Liability')
                                            <i class="fas fa-file-invoice text-rose-400 text-xs"></i>
                                        @elseif($acc->type == 'Equity')
                                            <i class="fas fa-piggy-bank text-blue-400 text-xs"></i>
                                        @elseif($acc->type == 'Income')
                                            <i class="fas fa-hand-holding-usd text-purple-400 text-xs"></i>
                                        @else
                                            <i class="fas fa-receipt text-amber-400 text-xs"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="font-bold text-white block text-xs">{{ $acc->name }}</span>
                                        @if($acc->is_system)
                                            <span class="text-[9px] text-indigo-400 font-mono uppercase tracking-wider"><i class="fas fa-lock text-[8px] mr-1"></i>System Account</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="p-4 font-mono text-xs font-bold text-indigo-400">
                                {{ $acc->code }}
                            </td>

                            <td class="p-4 text-xs">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border
                                    {{ $acc->type == 'Asset' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : '' }}
                                    {{ $acc->type == 'Liability' ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' : '' }}
                                    {{ $acc->type == 'Equity' ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : '' }}
                                    {{ $acc->type == 'Income' ? 'bg-purple-500/10 text-purple-400 border-purple-500/20' : '' }}
                                    {{ $acc->type == 'Expense' ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : '' }}">
                                    {{ $acc->type }}
                                </span>
                            </td>

                            <td class="p-4 text-xs text-slate-400">
                                {{ $acc->category ?: 'General' }}
                            </td>

                            <td class="p-4 text-right font-mono font-bold text-xs {{ $acc->current_balance < 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                                Rs. {{ number_format($acc->current_balance, 2) }}
                            </td>

                            <td class="p-4 text-center">
                                @if($acc->is_system)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-800 text-slate-400 border border-slate-700">
                                        Protected
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Active
                                    </span>
                                @endif
                            </td>

                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('general-ledger.index') }}?account_code={{ $acc->code }}" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-indigo-300 border border-slate-700 text-xs font-bold transition flex items-center gap-1" title="View Ledger">
                                        <i class="fas fa-book-open text-xs"></i> Ledger
                                    </a>
                                    <button @click="editAccount({{ json_encode($acc) }})" class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 text-xs transition" title="Edit">
                                        <i class="fas fa-pen text-xs"></i>
                                    </button>
                                    @if(!$acc->is_system)
                                        <form action="{{ route('accounts.destroy', $acc->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this account?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-rose-900/50 text-slate-400 hover:text-rose-400 border border-slate-700 text-xs transition" title="Delete">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center">
                                <div class="flex flex-col items-center justify-center opacity-60">
                                    <i class="fas fa-wallet text-4xl text-slate-600 mb-3"></i>
                                    <span class="text-slate-400 font-bold text-sm">No Accounts Found</span>
                                    <span class="text-slate-500 text-xs mt-1">There are currently no accounts matching your search or filter criteria.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ADD / EDIT ACCOUNT MODAL --}}
    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-zoomIn">
            
            <form action="{{ route('accounts.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id" x-model="form.id">

                <div class="bg-slate-950 px-6 py-4 border-b border-slate-800 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <h3 class="font-bold text-white text-sm" x-text="form.id ? 'Edit Account' : 'New Account'"></h3>
                    </div>
                    <button type="button" @click="closeModal()" class="text-slate-500 hover:text-white transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4 text-xs text-slate-300">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Account Type *</label>
                        <select name="type" x-model="form.type" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:border-indigo-500 outline-none">
                            <option value="Asset">Asset</option>
                            <option value="Liability">Liability</option>
                            <option value="Equity">Equity</option>
                            <option value="Income">Income</option>
                            <option value="Expense">Expense</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Category / Group</label>
                        <input type="text" name="category" x-model="form.category" placeholder="e.g. Current Assets, Operating Expenses" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:border-indigo-500 outline-none">
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-1">
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">GL Code *</label>
                            <input type="text" name="code" x-model="form.code" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2.5 text-xs font-mono font-bold text-white focus:border-indigo-500 outline-none">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Account Name *</label>
                            <input type="text" name="name" x-model="form.name" placeholder="e.g. Petty Cash Drawer" class="w-full bg-slate-950 border border-slate-700 rounded-xl p-2.5 text-xs text-white focus:border-indigo-500 outline-none">
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

    function accountsPage(initialAccounts) {
        return {
            isModalOpen: false,
            accounts: initialAccounts,
            form: {
                id: '',
                code: '',
                name: '',
                type: 'Asset',
                category: '',
                opening_balance: 0
            },

            getTypeCount(type) {
                return this.accounts.filter(a => a.type === type).length;
            },

            getTypeTotal(type) {
                const sum = this.accounts.filter(a => a.type === type).reduce((acc, a) => acc + (parseFloat(a.current_balance) || 0), 0);
                return sum.toLocaleString('en-US', { minimumFractionDigits: 2 });
            },

            filterType(type) {
                window.location.href = "{{ route('accounts.index') }}?type=" + type;
            },

            editAccount(acc) {
                this.form = { ...acc };
                this.isModalOpen = true;
            },

            openModal() {
                this.form = {
                    id: '',
                    code: Math.floor(100000 + Math.random() * 900000).toString(),
                    name: '',
                    type: 'Asset',
                    category: '',
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