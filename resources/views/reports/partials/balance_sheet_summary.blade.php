<div class="space-y-6">
    <div class="flex justify-between items-center bg-slate-900 text-white p-4 rounded-xl shadow-sm">
        <div>
            <h2 class="text-xl font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-file-invoice-dollar text-blue-400"></i> Balance Sheet Summary
            </h2>
            <p class="text-xs text-slate-400">Financial position summary: Assets = Liabilities + Equity</p>
        </div>
        <button onclick="window.print()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-lg border border-slate-700 transition">
            <i class="fas fa-print mr-1"></i> Print / PDF
        </button>
    </div>

    <!-- Balance Verification Banner -->
    <div class="p-4 rounded-xl shadow-sm flex items-center justify-between {{ abs($difference) < 0.01 ? 'bg-emerald-900 text-white border border-emerald-700' : 'bg-rose-900 text-white border border-rose-700' }}">
        <div class="flex items-center gap-3">
            <i class="{{ abs($difference) < 0.01 ? 'fas fa-check-circle text-emerald-400 text-2xl' : 'fas fa-exclamation-triangle text-rose-400 text-2xl' }}"></i>
            <div>
                <h4 class="font-extrabold text-sm uppercase tracking-wider">
                    {{ abs($difference) < 0.01 ? 'Balanced Statement' : 'Unbalanced Variance Detected' }}
                </h4>
                <p class="text-xs opacity-90">
                    Assets (Rs. {{ number_format($totalAssets, 2) }}) {{ abs($difference) < 0.01 ? 'equals' : 'does not equal' }} Liabilities + Equity (Rs. {{ number_format($totalLiabEquity, 2) }})
                </p>
            </div>
        </div>
        <div class="text-right font-mono font-extrabold text-lg">
            Variance: Rs. {{ number_format(abs($difference), 2) }}
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- ASSETS -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col h-full">
            <div class="bg-blue-900 text-white px-4 py-3 font-extrabold text-sm uppercase tracking-wider flex justify-between items-center">
                <span>1. ASSETS</span>
                <i class="fas fa-building text-blue-300"></i>
            </div>
            <div class="p-4 space-y-2 flex-1">
                @forelse($assets as $a)
                <div class="flex justify-between items-center text-xs py-1 border-b border-slate-100">
                    <span class="font-bold text-slate-700">{{ $a->name }}</span>
                    <span class="font-mono text-slate-900 font-bold">Rs. {{ number_format($a->current_balance, 2) }}</span>
                </div>
                @empty
                <p class="text-xs text-slate-400 italic">No asset accounts recorded.</p>
                @endforelse
            </div>
            <div class="bg-blue-50 border-t border-blue-100 px-4 py-3 flex justify-between items-center font-bold text-blue-950">
                <span class="text-xs uppercase">Total Assets</span>
                <span class="font-mono text-base font-extrabold">Rs. {{ number_format($totalAssets, 2) }}</span>
            </div>
        </div>

        <!-- LIABILITIES -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col h-full">
            <div class="bg-rose-900 text-white px-4 py-3 font-extrabold text-sm uppercase tracking-wider flex justify-between items-center">
                <span>2. LIABILITIES</span>
                <i class="fas fa-hand-holding-usd text-rose-300"></i>
            </div>
            <div class="p-4 space-y-2 flex-1">
                @forelse($liabilities as $l)
                <div class="flex justify-between items-center text-xs py-1 border-b border-slate-100">
                    <span class="font-bold text-slate-700">{{ $l->name }}</span>
                    <span class="font-mono text-slate-900 font-bold">Rs. {{ number_format($l->current_balance, 2) }}</span>
                </div>
                @empty
                <p class="text-xs text-slate-400 italic">No liability accounts recorded.</p>
                @endforelse
            </div>
            <div class="bg-rose-50 border-t border-rose-100 px-4 py-3 flex justify-between items-center font-bold text-rose-950">
                <span class="text-xs uppercase">Total Liabilities</span>
                <span class="font-mono text-base font-extrabold">Rs. {{ number_format($totalLiabilities, 2) }}</span>
            </div>
        </div>

        <!-- EQUITY -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col h-full">
            <div class="bg-purple-900 text-white px-4 py-3 font-extrabold text-sm uppercase tracking-wider flex justify-between items-center">
                <span>3. EQUITY</span>
                <i class="fas fa-chart-pie text-purple-300"></i>
            </div>
            <div class="p-4 space-y-2 flex-1">
                @forelse($equity as $e)
                <div class="flex justify-between items-center text-xs py-1 border-b border-slate-100">
                    <span class="font-bold text-slate-700">{{ $e->name }}</span>
                    <span class="font-mono text-slate-900 font-bold">Rs. {{ number_format($e->current_balance, 2) }}</span>
                </div>
                @empty
                <p class="text-xs text-slate-400 italic">No equity accounts recorded.</p>
                @endforelse
            </div>
            <div class="bg-purple-50 border-t border-purple-100 px-4 py-3 flex justify-between items-center font-bold text-purple-950">
                <span class="text-xs uppercase">Total Equity</span>
                <span class="font-mono text-base font-extrabold">Rs. {{ number_format($totalEquity, 2) }}</span>
            </div>
        </div>
    </div>
</div>
