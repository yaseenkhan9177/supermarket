@extends('layouts.admin')

@section('navbar_subtitle', 'Credit Purchase (Pay Later)')

@section('content')
<div x-data="creditPurchaseForm()" @supplier-selected.window="onSupplierSelected($event.detail)">

    <!-- Flash Messages -->
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Success!',
                text: "{{ session('success') }}",
                icon: 'success',
                background: '#1f2937',
                color: '#fff',
                confirmButtonColor: '#d97706'
            });
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Error!',
                text: "{{ session('error') }}",
                icon: 'error',
                background: '#1f2937',
                color: '#fff',
                confirmButtonColor: '#d97706'
            });
        });
    </script>
    @endif

    @if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Validation Error',
                html: '<ul class="text-left list-disc pl-4 text-xs">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
                icon: 'warning',
                background: '#1f2937',
                color: '#fff',
                confirmButtonColor: '#d97706'
            });
        });
    </script>
    @endif

    <div class="container mx-auto px-4 max-w-[1400px] pb-32">

        <!-- Top Header Bar / Navigation Switcher -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-white flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-amber-600 flex items-center justify-center text-white shadow-lg shadow-amber-600/30">
                        <i class="fas fa-file-invoice"></i>
                    </span>
                    Credit Purchase (Pay Later)
                </h1>
                <p class="text-xs text-gray-400 mt-1">Record purchases on credit, receive stock immediately, and update supplier payable balances.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                    <i class="fas fa-truck-loading"></i> Cash Purchase Entry
                </a>
                <a href="{{ route('supplier-returns.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                    <i class="fas fa-rotate-left"></i> Supplier Returns
                </a>
            </div>
        </div>

        <form action="/purchases/store" method="POST" @submit.prevent="submitForm">
            @csrf
            <input type="hidden" name="payment_type" value="Credit">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                <!-- Supplier Details Panel -->
                <div class="bg-gray-900 p-6 rounded-2xl border border-gray-800 shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1.5 h-full bg-amber-500"></div>

                    <div class="flex items-center gap-2 mb-4 border-b border-gray-800 pb-3">
                        <i class="fas fa-user-clock text-amber-400"></i>
                        <h3 class="text-white font-bold text-sm uppercase tracking-wider">Supplier Details</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Select Supplier *</label>
                            <div class="flex gap-2" @open-add-supplier-modal.window="showSupplierModal = true">
                                <x-supplier-search id="supplierSelect" name="supplier_id" :add-new="true" :required="true" />
                                <button type="button" @click="showSupplierModal = true" class="bg-amber-600 px-3.5 rounded-xl text-white hover:bg-amber-700 transition shrink-0"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>

                        <div class="bg-gray-950 rounded-xl p-3.5 border border-gray-800 flex justify-between items-center">
                            <span class="text-xs text-gray-400 font-bold uppercase">Current Debt / Payable</span>
                            <span class="text-lg font-bold text-amber-400 font-mono" x-text="'Rs. ' + supplierBalance">0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Bill Dates Panel -->
                <div class="bg-gray-900 p-6 rounded-2xl border border-gray-800 shadow-xl">
                    <div class="flex items-center gap-2 mb-4 border-b border-gray-800 pb-3">
                        <i class="fas fa-calendar-alt text-amber-400"></i>
                        <h3 class="text-white font-bold text-sm uppercase tracking-wider">Bill Dates</h3>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Purchase ID</label>
                            <input type="text" name="purchase_no" value="PC-{{ date('Y') }}-{{ rand(1000,9999) }}" readonly class="w-full bg-gray-950 border border-gray-800 rounded-xl p-2.5 text-xs font-mono text-gray-400">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Vendor Bill #</label>
                            <input type="text" name="vendor_bill_no" placeholder="e.g. INV-9988" class="w-full bg-gray-950 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:border-amber-500 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Bill Date</label>
                            <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" class="w-full bg-gray-950 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:border-amber-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-red-400 uppercase mb-1">Due Date *</label>
                            <input type="date" name="due_date" required class="w-full bg-gray-950 border border-amber-500/50 rounded-xl p-2.5 text-xs text-amber-300 focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="bg-gray-900 rounded-2xl border border-gray-800 shadow-xl overflow-hidden mb-6">
                <div class="p-4 bg-amber-950/20 border-b border-amber-900/30 flex justify-between items-center">
                    <h3 class="font-bold text-amber-200 text-sm flex items-center gap-2"><i class="fas fa-boxes text-amber-400"></i> Items (Stock In)</h3>
                    <button type="button" @click="addRow()" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow">
                        <i class="fas fa-plus mr-1"></i> Add Row
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="text-[11px] font-bold text-gray-400 uppercase bg-gray-950 border-b border-gray-800">
                                <th class="p-3 w-10">#</th>
                                <th class="p-3 w-32">Barcode</th>
                                <th class="p-3 min-w-[200px]">Description</th>
                                <th class="p-3 w-32">Expiry</th>
                                <th class="p-3 w-20 text-center">Qty</th>
                                <th class="p-3 w-28 text-right">Cost</th>
                                <th class="p-3 w-24 text-right">Total</th>
                                <th class="p-3 w-20 text-center">In Stock</th>
                                <th class="p-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800/60">
                            <template x-for="(row, index) in rows" :key="index">
                                <tr class="hover:bg-gray-800/40 transition">
                                    <td class="p-3 text-center text-gray-500 text-xs" x-text="index + 1"></td>

                                    <td class="p-3">
                                        <input type="text" :name="`items[${index}][code]`" x-model="row.code" @keydown.enter.prevent="fetchProduct(index)" @blur="fetchProduct(index)" class="w-full p-2 bg-gray-950 border border-gray-700 rounded-lg text-xs text-white font-mono focus:ring-1 focus:ring-amber-500 outline-none" placeholder="Scan...">
                                    </td>

                                    <td class="p-3">
                                        <input type="text" :name="`items[${index}][name]`" x-model="row.name" class="w-full p-2 bg-gray-950 border border-gray-700 rounded-lg text-xs text-white focus:ring-1 focus:ring-amber-500 outline-none" placeholder="Item name...">
                                        <input type="hidden" :name="`items[${index}][item_id]`" x-model="row.item_id">
                                    </td>

                                    <td class="p-3">
                                        <input type="date" :name="`items[${index}][expiry_date]`" class="w-full p-2 bg-gray-950 border border-gray-700 rounded-lg text-xs text-white focus:ring-1 focus:ring-amber-500 outline-none">
                                    </td>

                                    <td class="p-3">
                                        <input type="number" x-model="row.qty" :name="`items[${index}][qty]`" min="1" class="w-full p-2 bg-gray-950 border border-gray-700 rounded-lg text-center text-xs font-bold text-amber-400 focus:ring-1 focus:ring-amber-500 outline-none">
                                    </td>

                                    <td class="p-3">
                                        <input type="number" step="0.01" x-model="row.rate" :name="`items[${index}][rate]`" class="w-full p-2 bg-gray-950 border border-gray-700 rounded-lg text-right text-xs text-white focus:ring-1 focus:ring-amber-500 outline-none">
                                    </td>

                                    <td class="p-3 text-right font-bold text-white">
                                        <span x-text="'Rs. ' + (row.qty * row.rate).toFixed(2)"></span>
                                    </td>

                                    <td class="p-3 text-center">
                                        <span :class="row.stock > 0 ? 'bg-green-900/50 text-green-400 border border-green-700' : 'bg-gray-800 text-gray-500'" class="text-[11px] font-bold px-2 py-0.5 rounded-full" x-text="row.stock !== null ? row.stock : '—'"></span>
                                    </td>

                                    <td class="p-3 text-center">
                                        <button type="button" @click="removeRow(index)" class="text-gray-500 hover:text-red-400 transition p-1">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            <!-- Live Search Row -->
                            <tr class="bg-amber-950/20 border-t border-amber-900/40">
                                <td class="p-3 text-center"><i class="fas fa-search text-amber-400"></i></td>
                                <td class="p-3 relative" colspan="7">
                                    <input
                                        type="text"
                                        x-model="searchQuery"
                                        @input.debounce.200ms="performSearch()"
                                        @keydown.enter.prevent="selectFirstResult()"
                                        placeholder="🔍 Type product name or barcode to search and add..."
                                        class="w-full bg-gray-950 border border-amber-800 rounded-xl py-2.5 px-4 text-white focus:ring-2 focus:ring-amber-500 outline-none placeholder-gray-500 text-xs shadow-inner"
                                    >
                                    <!-- Dropdown Results -->
                                    <div x-show="searchResults.length > 0"
                                        @click.outside="searchResults = []"
                                        class="absolute top-14 left-3 w-[95%] bg-gray-900 border border-amber-700 rounded-xl shadow-2xl z-50 max-h-64 overflow-y-auto"
                                        style="display: none;">
                                        <ul>
                                            <template x-for="item in searchResults" :key="item.id">
                                                <li @click="addItem(item)" class="p-3 hover:bg-amber-600 hover:text-white cursor-pointer flex justify-between items-center border-b border-gray-800 last:border-0 group transition text-xs">
                                                    <div class="flex-1 min-w-0 pr-4">
                                                        <span class="font-bold text-gray-200 group-hover:text-white block truncate text-xs" x-text="item.name"></span>
                                                        <span class="text-[10px] text-gray-400 font-mono group-hover:text-amber-100" x-text="item.code"></span>
                                                    </div>
                                                    <div class="text-right whitespace-nowrap">
                                                        <span class="block font-bold text-amber-400 group-hover:text-white text-xs" x-text="'Rs. ' + item.price"></span>
                                                        <span class="text-[9px] uppercase font-bold px-1.5 py-0.5 rounded"
                                                            :class="item.stock_qty > 0 ? 'bg-green-900/60 text-green-300' : 'bg-red-900/60 text-red-300'"
                                                            x-text="item.stock_qty > 0 ? 'Stock: ' + item.stock_qty : 'Out of Stock'"></span>
                                                    </div>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </td>
                                <td class="p-3"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sticky Bottom Bar -->
            <div class="fixed bottom-0 left-0 w-full bg-gray-900 border-t border-gray-800 p-3 sm:p-4 shadow-[0_-5px_25px_rgba(0,0,0,0.5)] z-40">
                <div class="container mx-auto max-w-[1400px] flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">

                    <div class="w-full sm:w-1/3">
                        <input type="text" name="memo" placeholder="Remarks (e.g. Delivered via Rider)" class="w-full bg-gray-950 border border-gray-700 rounded-xl px-3 py-2 text-xs text-white focus:border-amber-500 outline-none">
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-3 sm:gap-6">
                        <div class="text-right">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase">Total Credit Amount</span>
                            <span class="block text-xl sm:text-2xl font-bold text-amber-400 font-mono" x-text="'Rs. ' + subtotal"></span>
                        </div>

                        <div class="hidden sm:block h-10 w-px bg-gray-800"></div>

                        <button type="submit" class="flex-1 sm:flex-none justify-center px-6 sm:px-8 py-2.5 sm:py-3 bg-amber-600 text-white font-bold rounded-xl shadow-lg hover:bg-amber-700 transition transform active:scale-95 text-xs sm:text-sm flex items-center gap-2">
                            <i class="fas fa-save"></i> Save Credit Bill
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <!-- Add Supplier Modal -->
    <div x-show="showSupplierModal" class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-4 bg-black/70 backdrop-blur-sm" style="display: none;">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-4 sm:p-6 max-h-[90vh] overflow-y-auto text-white">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <i class="fas fa-user-plus text-amber-400"></i> Add New Supplier
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Company / Name *</label>
                    <input type="text" x-model="newSupplier.name" class="w-full bg-gray-950 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:border-amber-500 outline-none" placeholder="e.g. ABC Distributors">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Phone</label>
                    <input type="text" x-model="newSupplier.phone" class="w-full bg-gray-950 border border-gray-700 rounded-xl p-2.5 text-xs text-white focus:border-amber-500 outline-none" placeholder="e.g. 03001234567">
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" @click="showSupplierModal = false" class="px-4 py-2 border border-gray-700 rounded-xl text-gray-300 hover:bg-gray-800 text-xs font-bold">Cancel</button>
                <button type="button" @click="saveSupplier()" class="px-4 py-2 bg-amber-600 text-white rounded-xl hover:bg-amber-700 text-xs font-bold">Save Supplier</button>
            </div>
        </div>
    </div>
