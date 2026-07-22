@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Breadcrumb / Back Navigation --}}
    <div class="flex items-center justify-between mb-8">
        <a href="{{ route('suppliers.index') }}" 
           class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white font-semibold transition text-sm">
            <i class="fas fa-arrow-left"></i> Back to Suppliers
        </a>
        <button onclick="window.print()" class="bg-indigo-600 text-white px-4 py-2 rounded-md flex items-center gap-2 print:hidden">
            <i class="fas fa-print"></i> Print Profile
        </button>
        <!-- Print Header (visible only when printing) -->
        <div class="hidden print:block text-center mb-6">
            <h1 class="text-3xl font-bold">Mart</h1>
            <h2 class="text-xl">Supplier Statement</h2>
            <p class="text-lg">{{ $supplier->name }}</p>
            <p class="text-sm">Printed on {{ now()->format('d M Y') }}</p>
        </div>
        <style>
            @media print {
                body { -webkit-print-color-adjust: exact; print-color-adjust: exact; margin: 0; }
                .no-print, .print\:hidden { display: none !important; }
                .print\:block { display: block !important; }
                .page-break { page-break-before: always; }
            }
        </style>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 text-xs font-bold uppercase rounded-full {{ $supplier->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                {{ $supplier->status }}
            </span>
            <span class="px-3 py-1 bg-indigo-100 text-indigo-800 text-xs font-mono font-bold rounded-full">
                {{ $supplier->code }}
            </span>
        </div>
    </div>

    {{-- Profile Header Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 h-full w-2 bg-indigo-600"></div>
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white">{{ $supplier->name }}</h1>
                @if($supplier->category)
                    <span class="px-2.5 py-1 bg-purple-100 text-purple-700 text-xs font-semibold rounded-full">
                        {{ $supplier->category->name }}
                    </span>
                @endif
            </div>
            <p class="text-slate-500 text-sm mt-1">
                <i class="fas fa-building mr-1"></i> {{ $supplier->company_name ?: 'Individual Supplier' }}
            </p>
        </div>
        
        <div class="flex flex-col text-left md:text-right">
            <div class="flex items-center gap-1.5 justify-start md:justify-end">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Current Balance</p>
                <div class="group relative inline-block cursor-pointer">
                    <i class="fas fa-info-circle text-slate-400 hover:text-indigo-600 text-xs"></i>
                    <div class="hidden group-hover:block absolute right-0 w-64 p-2 bg-slate-800 text-white text-[11px] rounded-lg shadow-xl z-50 pointer-events-none font-normal text-left">
                        <strong>Balance Rules:</strong><br>
                        • <span class="text-red-400 font-semibold">Positive (+):</span> Store owes supplier (Payable).<br>
                        • <span class="text-emerald-400 font-semibold">Negative (-):</span> Supplier owes store (Advance/Credit).
                    </div>
                </div>
            </div>

            @if($supplier->current_balance > 0)
                <h2 class="text-3xl font-black text-red-600 mt-1">Rs. {{ number_format($supplier->current_balance, 2) }}</h2>
                <span class="text-xs font-bold text-red-600 bg-red-100 dark:bg-red-950/40 px-2.5 py-1 rounded-full inline-block mt-1 self-start md:self-end border border-red-200">
                    <i class="fas fa-arrow-circle-up mr-1"></i> Payable — You Owe Supplier
                </span>
            @elseif($supplier->current_balance < 0)
                <h2 class="text-3xl font-black text-emerald-600 mt-1">Rs. {{ number_format(abs($supplier->current_balance), 2) }}</h2>
                <span class="text-xs font-bold text-emerald-700 bg-emerald-100 dark:bg-emerald-950/40 px-2.5 py-1 rounded-full inline-block mt-1 self-start md:self-end border border-emerald-200">
                    <i class="fas fa-arrow-circle-down mr-1"></i> Advance / Credit — Supplier Owes You
                </span>
            @else
                <h2 class="text-3xl font-black text-slate-400 mt-1">Rs. 0.00</h2>
                <span class="text-xs font-bold text-slate-500 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-full inline-block mt-1 self-start md:self-end">
                    <i class="fas fa-check-circle mr-1"></i> Settled (Zero Balance)
                </span>
            @endif
        </div>
    </div>

    {{-- KPI Widget Dashboard --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mb-8">
        
        <!-- Total Items Supplied -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Total Qty Supplied</div>
            <div class="flex items-baseline justify-between">
                <span class="text-2xl font-extrabold text-slate-800 dark:text-white">{{ number_format($totalItemsSupplied) }}</span>
                <span class="text-slate-300 dark:text-slate-700"><i class="fas fa-boxes text-lg"></i></span>
            </div>
            <div class="text-slate-400 text-[10px] mt-2">Sum of item quantities received</div>
        </div>

        <!-- Cash Purchases -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Cash Purchases</div>
            <div>
                <div class="flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold text-slate-800 dark:text-white">Rs. {{ number_format($totalCashAmount) }}</span>
                    <span class="text-slate-300 dark:text-slate-700"><i class="fas fa-wallet text-lg"></i></span>
                </div>
                <div class="text-slate-400 text-[10px] mt-2">{{ $totalCashCount }} cash {{ Str::plural('bill', $totalCashCount) }} recorded</div>
            </div>
        </div>

        <!-- Credit Purchases -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Credit Purchases</div>
            <div>
                <div class="flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold text-slate-800 dark:text-white">Rs. {{ number_format($totalCreditAmount) }}</span>
                    <span class="text-slate-300 dark:text-slate-700"><i class="fas fa-file-invoice-dollar text-lg"></i></span>
                </div>
                <div class="text-slate-400 text-[10px] mt-2">{{ $totalCreditCount }} credit {{ Str::plural('bill', $totalCreditCount) }} recorded</div>
            </div>
        </div>

        <!-- Total Outstanding Due -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between relative overflow-hidden">
            @if($outstandingAmount > 0)
                <div class="absolute top-0 left-0 w-full h-1 bg-red-500"></div>
            @endif
            <div class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Outstanding Due</div>
            <div class="flex items-baseline justify-between">
                <span class="text-2xl font-extrabold {{ $outstandingAmount > 0 ? 'text-red-600' : 'text-slate-800 dark:text-slate-200' }}">
                    Rs. {{ number_format($outstandingAmount) }}
                </span>
                <span class="text-slate-300 dark:text-slate-700"><i class="fas fa-exclamation-circle text-lg"></i></span>
            </div>
            <div class="text-slate-400 text-[10px] mt-2">Unpaid debt + opening balance</div>
        </div>

        <!-- Grand Total Purchases -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Grand Total Purchased</div>
            <div class="flex items-baseline justify-between">
                <span class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">Rs. {{ number_format($grandTotalAmount) }}</span>
                <span class="text-slate-300 dark:text-slate-700"><i class="fas fa-chart-line text-lg"></i></span>
            </div>
            <div class="text-slate-400 text-[10px] mt-2">Cash + credit purchases total</div>
        </div>
    </div>

{{-- Payments Accordion --}}
<div x-data="{ open: false }" class="mb-8">
    <button @click="open = !open" type="button" class="flex items-center gap-2 px-4 py-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-800 rounded-lg transition">
        <i class="fas fa-hand-holding-usd"></i>
        <span>Payment History & Record Payment</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    <div x-show="open" x-transition class="mt-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow">
        {{-- Record Payment Form --}}
        <form method="POST" action="{{ route('suppliers.payments.store', $supplier->id) }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Amount</label>
                <input type="number" step="0.01" name="amount" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100" />
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Date</label>
                <input type="date" name="payment_date" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100" />
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Method</label>
                <select name="payment_method" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="cheque">Cheque</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Reference Note</label>
                <input type="text" name="reference_note" class="mt-1 block w-full rounded-md border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100" placeholder="Cheque number, transaction ID, etc." />
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                    <i class="fas fa-save"></i> Record Payment
                </button>
            </div>
        </form>

        {{-- Payments Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-bold text-xs">
                    <tr>
                        <th class="p-2">Date</th>
                        <th class="p-2">Amount</th>
                        <th class="p-2">Method</th>
                        <th class="p-2">Reference</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($payments as $pay)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-2">{{ $pay->payment_date->format('d-M-Y') }}</td>
                            <td class="p-2 font-medium">Rs. {{ number_format($pay->amount, 2) }}</td>
                            <td class="p-2 capitalize">{{ str_replace('_', ' ', $pay->payment_method) }}</td>
                            <td class="p-2">{{ $pay->reference_note ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-4 text-center text-slate-450 dark:text-slate-500">No payments recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="mt-4">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Items to Reorder Accordion --}}
<div x-data="{ open: false }" class="mb-8 no-print">
    <button @click="open = !open" type="button" class="flex items-center justify-between w-full md:w-auto gap-2 px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-800 rounded-lg transition font-semibold shadow-sm">
        <div class="flex items-center gap-2">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Items to Reorder from This Supplier</span>
            <span class="ml-1.5 px-2 py-0.5 text-xs font-bold font-mono rounded-full bg-indigo-600 text-indigo-100">
                {{ $reorderItems->count() }}
            </span>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    <div x-show="open" x-transition class="mt-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-4 shadow-sm">
        @if($reorderItems->isEmpty())
            <div class="text-emerald-600 dark:text-emerald-400 font-medium py-2 flex items-center gap-2">
                <i class="fas fa-check-circle"></i> ✓ No items currently require reordering from this supplier.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-bold text-xs">
                        <tr>
                            <th class="p-3">Item Name</th>
                            <th class="p-3 text-center">Current Stock</th>
                            <th class="p-3 text-center">Min Level</th>
                            <th class="p-3 text-right">Shortage</th>
                            <th class="p-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($reorderItems as $item)
                            @php
                                $shortage = $item->min_stock_level - $item->on_hand;
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition">
                                <td class="p-3">
                                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ $item->description }}</div>
                                    <div class="text-xs text-slate-400 font-mono mt-0.5">Code: {{ $item->code }}</div>
                                </td>
                                <td class="p-3 text-center font-mono font-bold text-slate-700 dark:text-slate-300">{{ (float)$item->on_hand }}</td>
                                <td class="p-3 text-center font-mono text-slate-500 dark:text-slate-500">{{ (float)$item->min_stock_level }}</td>
                                <td class="p-3 text-right font-mono font-bold text-red-650" style="color: rgb(220, 38, 38);">-{{ (float)$shortage }}</td>
                                <td class="p-3 text-center">
                                    <a href="{{ route('purchases.create', ['supplier_id' => $supplier->id, 'item_id' => $item->id]) }}" 
                                       class="bg-white text-black border border-gray-300 hover:bg-gray-100 font-bold px-3 py-1.5 rounded-lg text-xs transition duration-150 inline-flex items-center gap-1 shadow-sm">
                                        <i class="fas fa-shopping-cart"></i> Reorder
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

    {{-- Main Column Grid Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {{-- Profile Metadata Left Card (4 cols) --}}
        <div class="lg:col-span-4 space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 shadow-sm">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-6 pb-2 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                    <i class="fas fa-info-circle text-indigo-500"></i> Profile Information
                </h3>

                <div class="space-y-4">
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Unique Supplier Code</span>
                        <span class="text-sm font-semibold font-mono text-slate-700 dark:text-slate-300">{{ $supplier->code ?: '—' }}</span>
                    </div>

                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Supplier Name</span>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $supplier->name }}</span>
                    </div>

                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Category</span>
                        <span class="inline-block bg-purple-50 dark:bg-purple-950/20 text-purple-600 dark:text-purple-400 text-xs font-semibold px-2 py-0.5 rounded mt-1">
                            {{ $supplier->category ? $supplier->category->name : 'No Category Assigned' }}
                        </span>
                    </div>

                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Company Name</span>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $supplier->company_name ?: '—' }}</span>
                    </div>

                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Phone Number</span>
                        @if($supplier->phone)
                            <a href="tel:{{ $supplier->phone }}" class="text-sm font-semibold text-indigo-500 hover:underline"><i class="fas fa-phone mr-1"></i> {{ $supplier->phone }}</a>
                        @else
                            <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">—</span>
                        @endif
                    </div>

                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Address</span>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-start gap-1">
                            <i class="fas fa-map-marker-alt text-slate-400 mt-1"></i>
                            <span>{{ $supplier->address ?: 'No address specified' }}</span>
                        </span>
                    </div>

                    <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                        <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Opening Balance (Debt)</span>
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Rs. {{ number_format($supplier->opening_balance, 2) }}</span>
                    </div>

                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide">Current Balance (Outstanding)</span>
                        <span class="text-sm font-bold {{ $supplier->current_balance > 0 ? 'text-red-600' : ($supplier->current_balance < 0 ? 'text-emerald-600' : 'text-slate-500') }}">
                            Rs. {{ number_format($supplier->current_balance, 2) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Action Links -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-3">
                <a href="{{ route('suppliers.ledger', $supplier->id) }}" class="w-full py-2.5 px-4 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition text-center">
                    <i class="fas fa-book"></i> View Full Account Ledger
                </a>
            </div>
        </div>

        {{-- Purchase Bills Right Card (8 cols) --}}
        <div class="lg:col-span-8">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-file-invoice text-indigo-500"></i> Recent Purchases
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-bold text-xs">
                            <tr>
                                <th class="p-4">Bill No</th>
                                <th class="p-4">Date</th>
                                <th class="p-4 text-center">Items</th>
                                <th class="p-4 text-right">Net Amount</th>
                                <th class="p-4">Payment Type</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($purchases as $purchase)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="p-4 font-mono font-bold text-slate-800 dark:text-white">
                                    {{ $purchase->purchase_no ?: '—' }}
                                </td>
                                <td class="p-4 text-slate-600 dark:text-slate-350">
                                    {{ Carbon\Carbon::parse($purchase->invoice_date)->format('d-M-Y') }}
                                </td>
                                <td class="p-4 text-center font-bold text-slate-700 dark:text-slate-350">
                                    {{ $purchase->items_count }}
                                </td>
                                <td class="p-4 text-right font-black text-slate-800 dark:text-white">
                                    Rs. {{ number_format($purchase->net_total, 2) }}
                                </td>
                                <td class="p-4">
                                    <span class="px-2.5 py-0.5 rounded text-xs font-bold border {{ strtolower($purchase->payment_type) === 'credit' ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-900' : 'bg-green-50 text-green-700 border-green-200 dark:bg-green-950/20 dark:text-green-400 dark:border-green-900' }}">
                                        {{ $purchase->payment_type }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $purchase->status === 'received' ? 'bg-emerald-100 text-emerald-800' : ($purchase->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                        {{ $purchase->status }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <a href="{{ route('purchases.print', $purchase->id) }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-1.5 text-indigo-500 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1.5 rounded-lg text-xs font-bold transition">
                                        <i class="fas fa-print"></i> Print
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-450 dark:text-slate-500 font-medium">
                                    No purchase transactions recorded for this supplier.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($purchases->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $purchases->links() }}
                </div>
                @endif
            </div>
        </div>

    </div>

</div>
@endsection
