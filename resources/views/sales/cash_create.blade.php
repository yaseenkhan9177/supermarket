<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <title>Cash Sales</title>
</head>

<body class="bg-gray-900 font-sans text-gray-200" x-data="cashSalesForm()">

    <nav class="bg-white border-b border-gray-200 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-green-600 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-coins text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-gray-900 leading-none tracking-tight">
                        OwnStore <span class="text-green-600">PRO</span>
                    </h1>
                    <span class="text-xs text-gray-500 font-medium mt-0.5">Cash Sales Entry</span>
                </div>
            </div>
            <div>
                <a href="/admin" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-800 hover:bg-black text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 max-w-[1400px]">

        @if(session('error'))
        <div class="bg-red-500 text-white p-4 rounded-lg mb-4">
            {{ session('error') }}
        </div>
        @endif

        <form action="{{ route('cash-sales.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">

                <div class="lg:col-span-4 bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
                    <div class="flex items-center gap-2 mb-4 border-b border-gray-700 pb-2">
                        <i class="fas fa-user text-green-400"></i>
                        <h3 class="text-white font-bold">Customer Info</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Customer</label>
                            <div class="flex gap-2">
                                <select name="customer_id" x-model="customer_id" @change="updateCustomerName" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-white focus:border-green-500 outline-none">
                                    <option value="">Walk-in Customer (Default)</option>
                                    @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}" data-name="{{ $cust->name }}">{{ $cust->name }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="customer_name" x-model="customer_name">
                                <button type="button" class="bg-gray-700 px-3 rounded text-white hover:bg-gray-600"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Phone / Mobile</label>
                            <input type="text" name="customer_phone" placeholder="Optional" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-gray-400 text-sm">
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 bg-white p-6 rounded-xl text-gray-800 shadow-lg">
                    <div class="flex items-center gap-2 mb-4 border-b pb-2">
                        <i class="fas fa-file-invoice text-green-600"></i>
                        <h3 class="text-gray-900 font-bold">Invoice Details</h3>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Invoice No</label>
                            <input type="text" name="invoice_no" value="CS-{{ date('Y') }}-{{ rand(1000,9999) }}" readonly class="w-full bg-gray-100 border border-gray-300 rounded p-2 font-mono text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Date</label>
                            <input type="date" name="sale_date" value="{{ date('Y-m-d') }}" class="w-full bg-white border border-gray-300 rounded p-2 text-sm">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Salesman</label>
                            <select name="salesman_id" class="w-full bg-white border border-gray-300 rounded p-2 text-sm">
                                <option value="">-- General Salesman --</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 bg-green-50 p-6 rounded-xl border border-green-200 text-gray-800 shadow-lg">
                    <div class="flex items-center gap-2 mb-4 border-b border-green-200 pb-2">
                        <i class="fas fa-cash-register text-green-600"></i>
                        <h3 class="text-green-800 font-bold">Payment & Change</h3>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Deposit To (Account)</label>
                            <select name="deposit_account" class="w-full bg-white border border-gray-300 rounded p-2 text-sm">
                                <option>010000: CASH DRAWER</option>
                                <option>010001: MAIN SAFE</option>
                            </select>
                        </div>

                        <div class="p-3 bg-white rounded border border-green-100">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-bold text-gray-600">Total Bill:</span>
                                <span class="text-lg font-bold text-gray-900" x-text="grandTotal">0.00</span>
                            </div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-bold text-gray-600">Received:</span>
                                <input type="number" step="1" x-model="cashReceived" class="w-24 text-right border rounded p-1 text-sm focus:ring-2 focus:ring-green-500 bg-green-50">
                            </div>
                            <div class="flex justify-between items-center border-t pt-2">
                                <span class="text-sm font-bold text-red-500">Change Due:</span>
                                <span class="text-xl font-extrabold text-red-600" x-text="changeDue">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden text-gray-800 mb-20">
                <div class="p-4 bg-gray-50 border-b flex justify-between items-center">
                    <h3 class="font-bold text-gray-900"><i class="fas fa-shopping-basket mr-2 text-gray-400"></i>Items Cart</h3>
                    <button type="button" @click="addRow()" class="bg-green-100 text-green-700 hover:bg-green-200 px-4 py-2 rounded text-sm font-bold transition">
                        <i class="fas fa-plus mr-1"></i> Add Row
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-bold text-gray-500 uppercase bg-gray-50 border-b">
                                <th class="p-3 w-10 text-center">#</th>
                                <th class="p-3 w-40">Barcode</th>
                                <th class="p-3">Item Name</th>
                                <th class="p-3 w-24">Qty</th>
                                <th class="p-3 w-32">Rate</th>
                                <th class="p-3 w-32">Total</th>
                                <th class="p-3 w-16 text-center"><i class="fas fa-trash"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in rows" :key="row.id">
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="p-3 text-center text-gray-400" x-text="index + 1"></td>

                                    <td class="p-3">
                                        <input type="text" x-model="row.barcode" @keydown.enter.prevent="fetchProduct(index)" class="w-full p-2 border rounded bg-gray-50 focus:bg-white focus:ring-2 focus:ring-green-500 text-sm" placeholder="Scan...">
                                    </td>

                                    <td class="p-3">
                                        <input type="text" x-model="row.name" class="w-full p-2 border rounded bg-gray-50 focus:bg-white text-sm" readonly>
                                        <input type="hidden" :name="`items[${index}][product_id]`" x-model="row.product_id">
                                        <input type="hidden" :name="`items[${index}][name]`" x-model="row.name">
                                        <input type="hidden" :name="`items[${index}][qty]`" x-model="row.qty">
                                        <input type="hidden" :name="`items[${index}][rate]`" x-model="row.rate">
                                    </td>

                                    <td class="p-3">
                                        <input type="number" x-model="row.qty" class="w-full p-2 border rounded text-center text-sm focus:ring-2 focus:ring-green-500">
                                    </td>

                                    <td class="p-3">
                                        <input type="number" step="0.01" x-model="row.rate" class="w-full p-2 border rounded text-right text-sm">
                                    </td>

                                    <td class="p-3 text-right font-bold text-gray-800">
                                        <span x-text="(row.qty * row.rate).toFixed(2)"></span>
                                    </td>

                                    <td class="p-3 text-center">
                                        <button type="button" @click="removeRow(index)" class="text-red-300 hover:text-red-500 transition">
                                            <i class="fas fa-times-circle"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="fixed bottom-0 left-0 w-full bg-white border-t p-4 shadow-lg z-40">
                <div class="container mx-auto max-w-[1400px] flex justify-end items-center gap-4">
                    <div class="text-right mr-4">
                        <span class="block text-xs text-gray-500">NET TOTAL</span>
                        <span class="block text-2xl font-bold text-gray-900" x-text="'Rs. ' + grandTotal"></span>
                    </div>
                    <a href="/admin" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded hover:bg-gray-300">Cancel</a>
                    <button type="submit" class="px-8 py-3 bg-green-600 text-white font-bold rounded shadow hover:bg-green-700 transition transform hover:-translate-y-1">
                        <i class="fas fa-check mr-2"></i> Complete Sale
                    </button>
                    <input type="hidden" name="cash_received" x-model="cashReceived">
                </div>
            </div>

        </form>
    </div>

    <script>
        function cashSalesForm() {
            return {
                cashReceived: 0,
                customer_id: '',
                customer_name: 'Walk-in Customer',
                rows: [{
                    id: Date.now(),
                    product_id: '',
                    barcode: '',
                    name: '',
                    qty: 1,
                    rate: 0
                }],

                updateCustomerName(e) {
                    const selectedOption = e.target.options[e.target.selectedIndex];
                    this.customer_name = selectedOption.dataset.name || 'Walk-in Customer';
                },

                fetchProduct(index) {
                    const barcode = this.rows[index].barcode;
                    if (!barcode) return;

                    fetch(`/api/products/search?barcode=${barcode}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.id) {
                                this.rows[index].product_id = data.id;
                                this.rows[index].name = data.description;
                                this.rows[index].rate = data.sale_rate;
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
                            // Demo Fallback
                            const demoProduct = {
                                id: 202,
                                name: 'DEMO ITEM ' + barcode,
                                price: 60.00
                            };
                            this.rows[index].product_id = demoProduct.id;
                            this.rows[index].name = demoProduct.name;
                            this.rows[index].rate = demoProduct.price;
                            if (index === this.rows.length - 1) {
                                this.addRow();
                            }
                        });
                },

                addRow() {
                    this.rows.push({
                        id: Date.now(),
                        product_id: '',
                        barcode: '',
                        name: '',
                        qty: 1,
                        rate: 0
                    });
                },

                removeRow(index) {
                    if (this.rows.length > 1) {
                        this.rows.splice(index, 1);
                    }
                },

                get grandTotal() {
                    let sum = this.rows.reduce((acc, row) => {
                        return acc + (row.qty * row.rate);
                    }, 0);
                    return sum.toFixed(2);
                },

                get changeDue() {
                    let total = parseFloat(this.grandTotal);
                    let received = parseFloat(this.cashReceived);
                    if (received > total) {
                        return (received - total).toFixed(2);
                    }
                    return "0.00";
                }
            }
        }
    </script>
</body>

</html>