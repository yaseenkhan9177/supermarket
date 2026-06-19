<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <title>Process Refund</title>
    
</head>

<body class="bg-gray-900 font-sans text-gray-200" x-data="refundForm()">

    <nav class="bg-white border-b border-gray-200 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-red-600 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-undo-alt text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-gray-900 leading-none tracking-tight">
                        OwnStore <span class="text-red-600">PRO</span>
                    </h1>
                    <span class="text-xs text-gray-500 font-medium mt-0.5">Cash Refund / Return</span>
                </div>
            </div>
            <div>
                <a href="/admin" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-800 hover:bg-black text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 max-w-[1400px] pb-32">

        <form action="{{ route('refunds.store') }}" method="POST">
            @csrf

            @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-md" role="alert">
                <p class="font-bold">Please check the following errors:</p>
                <ul class="list-disc pl-5 mt-2">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">

                <div class="lg:col-span-4 bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
                    <div class="flex items-center gap-2 mb-4 border-b border-gray-700 pb-2">
                        <i class="fas fa-user-minus text-red-400"></i>
                        <h3 class="text-white font-bold">Customer Details</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Select Customer</label>
                            <div class="flex gap-2">
                                <select name="customer_id" x-model="customer_id" @change="if(customer_id === 'new') showCustomerModal = true" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-white focus:border-red-500 outline-none">
                                    <option value="">-- Walk-in / Select --</option>
                                    <option value="new" class="text-blue-400 font-bold">+ Add New Customer</option>
                                    @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}">{{ $cust->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8 bg-white p-6 rounded-xl text-gray-800 shadow-lg">
                    <div class="flex items-center gap-2 mb-4 border-b pb-2">
                        <i class="fas fa-file-invoice-dollar text-red-600"></i>
                        <h3 class="text-gray-900 font-bold">Refund Transaction Info</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Credit Note #</label>
                            <input type="text" name="credit_no" value="CR-{{ date('Ymd') }}-{{ rand(100,999) }}" readonly class="w-full bg-red-50 border border-red-100 rounded p-2 text-red-800 font-mono font-bold">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Date</label>
                            <input type="date" name="refund_date" value="{{ date('Y-m-d') }}" class="w-full border border-gray-300 rounded p-2">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Refund Paid From</label>
                            <select name="paid_from_account" class="w-full border border-gray-300 rounded p-2">
                                <option>Cash Drawer</option>
                                <option>Main Safe</option>
                                <option>Bank Transfer</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Sales Rep</label>
                            <select name="salesman_id" class="w-full border border-gray-300 rounded p-2">
                                <option value="">-- Select Salesman --</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Memo / Reason</label>
                            <input type="text" name="memo" placeholder="e.g. Expired Product" class="w-full border border-gray-300 rounded p-2">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden text-gray-800 mb-24">
                <div class="p-4 bg-gray-50 border-b flex justify-between items-center">
                    <h3 class="font-bold text-gray-900"><i class="fas fa-box-open mr-2 text-gray-400"></i>Returned Items</h3>
                    <button type="button" @click="addRow()" class="bg-red-50 text-red-600 hover:bg-red-100 px-4 py-2 rounded text-sm font-bold transition">
                        <i class="fas fa-plus mr-1"></i> Add Row
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-bold text-gray-500 uppercase bg-gray-50 border-b">
                                <th class="p-3 w-12 text-center">#</th>
                                <th class="p-3 w-40">Barcode / ID</th>
                                <th class="p-3">Description</th>
                                <th class="p-3 w-24">Qty</th>
                                <th class="p-3 w-32">Rate</th>
                                <th class="p-3 w-20">Disc</th>
                                <th class="p-3 w-32 text-right">Net Amt</th>
                                <th class="p-3 w-16 text-center">Act</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in rows" :key="row.uid">
                                <tr class="border-b hover:bg-red-50 transition group">
                                    <td class="p-3 text-center text-gray-400" x-text="index + 1"></td>

                                    <td class="p-3">
                                        <input type="text" x-model="row.barcode" @keydown.enter.prevent="fetchProduct(index)" class="w-full p-1 border rounded bg-gray-50 text-sm focus:ring-2 focus:ring-red-500" placeholder="Scan...">
                                        <input type="hidden" :name="`rows[${index}][id]`" x-model="row.id">
                                    </td>

                                    <td class="p-3">
                                        <input type="text" :name="`rows[${index}][name]`" x-model="row.name" class="w-full p-1 border rounded bg-white text-sm text-gray-950 focus:ring-2 focus:ring-red-500" placeholder="Item description...">
                                    </td>

                                    <td class="p-3">
                                        <input type="number" :name="`rows[${index}][qty]`" x-model="row.qty" class="w-full p-1 border rounded text-center text-sm font-bold text-red-600">
                                    </td>

                                    <td class="p-3">
                                        <input type="number" step="0.01" :name="`rows[${index}][rate]`" x-model="row.rate" class="w-full p-1 border rounded text-right text-sm">
                                    </td>

                                    <td class="p-3">
                                        <input type="number" :name="`rows[${index}][disc]`" x-model="row.disc" class="w-full p-1 border rounded text-center text-sm">
                                    </td>

                                    <td class="p-3 text-right font-bold text-gray-900">
                                        <span x-text="calculateLineTotal(row)"></span>
                                    </td>

                                    <td class="p-3 text-center">
                                        <button type="button" @click="removeRow(index)" class="text-gray-300 hover:text-red-500 transition">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td class="p-3 text-center"><i class="fas fa-search text-gray-400"></i></td>
                                <td class="p-3 relative" colspan="2">
                                    <input type="text"
                                        x-model="searchQuery"
                                        @input.debounce.200ms="performSearch()"
                                        @keydown.enter.prevent="selectFirstResult()"
                                        placeholder="Type product name or code to search..."
                                        class="w-full bg-white border border-gray-300 rounded-lg py-2 px-3 pl-9 text-gray-900 focus:ring-2 focus:ring-red-500 outline-none text-sm shadow-sm">
                                    <i class="fas fa-barcode absolute left-6 top-5 text-gray-400 text-sm"></i>

                                    <!-- Search Results Dropdown -->
                                    <div x-show="searchResults.length > 0"
                                        @click.outside="searchResults = []"
                                        class="absolute top-12 left-3 w-[95%] bg-white border border-gray-200 rounded-lg shadow-xl z-50 max-h-60 overflow-y-auto"
                                        style="display: none;">
                                        <ul class="divide-y divide-gray-100">
                                            <template x-for="item in searchResults" :key="item.id">
                                                <li @click="addItem(item)" class="p-3 hover:bg-red-50 cursor-pointer flex justify-between items-center transition">
                                                    <div class="flex-1 min-w-0 pr-4">
                                                        <span class="font-bold text-gray-900 block truncate text-sm" x-text="item.name"></span>
                                                        <span class="text-xs text-gray-500 font-mono" x-text="item.code"></span>
                                                    </div>
                                                    <div class="text-right whitespace-nowrap">
                                                        <span class="block font-bold text-gray-900 text-sm" x-text="'Rs. ' + item.price"></span>
                                                        <span class="text-[10px] uppercase font-bold text-gray-500">Add to Refund</span>
                                                    </div>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </td>
                                <td colspan="5" class="p-3 text-xs text-gray-500 italic hidden md:table-cell align-middle">
                                    Use the search bar to find products quickly by name or code.
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="fixed bottom-0 left-0 w-full bg-white border-t p-4 shadow-[0_-5px_15px_rgba(0,0,0,0.1)] z-40">
                <div class="container mx-auto max-w-[1400px] flex justify-between items-center">

                    <div class="flex gap-4">
                        <button type="button" class="px-4 py-2 border rounded hover:bg-gray-100 text-gray-600 text-sm font-bold hidden sm:block">
                            <i class="fas fa-print mr-2"></i> Print Last
                        </button>
                    </div>

                    <div class="flex items-center gap-6">
                        <div class="text-right">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase">Total Refund</span>
                            <span class="block text-2xl font-bold text-red-600" x-text="'Rs. ' + grandTotal"></span>
                        </div>

                        <div class="h-10 w-px bg-gray-300 hidden sm:block"></div>

                        <a href="/admin" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded hover:bg-gray-300 hidden sm:block">
                            Cancel
                        </a>
                        <button type="submit" class="px-8 py-3 bg-red-600 text-white font-bold rounded shadow hover:bg-red-700 transition transform hover:-translate-y-1">
                            <i class="fas fa-save mr-2"></i> Process Refund
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <!-- Add Customer Modal -->
    <div x-show="showCustomerModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm" style="display: none;">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Add New Customer</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Name *</label>
                    <input type="text" x-model="newCustomer.name" class="w-full border rounded p-2 text-gray-900" placeholder="e.g. John Doe">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Phone</label>
                    <input type="text" x-model="newCustomer.phone" class="w-full border rounded p-2 text-gray-900" placeholder="e.g. 03001234567">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Address</label>
                    <input type="text" x-model="newCustomer.address" class="w-full border rounded p-2 text-gray-900">
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" @click="showCustomerModal = false; customer_id = ''" class="px-4 py-2 border rounded text-gray-600 hover:bg-gray-100 font-bold">Cancel</button>
                <button type="button" @click="saveCustomer()" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 font-bold">Save Customer</button>
            </div>
        </div>
    </div>

    <script>
        function refundForm() {
            return {
                customer_id: '',
                showCustomerModal: false,
                newCustomer: { name: '', phone: '', address: '' },
                searchQuery: '',
                searchResults: [],
                rows: [{
                    uid: Date.now(),
                    id: '',
                    barcode: '',
                    name: '',
                    qty: 1,
                    rate: 0,
                    disc: 0
                }],

                async performSearch() {
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
                    // Check if the first row is completely empty (the default blank row)
                    let firstRow = this.rows[0];
                    let isEmptyRow = this.rows.length === 1 && !firstRow.id && !firstRow.barcode && !firstRow.name;

                    if (isEmptyRow) {
                        // Replace the empty row
                        this.rows[0] = {
                            uid: Date.now(),
                            id: item.id,
                            barcode: item.code,
                            name: item.name,
                            qty: 1,
                            rate: item.price,
                            disc: 0
                        };
                    } else {
                        // Check if item already exists in the refund list
                        let existing = this.rows.find(r => r.id === item.id);
                        if (existing) {
                            existing.qty++;
                        } else {
                            // Add as new row
                            this.rows.push({
                                uid: Date.now(),
                                id: item.id,
                                barcode: item.code,
                                name: item.name,
                                qty: 1,
                                rate: item.price,
                                disc: 0
                            });
                        }
                    }
                    
                    this.searchQuery = '';
                    this.searchResults = [];
                },

                selectFirstResult() {
                    if (this.searchResults.length > 0) {
                        this.addItem(this.searchResults[0]);
                    }
                },

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
                            let select = document.querySelector('select[name="customer_id"]');
                            let option = new Option(data.customer.name, data.customer.id);
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

                addRow() {
                    this.rows.push({
                        uid: Date.now(),
                        id: '',
                        barcode: '',
                        name: '',
                        qty: 1,
                        rate: 0,
                        disc: 0
                    });
                },

                removeRow(index) {
                    if (this.rows.length > 1) {
                        this.rows.splice(index, 1);
                    }
                },

                fetchProduct(index) {
                    const barcode = this.rows[index].barcode;
                    if (!barcode) return;

                    fetch(`/cash-sales/search?q=${barcode}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.length > 0) {
                                let item = data.find(i => i.code === barcode) || data[0];
                                this.rows[index].id = item.id;
                                this.rows[index].name = item.name;
                                this.rows[index].rate = item.price;
                                this.rows[index].qty = 1;

                                if (index === this.rows.length - 1) {
                                    this.addRow();
                                }
                            } else {
                                alert('Product not found!');
                            }
                        })
                        .catch(err => {
                            console.error('API Error', err);
                        });
                },

                calculateLineTotal(row) {
                    let total = row.qty * row.rate;
                    let discount = row.disc;
                    return (total - discount).toFixed(2);
                },

                get grandTotal() {
                    let sum = this.rows.reduce((acc, row) => {
                        return acc + parseFloat(this.calculateLineTotal(row));
                    }, 0);
                    return sum.toFixed(2);
                }
            }
        }
    </script>
</body>

</html>