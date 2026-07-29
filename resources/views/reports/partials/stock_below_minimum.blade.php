<div class="space-y-6">
    <div class="flex justify-between items-center bg-slate-900 text-white p-4 rounded-xl shadow-sm">
        <div>
            <h2 class="text-xl font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-amber-400"></i> Stock Below Minimum
            </h2>
            <p class="text-xs text-slate-400">Items matching minimum stock threshold or out-of-stock criteria</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('stock.low-stock') }}" target="_blank" class="px-3 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-lg shadow-sm transition">
                <i class="fas fa-external-link-alt mr-1"></i> Open Low Stock Page
            </a>
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
                    <th class="px-4 py-3">Item Name</th>
                    <th class="px-4 py-3 text-center">Min Threshold</th>
                    <th class="px-4 py-3 text-center">Current On-Hand</th>
                    <th class="px-4 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @forelse($items as $item)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-2.5 font-mono text-xs text-slate-500">{{ $item->code }}</td>
                    <td class="px-4 py-2.5 font-bold text-slate-800">{{ $item->description }}</td>
                    <td class="px-4 py-2.5 text-center font-mono text-slate-600">{{ $item->min_stock_level ?? '-' }}</td>
                    <td class="px-4 py-2.5 text-center font-mono font-bold {{ $item->on_hand <= 0 ? 'text-rose-600' : 'text-amber-600' }}">
                        {{ number_format($item->on_hand) }}
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        @if($item->on_hand <= 0)
                        <span class="px-2.5 py-0.5 rounded font-extrabold text-[10px] bg-rose-100 text-rose-800 uppercase">
                            <i class="fas fa-times-circle mr-1"></i> Out of Stock
                        </span>
                        @else
                        <span class="px-2.5 py-0.5 rounded font-extrabold text-[10px] bg-amber-100 text-amber-800 uppercase">
                            <i class="fas fa-exclamation-circle mr-1"></i> Low Stock
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-6 text-center text-emerald-600 font-bold italic">
                        <i class="fas fa-check-circle mr-1"></i> All stock levels are currently sufficient! No low stock items.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
