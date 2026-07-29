<div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-slate-900 text-white p-4 rounded-xl shadow-sm gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-truck-loading text-amber-400"></i> Purchase by Item
            </h2>
            <p class="text-xs text-slate-400">Inventory quantity purchased and total procurement cost per item</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 bg-slate-800 p-1.5 rounded-lg border border-slate-700">
                <input type="date" id="pbi_date_from" value="{{ $fromDate }}" class="bg-transparent text-white text-xs px-2 py-1 outline-none">
                <span class="text-slate-500 text-xs">to</span>
                <input type="date" id="pbi_date_to" value="{{ $toDate }}" class="bg-transparent text-white text-xs px-2 py-1 outline-none">
                <button onclick="applyDateFilter('purchase_by_item', 'pbi_date_from', 'pbi_date_to')" class="px-3 py-1 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded transition">
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
                    <th class="px-4 py-3">Item Code</th>
                    <th class="px-4 py-3">Item Description</th>
                    <th class="px-4 py-3 text-center">Qty Purchased</th>
                    <th class="px-4 py-3 text-right">Avg Unit Cost</th>
                    <th class="px-4 py-3 text-right">Total Procurement Cost</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @forelse($purchaseItems as $row)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-2.5 font-mono text-xs text-slate-500">{{ $row->item->code ?? 'N/A' }}</td>
                    <td class="px-4 py-2.5 font-bold text-slate-800">{{ $row->item->description ?? 'Unknown Item' }}</td>
                    <td class="px-4 py-2.5 text-center font-mono text-slate-900 font-bold">{{ number_format($row->total_qty) }}</td>
                    <td class="px-4 py-2.5 text-right font-mono text-slate-600">
                        Rs. {{ $row->total_qty > 0 ? number_format($row->total_cost / $row->total_qty, 2) : '0.00' }}
                    </td>
                    <td class="px-4 py-2.5 text-right font-mono font-extrabold text-amber-600">
                        Rs. {{ number_format($row->total_cost, 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-slate-400 italic">No inventory purchases recorded in the selected date range.</td>
                </tr>
                @endforelse
            </tbody>
            @if(count($purchaseItems) > 0)
            <tfoot class="bg-slate-900 text-white font-bold">
                <tr>
                    <td colspan="2" class="px-4 py-3 uppercase">Total Purchases</td>
                    <td class="px-4 py-3 text-center font-mono">{{ number_format($totals['qty']) }}</td>
                    <td class="px-4 py-3"></td>
                    <td class="px-4 py-3 text-right font-mono text-amber-400 text-base">Rs. {{ number_format($totals['cost'], 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
