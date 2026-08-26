@extends('layouts.admin')

@section('title', 'Edit Invoice #' . $sale->invoice_no)

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12" x-data="invoiceEditor()">

    {{-- Breadcrumb & Top Bar --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('sales.today') }}" class="hover:text-blue-600 transition">Sales</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <a href="{{ route('sales.versions', $sale->id) }}" class="hover:text-blue-600 transition">Invoice #{{ $sale->invoice_no }}</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-slate-700 dark:text-slate-300 font-semibold">Edit</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white flex items-center gap-3">
                <i class="fas fa-file-invoice text-blue-600"></i>
                Edit Invoice #{{ $sale->invoice_no }}
                <span class="text-xs px-2.5 py-1 rounded-full font-bold uppercase tracking-wider
                    {{ $sale->payment_mode === 'Cash' ? 'bg-green-100 text-green-800 dark:bg-green-500/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400' }}">
                    {{ $sale->payment_mode }}
                </span>
            </h1>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('sales.versions', $sale->id) }}" class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition flex items-center gap-2">
                <i class="fas fa-history text-indigo-500"></i> Version History ({{ $sale->versions->count() }})
            </a>
            <a href="{{ route('sales.print', $sale->id) }}" target="_blank" class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 px-4 py-2 rounded-xl font-bold text-sm shadow-sm transition flex items-center gap-2">
                <i class="fas fa-print text-blue-500"></i> Print Current
            </a>
            <button type="button" @click="submitForm($event)" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-2 rounded-xl font-bold text-sm shadow-lg shadow-blue-500/25 transition flex items-center gap-2">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </div>

    {{-- Active Version & Modification Audit Banner --}}
    <div class="bg-indigo-50/80 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800/50 rounded-2xl p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 text-xs">
        <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-black text-sm flex items-center justify-center shadow-md shadow-indigo-500/30">
                V{{ ($sale->versions->max('version_number') ?? 1) }}
            </span>
            <div>
                <div class="font-bold text-slate-800 dark:text-white text-sm flex items-center gap-2">
                    Current Version: Version {{ ($sale->versions->max('version_number') ?? 1) }}
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">ACTIVE</span>
                </div>
                <div class="text-slate-500 dark:text-slate-400 mt-0.5">
                    Original Created By: <strong class="text-slate-700 dark:text-slate-200">{{ $sale->user?->name ?? 'Staff' }}</strong>
                    on {{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d M Y \a\t h:i A') : $sale->created_at->format('d M Y \a\t h:i A') }}
                </div>
            </div>
        </div>

        @php
            $latestVer = $sale->versions->first();
        @endphp
        @if($latestVer && $latestVer->action_type === 'edited')
        <div class="text-right sm:border-l sm:border-indigo-200 dark:sm:border-indigo-800/50 sm:pl-4">
            <span class="text-slate-400 font-semibold uppercase text-[10px] tracking-wider block">Last Edited</span>
            <div class="font-bold text-indigo-700 dark:text-indigo-300">
                By {{ $latestVer->user_name ?? 'Staff' }}
            </div>
            <div class="text-slate-500 text-[11px]">
                {{ $latestVer->created_at->format('d M Y \a\t h:i A') }}
            </div>
        </div>
        @endif
    </div>

    {{-- Error Banner --}}
    @if ($errors->any())
    <div class="bg-red-50 dark:bg-red-950/40 border-l-4 border-red-500 p-4 rounded-r-xl">
        <div class="flex items-start">
            <i class="fas fa-exclamation-triangle text-red-500 mt-1 mr-3"></i>
            <div>
                <h3 class="text-sm font-bold text-red-800 dark:text-red-300">Cannot save invoice changes:</h3>
                <ul class="mt-1 text-sm text-red-700 dark:text-red-400 list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- Main Edit Form --}}
    <form id="invoiceEditForm" method="POST" action="{{ route('sales.update', $sale->id) }}" @submit.prevent="submitForm($event)" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Hidden Concurrency Token --}}
        <input type="hidden" name="original_updated_at" value="{{ $sale->updated_at?->toISOString() }}">

        {{-- Invoice Meta Card --}}
        <div class="bg-white dark:bg-slate-800/80 rounded-2xl shadow-xl shadow-black/5 border border-slate-100 dark:border-slate-700/50 p-6">
            <h2 class="text-base font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2 border-b border-slate-100 dark:border-slate-700 pb-3">
                <i class="fas fa-info-circle text-blue-600"></i> Invoice Details
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                {{-- Read-Only Original Date & Time --}}
                <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700/70 rounded-xl p-3">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                        <i class="fas fa-lock text-slate-400"></i> Original Date & Time
                    </div>
                    <div class="text-sm font-bold text-slate-800 dark:text-white flex items-center justify-between">
                        <span>{{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') : 'N/A' }}</span>
                        <span class="text-xs text-blue-600 dark:text-blue-400 font-mono bg-blue-50 dark:bg-blue-900/40 px-2 py-0.5 rounded font-bold">
                            {{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('h:i A') : '' }}
                        </span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">Immutable creation timestamp</p>
                </div>

                {{-- Customer --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Customer</label>
                    <select name="customer_id" x-model="customerId" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Walk-in Customer --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ $sale->customer_id == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->phone ?? 'No Phone' }}) — Balance: Rs. {{ number_format($c->balance, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Payment Mode --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Payment Mode *</label>
                    <select name="payment_mode" x-model="paymentMode" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500">
                        <option value="Cash">Cash Sale</option>
                        <option value="Debit">Debit Sale (Credit)</option>
                        <option value="Card">Credit/Debit Card</option>
                        <option value="Online">Online Transfer</option>
                    </select>
                </div>

                {{-- Wallet Selector (for Cash/Online) --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Target Account / Wallet</label>
                    <select name="wallet_id" x-model="walletId" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500">
                        <option value="">-- None / Default --</option>
                        @foreach($wallets as $w)
                            <option value="{{ $w->id }}" {{ $sale->wallet_id == $w->id ? 'selected' : '' }}>
                                {{ $w->name }} (Rs. {{ number_format($w->balance, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Line Items Card --}}
        <div class="bg-white dark:bg-slate-800/80 rounded-2xl shadow-xl shadow-black/5 border border-slate-100 dark:border-slate-700/50 p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4 border-b border-slate-100 dark:border-slate-700 pb-3">
                <div>
                    <h2 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-boxes text-blue-600"></i> Invoice Line Items
                    </h2>
                    <p class="text-xs text-slate-500">Add, adjust, or remove items. Stock will synchronize automatically.</p>
                </div>

                {{-- Add Item Search Input --}}
                <div class="w-full sm:w-96 relative">
                    <div class="relative">
                        <input type="text"
                            x-model="productQuery"
                            @input.debounce.300ms="searchProduct()"
                            placeholder="🔍 Scan barcode or type product name..."
                            class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl pl-9 pr-4 py-2 text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-search absolute left-3 top-3 text-slate-400 text-xs"></i>
                    </div>

                    {{-- Search Dropdown --}}
                    <div x-show="searchResults.length > 0"
                        @click.away="searchResults = []"
                        class="absolute left-0 right-0 top-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl z-50 max-h-60 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                        <template x-for="item in searchResults" :key="item.id">
                            <div @click="addItem(item)" class="p-3 hover:bg-blue-50 dark:hover:bg-blue-900/30 cursor-pointer flex justify-between items-center transition">
                                <div>
                                    <div class="font-bold text-sm text-slate-800 dark:text-white flex items-center gap-2">
                                        <span x-text="item.description || item.name"></span>
                                        <span x-show="item.item_type === 'Service'" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">SERVICE</span>
                                    </div>
                                    <div class="text-xs text-slate-400">
                                        Code: <span x-text="item.code"></span> |
                                        <span x-show="item.item_type !== 'Service'">Stock: <strong class="text-emerald-600" x-text="item.on_hand"></strong></span>
                                        <span x-show="item.item_type === 'Service'" class="text-indigo-400 font-semibold">Non-Stock</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-sm text-slate-800 dark:text-white">Rs. <span x-text="parseFloat(item.sale_rate || item.sale_price || item.price || 0).toFixed(2)"></span></div>
                                    <span class="text-[10px] text-blue-600 font-bold">+ Add Line</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700/60">
                <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-900/80 text-xs uppercase font-bold text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">#</th>
                            <th class="py-3 px-4">Item Name / Code</th>
                            <th class="py-3 px-4 w-32 text-center">Qty</th>
                            <th class="py-3 px-4 w-36 text-right">Unit Rate (Rs.)</th>
                            <th class="py-3 px-4 w-36 text-right">Total (Rs.)</th>
                            <th class="py-3 px-4 w-36 text-center">Stock Delta</th>
                            <th class="py-3 px-4 w-16 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <template x-for="(row, index) in items" :key="row.uid">
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                <td class="py-3 px-4 text-center font-bold text-slate-400" x-text="index + 1"></td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                        <span x-text="row.item_name"></span>
                                        <span x-show="row.item_type === 'Service'" class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">SERVICE</span>
                                    </div>
                                    <div class="text-xs text-slate-400" x-text="'Code: ' + (row.item_code || 'N/A')"></div>

                                    {{-- Hidden Inputs for Form Submission --}}
                                    <input type="hidden" :name="'items[' + index + '][item_id]'" :value="row.item_id">
                                    <input type="hidden" :name="'items[' + index + '][batch_id]'" :value="row.batch_id || ''">
                                    <input type="hidden" :name="'items[' + index + '][qty]'" :value="row.qty">
                                    <input type="hidden" :name="'items[' + index + '][rate]'" :value="row.rate">
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <input type="number" step="any" min="0.01"
                                        x-model.number="row.qty"
                                        @input="recalculateTotals()"
                                        class="w-24 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-center font-bold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 text-sm">
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <input type="number" step="any" min="0"
                                        x-model.number="row.rate"
                                        @input="recalculateTotals()"
                                        class="w-28 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-right font-bold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500 text-sm">
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-slate-800 dark:text-white">
                                    Rs. <span x-text="(row.qty * row.rate).toFixed(2)"></span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <template x-if="row.item_type === 'Service'">
                                        <span class="text-xs text-indigo-400 font-semibold">Exempt</span>
                                    </template>
                                    <template x-if="row.item_type !== 'Service'">
                                        <div>
                                            <template x-if="getRowDelta(row) > 0">
                                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300">
                                                    -<span x-text="getRowDelta(row)"></span> to Deduct
                                                </span>
                                            </template>
                                            <template x-if="getRowDelta(row) < 0">
                                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300">
                                                    +<span x-text="Math.abs(getRowDelta(row))"></span> to Return
                                                </span>
                                            </template>
                                            <template x-if="getRowDelta(row) === 0">
                                                <span class="text-xs text-slate-400 font-medium">No Change</span>
                                            </template>
                                        </div>
                                    </template>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700 p-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition" title="Remove line">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <tr x-show="items.length === 0">
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                <i class="fas fa-shopping-basket text-3xl mb-2 opacity-40"></i>
                                <p class="font-bold">No items on this invoice.</p>
                                <p class="text-xs">Use the search bar above to add products.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Removed Items Alert --}}
            <div x-show="removedOriginalItems.length > 0" class="mt-4 p-3 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40 rounded-xl text-xs text-emerald-800 dark:text-emerald-300">
                <strong class="font-bold"><i class="fas fa-undo mr-1"></i> Stock will be returned for removed products:</strong>
                <ul class="list-disc list-inside mt-1 space-y-0.5">
                    <template x-for="rem in removedOriginalItems" :key="rem.item_id">
                        <li>
                            <span x-text="rem.item_name"></span>: <strong class="font-bold" x-text="'+' + rem.original_qty + ' units'"></strong> will be restored to available stock.
                        </li>
                    </template>
                </ul>
            </div>
        </div>

        {{-- Financial Calculations & Audit Reason Card --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- Audit Reason & Notes (2 Cols) --}}
            <div class="md:col-span-2 bg-white dark:bg-slate-800/80 rounded-2xl shadow-xl shadow-black/5 border border-slate-100 dark:border-slate-700/50 p-6 space-y-4">
                <h2 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2 border-b border-slate-100 dark:border-slate-700 pb-3">
                    <i class="fas fa-clipboard-check text-indigo-600"></i> Audit Reason & Authorization Notes
                </h2>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                        Reason for Editing Invoice <span class="text-slate-400 font-normal lowercase">(optional — recorded in version log)</span>
                    </label>
                    <textarea name="reason" rows="2" x-model="reason"
                        placeholder="e.g. Customer returned 2 items, added missed service line, adjusted rate..."
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl p-3 text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-blue-500"></textarea>
                    <p class="text-xs text-slate-400 mt-1">This reason will be recorded in Version #{{ ($sale->versions->max('version_number') ?? 1) + 1 }} and visible in audit history.</p>
                </div>

                {{-- Live Stock Delta Summary Box --}}
                <div class="bg-slate-50 dark:bg-slate-900/90 rounded-xl p-4 border border-slate-200 dark:border-slate-800">
                    <h3 class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-2">Live Inventory Synchronization Preview</h3>
                    <div class="text-xs text-slate-600 dark:text-slate-300 space-y-1">
                        <template x-for="delta in stockDeltaList" :key="delta.item_id">
                            <div class="flex justify-between items-center py-0.5">
                                <span class="font-medium" x-text="delta.item_name"></span>
                                <span class="font-bold font-mono"
                                    :class="{'text-emerald-600': delta.diff < 0, 'text-amber-600': delta.diff > 0, 'text-slate-400': delta.diff === 0}"
                                    x-text="delta.diff > 0 ? ('-' + delta.diff + ' (Deduct Stock)') : (delta.diff < 0 ? ('+' + Math.abs(delta.diff) + ' (Return Stock)') : '0 (Unchanged)')">
                                </span>
                            </div>
                        </template>
                        <div x-show="stockDeltaList.length === 0" class="text-slate-400 italic">
                            No stock changes required.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Totals & Submission Card (1 Col) --}}
            <div class="bg-white dark:bg-slate-800/80 rounded-2xl shadow-xl shadow-black/5 border border-slate-100 dark:border-slate-700/50 p-6 flex flex-col justify-between space-y-4">
                <h2 class="text-base font-bold text-slate-800 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-3">
                    Invoice Summary
                </h2>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Subtotal</span>
                        <span class="font-bold text-slate-800 dark:text-white">Rs. <span x-text="subtotal.toFixed(2)"></span></span>
                    </div>

                    {{-- Discount Input --}}
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 dark:text-slate-400">Discount (Rs.)</span>
                        <input type="number" step="any" min="0" name="discount_total" x-model.number="discountTotal" @input="recalculateTotals()"
                            class="w-28 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-right font-bold text-slate-800 dark:text-slate-100 text-sm">
                    </div>

                    {{-- Tax Input --}}
                    <div class="flex justify-between items-center">
                        <span class="text-slate-600 dark:text-slate-400">Tax / VAT (Rs.)</span>
                        <input type="number" step="any" min="0" name="tax_total" x-model.number="taxTotal" @input="recalculateTotals()"
                            class="w-28 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-right font-bold text-slate-800 dark:text-slate-100 text-sm">
                    </div>

                    <div class="pt-3 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
                        <span class="font-extrabold text-base text-slate-800 dark:text-white">Grand Total</span>
                        <span class="font-black text-xl text-emerald-600 dark:text-emerald-400">Rs. <span x-text="grandTotal.toFixed(2)"></span></span>
                    </div>

                    {{-- Paid Amount --}}
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-slate-600 dark:text-slate-400 font-medium">Amount Received (Rs.)</span>
                        <input type="number" step="any" min="0" name="paid_amount" x-model.number="paidAmount" @input="recalculateTotals()"
                            class="w-28 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-2 py-1 text-right font-bold text-slate-800 dark:text-slate-100 text-sm">
                    </div>

                    {{-- Change / Due Indicator --}}
                    <template x-if="paymentMode === 'Cash'">
                        <div class="flex justify-between text-xs text-slate-500 pt-1">
                            <span>Change Due:</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">Rs. <span x-text="Math.max(0, paidAmount - grandTotal).toFixed(2)"></span></span>
                        </div>
                    </template>

                    <template x-if="paymentMode === 'Debit'">
                        <div class="flex justify-between text-xs pt-1">
                            <span class="text-red-500 font-bold">New Customer Debt:</span>
                            <span class="font-bold text-red-600">Rs. <span x-text="Math.max(0, grandTotal - paidAmount).toFixed(2)"></span></span>
                        </div>
                    </template>
                </div>

                {{-- Action Buttons --}}
                <div class="pt-4 border-t border-slate-200 dark:border-slate-700 space-y-2">
                    <button type="button" @click="submitForm($event)"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-xl font-bold text-sm shadow-lg shadow-blue-500/25 transition flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i> Save Changes & Sync Stock
                    </button>

                    <a href="{{ route('sales.today') }}" class="w-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 py-2.5 px-4 rounded-xl font-bold text-xs text-center block transition">
                        Cancel & Go Back
                    </a>
                </div>
            </div>
        </div>
    </form>

    {{-- Danger Zone: Cancel Invoice Card --}}
    <div class="bg-red-50/50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/40 rounded-2xl p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="text-base font-bold text-red-700 dark:text-red-400 flex items-center gap-2">
                    <i class="fas fa-ban"></i> Cancel This Invoice
                </h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                    Cancelling will restore all items back to stock, reverse wallet/customer balances, and create a permanent cancellation record in the audit log.
                </p>
            </div>

            <form method="POST" action="{{ route('sales.cancel', $sale->id) }}" onsubmit="return confirmCancellation(event)">
                @csrf
                <input type="hidden" name="reason" id="cancellation_reason_input" value="">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-md transition flex items-center gap-2">
                    <i class="fas fa-trash-alt"></i> Cancel Invoice
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function invoiceEditor() {
        return {
            customerId: @json($sale->customer_id),
            paymentMode: @json($sale->payment_mode ?? 'Cash'),
            walletId: @json($sale->wallet_id),
            discountTotal: parseFloat(@json($sale->discount_total ?? 0)),
            taxTotal: parseFloat(@json($sale->tax_total ?? 0)),
            paidAmount: parseFloat(@json($sale->paid_amount ?? $sale->grand_total)),
            reason: '',
            productQuery: '',
            searchResults: [],

            // Original items from server for calculating deltas
            originalItems: @json($originalItems ?? []),

            // Active editable items list
            items: @json($items ?? []),
            taxSettings: @json($taxSettings ?? ['tax_enabled' => false, 'tax_rate' => 0.00]),

            get subtotal() {
                let sum = 0;
                this.items.forEach(i => {
                    sum += (parseFloat(i.qty) || 0) * (parseFloat(i.rate) || 0);
                });
                return Math.round(sum * 100) / 100;
            },

            get calculatedTax() {
                if (!this.taxSettings || !this.taxSettings.tax_enabled) {
                    return parseFloat(this.taxTotal) || 0;
                }
                let rate = parseFloat(this.taxSettings.tax_rate) || 0;
                let taxable = Math.max(0, this.subtotal - (parseFloat(this.discountTotal) || 0));
                return Math.round((taxable * rate / 100) * 100) / 100;
            },

            get grandTotal() {
                let sub = this.subtotal;
                let disc = parseFloat(this.discountTotal) || 0;
                let tax = (this.taxSettings && this.taxSettings.tax_enabled) ? this.calculatedTax : (parseFloat(this.taxTotal) || 0);
                return Math.max(0, Math.round((sub - disc + tax) * 100) / 100);
            },

            init() {
                this.recalculateTotals();
            },

            searchProduct() {
                let q = this.productQuery.trim();
                if (q.length < 1) {
                    this.searchResults = [];
                    return;
                }

                fetch(`/api/products/search?q=${encodeURIComponent(q)}`)
                    .then(res => res.json())
                    .then(data => {
                        this.searchResults = data;
                    })
                    .catch(err => {
                        console.error('Search error:', err);
                        this.searchResults = [];
                    });
            },

            addItem(product) {
                // Check if already in items list
                let existing = this.items.find(i => i.item_id === product.id);
                if (existing) {
                    existing.qty = (parseFloat(existing.qty) || 0) + 1;
                } else {
                    this.items.push({
                        uid: Date.now() + Math.random(),
                        item_id: product.id,
                        item_name: product.description || product.name,
                        item_code: product.code || product.barcode,
                        item_type: product.item_type || 'Inventory',
                        batch_id: null,
                        qty: 1,
                        rate: parseFloat(product.sale_rate || product.sale_price || product.price || 0),
                    });
                }

                this.productQuery = '';
                this.searchResults = [];
                this.recalculateTotals();
            },

            removeItem(index) {
                this.items.splice(index, 1);
                this.recalculateTotals();
            },

            recalculateTotals() {
                if (this.taxSettings && this.taxSettings.tax_enabled) {
                    this.taxTotal = this.calculatedTax;
                }
                if (this.paymentMode === 'Cash' && (!this.paidAmount || this.paidAmount <= 0)) {
                    this.paidAmount = this.grandTotal;
                }
            },

            getRowDelta(row) {
                let orig = this.originalItems.find(o => o.item_id === row.item_id);
                let origQty = orig ? orig.qty : 0;
                return (parseFloat(row.qty) || 0) - origQty;
            },

            get removedOriginalItems() {
                return this.originalItems.filter(orig => {
                    return !this.items.some(curr => curr.item_id === orig.item_id);
                }).map(orig => ({
                    item_id: orig.item_id,
                    item_name: orig.item_name,
                    original_qty: orig.qty
                }));
            },

            get stockDeltaList() {
                let deltas = [];
                let itemIds = new Set([
                    ...this.originalItems.map(o => o.item_id),
                    ...this.items.map(i => i.item_id)
                ]);

                itemIds.forEach(id => {
                    let orig = this.originalItems.find(o => o.item_id === id);
                    let curr = this.items.find(i => i.item_id === id);
                    let isService = (orig && orig.item_type === 'Service') || (curr && curr.item_type === 'Service');

                    if (isService) return;

                    let oldQty = orig ? orig.qty : 0;
                    let newQty = curr ? parseFloat(curr.qty) || 0 : 0;
                    let diff = newQty - oldQty;

                    deltas.push({
                        item_id: id,
                        item_name: (curr ? curr.item_name : (orig ? orig.item_name : 'Item #' + id)),
                        diff: diff
                    });
                });

                return deltas;
            },

            submitForm(event) {
                if (this.items.length === 0) {
                    alert('The invoice must contain at least one item.');
                    return;
                }

                const confirmed = confirm(
                    'You are about to create a new version of this invoice.\n\n' +
                    'The previous version will remain available in invoice history.\n\n' +
                    'Continue?'
                );

                if (!confirmed) {
                    return;
                }

                if (!this.reason || !this.reason.trim()) {
                    this.reason = 'Invoice modified by Admin';
                }

                const form = document.getElementById('invoiceEditForm');
                if (form) {
                    form.submit();
                }
            }
        };
    }

    function confirmCancellation(event) {
        let reason = prompt('Please enter the reason for cancelling this invoice (Required):');
        if (!reason || !reason.trim()) {
            alert('Cancellation aborted. A reason is required.');
            event.preventDefault();
            return false;
        }
        document.getElementById('cancellation_reason_input').value = reason.trim();
        return true;
    }
</script>
@endsection
