<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-slate-900 text-white p-4 rounded-xl shadow-sm gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-warehouse text-purple-400"></i> Purchase by Supplier
            </h2>
            <p class="text-xs text-slate-400">Total purchase order value per vendor / supplier</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 bg-slate-800 p-1.5 rounded-lg border border-slate-700">
                <input type="date" id="pbs_date_from" value="{{ $fromDate }}" class="bg-transparent text-white text-xs px-2 py-1 outline-none">
                <span class="text-slate-500 text-xs">to</span>
                <input type="date" id="pbs_date_to" value="{{ $toDate }}" class="bg-transparent text-white text-xs px-2 py-1 outline-none">
                <button onclick="applyDateFilter('purchase_by_supplier', 'pbs_date_from', 'pbs_date_to')" class="px-3 py-1 bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold rounded transition">
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
                    <th class="px-4 py-3">Supplier / Vendor</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3 text-center">Bills Count</th>
                    <th class="px-4 py-3 text-right">Total Purchased Value</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @forelse($suppliersData as $row)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-2.5 font-bold text-slate-800">
                        {{ $row->supplier->name ?? 'Direct Purchase' }}
                    </td>
                    <td class="px-4 py-2.5 text-xs font-mono text-slate-500">
                        {{ $row->supplier->phone ?? '-' }}
                    </td>
                    <td class="px-4 py-2.5 text-center font-mono text-slate-900 font-bold">
                        {{ number_format($row->total_bills) }}
                    </td>
                    <td class="px-4 py-2.5 text-right font-mono font-extrabold text-purple-600">
                        Rs. {{ number_format($row->total_purchased, 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-slate-400 italic">No supplier purchases recorded for the selected date range.</td>
                </tr>
                @endforelse
            </tbody>
            @if(count($suppliersData) > 0)
            <tfoot class="bg-slate-900 text-white font-bold">
                <tr>
                    <td colspan="2" class="px-4 py-3 uppercase">Total Purchases</td>
                    <td class="px-4 py-3 text-center font-mono">{{ number_format($totals['bills']) }}</td>
                    <td class="px-4 py-3 text-right font-mono text-purple-400 text-base">Rs. {{ number_format($totals['purchased'], 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
