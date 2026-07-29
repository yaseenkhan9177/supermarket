<div class="space-y-6">
    <!-- Header Toolbar -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-slate-900 text-white p-4 rounded-xl shadow-sm gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-chart-line text-emerald-400"></i> Profit & Loss Summary
            </h2>
            <p class="text-xs text-slate-400">Income vs Cost of Goods Sold & Operating Expenses</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 bg-slate-800 p-1.5 rounded-lg border border-slate-700">
                <input type="date" id="pl_date_from" value="{{ $fromDate }}" class="bg-transparent text-white text-xs px-2 py-1 outline-none">
                <span class="text-slate-500 text-xs">to</span>
                <input type="date" id="pl_date_to" value="{{ $toDate }}" class="bg-transparent text-white text-xs px-2 py-1 outline-none">
                <button onclick="applyDateFilter('profit_loss_summary', 'pl_date_from', 'pl_date_to')" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded transition">
                    Apply
                </button>
            </div>
            <button onclick="window.print()" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-lg border border-slate-700 transition">
                <i class="fas fa-print"></i>
            </button>
        </div>
    </div>

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Gross Sales</span>
            <div class="text-xl font-extrabold font-mono text-slate-900 mt-1">Rs. {{ number_format($grossSales, 2) }}</div>
            <span class="text-[11px] text-slate-400">Total invoice billing</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Cost of Goods Sold (COGS)</span>
            <div class="text-xl font-extrabold font-mono text-rose-600 mt-1">Rs. {{ number_format($cogs, 2) }}</div>
            <span class="text-[11px] text-slate-400">FIFO inventory cost</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Gross Profit</span>
            <div class="text-xl font-extrabold font-mono text-emerald-600 mt-1">Rs. {{ number_format($grossProfit, 2) }}</div>
            <span class="text-[11px] text-slate-400">Revenue minus COGS</span>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Operating Expenses</span>
            <div class="text-xl font-extrabold font-mono text-amber-600 mt-1">Rs. {{ number_format($operatingExpenses + $totalBadDebt, 2) }}</div>
            <span class="text-[11px] text-slate-400">Payments & write-offs</span>
        </div>
    </div>

    <!-- Final Net Profit Banner -->
    <div class="p-6 rounded-xl shadow-md flex justify-between items-center {{ $netProfit >= 0 ? 'bg-emerald-950 text-white border border-emerald-800' : 'bg-rose-950 text-white border border-rose-800' }}">
        <div>
            <span class="text-xs font-bold uppercase tracking-widest text-slate-400">NET PROFIT / LOSS</span>
            <h3 class="text-2xl font-extrabold mt-0.5">
                {{ $netProfit >= 0 ? 'Net Operating Income' : 'Net Operating Deficit' }}
            </h3>
        </div>
        <div class="text-3xl font-extrabold font-mono {{ $netProfit >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
            Rs. {{ number_format($netProfit, 2) }}
        </div>
    </div>

    <!-- Detailed breakdown table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="bg-slate-800 text-slate-100 px-4 py-3 text-xs font-bold uppercase tracking-wider">
            Income & Expense Statement Breakdown
        </div>
        <table class="w-full text-left text-sm">
            <tbody class="divide-y divide-slate-100 font-medium">
                <tr class="bg-slate-50 font-bold">
                    <td class="px-4 py-2.5 text-slate-900">Gross Sales Revenue</td>
                    <td class="px-4 py-2.5 text-right font-mono text-slate-900">Rs. {{ number_format($grossSales, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 text-slate-600 pl-8">Less: Sales Refunds & Returns</td>
                    <td class="px-4 py-2 text-right font-mono text-rose-600">- Rs. {{ number_format($totalRefunds, 2) }}</td>
                </tr>
                <tr class="bg-slate-50 font-bold">
                    <td class="px-4 py-2.5 text-slate-900">Net Sales Revenue</td>
                    <td class="px-4 py-2.5 text-right font-mono text-emerald-700">Rs. {{ number_format($netRevenue, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 text-slate-600 pl-8">Less: Cost of Goods Sold (COGS - FIFO Batch Cost)</td>
                    <td class="px-4 py-2 text-right font-mono text-rose-600">- Rs. {{ number_format($cogs, 2) }}</td>
                </tr>
                <tr class="bg-emerald-50 font-extrabold text-emerald-950">
                    <td class="px-4 py-3">Gross Operating Profit</td>
                    <td class="px-4 py-3 text-right font-mono text-base">Rs. {{ number_format($grossProfit, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 text-slate-600 pl-8">Less: Operating Expenses (Account Payments)</td>
                    <td class="px-4 py-2 text-right font-mono text-amber-600">- Rs. {{ number_format($operatingExpenses, 2) }}</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 text-slate-600 pl-8">Less: Customer Bad Debt Write-offs</td>
                    <td class="px-4 py-2 text-right font-mono text-amber-600">- Rs. {{ number_format($totalBadDebt, 2) }}</td>
                </tr>
                <tr class="bg-slate-900 text-white font-extrabold text-base">
                    <td class="px-4 py-3">Net Profit / (Loss)</td>
                    <td class="px-4 py-3 text-right font-mono {{ $netProfit >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                        Rs. {{ number_format($netProfit, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