</div>

<script>
    const supplierBalances = @json($suppliers->pluck('current_balance', 'id'));

    function creditPurchaseForm() {
        return {
            supplierId: '',
            supplierBalance: '0.00',
            rows: [{
                item_id: '',
                code: '',
                name: '',
                qty: 1,
                rate: 0,
                stock: null
            }],
            showSupplierModal: false,
            newSupplier: { name: '', phone: '' },
            searchQuery: '',
            searchResults: [],

            init() {},

            onSupplierSelected(detail) {
                this.supplierId = detail.id || '';
                this.updateBalance();
            },

            updateBalance() {
                if (this.supplierId && supplierBalances[this.supplierId] !== undefined) {
                    this.supplierBalance = parseFloat(supplierBalances[this.supplierId]).toFixed(2);
                } else {
                    this.supplierBalance = '0.00';
                }
            },

            async performSearch() {
                if (this.searchQuery.length < 1) {
                    this.searchResults = [];
                    return;
                }
                try {
                    let response = await fetch(`/cash-sales/search?q=${this.searchQuery}`);
                    this.searchResults = await response.json();
                } catch (e) {
                    console.error('Search failed');
                }
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
                        this.updateBalance();

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

            addRow() {
                this.rows.push({
                    item_id: '',
                    code: '',
                    name: '',
                    qty: 1,
                    rate: 0,
                    stock: null
                });
            },

            removeRow(index) {
                if (this.rows.length > 1) this.rows.splice(index, 1);
            },

            submitForm(e) {
                let validSupplier = document.getElementById('supplierSelect')?.value || this.supplierId;
                if (!validSupplier) {
                    Swal.fire({ title: 'Error', text: 'Please select a supplier', icon: 'error', background: '#1f2937', color: '#fff', confirmButtonColor: '#d97706' });
                    return;
                }
                if (parseFloat(this.subtotal) <= 0) {
                    Swal.fire({ title: 'Error', text: 'Total amount cannot be zero — please add items.', icon: 'error', background: '#1f2937', color: '#fff', confirmButtonColor: '#d97706' });
                    return;
                }
                e.target.submit();
            },

            get subtotal() {
                return this.rows.reduce((acc, row) => acc + (row.qty * row.rate), 0).toFixed(2);
            }
        }
    }
</script>
@endsection