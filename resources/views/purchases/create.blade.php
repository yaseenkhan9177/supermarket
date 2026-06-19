<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Purchase Entry | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-900 font-sans text-gray-200" x-data="purchaseForm()">

    <!-- SweetAlert Flash Logic -->
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

    <!-- Top Nav -->
    <nav class="bg-white border-b border-gray-200 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-truck-loading text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-extrabold text-gray-900 leading-none">OwnStore <span class="text-indigo-600">PRO</span></h1>
                    <span class="text-xs text-gray-500">Purchase Entry — Multi-Payment Bill</span>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('supplier-returns.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-orange-500 text-white text-sm font-bold rounded-lg shadow-sm hover:bg-orange-600 transition">
                    <i class="fas fa-rotate-left"></i> Returns
                </a>
                <a href="/dashboard" class="inline-flex items-center gap-2 px-5 py-2 bg-gray-800 hover:bg-black text-white text-sm font-bold rounded-lg shadow-sm transition">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 max-w-[1400px] pb-48">

        <form action="/purchases/store" method="POST" id="purchaseForm" @submit.prevent="submitForm">
            @csrf

            <!-- Top Panel: Vendor + Bill Details -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                <!-- Vendor Panel -->
                <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
                    <div class="flex items-center gap-2 mb-4 border-b border-gray-700 pb-3">
                        <i class="fas fa-user-tie text-indigo-400"></i>
                        <h3 class="text-white font-bold">Vendor / Supplier</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Select Vendor</label>
                            <div class="flex gap-2">
                                <select name="supplier_id" id="supplierSelect"
                                        @change="onSupplierChange($event.target.value)"
                                        class="w-full bg-gray-900 border border-gray-600 rounded-lg p-2.5 text-sm text-white focus:border-indigo-500 outline-none">
                                    <option value="">— Choose Supplier —</option>
                                    @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}{{ $sup->company_name ? ' — '.$sup->company_name : '' }}</option>
                                    @endforeach
                                </select>
                                <button type="button" @click="showSupplierModal = true"
                                        class="bg-indigo-600 px-3 rounded-lg text-white hover:bg-indigo-700 transition">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Credit Banner (shows if supplier has a return credit) -->
                        <div x-show="supplierCredit > 0" x-transition class="bg-purple-900/50 border border-purple-500 rounded-lg p-3 flex items-start gap-3">
                            <i class="fas fa-tag text-purple-400 mt-0.5"></i>
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
                                   class="w-full bg-gray-900 border border-gray-600 rounded-lg p-2.5 text-sm text-white focus:border-indigo-500 outline-none">
                        </div>
                    </div>
                </div>

                <!-- Bill Details Panel -->
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-lg text-gray-800">
                    <div class="flex items-center gap-2 mb-4 border-b pb-3">
                        <i class="fas fa-file-invoice-dollar text-indigo-600"></i>
                        <h3 class="font-bold text-gray-900">Bill Details</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Purchase ID</label>
                            <input type="text" name="purchase_no" value="PO-{{ date('Y') }}-{{ rand(1000,9999) }}" readonly
                                   class="w-full bg-gray-100 border border-gray-300 rounded-lg p-2.5 text-sm font-mono text-gray-700">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Date</label>
                            <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}"
                                   class="w-full bg-white border border-gray-300 rounded-lg p-2.5 text-sm">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Memo / Remarks</label>
                            <input type="text" name="memo" placeholder="Optional notes..."
                                   class="w-full bg-white border border-gray-300 rounded-lg p-2.5 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden text-gray-800 mb-6">
                <div class="p-4 bg-indigo-50 border-b border-indigo-100 flex justify-between items-center">
                    <h3 class="font-bold text-indigo-900"><i class="fas fa-boxes mr-2"></i>Items Received</h3>
                    <button type="button" @click="addRow()"
                            class="bg-indigo-600 text-white hover:bg-indigo-700 px-4 py-2 rounded-lg text-sm font-bold transition shadow">
                        <i class="fas fa-plus mr-1"></i> Add Row
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-bold text-gray-500 uppercase bg-gray-50 border-b">
                                <th class="p-3 w-10">#</th>
                                <th class="p-3 w-32">Barcode</th>
                                <th class="p-3 min-w-[200px]">Description</th>
                                <th class="p-3 w-24">Batch</th>
                                <th class="p-3 w-32">Expiry <span class="text-red-500">*</span></th>
                                <th class="p-3 w-20 text-center">Qty</th>
                                <th class="p-3 w-24 text-right">Cost</th>
                                <th class="p-3 w-20 text-center">Stock</th>
                                <th class="p-3 w-24 text-right">Total</th>
                                <th class="p-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in rows" :key="index">
                                <tr class="border-b hover:bg-indigo-50 transition">
                                    <td class="p-3 text-center text-gray-400 text-sm" x-text="index + 1"></td>
                                    <td class="p-3">
                                        <input type="text" :name="`items[${index}][code]`" x-model="row.code"
                                               @keydown.enter.prevent="fetchProduct(index)"
                                               @blur="fetchProduct(index)"
                                               class="w-full p-1.5 border rounded text-xs text-gray-900 focus:ring-1 focus:ring-indigo-400 outline-none"
                                               placeholder="Scan...">
                                    </td>
                                    <td class="p-3">
                                        <input type="text" :name="`items[${index}][name]`" x-model="row.name"
                                               class="w-full p-1.5 border rounded text-xs text-gray-900 focus:ring-1 focus:ring-indigo-400 outline-none"
                                               placeholder="Item name...">
                                        <input type="hidden" :name="`items[${index}][item_id]`" x-model="row.item_id">
                                    </td>
                                    <td class="p-3">
                                        <input type="text" :name="`items[${index}][batch_no]`" x-model="row.batch_no"
                                               class="w-full p-1.5 border rounded text-xs uppercase focus:ring-1 focus:ring-indigo-400 outline-none"
                                               placeholder="BATCH">
                                    </td>
                                    <td class="p-3">
                                        <input type="date" :name="`items[${index}][expiry_date]`" x-model="row.expiry_date"
                                               class="w-full p-1.5 border rounded text-xs focus:ring-1 focus:ring-indigo-400 outline-none">
                                    </td>
                                    <td class="p-3">
                                        <input type="number" x-model="row.qty" :name="`items[${index}][qty]`" min="1"
                                               class="w-full p-1.5 border rounded text-center text-sm font-bold text-indigo-700 focus:ring-1 focus:ring-indigo-400 outline-none">
                                    </td>
                                    <td class="p-3">
                                        <input type="number" step="0.01" x-model="row.rate" :name="`items[${index}][rate]`"
                                               class="w-full p-1.5 border rounded text-right text-sm focus:ring-1 focus:ring-indigo-400 outline-none">
                                    </td>
                                    <td class="p-3 text-center">
                                        <span :class="row.stock > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'"
                                              class="text-xs font-bold px-2 py-0.5 rounded-full"
                                              x-text="row.stock !== null ? row.stock : '—'"></span>
                                    </td>
                                    <td class="p-3 text-right font-bold text-gray-800">
                                        <span x-text="'Rs. ' + (row.qty * row.rate).toFixed(2)"></span>
                                    </td>
                                    <td class="p-3 text-center">
                                        <button type="button" @click="removeRow(index)" class="text-gray-300 hover:text-red-500 transition">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            <!-- Live Search Row -->
                            <tr class="bg-indigo-50/50 border-t-2 border-indigo-200">
                                <td class="p-3 text-center"><i class="fas fa-search text-indigo-500"></i></td>
                                <td class="p-3 relative" colspan="8">
                                    <input type="text" x-model="searchQuery"
                                           @input.debounce.200ms="performSearch()"
                                           @keydown.enter.prevent="selectFirstResult()"
                                           placeholder="🔍 Type product name or barcode to search..."
                                           class="w-full bg-white border border-indigo-300 rounded-lg py-2.5 px-4 text-gray-800 focus:ring-2 focus:ring-indigo-500 outline-none placeholder-gray-400 text-sm shadow-sm">
                                    <div x-show="searchResults.length > 0" @click.outside="searchResults = []"
                                         class="absolute top-14 left-3 w-[95%] bg-white border border-indigo-200 rounded-xl shadow-2xl z-50 max-h-64 overflow-y-auto"
                                         style="display:none;">
                                        <ul>
                                            <template x-for="item in searchResults" :key="item.id">
                                                <li @click="addItem(item)" class="p-3 hover:bg-indigo-600 hover:text-white cursor-pointer flex justify-between items-center border-b border-gray-100 last:border-0 group transition">
                                                    <div class="flex-1 min-w-0 pr-4">
                                                        <span class="font-bold text-gray-800 group-hover:text-white block truncate text-sm" x-text="item.name"></span>
                                                        <span class="text-xs text-gray-400 font-mono group-hover:text-indigo-200" x-text="item.code"></span>
                                                    </div>
                                                    <div class="text-right whitespace-nowrap">
                                                        <span class="block font-bold text-indigo-600 group-hover:text-white text-sm" x-text="'Rs. ' + item.price"></span>
                                                        <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded"
                                                              :class="item.stock_qty > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'"
                                                              x-text="item.stock_qty > 0 ? 'Stock: ' + item.stock_qty : 'Out of Stock'"></span>
                                                    </div>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════ -->
            <!-- IMPORT & CLEARING CHARGES / TAXES PANEL                        -->
            <!-- ═══════════════════════════════════════════════════════════════ -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden text-gray-800 mb-6">
                <div class="p-4 bg-amber-50 border-b border-amber-100 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-file-invoice text-amber-600"></i>
                        <h3 class="font-bold text-amber-900">Import & Clearing Charges / Taxes</h3>
                        <span class="text-xs text-amber-600 bg-amber-100 px-2 py-0.5 rounded-full">
                            Add one by one
                        </span>
                    </div>
                </div>

                <div class="p-4">
                    <!-- Add charge row -->
                    <div class="flex gap-3 items-end mb-4">
                        <div class="flex-1">
                            <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Charge Type</label>
                            <div class="flex gap-2">
                                <select id="chargeTypeSelect" x-model="newChargeTypeId"
                                        class="w-full border border-gray-300 rounded-lg p-2 text-sm text-gray-800 focus:ring-2 focus:ring-amber-400 outline-none">
                                    <option value="">— Select Charge Type —</option>
                                    @foreach(\App\Models\TaxChargeType::orderBy('name')->get() as $ct)
                                    <option value="{{ $ct->id }}">{{ $ct->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button"
                                        onclick="openChargeTypeModal()"
                                        title="Add New Charge Type"
                                        class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-2 rounded-lg text-sm font-bold transition flex-shrink-0">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="w-40">
                            <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Amount (Rs.)</label>
                            <input type="number" step="0.01" x-model="newChargeAmount"
                                   @keydown.enter.prevent="addCharge()"
                                   class="w-full border border-gray-300 rounded-lg p-2 text-right text-sm font-bold text-gray-800 focus:ring-2 focus:ring-amber-400 outline-none"
                                   placeholder="0.00">
                        </div>
                        <div>
                            <button type="button" @click="addCharge()"
                                    class="bg-amber-600 text-white hover:bg-amber-700 px-4 py-2 rounded-lg text-sm font-bold transition">
                                <i class="fas fa-plus mr-1"></i> Add
                            </button>
                        </div>
                    </div>

                    <!-- Added charges list -->
                    <div x-show="charges.length > 0" class="space-y-2">
                        <template x-for="(ch, ci) in charges" :key="ci">
                            <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg border border-amber-200">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 bg-amber-600 text-white rounded-full flex items-center justify-center text-xs font-bold" x-text="ci + 1"></span>
                                    <span class="font-bold text-gray-800 text-sm" x-text="ch.name"></span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-mono font-bold text-amber-800" x-text="'Rs. ' + parseFloat(ch.amount).toFixed(2)"></span>
                                    <button type="button" @click="removeCharge(ci)"
                                            class="text-red-400 hover:text-red-600 p-1 rounded hover:bg-red-50 transition">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <!-- Hidden inputs to submit with form -->
                                <input type="hidden" :name="`charges[${ci}][tax_charge_type_id]`" :value="ch.type_id">
                                <input type="hidden" :name="`charges[${ci}][amount]`" :value="ch.amount">
                            </div>
                        </template>

                        <!-- Charges Total -->
                        <div class="flex justify-end pt-2 border-t border-amber-200">
                            <div class="text-right">
                                <span class="text-xs font-bold text-gray-400 uppercase">Total Charges / Tax</span>
                                <span class="block text-lg font-bold text-amber-700" x-text="'Rs. ' + chargesTotal.toFixed(2)"></span>
                            </div>
                        </div>
                    </div>

                    <div x-show="charges.length === 0" class="text-center py-4 text-gray-400 text-sm">
                        <i class="fas fa-info-circle mr-1"></i> No charges added yet. Select a type and enter an amount above.
                    </div>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════════════════ -->
            <!-- PAYMENT SPLIT PANEL                                            -->
            <!-- ═══════════════════════════════════════════════════════════════ -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden text-gray-800 mb-6">
                <div class="p-4 bg-emerald-50 border-b border-emerald-100 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-money-bill-wave text-emerald-600"></i>
                        <h3 class="font-bold text-emerald-900">Payment Split</h3>
                        <span class="text-xs text-emerald-600 bg-emerald-100 px-2 py-0.5 rounded-full">
                            Split across multiple sources
                        </span>
                    </div>
                    <button type="button" @click="addPaymentRow()"
                            class="bg-emerald-600 text-white hover:bg-emerald-700 px-4 py-2 rounded-lg text-sm font-bold transition">
                        <i class="fas fa-plus mr-1"></i> Add Source
                    </button>
                </div>

                <!-- Credit Applied Banner -->
                <div x-show="supplierCredit > 0" class="bg-purple-50 border-b border-purple-100 px-6 py-3 flex items-center gap-3">
                    <i class="fas fa-magic text-purple-500"></i>
                    <p class="text-sm text-purple-700">
                        <strong>Auto-Credit Applied:</strong>
                        Rs. <span class="font-mono font-bold" x-text="supplierCredit.toFixed(2)"></span>
                        return credit will be deducted from the net total automatically.
                        Net payable after credit = Rs. <span class="font-mono font-bold text-purple-900" x-text="netAfterCredit.toFixed(2)"></span>
                    </p>
                </div>

                <div class="p-4 space-y-3">
                    <template x-for="(pay, idx) in payments" :key="idx">
                        <div class="flex gap-3 items-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <!-- Payment Method -->
                            <div class="flex-1">
                                <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Source</label>
                                <select :name="`payments[${idx}][method]`" x-model="pay.method"
                                        @change="onPaymentMethodChange(idx)"
                                        class="w-full border border-gray-300 rounded-lg p-2 text-sm text-gray-800 focus:ring-2 focus:ring-emerald-400 outline-none">
                                    <option value="Cash Drawer">💵 Cash Drawer</option>
                                    <option value="Bank Transfer">🏦 Bank Transfer</option>
                                    <option value="EasyPaisa">📱 EasyPaisa</option>
                                    <option value="JazzCash">📱 JazzCash</option>
                                    <option value="Cheque">📝 Cheque</option>
                                    <option value="Other">🔄 Other</option>
                                </select>
                            </div>
                            <!-- Account (optional, for double-entry) -->
                            <div class="flex-1">
                                <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Linked Account</label>
                                <select :name="`payments[${idx}][account_id]`" x-model="pay.account_id"
                                        class="w-full border border-gray-300 rounded-lg p-2 text-sm text-gray-800 focus:ring-2 focus:ring-emerald-400 outline-none">
                                    <option value="">— No Account —</option>
                                    @foreach($accounts as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Amount -->
                            <div class="w-36">
                                <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Amount (Rs.)</label>
                                <input type="number" step="0.01" :name="`payments[${idx}][amount]`" x-model="pay.amount"
                                       @input="validateSplit()"
                                       class="w-full border border-gray-300 rounded-lg p-2 text-right text-sm font-bold text-gray-800 focus:ring-2 focus:ring-emerald-400 outline-none"
                                       placeholder="0.00">
                            </div>
                            <!-- Reference -->
                            <div class="w-36">
                                <label class="text-[10px] font-bold text-gray-400 uppercase block mb-1">Ref / Cheque #</label>
                                <input type="text" :name="`payments[${idx}][reference_no]`" x-model="pay.reference_no"
                                       class="w-full border border-gray-300 rounded-lg p-2 text-sm text-gray-800 focus:ring-2 focus:ring-emerald-400 outline-none"
                                       placeholder="Optional">
                            </div>
                            <!-- Remove -->
                            <div class="pt-4">
                                <button type="button" @click="removePaymentRow(idx)"
                                        x-show="payments.length > 1"
                                        class="text-red-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Split Summary -->
                    <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                        <div class="flex gap-6 text-sm">
                            <div>
                                <span class="text-gray-400 text-xs uppercase font-bold block">Gross Bill</span>
                                <span class="font-bold text-gray-700" x-text="'Rs. ' + grossBill.toFixed(2)"></span>
                            </div>
                            <div x-show="supplierCredit > 0">
                                <span class="text-purple-400 text-xs uppercase font-bold block">Credit Applied</span>
                                <span class="font-bold text-purple-600" x-text="'- Rs. ' + Math.min(supplierCredit, grossBill).toFixed(2)"></span>
                            </div>
                            <div>
                                <span class="text-gray-400 text-xs uppercase font-bold block">Net Payable</span>
                                <span class="font-bold text-indigo-700 text-lg" x-text="'Rs. ' + netAfterCredit.toFixed(2)"></span>
                            </div>
                            <div>
                                <span class="text-gray-400 text-xs uppercase font-bold block">Allocated</span>
                                <span class="font-bold" :class="splitRemaining < -0.5 ? 'text-red-500' : 'text-emerald-600'"
                                      x-text="'Rs. ' + splitTotal.toFixed(2)"></span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-bold uppercase"
                                 :class="Math.abs(splitRemaining) < 0.5 ? 'text-emerald-600' : 'text-red-500'">
                                <span x-show="Math.abs(splitRemaining) < 0.5">
                                    <i class="fas fa-check-circle mr-1"></i> Balanced ✓
                                </span>
                                <span x-show="Math.abs(splitRemaining) >= 0.5">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
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
    <div class="fixed bottom-0 left-0 w-full bg-white border-t shadow-[0_-5px_20px_rgba(0,0,0,0.1)] z-40 p-4">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex gap-6 text-sm">
                <div class="text-right">
                    <span class="block text-[10px] text-gray-400 uppercase">Subtotal</span>
                    <span class="font-bold text-gray-700" x-text="'Rs. ' + subtotal.toFixed(2)"></span>
                </div>
                <div class="text-right">
                    <span class="block text-[10px] text-gray-400 uppercase">Charges/Tax</span>
                    <span class="font-bold text-amber-700" x-text="'Rs. ' + chargesTotal.toFixed(2)"></span>
                    <input type="hidden" x-model="tax" name="tax_amount">
                </div>
                <div>
                    <span class="block text-[10px] text-gray-400 uppercase">Discount</span>
                    <input type="number" x-model="discount" name="discount" class="w-20 border rounded p-1 text-right text-xs" placeholder="0">
                </div>
            </div>

            <div class="flex items-center gap-6">
                <div class="text-right">
                    <span class="block text-[10px] font-bold text-gray-400 uppercase">Net Payable</span>
                    <span class="block text-2xl font-bold text-indigo-600" x-text="'Rs. ' + netAfterCredit.toFixed(2)"></span>
                </div>
                <div class="h-10 w-px bg-gray-200"></div>
                <button type="button" @click="submitForm()"
                        :disabled="Math.abs(splitRemaining) > 0.5 || rows.filter(r=>r.item_id||r.code).length === 0"
                        :class="Math.abs(splitRemaining) < 0.5 ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-400 cursor-not-allowed'"
                        class="px-8 py-3 text-white font-bold rounded-xl shadow-lg transition transform hover:-translate-y-0.5 disabled:transform-none">
                    <i class="fas fa-save mr-2"></i> Save Bill
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Add Supplier Modal -->
    <div x-show="showSupplierModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm" style="display:none;">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Add New Supplier</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Company / Name *</label>
                    <input type="text" x-model="newSupplier.name" class="w-full border rounded-lg p-2.5 text-gray-900" placeholder="e.g. ABC Distributors">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Phone</label>
                    <input type="text" x-model="newSupplier.phone" class="w-full border rounded-lg p-2.5 text-gray-900" placeholder="e.g. 03001234567">
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" @click="showSupplierModal = false" class="px-4 py-2 border rounded-lg text-gray-600 hover:bg-gray-100 font-bold">Cancel</button>
                <button type="button" @click="saveSupplier()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-bold">Save Supplier</button>
            </div>
        </div>
    </div>

    <script>
        function purchaseForm() {
            return {
                rows: [
                    @if($prefilledItem)
                    {
                        item_id: '{{ $prefilledItem->id }}',
                        code: '{{ $prefilledItem->code }}',
                        name: '{{ addslashes($prefilledItem->description) }}',
                        batch_no: '',
                        expiry_date: '',
                        qty: 1,
                        rate: {{ $prefilledItem->cost_rate ?? 0 }},
                        stock: {{ $prefilledItem->on_hand ?? 0 }}
                    }
                    @else
                    {
                        item_id: '', code: '', name: '', batch_no: '', expiry_date: '', qty: 1, rate: 0, stock: null
                    }
                    @endif
                ],
                payments: [{
                    method: 'Cash Drawer', account_id: '', amount: 0, reference_no: ''
                }],
                tax: 0,
                discount: 0,
                supplierCredit: 0,
                showSupplierModal: false,
                newSupplier: { name: '', phone: '' },
                searchQuery: '',
                searchResults: [],
                // Import & Clearing charges
                charges: [],
                newChargeTypeId: '',
                newChargeAmount: 0,
                chargeTypeOptions: {
                    @foreach(\App\Models\TaxChargeType::orderBy('name')->get() as $ct)
                    '{{ $ct->id }}': '{{ $ct->name }}',
                    @endforeach
                },
                init() {
                    @if(request('supplier_id'))
                        this.onSupplierChange('{{ request('supplier_id') }}');
                    @endif
                },

                // ── Computed ──────────────────────────────────────────────────────
                get subtotal() {
                    return this.rows.reduce((s, r) => s + (parseFloat(r.qty || 0) * parseFloat(r.rate || 0)), 0);
                },
                get chargesTotal() {
                    return this.charges.reduce((s, c) => s + parseFloat(c.amount || 0), 0);
                },
                get grossBill() {
                    // tax is now auto-calculated from charges
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
                    this.rows.push({ item_id: '', code: '', name: '', batch_no: '', expiry_date: '', qty: 1, rate: 0, stock: null });
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
                validateSplit() {
                    // Triggered on amount change, reactive computation handles display
                },
                onPaymentMethodChange(idx) {
                    // Could auto-select a default account per method if desired
                },

                // ── Import charges methods ─────────────────────────────────────
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
                        let emptyIdx = this.rows.findIndex(r => !r.item_id && !r.code);
                        const newRow = { item_id: item.id, code: item.code, name: item.name, batch_no: '', expiry_date: '', qty: 1, rate: item.cost_price || 0, stock: item.stock_qty ?? 0 };
                        if (emptyIdx !== -1) { this.rows[emptyIdx] = newRow; } else { this.rows.push(newRow); }
                    }
                    this.searchQuery = '';
                    this.searchResults = [];
                },

                selectFirstResult() {
                    if (this.searchResults.length > 0) this.addItem(this.searchResults[0]);
                },

                async fetchProduct(index) {
                    const code = (this.rows[index].code || '').trim();
                    if (!code) return;
                    try {
                        const r    = await fetch(`/cash-sales/search?q=${code}`);
                        const data = await r.json();
                        if (data.length > 0) {
                            const item = data.find(i => i.code === code) || data[0];
                            this.rows[index].item_id = item.id;
                            this.rows[index].name    = item.name;
                            this.rows[index].rate    = item.cost_price || item.price || 0;
                            this.rows[index].stock   = item.stock_qty ?? 0;
                            if (index === this.rows.length - 1) this.addRow();
                        }
                    } catch(e) { console.error('Search failed'); }
                },

                async saveSupplier() {
                    if (!this.newSupplier.name) { alert('Supplier name is required.'); return; }
                    try {
                        const res  = await fetch('/suppliers/quick-store', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(this.newSupplier)
                        });
                        const data = await res.json();
                        if (data.success) {
                            const select = document.getElementById('supplierSelect');
                            const option = new Option(data.supplier.name, data.supplier.id, true, true);
                            select.add(option);
                            this.showSupplierModal = false;
                            this.newSupplier = { name: '', phone: '' };
                            Swal.fire({ title: 'Added!', text: 'Supplier saved.', icon: 'success', background: '#1f2937', color: '#fff', timer: 1500, showConfirmButton: false });
                        }
                    } catch(e) { alert('An error occurred.'); }
                },

                submitForm() {
                    // Final guard: split must balance
                    if (Math.abs(this.splitRemaining) > 0.5) {
                        Swal.fire({
                            title: 'Payments Not Balanced',
                            text: `Split total Rs. ${this.splitTotal.toFixed(2)} does not match net payable Rs. ${this.netAfterCredit.toFixed(2)}. Difference: Rs. ${Math.abs(this.splitRemaining).toFixed(2)}`,
                            icon: 'error',
                            background: '#1f2937',
                            color: '#fff',
                            confirmButtonColor: '#ef4444'
                        });
                        return;
                    }
                    document.getElementById('purchaseForm').submit();
                }
            }
        }
    </script>

    <!-- ══════════════════════════════════════════════════════════════ -->
    <!-- ADD NEW CHARGE TYPE MODAL (Vanilla JS — does not use Alpine)  -->
    <!-- ══════════════════════════════════════════════════════════════ -->

    <!-- Dark Backdrop -->
    <div id="chargeTypeBackdrop"
         class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center"
         onclick="closeChargeTypeModal()">
    </div>

    <!-- Modal Card -->
    <div id="chargeTypeModal"
         class="fixed z-50 hidden top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2
                bg-white rounded-2xl shadow-2xl w-full max-w-md p-6"
         style="transform: translate(-50%, -50%);">

        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                    <i class="fas fa-tag text-amber-600 text-sm"></i>
                </span>
                Add New Charge Type
            </h3>
            <button type="button" onclick="closeChargeTypeModal()"
                    class="text-gray-400 hover:text-gray-600 transition p-1 rounded-lg hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">
                Charge Type Name <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   id="chargeTypeNameInput"
                   placeholder="e.g. Custom Duty, Port Fees…"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm text-gray-900
                          focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition"
                   onkeydown="if(event.key==='Enter'){event.preventDefault();saveChargeType();}" />
            <p id="chargeTypeError"
               class="mt-1.5 text-sm text-red-600 font-medium hidden"></p>
        </div>

        <div class="flex gap-3 mt-6">
            <button type="button"
                    onclick="saveChargeType()"
                    id="chargeTypeSaveBtn"
                    class="flex-1 bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-4
                           rounded-lg text-sm transition flex items-center justify-center gap-2">
                <i class="fas fa-save"></i> Save
            </button>
            <button type="button"
                    onclick="closeChargeTypeModal()"
                    class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2.5 px-4
                           rounded-lg text-sm transition">
                Cancel
            </button>
        </div>
    </div>

    <!-- Success Toast -->
    <div id="chargeTypeToast"
         class="fixed bottom-6 right-6 z-50 hidden bg-emerald-600 text-white text-sm font-bold
                px-5 py-3 rounded-xl shadow-lg flex items-center gap-2 transition-all duration-300">
        <i class="fas fa-check-circle text-lg"></i>
        <span id="chargeTypeToastMsg">Charge type added successfully</span>
    </div>

    <script>
        /* ── Charge Type Modal: Vanilla JS ─────────────────────────────── */

        function openChargeTypeModal() {
            document.getElementById('chargeTypeBackdrop').classList.remove('hidden');
            document.getElementById('chargeTypeModal').classList.remove('hidden');
            document.getElementById('chargeTypeNameInput').value = '';
            hideChargeTypeError();
            // Autofocus after display
            setTimeout(function () {
                document.getElementById('chargeTypeNameInput').focus();
            }, 50);
        }

        function closeChargeTypeModal() {
            document.getElementById('chargeTypeBackdrop').classList.add('hidden');
            document.getElementById('chargeTypeModal').classList.add('hidden');
            document.getElementById('chargeTypeNameInput').value = '';
            hideChargeTypeError();
        }

        function showChargeTypeError(msg) {
            var el = document.getElementById('chargeTypeError');
            el.textContent = msg;
            el.classList.remove('hidden');
            document.getElementById('chargeTypeNameInput').classList.add('border-red-400', 'focus:ring-red-400');
        }

        function hideChargeTypeError() {
            var el = document.getElementById('chargeTypeError');
            el.classList.add('hidden');
            document.getElementById('chargeTypeNameInput').classList.remove('border-red-400', 'focus:ring-red-400');
        }

        function showChargeTypeToast(msg) {
            var toast = document.getElementById('chargeTypeToast');
            document.getElementById('chargeTypeToastMsg').textContent = msg || 'Charge type added successfully';
            toast.classList.remove('hidden');
            setTimeout(function () {
                toast.classList.add('hidden');
            }, 2500);
        }

        function saveChargeType() {
            var nameInput = document.getElementById('chargeTypeNameInput');
            var name = nameInput.value.trim();

            if (!name) {
                showChargeTypeError('Charge type name cannot be empty.');
                nameInput.focus();
                return;
            }

            var saveBtn = document.getElementById('chargeTypeSaveBtn');
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';

            var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('/tax-charge-types', {
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
                    /* ── 1. Add <option> to the visible <select> dropdown ── */
                    var select = document.getElementById('chargeTypeSelect');
                    var newOpt = document.createElement('option');
                    newOpt.value = data.id;
                    newOpt.textContent = data.name;
                    select.appendChild(newOpt);
                    select.value = data.id;   // visually select it

                    /* ── 2. Sync with Alpine.js state ────────────────────── */
                    // Update the x-model binding (newChargeTypeId) and chargeTypeOptions map
                    // We reach into Alpine's component data via the root element's __x property
                    var alpineRoot = document.querySelector('[x-data]');
                    if (alpineRoot && alpineRoot._x_dataStack) {
                        // Alpine v3 — use Alpine.store or $data approach
                        try {
                            var alpineData = alpineRoot._x_dataStack[0];
                            alpineData.newChargeTypeId = String(data.id);
                            alpineData.chargeTypeOptions[String(data.id)] = data.name;
                        } catch (e) { /* silent */ }
                    }
                    // Fallback: dispatch a native 'change' event so Alpine picks up the new value
                    select.dispatchEvent(new Event('change'));

                    /* ── 3. Close modal & show toast ─────────────────────── */
                    closeChargeTypeModal();
                    showChargeTypeToast('Charge type "' + data.name + '" added successfully');

                } else {
                    showChargeTypeError(data.message || 'Failed to save. Please try again.');
                }
            })
            .catch(function (err) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save"></i> Save';
                showChargeTypeError('Network error. Please check your connection.');
                console.error('saveChargeType error:', err);
            });
        }

        /* Close modal on Escape key */
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                var modal = document.getElementById('chargeTypeModal');
                if (!modal.classList.contains('hidden')) {
                    closeChargeTypeModal();
                }
            }
        });
    </script>

</body>

</html>