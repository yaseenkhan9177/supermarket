@extends('layouts.admin')

@section('navbar_subtitle', 'Purchase Entry — Multi-Payment Bill')

@section('content')
<div x-data="purchaseForm()" @supplier-selected.window="onSupplierSelected($event.detail)">

    <!-- Flash Messages -->
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({ title: 'Success!', text: "{{ session('success') }}", icon: 'success', background: '#1f2937', color: '#fff', confirmButtonColor: '#4f46e5' });
        });
    </script>
    @endif
    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({ title: 'Error!', text: "{{ session('error') }}", icon: 'error', background: '#1f2937', color: '#fff', confirmButtonColor: '#ef4444' });
        });
    </script>
    @endif

    <div class="container mx-auto px-4 max-w-[1400px] pb-48">

        <!-- Top Header Bar / Navigation Switcher -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-white flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-600/30">
                        <i class="fas fa-truck-loading"></i>
                    </span>
                    Purchase Entry
                </h1>
                <p class="text-xs text-gray-400 mt-1">Record direct cash purchases, store incoming stock, allocate charges, and split payments.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('purchases.create-credit') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                    <i class="fas fa-file-invoice-dollar"></i> Credit Purchase (Pay Later)
                </a>
                <a href="{{ route('supplier-returns.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                    <i class="fas fa-rotate-left"></i> Supplier Returns
                </a>
            </div>
        </div>

        <form action="/purchases/store" method="POST" id="purchaseForm" @submit.prevent="submitForm">
            @csrf

            <!-- Top Panel: Vendor + Bill Details -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                <!-- Vendor Panel -->
                <div class="bg-gray-900 p-6 rounded-2xl border border-gray-800 shadow-xl">
                    <div class="flex items-center gap-2 mb-4 border-b border-gray-800 pb-3">
                        <i class="fas fa-user-tie text-indigo-400"></i>
                        <h3 class="text-white font-bold text-sm uppercase tracking-wider">Vendor / Supplier</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Select Vendor *</label>
                            <div class="flex gap-2" @open-add-supplier-modal.window="showSupplierModal = true">
                                <x-supplier-search id="supplierSelect" name="supplier_id" :add-new="true" :required="true" />
                                <button type="button" @click="showSupplierModal = true"
                                        class="bg-indigo-600 px-3.5 rounded-lg text-white hover:bg-indigo-700 transition shrink-0">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Credit Banner (shows if supplier has a return credit) -->
                        <div x-show="supplierCredit > 0" x-transition class="bg-purple-900/40 border border-purple-500/60 rounded-xl p-3.5 flex items-start gap-3">
                            <i class="fas fa-tag text-purple-400 mt-0.5 text-lg"></i>
                            <div>
                                <p class="text-purple-200 font-bold text-sm">Return Credit Available!</p>
                                <p class="text-purple-300 text-xs mt-0.5">
                                    Rs. <span class="font-mono font-bold" x-text="supplierCredit.toFixed(2)"></span>
                                    will be automatically deducted from this bill's total.
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Vendor Bill #</label>
                            <input type="text" name="vendor_bill_no" placeholder="e.g. INV-9988"
                                   class="w-full bg-gray-950 border border-gray-700 rounded-xl p-2.5 text-sm text-white focus:border-indigo-500 outline-none">
                        </div>
                    </div>
                </div>

                <!-- Bill Details Panel -->
                <div class="bg-gray-900 p-6 rounded-2xl border border-gray-800 shadow-xl">
                    <div class="flex items-center gap-2 mb-4 border-b border-gray-800 pb-3">
                        <i class="fas fa-file-invoice-dollar text-indigo-400"></i>
                        <h3 class="text-white font-bold text-sm uppercase tracking-wider">Bill Details</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Purchase ID</label>
                            <input type="text" name="purchase_no" value="PO-{{ date('Y') }}-{{ rand(1000,9999) }}" readonly
                                   class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-sm font-mono text-gray-400">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Date</label>
                            <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}"
                                   class="w-full bg-gray-950 border border-gray-700 rounded-xl p-2.5 text-sm text-white focus:border-indigo-500 outline-none">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Memo / Remarks</label>
                            <input type="text" name="memo" placeholder="Optional notes..."
                                   class="w-full bg-gray-950 border border-gray-700 rounded-xl p-2.5 text-sm text-white focus:border-indigo-500 outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-gray-900 rounded-2xl border border-gray-800 shadow-xl overflow-hidden mb-6">
                <div class="p-4 bg-gray-800/60 border-b border-gray-800 flex justify-between items-center">
                    <h3 class="font-bold text-white text-sm flex items-center gap-2">
                        <i class="fas fa-boxes text-indigo-400"></i> Items Received
                    </h3>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="openQuickProductModal({})"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition shadow flex items-center gap-1.5">
                            <i class="fas fa-box-open"></i> New Product
                        </button>
                        <button type="button" @click="addRow()"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow">
                            <i class="fas fa-plus mr-1"></i> Add Row
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="text-[11px] font-bold text-gray-400 uppercase bg-gray-950 border-b border-gray-800">
                                <th class="p-3 w-10">#</th>
                                <th class="p-3 w-32">Barcode</th>
                                <th class="p-3 min-w-[200px]">Description</th>
                                <th class="p-3 w-28">Batch</th>
                                <th class="p-3 w-32">Expiry</th>
                                <th class="p-3 w-36">Store To</th>
                                <th class="p-3 w-20 text-center">Qty</th>
                                <th class="p-3 w-28 text-right">Cost</th>
                                <th class="p-3 w-20 text-center">Stock</th>
                                <th class="p-3 w-28 text-right">Total</th>
                                <th class="p-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800/60">
                            <template x-for="(row, index) in rows" :key="index">
                                <tr class="hover:bg-gray-800/40 transition">
                                    <td class="p-3 text-center text-gray-500 text-xs" x-text="index + 1"></td>
                                    <td class="p-3">
                                        <input type="text" :name="`items[${index}][code]`" x-model="row.code"
                                               @keydown.enter.prevent="fetchProduct(index)"
                                               @blur="fetchProduct(index)"
                                               class="w-full p-2 bg-gray-950 border border-gray-700 rounded-lg text-xs text-white focus:ring-1 focus:ring-indigo-500 outline-none font-mono"
                                               placeholder="Scan...">
                                    </td>
                                    <td class="p-3">
                                        <input type="text" :name="`items[${index}][name]`" x-model="row.name"
                                               class="w-full p-2 bg-gray-950 border border-gray-700 rounded-lg text-xs text-white focus:ring-1 focus:ring-indigo-500 outline-none"
                                               placeholder="Item name...">
                                        <input type="hidden" :name="`items[${index}][item_id]`" x-model="row.item_id">
                                    </td>
                                    <td class="p-3">
                                        <input type="text" :name="`items[${index}][batch_no]`" x-model="row.batch_no"
                                               class="w-full p-2 bg-gray-950 border border-gray-700 rounded-lg text-xs uppercase text-white focus:ring-1 focus:ring-indigo-500 outline-none font-mono"
                                               placeholder="BATCH">
                                    </td>
                                    <td class="p-3">
                                        <input type="date" :name="`items[${index}][expiry_date]`" x-model="row.expiry_date"
                                               class="w-full p-2 bg-gray-950 border border-gray-700 rounded-lg text-xs text-white focus:ring-1 focus:ring-indigo-500 outline-none">
                                    </td>
                                    <td class="p-3">
                                        <select :name="`items[${index}][godam_id]`" x-model="row.godam_id"
                                                class="w-full p-2 bg-gray-950 border border-gray-700 rounded-lg text-xs text-white focus:ring-1 focus:ring-indigo-500 outline-none">
                                            <option value="">— Shop Floor —</option>
                                            @foreach($godams as $g)
                                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="p-3">
                                        <input type="number" x-model="row.qty" :name="`items[${index}][qty]`" min="1"
                                               class="w-full p-2 bg-gray-950 border border-gray-700 rounded-lg text-center text-xs font-bold text-indigo-400 focus:ring-1 focus:ring-indigo-500 outline-none">
                                    </td>
                                    <td class="p-3">
                                        <input type="number" step="0.01" x-model="row.rate" :name="`items[${index}][rate]`"
                                               class="w-full p-2 bg-gray-950 border border-gray-700 rounded-lg text-right text-xs text-white focus:ring-1 focus:ring-indigo-500 outline-none">
                                    </td>
                                    <td class="p-3 text-center">
                                        <span :class="row.stock > 0 ? 'bg-green-900/50 text-green-400 border border-green-700' : 'bg-gray-800 text-gray-500'"
                                              class="text-[11px] font-bold px-2 py-0.5 rounded-full"
                                              x-text="row.stock !== null ? row.stock : '—'"></span>
                                    </td>
                                    <td class="p-3 text-right font-bold text-white">
                                        <span x-text="'Rs. ' + (row.qty * row.rate).toFixed(2)"></span>
                                    </td>
                                    <td class="p-3 text-center">
                                        <button type="button" @click="removeRow(index)" class="text-gray-500 hover:text-red-400 transition p-1">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            <!-- Live Search Row -->
                            <tr class="bg-indigo-950/20 border-t border-indigo-900/40">
                                <td class="p-3 text-center"><i class="fas fa-search text-indigo-400"></i></td>
                                <td class="p-3 relative" colspan="9">
                                    <x-smart-product-search @product-selected="addItem($event.detail)" @product-not-found="openQuickProductModal($event.detail)" placeholder="🔍 Search product by name, code or barcode..." />
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- IMPORT & CLEARING CHARGES / TAXES PANEL -->
            <div class="bg-gray-900 rounded-2xl border border-gray-800 shadow-xl overflow-hidden mb-6">
                <div class="p-4 bg-amber-950/30 border-b border-amber-900/40 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-file-invoice text-amber-400"></i>
                        <h3 class="font-bold text-amber-200 text-sm">Import & Clearing Charges / Taxes</h3>
                        <span class="text-[10px] text-amber-300 bg-amber-900/50 border border-amber-700/50 px-2 py-0.5 rounded-full font-semibold">
                            Add custom charge items
                        </span>
                    </div>
                </div>

                <div class="p-5">
                    <!-- Add charge row -->
                    <div class="flex gap-3 items-end mb-4">
                        <div class="flex-1">
                            <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Charge Type</label>
                            <div class="flex gap-2">
                                <select id="chargeTypeSelect" x-model="newChargeTypeId"
                                        class="w-full bg-gray-950 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:ring-2 focus:ring-amber-500 outline-none">
                                    <option value="">— Select Charge Type —</option>
                                    @foreach(\App\Models\TaxChargeType::orderBy('name')->get() as $ct)
                                    <option value="{{ $ct->id }}">{{ $ct->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button"
                                        onclick="openChargeTypeModal()"
                                        title="Add New Charge Type"
                                        class="bg-amber-600 hover:bg-amber-700 text-white px-3.5 py-2.5 rounded-xl text-xs font-bold transition flex-shrink-0">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="w-44">
                            <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Amount (Rs.)</label>
                            <input type="number" step="0.01" x-model="newChargeAmount"
                                   @keydown.enter.prevent="addCharge()"
                                   class="w-full bg-gray-950 border border-gray-700 rounded-xl p-2.5 text-right text-xs font-bold text-amber-300 focus:ring-2 focus:ring-amber-500 outline-none"
                                   placeholder="0.00">
                        </div>
                        <div>
                            <button type="button" @click="addCharge()"
                                    class="bg-amber-600 text-white hover:bg-amber-700 px-5 py-2.5 rounded-xl text-xs font-bold transition">
                                <i class="fas fa-plus mr-1"></i> Add
                            </button>
                        </div>
                    </div>

                    <!-- Added charges list -->
                    <div x-show="charges.length > 0" class="space-y-2">
                        <template x-for="(ch, ci) in charges" :key="ci">
                            <div class="flex items-center justify-between p-3 bg-gray-950/60 rounded-xl border border-amber-900/40">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 bg-amber-600 text-white rounded-full flex items-center justify-center text-xs font-bold" x-text="ci + 1"></span>
                                    <span class="font-bold text-gray-200 text-xs" x-text="ch.name"></span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-mono font-bold text-amber-300 text-xs" x-text="'Rs. ' + parseFloat(ch.amount).toFixed(2)"></span>
                                    <button type="button" @click="removeCharge(ci)"
                                            class="text-red-400 hover:text-red-300 p-1 rounded hover:bg-red-950/40 transition">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <input type="hidden" :name="`charges[${ci}][tax_charge_type_id]`" :value="ch.type_id">
                                <input type="hidden" :name="`charges[${ci}][amount]`" :value="ch.amount">
                            </div>
                        </template>

                        <!-- Charges Total -->
                        <div class="flex justify-end pt-2 border-t border-gray-800">
                            <div class="text-right">
                                <span class="text-[10px] font-bold text-gray-400 uppercase block">Total Charges / Tax</span>
                                <span class="text-base font-bold text-amber-400" x-text="'Rs. ' + chargesTotal.toFixed(2)"></span>
                            </div>
                        </div>
                    </div>

                    <div x-show="charges.length === 0" class="text-center py-3 text-gray-500 text-xs">
                        <i class="fas fa-info-circle mr-1 text-amber-500/70"></i> No additional charges added yet.
                    </div>
                </div>
            </div>

            <!-- PAYMENT SPLIT PANEL -->
            <div class="bg-gray-900 rounded-2xl border border-gray-800 shadow-xl overflow-hidden mb-6">
                <div class="p-4 bg-emerald-950/30 border-b border-emerald-900/40 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-money-bill-wave text-emerald-400"></i>
                        <h3 class="font-bold text-emerald-200 text-sm">Payment Split</h3>
                        <span class="text-[10px] text-emerald-300 bg-emerald-900/50 border border-emerald-700/50 px-2 py-0.5 rounded-full font-semibold">
                            Split across multiple payment sources
                        </span>
                    </div>
                    <button type="button" @click="addPaymentRow()"
                            class="bg-emerald-600 text-white hover:bg-emerald-700 px-4 py-2 rounded-xl text-xs font-bold transition">
                        <i class="fas fa-plus mr-1"></i> Add Source
                    </button>
                </div>

                <!-- Credit Applied Banner -->
                <div x-show="supplierCredit > 0" class="bg-purple-950/40 border-b border-purple-900/50 px-6 py-3 flex items-center gap-3">
                    <i class="fas fa-magic text-purple-400"></i>
                    <p class="text-xs text-purple-200">
                        <strong>Auto-Credit Applied:</strong>
                        Rs. <span class="font-mono font-bold" x-text="supplierCredit.toFixed(2)"></span>
                        return credit will be deducted from the net total automatically.
                        Net payable after credit = Rs. <span class="font-mono font-bold text-purple-300" x-text="netAfterCredit.toFixed(2)"></span>
                    </p>
                </div>

                <div class="p-5 space-y-3">
                    <template x-for="(pay, idx) in payments" :key="idx">
                        <div class="flex flex-col sm:flex-row gap-3 items-center p-3 bg-gray-950/70 rounded-xl border border-gray-800">
                            <!-- Payment Method -->
                            <div class="flex-1 w-full">
                                <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Source</label>
                                <select :name="`payments[${idx}][method]`" x-model="pay.method"
                                        class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2 text-xs text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                                    <option value="Cash Drawer">💵 Cash Drawer</option>
                                    <option value="Bank Transfer">🏦 Bank Transfer</option>
                                    <option value="EasyPaisa">📱 EasyPaisa</option>
                                    <option value="JazzCash">📱 JazzCash</option>
                                    <option value="Cheque">📝 Cheque</option>
                                    <option value="Other">🔄 Other</option>
                                </select>
                            </div>
                            <!-- Account (optional, for double-entry) -->
                            <div class="flex-1 w-full">
                                <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Linked Account</label>
                                <select :name="`payments[${idx}][account_id]`" x-model="pay.account_id"
                                        class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2 text-xs text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                                    <option value="">— No Account —</option>
                                    @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Amount -->
                            <div class="w-full sm:w-36">
                                <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Amount (Rs.)</label>
                                <input type="number" step="0.01" :name="`payments[${idx}][amount]`" x-model="pay.amount"
                                       class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2 text-right text-xs font-bold text-emerald-300 focus:ring-2 focus:ring-emerald-500 outline-none"
                                       placeholder="0.00">
                            </div>
                            <!-- Reference -->
                            <div class="w-full sm:w-36">
                                <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Ref / Cheque #</label>
                                <input type="text" :name="`payments[${idx}][reference_no]`" x-model="pay.reference_no"
                                       class="w-full bg-gray-900 border border-gray-700 rounded-lg p-2 text-xs text-white focus:ring-2 focus:ring-emerald-500 outline-none"
                                       placeholder="Optional">
                            </div>
                            <!-- Remove -->
                            <div class="pt-4">
                                <button type="button" @click="removePaymentRow(idx)"
                                        x-show="payments.length > 1"
                                        class="text-red-400 hover:text-red-300 p-2 rounded-lg hover:bg-red-950/40 transition">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Split Summary -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pt-3 border-t border-gray-800">
                        <div class="flex flex-wrap gap-6 text-xs">
                            <div>
                                <span class="text-gray-400 text-[10px] uppercase font-bold block">Gross Bill</span>
                                <span class="font-bold text-gray-200" x-text="'Rs. ' + grossBill.toFixed(2)"></span>
                            </div>
                            <div x-show="supplierCredit > 0">
                                <span class="text-purple-400 text-[10px] uppercase font-bold block">Credit Applied</span>
                                <span class="font-bold text-purple-300" x-text="'- Rs. ' + Math.min(supplierCredit, grossBill).toFixed(2)"></span>
                            </div>
                            <div>
                                <span class="text-gray-400 text-[10px] uppercase font-bold block">Net Payable</span>
                                <span class="font-bold text-indigo-400 text-sm" x-text="'Rs. ' + netAfterCredit.toFixed(2)"></span>
                            </div>
                            <div>
                                <span class="text-gray-400 text-[10px] uppercase font-bold block">Allocated</span>
                                <span class="font-bold text-sm" :class="splitRemaining < -0.5 ? 'text-red-400' : 'text-emerald-400'"
                                      x-text="'Rs. ' + splitTotal.toFixed(2)"></span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-bold uppercase"
                                 :class="Math.abs(splitRemaining) < 0.5 ? 'text-emerald-400' : 'text-red-400'">
                                <span x-show="Math.abs(splitRemaining) < 0.5" class="flex items-center gap-1">
                                    <i class="fas fa-check-circle text-emerald-400"></i> Balanced ✓
                                </span>
                                <span x-show="Math.abs(splitRemaining) >= 0.5" class="flex items-center gap-1">
                                    <i class="fas fa-exclamation-circle text-red-400"></i>
                                    Remaining: Rs. <span x-text="Math.abs(splitRemaining).toFixed(2)"></span>
                                    <span x-show="splitRemaining > 0.5"> (short)</span>
                                    <span x-show="splitRemaining < -0.5"> (excess)</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <!-- Sticky Bottom Bar -->
    <div class="fixed bottom-0 left-0 w-full bg-gray-900 border-t border-gray-800 shadow-[0_-5px_25px_rgba(0,0,0,0.5)] z-40 p-3 sm:p-4">
        <div class="container mx-auto max-w-[1400px] flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
            <div class="flex flex-wrap justify-between sm:justify-start gap-4 sm:gap-6 text-xs">
                <div class="text-left sm:text-right">
                    <span class="block text-[10px] text-gray-400 uppercase">Subtotal</span>
                    <span class="font-bold text-gray-200 text-sm" x-text="'Rs. ' + subtotal.toFixed(2)"></span>
                </div>
                <div class="text-left sm:text-right">
                    <span class="block text-[10px] text-gray-400 uppercase">Charges/Tax</span>
                    <span class="font-bold text-amber-400 text-sm" x-text="'Rs. ' + chargesTotal.toFixed(2)"></span>
                    <input type="hidden" x-model="tax" name="tax_amount">
                </div>
                <div>
                    <span class="block text-[10px] text-gray-400 uppercase">Discount</span>
                    <input type="number" x-model="discount" name="discount" class="w-20 bg-gray-950 border border-gray-700 rounded p-1 text-right text-xs text-white" placeholder="0">
                </div>
            </div>

            <div class="flex items-center justify-between sm:justify-end gap-3 sm:gap-6">
                <div class="text-right">
                    <span class="block text-[10px] font-bold text-gray-400 uppercase">Net Payable</span>
                    <span class="block text-xl sm:text-2xl font-bold text-indigo-400 font-mono" x-text="'Rs. ' + netAfterCredit.toFixed(2)"></span>
                </div>
                <div class="hidden sm:block h-10 w-px bg-gray-800"></div>
                <button type="button" @click="submitForm()"
                        :disabled="Math.abs(splitRemaining) > 0.5 || rows.filter(r=>r.item_id||r.code).length === 0"
                        :class="Math.abs(splitRemaining) < 0.5 && rows.filter(r=>r.item_id||r.code).length > 0 ? 'bg-indigo-600 hover:bg-indigo-700 text-white cursor-pointer' : 'bg-gray-800 text-gray-500 cursor-not-allowed border border-gray-700'"
                        class="flex-1 sm:flex-none justify-center px-6 sm:px-8 py-2.5 sm:py-3 font-bold rounded-xl shadow-lg transition transform active:scale-95 disabled:transform-none flex items-center gap-2 text-xs sm:text-sm">
                    <i class="fas fa-save"></i> Save Bill
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Add Supplier Modal -->
    <div x-show="showSupplierModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 backdrop-blur-sm" style="display:none;">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-6 text-white">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="fas fa-user-plus text-indigo-400"></i> Add New Supplier
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Company / Name *</label>
                    <input type="text" x-model="newSupplier.name" class="w-full bg-gray-950 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:border-indigo-500 outline-none" placeholder="e.g. ABC Distributors">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Phone</label>
                    <input type="text" x-model="newSupplier.phone" class="w-full bg-gray-950 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:border-indigo-500 outline-none" placeholder="e.g. 03001234567">
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" @click="showSupplierModal = false" class="px-4 py-2 border border-gray-700 rounded-xl text-gray-300 hover:bg-gray-800 text-xs font-bold">Cancel</button>
                <button type="button" @click="saveSupplier()" class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 text-xs font-bold">Save Supplier</button>
            </div>
        </div>
    </div>

    <!-- Modal: Add New Charge Type -->
    <div id="chargeTypeModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-[100] hidden">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 w-full max-w-md shadow-2xl text-white">
            <div class="flex justify-between items-center border-b border-gray-800 pb-3 mb-4">
                <h3 class="font-bold text-amber-400 flex items-center gap-2 text-sm">
                    <i class="fas fa-file-circle-plus"></i> Add New Charge Type
                </h3>
                <button type="button" onclick="closeChargeTypeModal()" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase block mb-1">Charge Type Name *</label>
                    <input type="text" id="newChargeTypeName" placeholder="e.g. Customs Clearance, Freight"
                           class="w-full bg-gray-950 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:ring-2 focus:ring-amber-500 outline-none">
                    <p id="chargeTypeModalError" class="text-red-400 text-xs mt-1 hidden"></p>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeChargeTypeModal()" class="px-4 py-2 border border-gray-700 rounded-xl text-gray-300 hover:bg-gray-800 text-xs font-bold">Cancel</button>
                <button type="button" id="saveChargeTypeBtn" onclick="saveChargeType()" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition">
                    <i class="fas fa-save mr-1"></i> Save
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Quick Add Product -->
    <div x-show="showQuickProductModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-[100]" style="display:none;">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 w-full max-w-lg shadow-2xl text-white">
            <div class="flex justify-between items-center border-b border-gray-800 pb-3 mb-4">
                <h3 class="font-bold text-indigo-400 flex items-center gap-2 text-sm">
                    <i class="fas fa-box-open"></i> Quick Add New Product
                </h3>
                <button type="button" @click="showQuickProductModal = false" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase block mb-1">Product Name / Description *</label>
                    <input type="text" x-model="quickProduct.description" placeholder="e.g. Cooking Oil 1L"
                           class="w-full bg-gray-950 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase block mb-1">Barcode / SKU</label>
                        <input type="text" x-model="quickProduct.barcode" placeholder="Barcode"
                               class="w-full bg-gray-950 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase block mb-1">Type</label>
                        <select x-model="quickProduct.item_type" class="w-full bg-gray-950 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="Inventory">Inventory</option>
                            <option value="Service">Service</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase block mb-1">Cost Rate *</label>
                        <input type="number" step="0.01" min="0" x-model="quickProduct.cost_rate" placeholder="Cost"
                               class="w-full bg-gray-950 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase block mb-1">Sale Price *</label>
                        <input type="number" step="0.01" min="0" x-model="quickProduct.price" placeholder="Price"
                               class="w-full bg-gray-950 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase block mb-1">Tax Rate %</label>
                        <input type="number" step="0.01" min="0" max="100" x-model="quickProduct.tax_rate" placeholder="Optional"
                               class="w-full bg-gray-950 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" @click="showQuickProductModal = false" class="px-4 py-2 border border-gray-700 rounded-xl text-gray-300 hover:bg-gray-800 text-xs font-bold">Cancel</button>
                <button type="button" @click="saveQuickProduct()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1">
                    <i class="fas fa-save"></i> Save Product
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function purchaseForm() {
        return {
            supplierId: '{{ request('supplier_id') ?? '' }}',
            rows: [
                @if($prefilledItem)
                {
                    item_id: '{{ $prefilledItem->id }}',
                    code: '{{ $prefilledItem->code }}',
                    name: '{{ addslashes($prefilledItem->description) }}',
                    batch_no: '',
                    expiry_date: '',
                    godam_id: '',
                    qty: 1,
                    rate: {{ $prefilledItem->cost_rate ?? 0 }},
                    stock: {{ $prefilledItem->on_hand ?? 0 }}
                }
                @else
                {
                    item_id: '', code: '', name: '', batch_no: '', expiry_date: '', godam_id: '', qty: 1, rate: 0, stock: null
                }
                @endif
            ],
            godams: @json($godams),
            payments: [{
                method: 'Cash Drawer', account_id: '', amount: 0, reference_no: ''
            }],
            tax: 0,
            discount: 0,
            supplierCredit: 0,
            showSupplierModal: false,
            showQuickProductModal: false,
            quickProduct: { description: '', code: '', barcode: '', cost_rate: 0, price: 0, tax_rate: '', item_type: 'Inventory' },
            openQuickProductModal(detail) {
                let q = (detail && detail.query) ? detail.query : '';
                this.quickProduct = {
                    description: q,
                    code: q,
                    barcode: q,
                    cost_rate: 0,
                    price: 0,
                    tax_rate: '',
                    item_type: 'Inventory'
                };
                this.showQuickProductModal = true;
            },
            async saveQuickProduct() {
                if (!this.quickProduct.description || !this.quickProduct.description.trim()) {
                    alert('Product name is required.');
                    return;
                }
                const payload = {
                    description: this.quickProduct.description.trim(),
                    code: this.quickProduct.code || '',
                    barcode: this.quickProduct.barcode || '',
                    cost_rate: parseFloat(this.quickProduct.cost_rate) || 0,
                    price: parseFloat(this.quickProduct.price) || 0,
                    tax_rate: (this.quickProduct.tax_rate !== '' && this.quickProduct.tax_rate !== null) ? parseFloat(this.quickProduct.tax_rate) : null,
                    item_type: this.quickProduct.item_type || 'Inventory'
                };
                try {
                    let res = await fetch('{{ route("items.quick-store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    let data = await res.json();
                    if (data.success) {
                        this.addItem({
                            id: data.product.id,
                            code: data.product.code,
                            name: data.product.description,
                            cost_price: data.product.cost_rate,
                            price: data.product.price,
                            stock_qty: 0
                        });
                        this.showQuickProductModal = false;
                        this.quickProduct = { description: '', code: '', barcode: '', cost_rate: 0, price: 0, tax_rate: '', item_type: 'Inventory' };
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ title: 'Added!', text: 'Product created and added to bill.', icon: 'success', background: '#1f2937', color: '#fff', timer: 1500, showConfirmButton: false });
                        }
                    } else {
                        alert(data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Error creating product.'));
                    }
                } catch(e) {
                    console.error('Quick store failed', e);
                    alert('Failed to create product.');
                }
            },
            newSupplier: { name: '', phone: '' },
            searchQuery: '',
            searchResults: [],
            charges: [],
            newChargeTypeId: '',
            newChargeAmount: 0,
            chargeTypeOptions: {
                @foreach(\App\Models\TaxChargeType::orderBy('name')->get() as $ct)
                '{{ $ct->id }}': '{{ $ct->name }}',
                @endforeach
            },
            init() {
                if (this.supplierId) {
                    this.onSupplierChange(this.supplierId);
                }
            },

            onSupplierSelected(detail) {
                if (detail && detail.id) {
                    this.supplierId = detail.id;
                    this.onSupplierChange(detail.id);
                } else {
                    this.supplierId = '';
                    this.supplierCredit = 0;
                }
            },

            // ── Computed ──────────────────────────────────────────────────────
            get subtotal() {
                return this.rows.reduce((s, r) => s + (parseFloat(r.qty || 0) * parseFloat(r.rate || 0)), 0);
            },
            get chargesTotal() {
                return this.charges.reduce((s, c) => s + parseFloat(c.amount || 0), 0);
            },
            get grossBill() {
                this.tax = this.chargesTotal;
                return this.subtotal + this.chargesTotal - parseFloat(this.discount || 0);
            },
            get netAfterCredit() {
                const credit = Math.min(this.supplierCredit, this.grossBill);
                return Math.max(0, this.grossBill - credit);
            },
            get splitTotal() {
                return this.payments.reduce((s, p) => s + parseFloat(p.amount || 0), 0);
            },
            get splitRemaining() {
                return this.netAfterCredit - this.splitTotal;
            },

            // ── Methods ───────────────────────────────────────────────────────
            addRow() {
                this.rows.push({ item_id: '', code: '', name: '', batch_no: '', expiry_date: '', godam_id: '', qty: 1, rate: 0, stock: null });
            },
            removeRow(index) {
                if (this.rows.length > 1) this.rows.splice(index, 1);
            },
            addPaymentRow() {
                this.payments.push({ method: 'Cash Drawer', account_id: '', amount: 0, reference_no: '' });
            },
            removePaymentRow(index) {
                if (this.payments.length > 1) this.payments.splice(index, 1);
            },
            addCharge() {
                if (!this.newChargeTypeId || !this.newChargeAmount || parseFloat(this.newChargeAmount) <= 0) {
                    Swal.fire({ title: 'Missing Info', text: 'Please select a charge type and enter an amount.', icon: 'warning', background: '#1f2937', color: '#fff', confirmButtonColor: '#f59e0b' });
                    return;
                }
                this.charges.push({
                    type_id: this.newChargeTypeId,
                    name: this.chargeTypeOptions[this.newChargeTypeId] || 'Unknown',
                    amount: parseFloat(this.newChargeAmount)
                });
                this.newChargeTypeId = '';
                this.newChargeAmount = 0;
            },
            removeCharge(index) {
                this.charges.splice(index, 1);
            },

            async onSupplierChange(supplierId) {
                this.supplierCredit = 0;
                if (!supplierId) return;
                try {
                    const res  = await fetch(`/api/supplier/${supplierId}/credit`);
                    const data = await res.json();
                    this.supplierCredit = parseFloat(data.credit_amount || 0);
                } catch(e) {
                    console.error('Failed to fetch supplier credit', e);
                }
            },

            async performSearch() {
                if (this.searchQuery.length < 1) { this.searchResults = []; return; }
                try {
                    const r = await fetch(`/cash-sales/search?q=${this.searchQuery}`);
                    this.searchResults = await r.json();
                } catch(e) { console.error('Search failed'); }
            },

            addItem(item) {
                let existing = this.rows.find(r => r.item_id == item.id);
                if (existing) {
                    existing.qty++;
                } else {
                    let emptyIdx = this.rows.findIndex(r => !r.item_id);
                    let newRow = {
                        item_id: item.id,
                        code: item.code,
                        name: item.name,
                        batch_no: '',
                        expiry_date: '',
                        godam_id: '',
                        qty: 1,
                        rate: item.cost_price || 0,
                        stock: item.stock_qty ?? 0
                    };
                    if (emptyIdx !== -1) {
                        this.rows[emptyIdx] = newRow;
                    } else {
                        this.rows.push(newRow);
                    }
                }
                this.searchQuery = '';
                this.searchResults = [];
            },

            selectFirstResult() {
                if (this.searchResults.length > 0) this.addItem(this.searchResults[0]);
            },

            async fetchProduct(index) {
                const code = this.rows[index].code ? this.rows[index].code.trim() : '';
                if (!code) return;

                try {
                    let response = await fetch(`/cash-sales/search?q=${code}`);
                    let data = await response.json();

                    if (data.length > 0) {
                        let item = data.find(i => i.code === code) || data[0];
                        this.rows[index].item_id = item.id;
                        this.rows[index].name = item.name;
                        this.rows[index].rate = item.cost_price || item.price || 0;
                        this.rows[index].stock = item.stock_qty ?? 0;

                        if (index === this.rows.length - 1) this.addRow();
                    } else {
                        Swal.fire({
                            title: 'New Item!',
                            text: 'This barcode does not exist yet. You can type the description and cost rate manually!',
                            icon: 'info',
                            background: '#1f2937',
                            color: '#fff',
                            timer: 3000,
                            showConfirmButton: false
                        });
                        this.rows[index].item_id = 'new';
                        this.rows[index].name = '';
                        this.rows[index].rate = 0;
                        this.rows[index].stock = 0;
                        if (index === this.rows.length - 1) this.addRow();
                    }
                } catch (error) {
                    console.error("Search failed");
                }
            },

            async saveSupplier() {
                if (!this.newSupplier.name) {
                    alert('Supplier name is required.');
                    return;
                }

                try {
                    let response = await fetch('/suppliers/quick-store', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.newSupplier)
                    });

                    let data = await response.json();
                    if (data.success) {
                        this.supplierId = data.supplier.id;
                        this.onSupplierChange(data.supplier.id);

                        this.showSupplierModal = false;
                        this.newSupplier = { name: '', phone: '' };

                        Swal.fire({
                            title: 'Added!',
                            text: 'Supplier saved successfully.',
                            icon: 'success',
                            background: '#1f2937',
                            color: '#fff',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        alert('Failed to save supplier.');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred.');
                }
            },

            submitForm() {
                let validSupplier = document.getElementById('supplierSelect')?.value || this.supplierId;
                if (!validSupplier) {
                    Swal.fire({ title: 'Error', text: 'Please select a supplier', icon: 'error', background: '#1f2937', color: '#fff', confirmButtonColor: '#ef4444' });
                    return;
                }
                if (Math.abs(this.splitRemaining) > 0.5) {
                    Swal.fire({ title: 'Payment Split Error', text: 'The payment split amount must equal the Net Payable total.', icon: 'warning', background: '#1f2937', color: '#fff', confirmButtonColor: '#f59e0b' });
                    return;
                }
                document.getElementById('purchaseForm').submit();
            }
        }
    }

    /* Modal helpers for TaxChargeType */
    function openChargeTypeModal() {
        document.getElementById('newChargeTypeName').value = '';
        document.getElementById('chargeTypeModalError').classList.add('hidden');
        document.getElementById('chargeTypeModal').classList.remove('hidden');
    }

    function closeChargeTypeModal() {
        document.getElementById('chargeTypeModal').classList.add('hidden');
    }

    function showChargeTypeError(msg) {
        var errEl = document.getElementById('chargeTypeModalError');
        errEl.textContent = msg;
        errEl.classList.remove('hidden');
    }

    function saveChargeType() {
        var name = document.getElementById('newChargeTypeName').value.trim();
        if (!name) {
            showChargeTypeError('Please enter a charge type name.');
            return;
        }

        var saveBtn = document.getElementById('saveChargeTypeBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        var csrf = '{{ csrf_token() }}';

        fetch('/tax-charge-types/quick-store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({ name: name })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Save';

            if (data.success) {
                var select = document.getElementById('chargeTypeSelect');
                var newOpt = document.createElement('option');
                newOpt.value = data.id;
                newOpt.textContent = data.name;
                select.appendChild(newOpt);
                select.value = data.id;

                var alpineRoot = document.querySelector('[x-data]');
                if (alpineRoot && alpineRoot._x_dataStack) {
                    try {
                        var alpineData = alpineRoot._x_dataStack[0];
                        alpineData.newChargeTypeId = String(data.id);
                        alpineData.chargeTypeOptions[String(data.id)] = data.name;
                    } catch (e) {}
                }
                select.dispatchEvent(new Event('change'));
                closeChargeTypeModal();
            } else {
                showChargeTypeError(data.message || 'Failed to save. Please try again.');
            }
        })
        .catch(function (err) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Save';
            showChargeTypeError('Network error. Please check your connection.');
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            var modal = document.getElementById('chargeTypeModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeChargeTypeModal();
            }
        }
    });
</script>
@endsection