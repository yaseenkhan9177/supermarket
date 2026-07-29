<div class="space-y-6">
    <div class="flex justify-between items-center bg-slate-900 text-white p-4 rounded-xl shadow-sm">
        <div>
            <h2 class="text-xl font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-balance-scale text-emerald-400"></i> Trial Balance
            </h2>
            <p class="text-xs text-slate-400">General Ledger Accounts Debit / Credit summary as of {{ now()->format('d M Y') }}</p>
        </div>
        <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-lg border border-slate-700 transition">
            <i class="fas fa-print mr-1"></i> Print / PDF
        </button>
    </div>

    @foreach($processed as $type => $rows)
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-800 text-slate-100 px-4 py-2 text-xs font-extrabold uppercase tracking-wider flex justify-between items-center">
            <span>{{ $type }} ACCOUNTS</span>
            <span class="text-slate-400 font-mono">{{ count($rows) }} account(s)</span>
        </div>
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 text-xs uppercase font-bold">
                <tr>
                    <th class="px-4 py-2.5">GL Code</th>
                    <th class="px-4 py-2.5">Account Name</th>
                    <th class="px-4 py-2.5 text-right">Debit (PKR)</th>
                    <th class="px-4 py-2.5 text-right">Credit (PKR)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @foreach($rows as $r)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-2 font-mono text-slate-500 text-xs">{{ $r['account']->gl_code }}</td>
                    <td class="px-4 py-2 text-slate-800 font-bold">{{ $r['account']->name }}</td>
                    <td class="px-4 py-2 text-right font-mono {{ $r['debit'] > 0 ? 'text-slate-900 font-bold' : 'text-slate-300' }}">
                        {{ $r['debit'] > 0 ? number_format($r['debit'], 2) : '-' }}
                    </td>
                    <td class="px-4 py-2 text-right font-mono {{ $r['credit'] > 0 ? 'text-slate-900 font-bold' : 'text-slate-300' }}">
                        {{ $r['credit'] > 0 ? number_format($r['credit'], 2) : '-' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

    <div class="bg-slate-900 text-white rounded-xl p-4 flex justify-between items-center font-bold shadow-lg border border-slate-800">
        <div class="text-base font-extrabold">Trial Balance Total</div>
        <div class="flex items-center gap-8 font-mono text-base">
            <div>Debit: <span class="text-emerald-400">Rs. {{ number_format($totals['debit'], 2) }}</span></div>
            <div>Credit: <span class="text-emerald-400">Rs. {{ number_format($totals['credit'], 2) }}</span></div>
        </div>
    </div>
</div>
