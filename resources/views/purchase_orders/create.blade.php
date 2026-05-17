<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- SweetAlert for nice alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-900 font-sans text-gray-200" x-data="poForm(@json(session('success')), @json(session('error')))">

    <nav class="bg-white border-b border-gray-200 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-sky-600 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-clipboard-list text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-gray-900 leading-none tracking-tight">
                        OwnStore <span class="text-sky-600">PRO</span>
                    </h1>
                    <span class="text-xs text-gray-500 font-medium mt-0.5">Purchase Order (Draft / Request)</span>
                </div>
            </div>
            <div>
                <a href="/dashboard" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-800 hover:bg-black text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 max-w-[1400px] pb-32">

        <form action="/purchase-orders/store" method="POST" @submit.prevent="submitForm">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-sky-500"></div>
                    <div class="flex items-center gap-2 mb-4 border-b border-gray-700 pb-2">
                        <i class="fas fa-truck text-sky-400"></i>
                        <h3 class="text-white font-bold">Order To (Vendor)</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Select Supplier</label>
                            <select x-model="supplierId" name="supplier_id" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm text-white focus:border-sky-500 outline-none">
                                <option value="">-- Choose Supplier --</option>
                                @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="bg-sky-900/30 p-3 rounded border border-sky-800 text-xs text-sky-200">
                            <i class="fas fa-info-circle mr-1"></i> This creates a request only. Inventory will <strong>NOT</strong> increase until you convert this to a Bill.
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-lg text-gray-800">
                    <div class="flex items-center gap-2 mb-4 border-b pb-2">
                        <i class="fas fa-calendar-check text-sky-600"></i>
                        <h3 class="font-bold text-gray-900">Order Dates</h3>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">PO Number</label>
                            <input type="text" name="po_number" value="PO-{{ date('Y') }}-{{ rand(100,999) }}" readonly class="w-full bg-gray-100 border border-gray-300 rounded p-2 text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Order Date</label>
                            <input type="date" name="order_date" value="{{ date('Y-m-d') }}" class="w-full bg-white border border-gray-300 rounded p-2 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Expected Delivery</label>
                        <input type="date" name="delivery_date" class="w-full bg-white border border-gray-300 rounded p-2 text-sm">
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden text-gray-800 mb-6">
                <div class="p-4 bg-sky-50 border-b border-sky-100 flex justify-between items-center">
                    <h3 class="font-bold text-sky-900"><i class="fas fa-box mr-2"></i>Items Ordered</h3>
                    <button type="button" @click="addRow()" class="bg-sky-600 text-white hover:bg-sky-700 px-4 py-2 rounded text-sm font-bold transition shadow">
                        <i class="fas fa-plus mr-1"></i> Add Item
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-bold text-gray-500 uppercase bg-gray-50 border-b">
                                <th class="p-3 w-10">#</th>
                                <th class="p-3 w-32">Barcode</th>
                                <th class="p-3">Item Description</th>
                                <th class="p-3 w-24 text-center">Qty</th>
                                <th class="p-3 w-32 text-right">Est. Cost</th>
                                <th class="p-3 w-32 text-right">Est. Total</th>
                                <th class="p-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in rows" :key="index">
                                <tr class="border-b hover:bg-sky-50 transition">
                                    <td class="p-3 text-center text-gray-400" x-text="index + 1"></td>

                                    <td class="p-3">
                                        <input type="text" x-model="row.code" @keydown.enter.prevent="fetchProduct(index)" class="w-full p-1 border rounded text-xs" placeholder="Scan...">
                                    </td>

                                    <td class="p-3">
                                        <input type="text" x-model="row.name" class="w-full p-1 border rounded text-xs bg-gray-50" readonly>
                                        <input type="hidden" :name="`items[${index}][item_id]`" x-model="row.item_id">
                                    </td>

                                    <td class="p-3">
                                        <input type="number" x-model="row.qty" :name="`items[${index}][qty]`" class="w-full p-1 border rounded text-center text-sm font-bold text-sky-700">
                                    </td>

                                    <td class="p-3">
                                        <input type="number" step="0.01" x-model="row.rate" :name="`items[${index}][rate]`" class="w-full p-1 border rounded text-right text-sm">
                                    </td>

                                    <td class="p-3 text-right font-bold text-gray-900">
                                        <span x-text="(row.qty * row.rate).toFixed(2)"></span>
                                    </td>

                                    <td class="p-3 text-center">
                                        <button type="button" @click="removeRow(index)" class="text-gray-300 hover:text-red-500">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="fixed bottom-0 left-0 w-full bg-white border-t p-4 shadow-[0_-5px_15px_rgba(0,0,0,0.1)] z-40">
                <div class="container mx-auto max-w-[1400px] flex justify-between items-center">

                    <div class="w-1/3">
                        <input type="text" name="memo" placeholder="Internal Note (e.g. Call before delivery)" class="w-full border-b border-gray-300 focus:border-sky-500 outline-none text-sm py-2">
                    </div>

                    <div class="flex items-center gap-6">
                        <div class="text-right">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase">Estimated Total</span>
                            <span class="block text-2xl font-bold text-sky-600" x-text="'Rs. ' + subtotal"></span>
                        </div>

                        <div class="h-10 w-px bg-gray-300"></div>

                        <button type="submit" class="px-8 py-3 bg-sky-600 text-white font-bold rounded shadow hover:bg-sky-700 transition transform hover:-translate-y-1">
                            <i class="fas fa-paper-plane mr-2"></i> Create Order
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <script>
        function poForm(sessionSuccess, sessionError) {
            return {
                supplierId: '',
                rows: [{
                    item_id: '',
                    code: '',
                    name: '',
                    qty: 1,
                    rate: 0
                }],

                init() {
                    if (sessionSuccess) Swal.fire('Success', sessionSuccess, 'success');
                    if (sessionError) Swal.fire('Error', sessionError, 'error');
                },

                fetchProduct(index) {
                    const code = this.rows[index].code;
                    if (!code) return;

                    // USING REAL API (Important fix!)
                    fetch(`/api/products/search?barcode=${code}`)
                        .then(response => {
                            if (response.status === 404) throw new Error('Item not found');
                            if (!response.ok) throw new Error('Server error');
                            return response.json();
                        })
                        .then(data => {
                            this.rows[index].item_id = data.id;
                            this.rows[index].name = data.description;
                            this.rows[index].rate = 0; // PO usually needs cost price, defaulted to 0 for user input

                            if (index === this.rows.length - 1) this.addRow();
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: error.message
                            });
                            this.rows[index].item_id = '';
                            this.rows[index].name = '';
                        });
                },
                addRow() {
                    this.rows.push({
                        item_id: '',
                        code: '',
                        name: '',
                        qty: 1,
                        rate: 0
                    });
                },
                removeRow(index) {
                    if (this.rows.length > 1) this.rows.splice(index, 1);
                },
                submitForm(e) {
                    if (!this.supplierId) {
                        Swal.fire('Error', 'Please select a supplier', 'error');
                        return;
                    }
                    if (this.subtotal <= 0) {
                        Swal.fire('Error', 'Order amount cannot be zero', 'error');
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
</body>

</html>