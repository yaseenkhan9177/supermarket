<div class="space-y-6">
    <div class="flex justify-between items-center bg-slate-900 text-white p-4 rounded-xl shadow-sm">
        <div>
            <h2 class="text-xl font-extrabold text-white flex items-center gap-2">
                <i class="fas fa-calendar-times text-rose-400"></i> Stock Expiry Report
            </h2>
            <p class="text-xs text-slate-400">Batches with configured expiration dates ordered by urgency</p>
        </div>
        <button onclick="window.print()" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-lg border border-slate-700 transition">
            <i class="fas fa-print"></i>
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-800 text-slate-100 text-xs uppercase font-bold">
                <tr>
                    <th class="px-4 py-3">Item Code</th>
                    <th class="px-4 py-3">Item Name</th>
                    <th class="px-4 py-3 font-mono">Batch No</th>
                    <th class="px-4 py-3 text-center">Available Qty</th>
                    <th class="px-4 py-3 text-right">Cost Price</th>
                    <th class="px-4 py-3">Expiry Date</th>
                    <th class="px-4 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 font-medium">
                @forelse($batches as $batch)
                @php
                    $isExpired = $batch->expires_at->isPast();
                    $daysLeft = (int)now()->diffInDays($batch->expires_at, false);
                @endphp
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-2.5 font-mono text-xs text-slate-500">{{ $batch->item->code ?? 'N/A' }}</td>
                    <td class="px-4 py-2.5 font-bold text-slate-800">{{ $batch->item->description ?? 'Unknown Item' }}</td>
                    <td class="px-4 py-2.5 font-mono text-xs text-slate-600 font-bold">{{ $batch->batch_no }}</td>
                    <td class="px-4 py-2.5 text-center font-mono font-bold text-slate-900">{{ number_format($batch->quantity_available) }}</td>
                    <td class="px-4 py-2.5 text-right font-mono text-slate-700">Rs. {{ number_format($batch->cost_price, 2) }}</td>
                    <td class="px-4 py-2.5 text-xs font-mono font-bold text-slate-800">
                        {{ $batch->expires_at->format('d M Y') }}
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        @if($isExpired)
                        <span class="px-2.5 py-0.5 rounded font-extrabold text-[10px] bg-rose-100 text-rose-800 uppercase">
                            Expired ({{ abs($daysLeft) }} days ago)
                        </span>
                        @elseif($daysLeft <= 30)
                        <span class="px-2.5 py-0.5 rounded font-extrabold text-[10px] bg-amber-100 text-amber-800 uppercase">
                            Expiring in {{ $daysLeft }} days
                        </span>
                        @else
                        <span class="px-2.5 py-0.5 rounded font-extrabold text-[10px] bg-emerald-100 text-emerald-800 uppercase">
                            Valid ({{ $daysLeft }} days)
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-slate-400 italic">No batches with expiration dates found in system.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
