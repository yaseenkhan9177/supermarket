@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto" x-data="debitSaleInvoice()">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 mb-6">

        <div class="bg-slate-900 rounded-xl p-4 lg:p-5 border border-red-900/50 shadow-lg shadow-red-900/10">
            <h3 class="text-white font-bold mb-4 flex items-center gap-2">
                <i class="fas fa-user text-red-500"></i> Customer (Required)
            </h3>
            <select x-model="customer_id" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2.5 text-white mb-3 focus:border-red-500 outline-none">
                <option value="">-- Select Customer --</option>
                @foreach($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                @endforeach
            </select>
            <p x-show="!customer_id" class="text-xs text-red-400 mt-1">* You must select a customer for Debit sales.</p>
        </div>

        <div class="bg-slate-900 rounded-xl p-4 lg:p-5 border border-slate-800">
            <h3 class="text-white font-bold mb-4">
                <i class="fas fa-file-invoice text-slate-400"></i> Invoice Details
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

        <div class="bg-slate-900 rounded-xl p-4 lg:p-5 border border-slate-800 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-bl from-red-500/20 to-transparent rounded-bl-full"></div>
            <h3 class="text-white font-bold mb-4 relative z-10">
                <i class="fas fa-hand-holding-usd text-red-500"></i> Payment Status
            </h3>
            <div class="flex justify-between items-end mb-3">
                <span class="text-sm text-slate-400 font-bold uppercase">Total Bill</span>
                <span class="text-2xl lg:text-3xl font-extrabold text-white tracking-tight" x-text="'Rs. ' + netTotal"></span>
            </div>

            <div class="flex gap-2 mb-2">
                <div class="relative flex-1">
                    <span class="absolute left-3 top-2.5 text-slate-500 font-bold">Paid</span>
                    <input type="number" x-model="received_amount" placeholder="0" class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-12 pr-3 py-2 text-white font-bold focus:border-red-500 outline-none">
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
                            <td class="p-4 text-right font-bold text-white font-mono" x-text="(row.qty * row.price).toFixed(2)"></td>
                            <td class="p-4 text-center">
                                <button @click="removeRow(index)" class="text-red-500 hover:text-white transition"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                    </template>

                    <tr class="bg-slate-800/30">
                        <td class="p-3 text-center"><i class="fas fa-search text-red-500"></i></td>
                        <td class="p-3 relative" colspan="2">
                            <input type="text"
                                x-model="searchQuery"
                                @input.debounce.200ms="performSearch()"
                                @keydown.enter.prevent="selectFirstResult()"
                                placeholder="Type 1 letter to search..."
                                class="w-full bg-slate-950 border border-slate-700 rounded-lg py-3 px-4 pl-10 text-white focus:ring-2 focus:ring-red-500 outline-none placeholder-slate-500 shadow-inner text-sm lg:text-base">

                            <i class="fas fa-barcode absolute left-6 top-6 text-slate-500"></i>

                            <div x-show="searchResults.length > 0"
                                @click.outside="searchResults = []"
                                class="absolute top-14 left-3 w-[95%] lg:w-[98%] bg-slate-900 border border-slate-700 rounded-xl shadow-2xl z-50 max-h-72 overflow-y-auto custom-scrollbar"
                                style="display: none;">
                                <ul>
                                    <template x-for="item in searchResults" :key="item.id">
                                        <li @click="addItem(item)" class="p-3 hover:bg-red-900/50 cursor-pointer flex justify-between items-center border-b border-slate-800 last:border-0 group transition">
                                            <div class="flex-1 min-w-0 pr-4">
                                                <span class="font-bold text-white block truncate text-sm lg:text-base" x-text="item.name"></span>
                                                <span class="text-xs text-slate-400 font-mono group-hover:text-red-200" x-text="item.code"></span>
                                            </div>
                                            <div class="text-right whitespace-nowrap">
                                                <span class="block font-bold text-white text-sm" x-text="'Rs. ' + item.price"></span>
                                                <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-slate-800 group-hover:bg-red-500 group-hover:text-white"
                                                    :class="item.stock_qty > 0 ? 'text-yellow-500' : 'text-red-500'"
                                                    x-text="item.stock_qty > 0 ? 'Stock: ' + item.stock_qty : 'Out of Stock'"></span>
                                            </div>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </td>
                        <td colspan="4" class="p-4 text-xs text-slate-500 italic hidden lg:table-cell">
                            Adding to Debit Invoice.
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

            get netTotal() {
                return this.rows.reduce((sum, row) => sum + (row.qty * row.price), 0).toFixed(2);
            },

            async performSearch() {
                // 1-char search
                if (this.searchQuery.length < 1) {
                    this.searchResults = [];
                    return;
                }
                try {
                    let response = await fetch(`/debit-sales/search?q=${this.searchQuery}`);
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