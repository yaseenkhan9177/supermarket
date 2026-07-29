<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-slate-900 text-white p-4 rounded-xl shadow-sm gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-user-check text-blue-400"></i> Profit by Customer (FIFO Cost)
            </h2>
            <p class="text-xs text-slate-400">Revenue minus exact FIFO product cost per customer account</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 bg-slate-800 p-1.5 rounded-lg border border-slate-700">
                <input type="date" id="pbc_profit_date_from" value="{{ $fromDate }}" class="bg-transparent text-white text-xs px-2 py-1 outline-none">
                <span class="text-slate-500 text-xs">to</span>
                <input type="date" id="pbc_profit_date_to" value="{{ $toDate }}" class="bg-transparent text-white text-xs px-2 py-1 outline-none">
                <button onclick="applyDateFilter('profit_by_customer', 'pbc_profit_date_from', 'pbc_profit_date_to')" class="px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded transition">
                    Apply
                </button>
            </div>
            <button onclick="window.print()" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-lg border border-slate-700 transition">
                <i class="fas fa-print"></i>
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-800 text-slate-100 text-xs uppercase font-bold">
                <tr>
                    <th class="px-4 py-3">Customer Name</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3 text-center">Orders Count</th>
                    <th class="px-4 py-3 text-right">Revenue</th>
                    <th class="px-4 py-3 text-right">FIFO Cost</th>
                    <th class="px-4 py-3 text-right">Net Profit</th>
                    <th class="px-4 py-3 text-center">Margin %</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @forelse($customerProfitList as $row)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-2.5 font-bold text-slate-800">{{ $row['customer_name'] }}</td>
                    <td class="px-4 py-2.5 text-xs font-mono text-slate-500">{{ $row['phone'] }}</td>
                    <td class="px-4 py-2.5 text-center font-mono text-slate-900 font-bold">{{ number_format($row['orders']) }}</td>
                    <td class="px-4 py-2.5 text-right font-mono text-slate-700">Rs. {{ number_format($row['revenue'], 2) }}</td>
                    <td class="px-4 py-2.5 text-right font-mono text-rose-600">Rs. {{ number_format($row['cost'], 2) }}</td>
                    <td class="px-4 py-2.5 text-right font-mono font-extrabold {{ $row['profit'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        Rs. {{ number_format($row['profit'], 2) }}
                    </td>
                    <td class="px-4 py-2.5 text-center font-mono text-xs">
                        <span class="px-2 py-0.5 rounded font-bold {{ $row['margin'] >= 20 ? 'bg-emerald-100 text-emerald-800' : ($row['margin'] >= 0 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                            {{ number_format($row['margin'], 1) }}%
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-slate-400 italic">No customer sales recorded in the selected date range.</td>
                </tr>
                @endforelse
            </tbody>
            @if(count($customerProfitList) > 0)
            <tfoot class="bg-slate-900 text-white font-bold">
                <tr>
                    <td colspan="2" class="px-4 py-3 uppercase">Total Profit</td>
                    <td class="px-4 py-3 text-center font-mono">{{ number_format($totals['orders']) }}</td>
                    <td class="px-4 py-3 text-right font-mono">Rs. {{ number_format($totals['revenue'], 2) }}</td>
                    <td class="px-4 py-3 text-right font-mono text-rose-400">Rs. {{ number_format($totals['cost'], 2) }}</td>
                    <td class="px-4 py-3 text-right font-mono text-emerald-400 text-base">Rs. {{ number_format($totals['profit'], 2) }}</td>
                    <td class="px-4 py-3 text-center font-mono text-emerald-400">
                        {{ $totals['revenue'] > 0 ? number_format(($totals['profit'] / $totals['revenue']) * 100, 1) : '0' }}%
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
