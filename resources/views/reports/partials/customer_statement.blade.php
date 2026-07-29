<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-slate-900 text-white p-4 rounded-xl shadow-sm gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-file-invoice text-emerald-400"></i> Customer Statement
            </h2>
            <p class="text-xs text-slate-400">Full ledger transaction history & running balance for selected customer</p>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="w-64">
                <select onchange="loadReport('customer_statement', { customer_id: this.value, date_from: '{{ $fromDate }}', date_to: '{{ $toDate }}' })" class="w-full bg-slate-800 border border-slate-700 text-white text-xs font-bold rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Select Customer --</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ $selectedCustomerId == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} (Balance: Rs. {{ number_format($c->balance, 2) }})
                    </option>
                    @endforeach
                </select>
            </div>
            <button onclick="window.print()" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-lg border border-slate-700 transition">
                <i class="fas fa-print"></i>
            </button>
        </div>
    </div>

    @if($selectedCustomer)
    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-200 grid grid-cols-1 md:grid-cols-4 gap-4 text-slate-800">
        <div>
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Customer Name</span>
            <div class="text-lg font-extrabold text-slate-900 mt-0.5">{{ $selectedCustomer->name }}</div>
            <span class="text-xs text-slate-500 font-mono">{{ $selectedCustomer->phone ?? 'No Phone' }}</span>
        </div>
        <div>
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Address</span>
            <div class="text-xs font-medium text-slate-700 mt-1">{{ $selectedCustomer->address ?? 'Not specified' }}</div>
        </div>
        <div>
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Credit Limit</span>
            <div class="text-base font-bold font-mono text-slate-700 mt-0.5">Rs. {{ number_format($selectedCustomer->credit_limit, 2) }}</div>
        </div>
        <div>
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider">Current Account Balance</span>
            <div class="text-xl font-extrabold font-mono {{ $selectedCustomer->balance > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-0.5">
                Rs. {{ number_format($selectedCustomer->balance, 2) }}
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-800 text-slate-100 text-xs uppercase font-bold">
                <tr>
                    <th class="px-4 py-3">Date / Time</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Method</th>
                    <th class="px-4 py-3">Note / Ref</th>
                    <th class="px-4 py-3 text-right">Amount (PKR)</th>
                    <th class="px-4 py-3 text-right">Running Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @forelse($entries as $e)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-2.5 text-xs font-mono text-slate-500">{{ $e->created_at->format('d M Y, h:i A') }}</td>
                    <td class="px-4 py-2.5 text-xs">
                        <span class="px-2 py-0.5 rounded font-bold uppercase text-[10px]
                            @if(in_array($e->type, ['sale', 'payment_reversal'])) bg-rose-100 text-rose-800
                            @elseif(in_array($e->type, ['payment_received', 'return'])) bg-emerald-100 text-emerald-800
                            @else bg-slate-100 text-slate-800 @endif">
                            {{ str_replace('_', ' ', $e->type) }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-xs font-mono text-slate-600 uppercase">{{ $e->method ?? '-' }}</td>
                    <td class="px-4 py-2.5 text-xs text-slate-700">{{ $e->note ?? '-' }}</td>
                    <td class="px-4 py-2.5 text-right font-mono font-bold {{ $e->amount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                        {{ $e->amount > 0 ? '+ Rs. ' . number_format($e->amount, 2) : '- Rs. ' . number_format(abs($e->amount), 2) }}
                    </td>
                    <td class="px-4 py-2.5 text-right font-mono font-extrabold text-slate-900">
                        Rs. {{ number_format($e->balance_after, 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-slate-400 italic">No ledger transaction entries recorded for this customer.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @else
    <div class="bg-white rounded-xl p-12 text-center border border-slate-200 shadow-sm text-slate-400">
        <i class="fas fa-user-circle text-5xl mb-3 text-slate-300"></i>
        <p class="font-bold text-sm text-slate-600">Please select a customer from the top dropdown menu to view their full account statement.</p>
    </div>
    @endif
</div>
