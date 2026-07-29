<div class="space-y-6">
    <div class="flex justify-between items-center bg-slate-900 text-white p-4 rounded-xl shadow-sm">
        <div>
            <h2 class="text-xl font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-book-open text-indigo-400"></i> General Ledger Accounts & Balances
            </h2>
            <p class="text-xs text-slate-400">Master Chart of Accounts listing & current balance status</p>
        </div>
        <div class="w-72">
            <select onchange="loadReport('general_ledger', { account_id: this.value })" class="w-full bg-slate-800 border border-slate-700 text-white text-xs font-bold rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">-- All Accounts --</option>
                @foreach($accounts as $acc)
                <option value="{{ $acc->id }}" {{ $selectedAccountId == $acc->id ? 'selected' : '' }}>
                    [{{ $acc->gl_code }}] {{ $acc->name }} ({{ $acc->account_type }})
                </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-xl text-amber-900 text-xs flex items-center gap-3">
        <i class="fas fa-info-circle text-amber-600 text-lg"></i>
        <div>
            <strong class="font-bold">Transaction Ledger Note:</strong>
            Individual per-transaction debit/credit journal postings are not active in this store database yet. Showing current master balances for General Ledger accounts.
        </div>
    </div>

    @if($selectedAccount)
    <div class="bg-slate-900 text-white rounded-xl p-6 shadow-md border border-slate-800 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">GL Code</span>
            <div class="text-xl font-extrabold font-mono text-indigo-400">{{ $selectedAccount->gl_code }}</div>
        </div>
        <div>
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Account Name</span>
            <div class="text-lg font-bold text-white">{{ $selectedAccount->name }}</div>
        </div>
        <div>
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Account Type</span>
            <div class="text-sm font-bold text-emerald-400 uppercase">{{ $selectedAccount->account_type }}</div>
        </div>
        <div>
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Current Balance</span>
            <div class="text-xl font-extrabold font-mono text-white">Rs. {{ number_format($selectedAccount->current_balance, 2) }}</div>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-800 text-slate-100 text-xs uppercase font-bold">
                <tr>
                    <th class="px-4 py-3">GL Code</th>
                    <th class="px-4 py-3">GL Type</th>
                    <th class="px-4 py-3">Account Name</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3 text-right">Opening Balance</th>
                    <th class="px-4 py-3 text-right">Current Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @foreach($accounts as $acc)
                @if(!$selectedAccountId || $selectedAccountId == $acc->id)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-2.5 font-mono text-slate-500 text-xs">{{ $acc->gl_code }}</td>
                    <td class="px-4 py-2.5 text-xs text-slate-400 font-mono">{{ $acc->gl_type }}</td>
                    <td class="px-4 py-2.5 font-bold text-slate-800">{{ $acc->name }}</td>
                    <td class="px-4 py-2.5 text-xs">
                        <span class="px-2 py-0.5 rounded font-bold uppercase text-[10px] 
                            @if($acc->account_type == 'ASSETS') bg-blue-100 text-blue-800
                            @elseif($acc->account_type == 'LIABILITIES') bg-rose-100 text-rose-800
                            @elseif($acc->account_type == 'EQUITY') bg-purple-100 text-purple-800
                            @elseif($acc->account_type == 'INCOME') bg-emerald-100 text-emerald-800
                            @else bg-amber-100 text-amber-800 @endif">
                            {{ $acc->account_type }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-right font-mono text-slate-500">Rs. {{ number_format($acc->opening_balance, 2) }}</td>
                    <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-900">Rs. {{ number_format($acc->current_balance, 2) }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
