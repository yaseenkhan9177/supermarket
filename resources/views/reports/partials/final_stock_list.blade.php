<div class="space-y-6">
    <div class="flex justify-between items-center bg-slate-900 text-white p-4 rounded-xl shadow-sm">
        <div>
            <h2 class="text-xl font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-boxes text-emerald-400"></i> Final Stock List
            </h2>
            <p class="text-xs text-slate-400">Complete item inventory master with current on-hand quantity and total cost valuation</p>
        </div>
        <button onclick="window.print()" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-lg border border-slate-700 transition">
            <i class="fas fa-print"></i>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Inventory Items</span>
            <div class="text-xl font-extrabold font-mono text-slate-900 mt-1">{{ number_format(count($items)) }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total On-Hand Units</span>
            <div class="text-xl font-extrabold font-mono text-blue-600 mt-1">{{ number_format($totalOnHand) }}</div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Inventory Stock Valuation</span>
            <div class="text-xl font-extrabold font-mono text-emerald-600 mt-1">Rs. {{ number_format($totalStockValue, 2) }}</div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-800 text-slate-100 text-xs uppercase font-bold">
                <tr>
                    <th class="px-4 py-3">Item Code</th>
                    <th class="px-4 py-3">Item Description</th>
                    <th class="px-4 py-3 text-right">Cost Rate</th>
                    <th class="px-4 py-3 text-right">Sale Price</th>
                    <th class="px-4 py-3 text-center">On-Hand Qty</th>
                    <th class="px-4 py-3 text-right">Stock Valuation</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @forelse($items as $item)
                @php $val = (float)$item->on_hand * (float)$item->cost_rate; @endphp
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-2.5 font-mono text-xs text-slate-500">{{ $item->code }}</td>
                    <td class="px-4 py-2.5 font-bold text-slate-800">{{ $item->description }}</td>
                    <td class="px-4 py-2.5 text-right font-mono text-slate-600">Rs. {{ number_format($item->cost_rate, 2) }}</td>
                    <td class="px-4 py-2.5 text-right font-mono text-slate-600">Rs. {{ number_format($item->sale_rate, 2) }}</td>
                    <td class="px-4 py-2.5 text-center font-mono font-bold {{ $item->on_hand <= 0 ? 'text-rose-600' : 'text-slate-900' }}">
                        {{ number_format($item->on_hand) }}
                    </td>
                    <td class="px-4 py-2.5 text-right font-mono font-extrabold text-emerald-600">
                        Rs. {{ number_format($val, 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-slate-400 italic">No inventory items found.</td>
                </tr>
                @endforelse
            </tbody>
            @if(count($items) > 0)
            <tfoot class="bg-slate-900 text-white font-bold">
                <tr>
                    <td colspan="4" class="px-4 py-3 uppercase">Total Stock Valuation</td>
                    <td class="px-4 py-3 text-center font-mono text-blue-400">{{ number_format($totalOnHand) }}</td>
                    <td class="px-4 py-3 text-right font-mono text-emerald-400 text-base">Rs. {{ number_format($totalStockValue, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
