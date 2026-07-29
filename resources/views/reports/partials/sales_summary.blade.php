<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-slate-900 text-white p-4 rounded-xl shadow-sm gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-shopping-cart text-indigo-400"></i> Sales Summary Report
            </h2>
            <p class="text-xs text-slate-400">Daily sales totals and transaction counts</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 bg-slate-800 p-1.5 rounded-lg border border-slate-700">
                <input type="date" id="ss_date_from" value="{{ $fromDate }}" class="bg-transparent text-white text-xs px-2 py-1 outline-none">
                <span class="text-slate-500 text-xs">to</span>
                <input type="date" id="ss_date_to" value="{{ $toDate }}" class="bg-transparent text-white text-xs px-2 py-1 outline-none">
                <button onclick="applyDateFilter('sales_summary', 'ss_date_from', 'ss_date_to')" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded transition">
                    Apply
                </button>
            </div>
            <button onclick="window.print()" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-lg border border-slate-700 transition">
                <i class="fas fa-print"></i>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Sales Revenue</span>
            <div class="text-xl font-extrabold font-mono text-emerald-600 mt-1">Rs. {{ number_format($totals['grand_total'], 2) }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Transactions</span>
            <div class="text-xl font-extrabold font-mono text-slate-900 mt-1">{{ number_format($totals['transactions']) }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Discounts Given</span>
            <div class="text-xl font-extrabold font-mono text-rose-600 mt-1">Rs. {{ number_format($totals['discount'], 2) }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Average Ticket Size</span>
            <div class="text-xl font-extrabold font-mono text-indigo-600 mt-1">
                Rs. {{ $totals['transactions'] > 0 ? number_format($totals['grand_total'] / $totals['transactions'], 2) : '0.00' }}
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-800 text-slate-100 text-xs uppercase font-bold">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3 text-center">Transactions</th>
                    <th class="px-4 py-3 text-right">Subtotal</th>
                    <th class="px-4 py-3 text-right">Discounts</th>
                    <th class="px-4 py-3 text-right">Tax</th>
                    <th class="px-4 py-3 text-right">Net Revenue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @forelse($dailySales as $row)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-2.5 font-bold text-slate-800">{{ \Carbon\Carbon::parse($row->date)->format('d M Y, D') }}</td>
                    <td class="px-4 py-2.5 text-center font-mono text-slate-700">{{ $row->total_transactions }}</td>
                    <td class="px-4 py-2.5 text-right font-mono text-slate-600">Rs. {{ number_format($row->subtotal, 2) }}</td>
                    <td class="px-4 py-2.5 text-right font-mono text-rose-600">Rs. {{ number_format($row->discount_total, 2) }}</td>
                    <td class="px-4 py-2.5 text-right font-mono text-slate-600">Rs. {{ number_format($row->tax_total, 2) }}</td>
                    <td class="px-4 py-2.5 text-right font-mono font-extrabold text-emerald-600">Rs. {{ number_format($row->grand_total, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-slate-400 italic">No sales recorded for the selected date range.</td>
                </tr>
                @endforelse
            </tbody>
            @if(count($dailySales) > 0)
            <tfoot class="bg-slate-900 text-white font-bold">
                <tr>
                    <td class="px-4 py-3 uppercase">Total</td>
                    <td class="px-4 py-3 text-center font-mono">{{ number_format($totals['transactions']) }}</td>
                    <td class="px-4 py-3 text-right font-mono">Rs. {{ number_format($totals['subtotal'], 2) }}</td>
                    <td class="px-4 py-3 text-right font-mono text-rose-400">Rs. {{ number_format($totals['discount'], 2) }}</td>
                    <td class="px-4 py-3 text-right font-mono">Rs. {{ number_format($totals['tax'], 2) }}</td>
                    <td class="px-4 py-3 text-right font-mono text-emerald-400 text-base">Rs. {{ number_format($totals['grand_total'], 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
