@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto" x-data="debitSaleInvoice()">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 mb-8">

        <div class="bg-slate-900 rounded-2xl shadow-xl border border-red-900/50 shadow-red-900/10 p-6 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-red-500/10 group-hover:bg-red-500/20 rounded-full blur-2xl transition duration-300"></div>
            <h3 class="text-white text-lg font-bold mb-5 flex items-center gap-3 relative z-10">
                <div class="w-8 h-8 rounded-lg bg-red-500/20 text-red-500 border border-red-500/30 flex items-center justify-center shadow-sm">
                    <i class="fas fa-user text-sm"></i>
                </div>
                Customer (Required)
            </h3>
            <select id="customer-select" autofocus x-model="customer_id" @change="if(customer_id === 'new') showCustomerModal = true" @keydown.enter.prevent="if (customer_id) { document.getElementById('item-search').focus() }" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2.5 text-white mb-3 focus:border-red-500 outline-none">
                <option value="">-- Select Customer --</option>
                <option value="new" class="text-blue-400 font-bold">+ Add New Customer</option>
                @foreach($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }}</option>
                @endforeach
            </select>
            <p x-show="!customer_id" class="text-xs text-red-400 mt-1">* You must select a customer for Debit sales.</p>
        </div>

        <div class="bg-slate-900 rounded-2xl shadow-xl border border-slate-800 p-6 relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-slate-500/10 group-hover:bg-slate-500/20 rounded-full blur-2xl transition duration-300"></div>
            <h3 class="text-white text-lg font-bold mb-5 flex items-center gap-3 relative z-10">
                <div class="w-8 h-8 rounded-lg bg-slate-500/20 text-slate-400 border border-slate-500/30 flex items-center justify-center shadow-sm">
                    <i class="fas fa-file-invoice text-sm"></i>
                </div>
                Invoice Details
            </h3>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[10px] text-slate-400 font-bold uppercase">Inv No</label>
                    <input type="text" x-model="invoice_no" class="w-full bg-slate-950 border border-slate-700 rounded p-2 text-white font-mono text-sm focus:border-red-500 outline-none" readonly>
                </div>
                <div>
                    <label class="text-[10px] text-slate-400 font-bold uppercase">Date</label>
                    <input type="date" x-model="date" class="w-full bg-slate-950 border border-slate-700 rounded p-2 text-white text-sm focus:border-red-500 outline-none">
                </div>
            </div>
        </div>

        <div class="bg-slate-900 rounded-2xl shadow-xl border border-slate-800 p-6 relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-red-400 to-red-600"></div>
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-red-500/10 group-hover:bg-red-500/20 rounded-full blur-2xl transition duration-300"></div>
            <h3 class="text-white text-lg font-bold mb-4 flex items-center gap-3 relative z-10">
                <div class="w-8 h-8 rounded-lg bg-red-500/20 text-red-500 border border-red-500/30 flex items-center justify-center shadow-sm">
                    <i class="fas fa-hand-holding-usd text-sm"></i>
                </div>
                Payment Status
            </h3>
            <div class="flex justify-between items-end mb-3">
                <span class="text-sm text-slate-400 font-bold uppercase">Total Bill</span>
                <span class="text-3xl lg:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-rose-300 tracking-tight" x-text="'Rs. ' + netTotal"></span>
            </div>

            <div class="flex gap-2 mb-2">
                <div class="relative flex-1">
                    <span class="absolute left-3 top-2.5 text-slate-500 font-bold">Paid</span>
                    <input type="number" id="received-amount" x-model="received_amount" @keydown.enter.prevent="saveInvoice()" placeholder="0" class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-12 pr-3 py-2 text-white font-bold focus:border-red-500 outline-none">
                </div>
                <button @click="received_amount = 0" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-lg text-xs">
                    Zero
                </button>
            </div>

            <div class="text-right border-t border-slate-800 pt-2">
                <span class="text-xs text-slate-500 font-bold uppercase mr-2">Balance Due: </span>
                <span class="font-mono font-bold text-lg text-red-500" x-text="'Rs. ' + (netTotal - received_amount).toFixed(2)"></span>
            </div>
        </div>
    </div>

    <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl mb-24 overflow-hidden relative">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-slate-700 to-slate-600"></div>
        <div class="bg-slate-950 px-6 py-4 border-b border-slate-800 flex justify-between items-center">
             <h3 class="font-bold text-sm text-slate-300 flex items-center gap-2">
                 <i class="fas fa-shopping-cart text-slate-500"></i> Cart Items
             </h3>
        </div>
        <div class="p-4 lg:px-6 bg-slate-800/30 border-b border-slate-800 flex items-start gap-4 z-20 relative">
            <div class="pt-3 hidden sm:block">
                <i class="fas fa-search text-red-500 text-xl"></i>
            </div>
            <div class="flex-1 relative">
                <div class="flex justify-between items-end mb-2">
                    <span class="text-[11px] text-slate-400"><i class="fas fa-keyboard mr-1"></i> Select customer first. Press Enter to add item, Enter again when done to go to Paid amount</span>
                    <span class="text-[11px] text-slate-500 italic hidden lg:block">Adding to Debit Invoice.</span>
                </div>
                <div class="relative">
                    <input type="text"
                        id="item-search"
                        x-model="searchQuery"
                        @input.debounce.200ms="performSearch()"
                        @keydown.enter.prevent="if ((searchQuery || '').trim() === '') { document.getElementById('received-amount').focus() } else { selectFirstResult() }"
                        placeholder="Search by code or name..."
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl py-3.5 px-4 pl-11 text-white focus:ring-2 focus:ring-red-500 outline-none placeholder-slate-500 shadow-inner min-h-[48px] text-[15px] leading-tight">
                    <i class="fas fa-barcode absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-base"></i>
                    
                    <div x-show="searchResults.length > 0"
                        @click.outside="searchResults = []"
                        class="absolute top-[calc(100%+6px)] left-0 w-full bg-slate-900 border border-slate-700 rounded-xl shadow-2xl z-50 max-h-80 overflow-y-auto custom-scrollbar"
                        style="display: none;">
                        <ul>
                            <template x-for="item in searchResults" :key="item.id">
                                <li @click="addItem(item)" class="px-4 py-3.5 hover:bg-red-900/50 cursor-pointer flex justify-between items-center border-b border-slate-800 last:border-0 group transition">
                                    <div class="flex-1 min-w-0 pr-6">
                                        <span class="font-bold text-white block truncate text-[15px]" x-text="item.name"></span>
                                        <span class="text-xs text-slate-400 font-mono group-hover:text-red-200 mt-0.5 block" x-text="item.code"></span>
                                    </div>
                                    <div class="text-right whitespace-nowrap">
                                        <span class="block font-bold text-white text-base" x-text="'Rs. ' + item.price"></span>
                                        <span class="text-xs uppercase font-bold px-2 py-0.5 rounded-md bg-slate-800 group-hover:bg-red-500 group-hover:text-white mt-1 inline-block"
                                            :class="item.stock_qty > 0 ? 'text-yellow-400' : 'text-red-400'"
                                            x-text="item.stock_qty > 0 ? 'Stock: ' + item.stock_qty : 'Out of Stock'"></span>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-400 min-w-[600px]">
                <thead class="bg-slate-900 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="p-4 w-12 text-center">#</th>
                        <th class="p-4 w-32">Code</th>
                        <th class="p-4">Item Name</th>
                        <th class="p-4 w-20 text-center">Qty</th>
                        <th class="p-4 w-28 text-right">Rate</th>
                        <th class="p-4 w-28 text-right">Total</th>
                        <th class="p-4 w-12 text-center"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">

                    
                    <tr x-show="rows.length === 0">
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center opacity-50">
                                <i class="fas fa-shopping-cart text-3xl text-slate-600 mb-3"></i>
                                <span class="text-slate-500 text-sm font-medium">No items added yet</span>
                                <span class="text-slate-600 text-[11px] mt-1">Search or scan barcode below to begin</span>
                            </div>
                        </td>
                    </tr>
                    <template x-for="(row, index) in rows" :key="index">
                        <tr class="hover:bg-slate-800/50 transition">
                            <td class="p-4 text-center text-slate-500" x-text="index + 1"></td>
                            <td class="p-4 font-mono text-slate-300 text-xs" x-text="row.code"></td>
                            <td class="p-4">
                                <div class="flex flex-col">
                                    <span class="text-white font-bold text-sm lg:text-base" x-text="row.name"></span>
                                    <span class="text-[10px] text-slate-500 mt-0.5">
                                        Stock: <span class="text-yellow-500 font-mono font-bold" x-text="row.stock"></span>
                                    </span>
                                </div>
                            </td>
                            <td class="p-4">
                                <input type="number" x-model="row.qty" min="1" @keydown.enter.prevent="document.getElementById('item-search').focus()" class="qty-input w-full bg-slate-950 border border-slate-700 rounded-lg p-1.5 text-center text-white font-bold focus:border-blue-500 outline-none">
                            </td>
                            <td class="p-4 text-right">
                                <input type="number" x-model="row.price" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-1.5 text-right text-white font-mono focus:border-blue-500 outline-none">
                            </td>
                            <td class="p-4 text-right font-bold text-white font-mono" x-text="(row.qty * row.price).toFixed(2)"></td>
                            <td class="p-4 text-center">
                                <button @click="removeRow(index)" class="text-red-500 hover:text-white transition"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                    </template>


                </tbody>
            </table>
        </div>
    </div>

    <!-- Sticky Footer Actions -->
    <div class="fixed bottom-0 left-0 w-full bg-slate-900/95 backdrop-blur-md border-t border-slate-800 p-4 z-40">
        <div class="max-w-7xl mx-auto flex justify-end gap-4">
            <button @click="window.location.href='/admin/dashboard'" class="px-4 lg:px-6 py-3 rounded-xl border border-slate-700 text-slate-400 font-bold hover:bg-slate-800 text-sm lg:text-base">
                Cancel
            </button>
            <button @click="saveInvoice()" class="px-6 lg:px-8 py-3 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold shadow-lg shadow-red-900/50 flex items-center gap-2 transform active:scale-95 transition text-sm lg:text-base">
                <i class="fas fa-save"></i> <span class="hidden lg:inline">Confirm Debit Sale</span><span class="lg:hidden">Save</span>
            </button>
        </div>
    </div>

    <!-- Success Modal -->
    <div x-show="showSuccess" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-sm flex flex-col max-h-[90vh]">
            <div class="bg-red-600 p-4 text-center">
                <h2 class="text-white font-bold text-lg"><i class="fas fa-check-circle"></i> Sale Saved</h2>
            </div>
            <div class="p-4 bg-gray-100 overflow-y-auto flex-1 flex justify-center">
                <div class="bg-white p-2 shadow-sm w-[300px] text-black" x-html="receiptHtml"></div>
            </div>
            <div class="p-4 bg-white border-t border-gray-200 flex gap-3">
                <button @click="printReceipt()" class="flex-1 py-3 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold">Print</button>
                <button @click="resetForm()" class="flex-1 py-3 rounded-lg border border-gray-300 text-gray-700 font-bold">New Sale</button>
            </div>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div x-show="showCustomerModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm" style="display: none;">
        <div class="bg-slate-900 border border-slate-700 rounded-xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-xl font-bold text-white mb-4"><i class="fas fa-user-plus text-blue-500 mr-2"></i>Add New Customer</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-slate-400 mb-1">Name *</label>
                    <input type="text" x-model="newCustomer.name" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 text-white focus:border-blue-500 outline-none" placeholder="e.g. John Doe">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-400 mb-1">Phone</label>
                    <input type="text" x-model="newCustomer.phone" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 text-white focus:border-blue-500 outline-none" placeholder="e.g. 03001234567">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-400 mb-1">Address</label>
                    <input type="text" x-model="newCustomer.address" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 text-white focus:border-blue-500 outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" @click="showCustomerModal = false; customer_id = ''" class="px-4 py-2 border border-slate-700 rounded-lg text-slate-400 hover:bg-slate-800 font-bold">Cancel</button>
                <button type="button" @click="saveCustomer()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500 font-bold">Save Customer</button>
            </div>
        </div>
    </div>

