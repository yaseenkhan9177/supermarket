<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Process Return | OwnStore PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-950 text-gray-200 font-sans min-h-screen" x-data="returnForm()">

    <!-- Nav -->
    <nav class="bg-gray-900 border-b border-gray-800 px-6 py-3 sticky top-0 z-50 shadow-lg mb-8">
        <div class="container mx-auto max-w-[1200px] flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('supplier-returns.index') }}" class="text-gray-400 hover:text-orange-400 transition">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="w-9 h-9 rounded-full bg-orange-500 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-rotate-left text-sm"></i>
                </div>
                <div>
                    <h1 class="text-lg font-extrabold text-white leading-none">Process Supplier Return</h1>
                    <span class="text-xs text-gray-400">Review items and choose resolution</span>
                </div>
            </div>
            <a href="{{ route('supplier-returns.index') }}" class="text-gray-400 hover:text-gray-200 text-sm flex items-center gap-2">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </nav>

    <!-- Flash Errors -->
    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', () =>
            Swal.fire({ title: 'Error', text: "{{ session('error') }}", icon: 'error', background: '#111827', color: '#fff', confirmButtonColor: '#ef4444' })
        );
    </script>
    @endif
    @if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', () =>
            Swal.fire({
                title: 'Validation Error',
                html: '<ul class="text-left list-disc pl-4 text-sm">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>',
                icon: 'warning', background: '#111827', color: '#fff', confirmButtonColor: '#ef4444'
            })
        );
    </script>
    @endif

    <form action="{{ route('supplier-returns.store') }}" method="POST" class="container mx-auto px-6 max-w-[1200px] pb-32">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left: Items to Return -->
            <div class="lg:col-span-2 space-y-4">
                <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
                    <div class="bg-orange-500/10 border-b border-orange-500/30 p-4 flex items-center gap-2">
                        <i class="fas fa-boxes text-orange-400"></i>
                        <h2 class="font-bold text-orange-300">Items Being Returned</h2>
                        <span class="ml-auto text-xs text-gray-400">{{ $batches->count() }} batch(es)</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-800 text-gray-400 text-xs uppercase font-bold">
                                <tr>
                                    <th class="p-4">Item</th>
                                    <th class="p-4">Batch / Expiry</th>
                                    <th class="p-4 text-center">Available</th>
                                    <th class="p-4 text-center">Return Qty</th>
                                    <th class="p-4 text-right">Cost / Unit</th>
                                    <th class="p-4 text-right">Line Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                @foreach($batches as $batch)
                                @php
                                    $expired = $batch->expires_at && now()->gt($batch->expires_at);
                                    $daysLeft = $batch->expires_at ? now()->diffInDays($batch->expires_at, false) : null;
                                @endphp
                                <tr class="hover:bg-gray-800/50 transition">
                                    <td class="p-4">
                                        <input type="hidden" name="items[{{ $loop->index }}][batch_id]" value="{{ $batch->id }}">
                                        <input type="hidden" name="items[{{ $loop->index }}][item_id]" value="{{ $batch->item_id }}">
                                        <p class="font-bold text-white">{{ $batch->item->description ?? 'Unknown Item' }}</p>
                                        <p class="text-xs text-gray-400 font-mono">{{ $batch->item->code ?? '' }}</p>
                                    </td>
                                    <td class="p-4">
                                        <p class="font-mono text-sm text-indigo-300">{{ $batch->batch_no }}</p>
                                        @if($batch->expires_at)
                                            <p class="text-xs mt-0.5 {{ $expired ? 'text-red-400 font-bold' : 'text-yellow-300' }}">
                                                {{ $expired ? '⚠ Expired' : '🕐 Expires' }}:
                                                {{ \Carbon\Carbon::parse($batch->expires_at)->format('d M Y') }}
                                                @if(!$expired && $daysLeft !== null)
                                                    <span class="text-gray-400">({{ $daysLeft }}d left)</span>
                                                @endif
                                            </p>
                                        @endif
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="font-bold text-orange-300">{{ $batch->quantity_available }}</span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <input type="number"
                                               name="items[{{ $loop->index }}][qty_returned]"
                                               x-model="lines[{{ $loop->index }}].qty"
                                               @input="recalc()"
                                               min="1" max="{{ $batch->quantity_available }}"
                                               value="{{ $batch->quantity_available }}"
                                               class="w-20 text-center border border-gray-600 bg-gray-800 text-white rounded-lg p-1.5 text-sm font-bold focus:ring-2 focus:ring-orange-500 outline-none">
                                    </td>
                                    <td class="p-4 text-right">
                                        <input type="number" step="0.01"
                                               name="items[{{ $loop->index }}][cost_rate]"
                                               x-model="lines[{{ $loop->index }}].cost"
                                               @input="recalc()"
                                               value="{{ $batch->cost_price }}"
                                               class="w-24 text-right border border-gray-600 bg-gray-800 text-white rounded-lg p-1.5 text-sm focus:ring-2 focus:ring-orange-500 outline-none">
                                    </td>
                                    <td class="p-4 text-right">
                                        <span class="font-bold text-white"
                                              x-text="'Rs. ' + (lines[{{ $loop->index }}].qty * lines[{{ $loop->index }}].cost).toFixed(2)"></span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-800">
                                <tr>
                                    <td colspan="5" class="p-4 text-right font-bold text-gray-300 uppercase text-xs tracking-wide">Total Return Value:</td>
                                    <td class="p-4 text-right font-extrabold text-xl text-orange-400" x-text="'Rs. ' + totalValue.toFixed(2)"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Resolution Panel -->
            <div class="space-y-5">

                <!-- Supplier -->
                <div class="bg-gray-900 rounded-2xl border border-gray-800 p-5">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-2">
                        <i class="fas fa-user-tie mr-1 text-indigo-400"></i> Supplier
                    </label>
                    <select name="supplier_id" required
                            class="w-full border border-gray-700 bg-gray-800 text-white rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">— Select Supplier —</option>
                        @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}">{{ $sup->name }}{{ $sup->company_name ? ' — '.$sup->company_name : '' }}</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-gray-500 mt-1">Select the supplier this stock was purchased from.</p>
                </div>

                <!-- Date -->
                <div class="bg-gray-900 rounded-2xl border border-gray-800 p-5">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-2">
                        <i class="fas fa-calendar mr-1 text-indigo-400"></i> Return Date
                    </label>
                    <input type="date" name="return_date" value="{{ date('Y-m-d') }}" required
                           class="w-full border border-gray-700 bg-gray-800 text-white rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>

                <!-- Resolution Toggle -->
                <div class="bg-gray-900 rounded-2xl border border-gray-800 p-5">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-3">
                        <i class="fas fa-code-branch mr-1 text-orange-400"></i> Resolution Method
                    </label>

                    <!-- Scenario A -->
                    <label class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer transition mb-3"
                           :class="resolution === 'cash_refund' ? 'border-emerald-500 bg-emerald-950/30' : 'border-gray-700 hover:border-gray-600'">
                        <input type="radio" name="resolution" value="cash_refund" x-model="resolution" class="mt-0.5">
                        <div>
                            <p class="font-bold text-emerald-300 text-sm">💵 Scenario A — Cash Refund</p>
                            <p class="text-xs text-gray-400 mt-0.5">Supplier pays us cash immediately. The refund amount will be credited to the selected account.</p>
                        </div>
                    </label>

                    <!-- Scenario B -->
                    <label class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer transition"
                           :class="resolution === 'store_credit' ? 'border-purple-500 bg-purple-950/30' : 'border-gray-700 hover:border-gray-600'">
                        <input type="radio" name="resolution" value="store_credit" x-model="resolution" class="mt-0.5">
                        <div>
                            <p class="font-bold text-purple-300 text-sm">🏷️ Scenario B — Store Credit</p>
                            <p class="text-xs text-gray-400 mt-0.5">No cash today. The return value is logged as a supplier credit and <strong class="text-purple-300">auto-deducted from the next bill</strong> for this supplier.</p>
                        </div>
                    </label>
                </div>

                <!-- Cash Account (shown for Scenario A only) -->
                <div x-show="resolution === 'cash_refund'" x-transition class="bg-gray-900 rounded-2xl border border-emerald-700 p-5">
                    <label class="text-xs font-bold text-emerald-400 uppercase tracking-wide block mb-2">
                        <i class="fas fa-wallet mr-1"></i> Cash Goes Into...
                    </label>
                    <select name="account_id"
                            :required="resolution === 'cash_refund'"
                            class="w-full border border-gray-700 bg-gray-800 text-white rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="">— Select Account —</option>
                        @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Credit Info (shown for Scenario B only) -->
                <div x-show="resolution === 'store_credit'" x-transition class="bg-purple-950/30 rounded-2xl border border-purple-700 p-5">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-purple-400 mt-0.5"></i>
                        <div>
                            <p class="text-sm font-bold text-purple-300">How Store Credit Works</p>
                            <p class="text-xs text-gray-400 mt-1">
                                After processing, this supplier's ledger balance will go to
                                <strong class="text-purple-300">–Rs. <span x-text="totalValue.toFixed(2)"></span></strong>
                                (negative = credit we hold).
                                When you create the next purchase bill for this supplier, the system will
                                automatically detect and apply this credit, reducing your net payable.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-gray-900 rounded-2xl border border-gray-800 p-5">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wide block mb-2">Notes (Optional)</label>
                    <textarea name="notes" rows="3" placeholder="Reason for return, supplier instructions..."
                              class="w-full border border-gray-700 bg-gray-800 text-white rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none"></textarea>
                </div>

                <!-- Submit -->
                <button type="submit"
                        :class="resolution === 'cash_refund' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-purple-600 hover:bg-purple-700'"
                        class="w-full py-3.5 text-white font-extrabold rounded-xl shadow-xl transition flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-rotate-left"></i>
                    <span x-show="resolution === 'cash_refund'">Process Return — Cash Refund</span>
                    <span x-show="resolution === 'store_credit'">Process Return — Apply Store Credit</span>
                </button>
                <p class="text-center text-[10px] text-gray-500 mt-2">This action will update inventory and the supplier ledger.</p>
            </div>
        </div>
    </form>

    <script>
        function returnForm() {
            const lines = @json($batches->map(fn($b) => ['qty' => $b->quantity_available, 'cost' => (float)$b->cost_price]));

            return {
                resolution: 'store_credit',
                lines: lines,

                get totalValue() {
                    return this.lines.reduce((s, l) => s + (parseFloat(l.qty || 0) * parseFloat(l.cost || 0)), 0);
                },

                recalc() {
                    // Reactive — getter auto-updates
                }
            }
        }
    </script>
</body>
</html>
