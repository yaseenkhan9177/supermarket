<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-slate-900 text-white p-4 rounded-xl shadow-sm gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-users text-blue-400"></i> Sales by Customer
            </h2>
            <p class="text-xs text-slate-400">Total sales volume and order counts per customer</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 bg-slate-800 p-1.5 rounded-lg border border-slate-700">
                <input type="date" id="sbc_date_from" value="{{ $fromDate }}" class="bg-transparent text-white text-xs px-2 py-1 outline-none">
                <span class="text-slate-500 text-xs">to</span>
                <input type="date" id="sbc_date_to" value="{{ $toDate }}" class="bg-transparent text-white text-xs px-2 py-1 outline-none">
                <button onclick="applyDateFilter('sales_by_customer', 'sbc_date_from', 'sbc_date_to')" class="px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded transition">
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
                    <th class="px-4 py-3 text-right">Total Revenue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @forelse($customersData as $row)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-2.5 font-bold text-slate-800">
                        {{ $row->customer->name ?? ($row->customer_name ?: 'Cash Customer / Walk-in') }}
                    </td>
                    <td class="px-4 py-2.5 text-xs font-mono text-slate-500">
                        {{ $row->customer->phone ?? '-' }}
                    </td>
                    <td class="px-4 py-2.5 text-center font-mono text-slate-900 font-bold">
                        {{ number_format($row->total_orders) }}
                    </td>
                    <td class="px-4 py-2.5 text-right font-mono font-extrabold text-blue-600">
                        Rs. {{ number_format($row->total_spent, 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-slate-400 italic">No customer sales recorded for the selected date range.</td>
                </tr>
                @endforelse
            </tbody>
            @if(count($customersData) > 0)
            <tfoot class="bg-slate-900 text-white font-bold">
                <tr>
                    <td colspan="2" class="px-4 py-3 uppercase">Total Sales</td>
                    <td class="px-4 py-3 text-center font-mono">{{ number_format($totals['orders']) }}</td>
                    <td class="px-4 py-3 text-right font-mono text-blue-400 text-base">Rs. {{ number_format($totals['spent'], 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
