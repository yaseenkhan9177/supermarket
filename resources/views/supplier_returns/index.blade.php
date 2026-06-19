<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Expiry & Returns Dashboard | OwnStore PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @keyframes pulse-border {
            0%, 100% { border-color: #ef4444; }
            50% { border-color: #fca5a5; }
        }
        .expired-pulse { animation: pulse-border 1.5s ease-in-out infinite; }
    </style>
</head>
<body class="bg-gray-950 text-gray-200 font-sans min-h-screen" x-data="expiryDashboard()">

    <!-- Nav -->
    <nav class="bg-gray-900 border-b border-gray-800 px-6 py-3 sticky top-0 z-50 shadow-lg mb-8">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-biohazard"></i>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-white leading-none">OwnStore <span class="text-orange-400">PRO</span></h1>
                    <span class="text-xs text-gray-400">Expiry & Returns Management</span>
                </div>
            </div>
            <div class="flex gap-3 items-center">
                <form method="GET" action="{{ route('supplier-returns.index') }}" class="flex items-center gap-2">
                    <label class="text-xs text-gray-400 font-bold">Show items expiring within:</label>
                    <select name="days" onchange="this.form.submit()"
                            class="bg-gray-800 border border-gray-600 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500 outline-none">
                        @foreach([1,2,3,5,7,14,30] as $d)
                            <option value="{{ $d }}" {{ $days == $d ? 'selected' : '' }}>{{ $d }} day{{ $d > 1 ? 's' : '' }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-truck-loading"></i> New Purchase
                </a>
                <a href="{{ route('suppliers.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 text-white text-sm font-bold rounded-lg hover:bg-gray-600 transition">
                    <i class="fas fa-home"></i> Back
                </a>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () =>
            Swal.fire({ title: 'Done!', text: "{{ session('success') }}", icon: 'success', background: '#111827', color: '#fff', timer: 3000, showConfirmButton: false })
        );
    </script>
    @endif
    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', () =>
            Swal.fire({ title: 'Error', text: "{{ session('error') }}", icon: 'error', background: '#111827', color: '#fff', confirmButtonColor: '#ef4444' })
        );
    </script>
    @endif

    <div class="container mx-auto px-6 max-w-[1400px] pb-32">

        <!-- Summary Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-gray-900 rounded-2xl border border-red-800 p-5 shadow-lg">
                <p class="text-xs font-bold text-red-400 uppercase tracking-widest mb-1">Already Expired</p>
                <h2 class="text-3xl font-extrabold text-red-400">{{ $expiredBatches->count() }}</h2>
                <p class="text-xs text-gray-500 mt-1">batches still in stock</p>
            </div>
            <div class="bg-gray-900 rounded-2xl border border-orange-700 p-5 shadow-lg">
                <p class="text-xs font-bold text-orange-400 uppercase tracking-widest mb-1">Expiring in {{ $days }} Days</p>
                <h2 class="text-3xl font-extrabold text-orange-400">{{ $expiringBatches->count() }}</h2>
                <p class="text-xs text-gray-500 mt-1">batches need attention</p>
            </div>
            <div class="bg-gray-900 rounded-2xl border border-gray-700 p-5 shadow-lg">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Stock at Risk</p>
                <h2 class="text-3xl font-extrabold text-yellow-400">
                    {{ $expiringBatches->sum('quantity_available') + $expiredBatches->sum('quantity_available') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">units</p>
            </div>
            <div class="bg-gray-900 rounded-2xl border border-gray-700 p-5 shadow-lg">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Value at Risk</p>
                <h2 class="text-3xl font-extrabold text-pink-400">
                    Rs. {{ number_format(
                        $expiringBatches->sum(fn($b) => $b->quantity_available * $b->cost_price) +
                        $expiredBatches->sum(fn($b) => $b->quantity_available * $b->cost_price)
                    , 0) }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">at cost price</p>
            </div>
        </div>

        <!-- Initiate Return Button -->
        <div x-show="selectedBatches.length > 0" x-transition class="mb-6">
            <div class="bg-orange-500/20 border border-orange-500 rounded-xl p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-check-square text-orange-400 text-xl"></i>
                    <span class="text-orange-200 font-bold">
                        <span x-text="selectedBatches.length"></span> batch(es) selected —
                        <span x-text="totalSelectedQty"></span> units —
                        Rs. <span x-text="totalSelectedValue.toFixed(2)"></span>
                    </span>
                </div>
                <button @click="initiateReturn()" class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-lg transition flex items-center gap-2">
                    <i class="fas fa-rotate-left"></i> Initiate Return →
                </button>
            </div>
        </div>

        <!-- ─── ALREADY EXPIRED ─────────────────────────────────────────── -->
        @if($expiredBatches->isNotEmpty())
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-3 w-3 rounded-full bg-red-500 animate-ping"></div>
                <h2 class="text-lg font-extrabold text-red-400 uppercase tracking-wider">⚠ Already Expired — Action Required</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($expiredBatches as $batch)
                <div class="bg-gray-900 border-2 border-red-700 expired-pulse rounded-2xl p-5 relative overflow-hidden cursor-pointer transition-all"
                     :class="selectedBatches.includes({{ $batch->id }}) ? 'ring-2 ring-red-400 bg-red-950/30' : ''"
                     @click="toggleBatch({{ $batch->id }}, {{ $batch->quantity_available }}, {{ $batch->cost_price }})">
                    <div class="absolute top-0 right-0 bg-red-600 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl">EXPIRED</div>

                    <div class="flex items-start gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-red-900/50 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-skull text-red-400"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-white truncate">{{ $batch->item->description ?? 'Unknown Item' }}</h3>
                            <p class="text-xs text-gray-400 font-mono">{{ $batch->batch_no }}</p>
                        </div>
                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                             :class="selectedBatches.includes({{ $batch->id }}) ? 'bg-red-500 border-red-500' : 'border-gray-600'">
                            <i class="fas fa-check text-white text-[10px]" x-show="selectedBatches.includes({{ $batch->id }})"></i>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase font-bold">Expired On</p>
                            <p class="font-bold text-red-400">{{ \Carbon\Carbon::parse($batch->expires_at)->format('d M Y') }}</p>
                            <p class="text-xs text-red-300">{{ \Carbon\Carbon::parse($batch->expires_at)->diffForHumans() }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase font-bold">Qty in Stock</p>
                            <p class="font-bold text-orange-300">{{ $batch->quantity_available }} units</p>
                            <p class="text-xs text-gray-400">Rs. {{ number_format($batch->cost_price, 2) }} /unit</p>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-800 flex justify-between items-center">
                        <span class="text-xs text-gray-500">Total Value</span>
                        <span class="font-bold text-white">Rs. {{ number_format($batch->quantity_available * $batch->cost_price, 2) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- ─── EXPIRING SOON ────────────────────────────────────────────── -->
        @if($expiringBatches->isNotEmpty())
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-3 w-3 rounded-full bg-orange-400"></div>
                <h2 class="text-lg font-extrabold text-orange-300 uppercase tracking-wider">🕐 Expiring Within {{ $days }} Day(s)</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($expiringBatches as $batch)
                @php
                    $daysLeft  = now()->diffInDays($batch->expires_at, false);
                    $urgency   = $daysLeft <= 1
                        ? ['border' => 'border-red-600',    'badge' => 'bg-red-600',    'icon' => 'text-red-400',    'text' => 'text-red-300',    'label' => '🔴 Today/Tomorrow']
                        : ($daysLeft <= 2
                            ? ['border' => 'border-orange-600', 'badge' => 'bg-orange-600', 'icon' => 'text-orange-400', 'text' => 'text-orange-300', 'label' => '🟠 2 Days Left']
                            : ['border' => 'border-yellow-600', 'badge' => 'bg-yellow-600', 'icon' => 'text-yellow-400', 'text' => 'text-yellow-200', 'label' => '🟡 ' . $daysLeft . ' Days Left']);
                @endphp
                <div class="bg-gray-900 border-2 {{ $urgency['border'] }} rounded-2xl p-5 relative overflow-hidden cursor-pointer transition-all"
                     :class="selectedBatches.includes({{ $batch->id }}) ? 'ring-2 ring-white bg-white/5' : ''"
                     @click="toggleBatch({{ $batch->id }}, {{ $batch->quantity_available }}, {{ $batch->cost_price }})">

                    <div class="absolute top-0 right-0 {{ $urgency['badge'] }} text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl">
                        {{ $urgency['label'] }}
                    </div>

                    <div class="flex items-start gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-gray-800 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock {{ $urgency['icon'] }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-white truncate">{{ $batch->item->description ?? 'Unknown Item' }}</h3>
                            <p class="text-xs text-gray-400 font-mono">{{ $batch->batch_no }}</p>
                        </div>
                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0"
                             :class="selectedBatches.includes({{ $batch->id }}) ? 'bg-indigo-500 border-indigo-500' : 'border-gray-600'">
                            <i class="fas fa-check text-white text-[10px]" x-show="selectedBatches.includes({{ $batch->id }})"></i>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase font-bold">Expires On</p>
                            <p class="font-bold {{ $urgency['text'] }}">{{ \Carbon\Carbon::parse($batch->expires_at)->format('d M Y') }}</p>
                            <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($batch->expires_at)->diffForHumans() }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase font-bold">Qty in Stock</p>
                            <p class="font-bold text-white">{{ $batch->quantity_available }} units</p>
                            <p class="text-xs text-gray-400">Rs. {{ number_format($batch->cost_price, 2) }} /unit</p>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-gray-800 flex justify-between items-center">
                        <span class="text-xs text-gray-500">Total Value</span>
                        <span class="font-bold text-white">Rs. {{ number_format($batch->quantity_available * $batch->cost_price, 2) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($expiringBatches->isEmpty() && $expiredBatches->isEmpty())
        <div class="text-center py-20">
            <div class="w-20 h-20 rounded-full bg-emerald-900/50 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-shield-alt text-emerald-400 text-3xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-emerald-400 mb-2">All Clear!</h2>
            <p class="text-gray-400">No items expiring within the next {{ $days }} day(s).</p>
        </div>
        @endif

        <!-- Recent Returns -->
        @if($recentReturns->isNotEmpty())
        <div class="mt-10">
            <h2 class="text-lg font-bold text-gray-400 mb-4 uppercase tracking-wider"><i class="fas fa-history mr-2"></i>Recent Returns</h2>
            <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-800 text-gray-400 text-xs uppercase font-bold">
                        <tr>
                            <th class="p-4">Return #</th>
                            <th class="p-4">Supplier</th>
                            <th class="p-4">Date</th>
                            <th class="p-4">Resolution</th>
                            <th class="p-4 text-right">Value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        @foreach($recentReturns as $ret)
                        <tr class="hover:bg-gray-800/50 transition">
                            <td class="p-4 font-mono font-bold text-indigo-400">{{ $ret->return_no }}</td>
                            <td class="p-4 text-white">{{ $ret->supplier->name ?? '—' }}</td>
                            <td class="p-4 text-gray-400">{{ $ret->return_date?->format('d M Y') ?? '—' }}</td>
                            <td class="p-4">
                                @if($ret->resolution === 'cash_refund')
                                    <span class="text-xs font-bold bg-emerald-900 text-emerald-300 px-2 py-0.5 rounded-full">Cash Refund</span>
                                @else
                                    <span class="text-xs font-bold bg-purple-900 text-purple-300 px-2 py-0.5 rounded-full">Store Credit</span>
                                @endif
                            </td>
                            <td class="p-4 text-right font-bold text-white">Rs. {{ number_format($ret->total_value, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

    <script>
        function expiryDashboard() {
            return {
                selectedBatches: [],
                batchQty: {},
                batchCost: {},

                get totalSelectedQty() {
                    return this.selectedBatches.reduce((s, id) => s + (this.batchQty[id] || 0), 0);
                },
                get totalSelectedValue() {
                    return this.selectedBatches.reduce((s, id) => s + ((this.batchQty[id] || 0) * (this.batchCost[id] || 0)), 0);
                },

                toggleBatch(id, qty, cost) {
                    const idx = this.selectedBatches.indexOf(id);
                    if (idx === -1) {
                        this.selectedBatches.push(id);
                        this.batchQty[id]  = qty;
                        this.batchCost[id] = cost;
                    } else {
                        this.selectedBatches.splice(idx, 1);
                    }
                },

                initiateReturn() {
                    if (this.selectedBatches.length === 0) return;
                    const batchList = this.selectedBatches.join(',');
                    window.location.href = `{{ route('supplier-returns.create') }}?batches=${batchList}`;
                }
            }
        }
    </script>
</body>
</html>