</div>

<script>
    function debitSaleInvoice() {
        return {
            customer_id: '',
            salesman_id: '{{ Auth::id() }}',
            invoice_no: '{{ $nextInvoice }}',
            date: new Date().toISOString().slice(0, 10),
            rows: [],
            received_amount: '', // Can be empty or partial
            searchQuery: '',
            searchResults: [],
            showSuccess: false,
            receiptHtml: '',
            lastSaleId: null,
            showCustomerModal: false,
            newCustomer: { name: '', phone: '', address: '' },

            async saveCustomer() {
                if (!this.newCustomer.name) {
                    alert('Customer name is required.');
                    return;
                }
                try {
                    let response = await fetch('/customers/quick-store', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.newCustomer)
                    });
                    let data = await response.json();
                    if (data.success) {
                        let select = document.querySelector('select[x-model="customer_id"]');
                        let phoneStr = data.customer.phone ? ' (' + data.customer.phone + ')' : '';
                        let option = new Option(data.customer.name + phoneStr, data.customer.id);
                        select.add(option);
                        this.customer_id = data.customer.id;
                        this.showCustomerModal = false;
                        this.newCustomer = { name: '', phone: '', address: '' };
                    } else {
                        alert('Failed to save customer.');
                    }
                } catch (err) {
                    console.error(err);
                    alert('System error while saving customer.');
                }
            },

            get netTotal() {
                return this.rows.reduce((sum, row) => sum + (row.qty * row.price), 0).toFixed(2);
            },

            async performSearch() {
                const query = (this.searchQuery || '').trim();
                if (query.length < 1) {
                    this.searchResults = [];
                    return;
                }
                try {
                    let response = await fetch(`/debit-sales/search?q=${encodeURIComponent(query)}`);
                    this.searchResults = await response.json();
                } catch (error) {
                    console.error("Search failed");
                }
            },

            addItem(item) {
                let existing = this.rows.find(r => r.id === item.id);
                if (existing) {
                    if (existing.qty < item.stock_qty) {
                        existing.qty++;
                    } else {
                        alert(`Only ${item.stock_qty} left in stock!`);
                    }
                } else {
                    if (item.stock_qty > 0) {
                        this.rows.push({
                            id: item.id,
                            code: item.code,
                            name: item.name,
                            qty: 1,
                            price: item.price,
                            stock: item.stock_qty
                        });
                    } else {
                        alert('Out of Stock!');
                    }
                }
                this.searchQuery = '';
                this.searchResults = [];
                this.$nextTick(() => {
                    let index = this.rows.findIndex(r => r.id === item.id);
                    if (index !== -1) {
                        const qtyInputs = document.querySelectorAll('.qty-input');
                        if (qtyInputs[index]) {
                            qtyInputs[index].focus();
                            qtyInputs[index].select();
                        }
                    }
                });
            },

            selectFirstResult() {
                if (this.searchResults.length > 0) this.addItem(this.searchResults[0]);
            },
            removeRow(index) {
                this.rows.splice(index, 1);
            },

            async saveInvoice() {
                // 1. Mandatory Customer Check
                if (!this.customer_id) {
                    alert("Please select a Customer for Debit Sales.");
                    return;
                }
                if (this.rows.length === 0) {
                    alert("Please add items.");
                    return;
                }

                let payload = {
                    invoice_no: this.invoice_no,
                    customer_id: this.customer_id,
                    salesman_id: this.salesman_id,
                    date: this.date,
                    rows: this.rows,
                    grand_total: this.netTotal,
                    received_amount: this.received_amount
                };

                try {
                    // Send to DEBIT Controller
                    let response = await fetch('/debit-sales/store', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    });
                    let result = await response.json();
                    if (result.success) {
                        this.receiptHtml = result.receipt_html;
                        this.lastSaleId = result.sale_id;
                        this.showSuccess = true;

                        // Auto-Show Bill Logic
                        if (result.print_url) {
                            window.open(result.print_url, '_blank', 'width=400,height=600');
                        }
                    } else {
                        alert("Error: " + result.message);
                    }
                } catch (error) {
                    alert("System Error.");
                }
            },

            printReceipt() {
                if (this.lastSaleId) window.open(`/debit-sales/${this.lastSaleId}/print`, '_blank', 'width=400,height=600');
            },
            resetForm() {
                window.location.reload();
            }
        }
    }
</script>
@endsection