@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto" x-data="cashSaleInvoice()">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 mb-6">

        <div class="bg-slate-900 rounded-xl p-4 lg:p-5 border border-slate-800">
            <h3 class="text-white font-bold mb-4 flex items-center gap-2">
                <i class="fas fa-user text-blue-500"></i> Customer
            </h3>
            <select x-model="customer_id" @change="if(customer_id === 'new') showCustomerModal = true" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2.5 text-white mb-3 focus:border-blue-500 outline-none">
                <option value="">Walk-in Customer</option>
                <option value="new" class="text-blue-400 font-bold">+ Add New Customer</option>
                @foreach($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }}</option>
                @endforeach
            </select>
        </div>

        <div class="bg-slate-900 rounded-xl p-4 lg:p-5 border border-slate-800">
            <h3 class="text-white font-bold mb-4">
                <i class="fas fa-file-invoice text-green-500"></i> Invoice
            </h3>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[10px] text-slate-400 font-bold uppercase">Inv No</label>
                    <input type="text" x-model="invoice_no" class="w-full bg-slate-950 border border-slate-700 rounded p-2 text-white font-mono text-sm focus:border-blue-500 outline-none" readonly>
                </div>
                <div>
                    <label class="text-[10px] text-slate-400 font-bold uppercase">Date</label>
                    <input type="date" x-model="date" class="w-full bg-slate-950 border border-slate-700 rounded p-2 text-white text-sm focus:border-blue-500 outline-none">
                </div>
            </div>
        </div>

        <div class="bg-slate-900 rounded-xl p-4 lg:p-5 border border-slate-800 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-green-500/20 to-transparent rounded-bl-full"></div>
            <h3 class="text-white font-bold mb-2 relative z-10">
                <i class="fas fa-wallet text-yellow-500"></i> Payment
            </h3>
            <span class="text-xs text-blue-400 font-bold block mb-4">Channel: {{ $activeWalletName }}</span>
            <div class="flex justify-between items-center mb-3">
                <span class="text-xs text-slate-400 font-bold uppercase">Return Adj (Rs)</span>
                <input type="number" x-model="return_adjustment" placeholder="0" class="w-24 bg-slate-950 border border-slate-700 rounded p-1 text-right text-white text-xs focus:border-blue-500 outline-none">
            </div>
            <div class="flex justify-between items-end mb-3">
                <span class="text-sm text-slate-400 font-bold uppercase">Total</span>
                <span class="text-2xl lg:text-3xl font-extrabold text-green-400 tracking-tight" x-text="'Rs. ' + netTotal"></span>
            </div>
            <div class="flex gap-2 mb-2">
                <div class="relative flex-1">
                    <span class="absolute left-3 top-2.5 text-slate-500 font-bold">Rs.</span>
                    <input type="number" x-model="received_amount" placeholder="0" class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-10 pr-3 py-2 text-white font-bold focus:border-yellow-500 outline-none text-lg">
                </div>
                <button @click="received_amount = netTotal" class="px-3 lg:px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-lg shadow-md transition text-xs lg:text-sm whitespace-nowrap">
                    Exact
                </button>
            </div>
            <div class="text-right border-t border-slate-800 pt-2">
                <span class="text-xs text-slate-500 font-bold uppercase mr-2">Change: </span>
                <span class="font-mono font-bold text-lg" :class="(received_amount - netTotal) < 0 ? 'text-red-500' : 'text-yellow-400'" x-text="'Rs. ' + (received_amount - netTotal).toFixed(2)"></span>
            </div>
        </div>
    </div>

    <div class="bg-slate-900 rounded-xl border border-slate-800 shadow-lg mb-24 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-400 min-w-[600px]">
                <thead class="bg-slate-950 text-slate-300 font-bold uppercase text-xs tracking-wider border-b border-slate-800">
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
                                <input type="number" x-model="row.qty" min="1" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-1.5 text-center text-white font-bold focus:border-blue-500 outline-none">
                            </td>
                            <td class="p-4 text-right">
                                <input type="number" x-model="row.price" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-1.5 text-right text-white font-mono focus:border-blue-500 outline-none">
                            </td>
                            <td class="p-4 text-right font-bold text-green-400 font-mono" x-text="(row.qty * row.price).toFixed(2)"></td>
                            <td class="p-4 text-center">
                                <button @click="removeRow(index)" class="text-red-500 hover:text-white transition"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                    </template>

                    <tr class="bg-slate-800/30">
                        <td class="p-3 text-center"><i class="fas fa-search text-blue-500"></i></td>
                        <td class="p-3 relative" colspan="2">

                            <input type="text"
                                x-model="searchQuery"
                                @input.debounce.200ms="performSearch()"
                                @keydown.enter.prevent="selectFirstResult()"
                                placeholder="Type 1 letter to search..."
                                class="w-full bg-slate-950 border border-slate-700 rounded-lg py-3 px-4 pl-10 text-white focus:ring-2 focus:ring-blue-500 outline-none placeholder-slate-500 shadow-inner text-sm lg:text-base">

                            <i class="fas fa-barcode absolute left-6 top-6 text-slate-500"></i>

                            <div x-show="searchResults.length > 0"
                                @click.outside="searchResults = []"
                                class="absolute top-14 left-3 w-[95%] lg:w-[98%] bg-slate-900 border border-slate-700 rounded-xl shadow-2xl z-50 max-h-72 overflow-y-auto custom-scrollbar"
                                style="display: none;">
                                <ul>
                                    <template x-for="item in searchResults" :key="item.id">
                                        <li @click="addItem(item)" class="p-3 hover:bg-blue-600 cursor-pointer flex justify-between items-center border-b border-slate-800 last:border-0 group transition">
                                            <div class="flex-1 min-w-0 pr-4">
                                                <span class="font-bold text-white block truncate text-sm lg:text-base" x-text="item.name"></span>
                                                <span class="text-xs text-slate-400 font-mono group-hover:text-blue-200" x-text="item.code"></span>
                                            </div>
                                            <div class="text-right whitespace-nowrap">
                                                <span class="block font-bold text-green-400 text-sm" x-text="'Rs. ' + item.price"></span>
                                                <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-slate-800 group-hover:bg-blue-500 group-hover:text-white"
                                                    :class="item.stock_qty > 0 ? 'text-yellow-500' : 'text-red-500'"
                                                    x-text="item.stock_qty > 0 ? 'Stock: ' + item.stock_qty : 'Out of Stock'"></span>
                                            </div>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </td>
                        <td colspan="4" class="p-4 text-xs text-slate-500 italic hidden lg:table-cell">
                            Type to search instantly.
                        </td>
                    </tr>

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
            <button @click="saveInvoice()" class="px-6 lg:px-8 py-3 rounded-xl bg-green-600 hover:bg-green-500 text-white font-bold shadow-lg flex items-center gap-2 transform active:scale-95 transition text-sm lg:text-base">
                <i class="fas fa-check-circle"></i> <span class="hidden lg:inline">Complete Sale</span><span class="lg:hidden">Done</span>
            </button>
        </div>
    </div>

    <!-- Success Modal -->
    <div x-show="showSuccess" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-sm flex flex-col max-h-[90vh]">
            <div class="bg-green-600 p-4 text-center">
                <h2 class="text-white font-bold text-lg"><i class="fas fa-check-circle"></i> Sale Complete</h2>
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
    function cashSaleInvoice() {
        return {
            customer_id: '',
            salesman_id: '{{ Auth::id() }}',
            invoice_no: '{{ $nextInvoice }}',
            date: new Date().toISOString().slice(0, 10),
            rows: [],
            received_amount: '',
            return_adjustment: '',
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
                        // Find the select element inside the cashSaleInvoice component
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
                const sub = this.rows.reduce((sum, row) => sum + (row.qty * row.price), 0);
                const ret = parseFloat(this.return_adjustment) || 0;
                return Math.max(0, sub - ret).toFixed(2);
            },

            async performSearch() {
                // ✅ 1-char search
                if (this.searchQuery.length < 1) {
                    this.searchResults = [];
                    return;
                }
                try {
                    let response = await fetch(`/cash-sales/search?q=${this.searchQuery}`);
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
            },

            selectFirstResult() {
                if (this.searchResults.length > 0) this.addItem(this.searchResults[0]);
            },
            removeRow(index) {
                this.rows.splice(index, 1);
            },

            async saveInvoice() {
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
                    received_amount: this.received_amount,
                    return_adjustment: parseFloat(this.return_adjustment) || 0
                };
                try {
                    let response = await fetch('/cash-sales/store', {
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
                if (this.lastSaleId) window.open(`/cash-sales/${this.lastSaleId}/print`, '_blank', 'width=400,height=600');
            },
            resetForm() {
                window.location.reload();
            }
        }
    }
</script>
@endsection