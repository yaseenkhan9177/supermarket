<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Entry | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-900 font-sans text-gray-200" x-data="purchaseForm()">

    <!-- SweetAlert Logic -->
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Success!',
                text: "{{ session('success') }}",
                icon: 'success',
                background: '#1f2937',
                color: '#fff',
                confirmButtonColor: '#4f46e5'
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
                confirmButtonColor: '#ef4444'
            });
        });
    </script>
    @endif

    @if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Validation Error',
                html: '<ul class="text-left list-disc pl-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
                icon: 'warning',
                background: '#1f2937',
                color: '#fff',
                confirmButtonColor: '#ef4444'
            });
        });
    </script>
    @endif

    <nav class="bg-white border-b border-gray-200 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-truck-loading text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-gray-900 leading-none tracking-tight">
                        OwnStore <span class="text-indigo-600">PRO</span>
                    </h1>
                    <span class="text-xs text-gray-500 font-medium mt-0.5">Purchase Entry (Cash Bill Tax)</span>
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

        <form action="/purchases/store" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
                    <div class="flex items-center gap-2 mb-4 border-b border-gray-700 pb-2">
                        <i class="fas fa-user-tie text-indigo-400"></i>
                        <h3 class="text-white font-bold">Vendor / Supplier</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Select Vendor</label>
                            <div class="flex gap-2">
                                <select name="supplier_id" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm text-white focus:border-indigo-500 outline-none">
                                    <option value="">-- Choose Supplier --</option>
                                    @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="bg-indigo-600 px-3 rounded text-white hover:bg-indigo-700"><i class="fas fa-plus"></i></button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Vendor Bill #</label>
                            <input type="text" name="vendor_bill_no" placeholder="e.g. INV-9988" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm text-white focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-lg text-gray-800">
                    <div class="flex items-center gap-2 mb-4 border-b pb-2">
                        <i class="fas fa-file-invoice-dollar text-indigo-600"></i>
                        <h3 class="font-bold text-gray-900">Bill Details</h3>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Purchase ID</label>
                            <input type="text" name="purchase_no" value="PO-{{ date('Y') }}-{{ rand(1000,9999) }}" readonly class="w-full bg-gray-100 border border-gray-300 rounded p-2 text-sm font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Date</label>
                            <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" class="w-full bg-white border border-gray-300 rounded p-2 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Paid From</label>
                        <select name="paid_from_account" class="w-full bg-white border border-gray-300 rounded p-2 text-sm">
                            <option>Cash Drawer</option>
                            <option>Bank Account</option>
                            <option>Credit (Pay Later)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden text-gray-800 mb-6">
                <div class="p-4 bg-indigo-50 border-b border-indigo-100 flex justify-between items-center">
                    <h3 class="font-bold text-indigo-900"><i class="fas fa-boxes mr-2"></i>Items Received</h3>
                    <button type="button" @click="addRow()" class="bg-indigo-600 text-white hover:bg-indigo-700 px-4 py-2 rounded text-sm font-bold transition shadow">
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
                                <th class="p-3 w-32">Expiry</th>
                                <th class="p-3 w-20 text-center">Qty</th>
                                <th class="p-3 w-24 text-right">Cost</th>
                                <th class="p-3 w-24 text-right">Total</th>
                                <th class="p-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in rows" :key="index">
                                <tr class="border-b hover:bg-indigo-50 transition">
                                    <td class="p-3 text-center text-gray-400" x-text="index + 1"></td>

                                    <td class="p-3">
                                        <input type="text" x-model="row.code" @keydown.enter.prevent="fetchProduct(index)" class="w-full p-1 border rounded text-xs" placeholder="Scan...">
                                    </td>

                                    <td class="p-3">
                                        <input type="text" x-model="row.name" class="w-full p-1 border rounded text-xs bg-gray-50" readonly>
                                        <input type="hidden" :name="`items[${index}][item_id]`" x-model="row.item_id">
                                    </td>

                                    <td class="p-3">
                                        <input type="text" :name="`items[${index}][batch_no]`" class="w-full p-1 border rounded text-xs uppercase" placeholder="BATCH">
                                    </td>

                                    <td class="p-3">
                                        <input type="date" :name="`items[${index}][expiry_date]`" class="w-full p-1 border rounded text-xs">
                                    </td>

                                    <td class="p-3">
                                        <input type="number" x-model="row.qty" :name="`items[${index}][qty]`" class="w-full p-1 border rounded text-center text-sm font-bold text-indigo-700">
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

                    <div class="w-1/3 flex gap-4">
                        <input type="text" name="memo" placeholder="Memo / Remarks..." class="flex-1 border-b border-gray-300 focus:border-indigo-500 outline-none text-sm py-2">
                    </div>

                    <div class="flex items-center gap-6">
                        <div class="flex gap-4 text-right text-sm">
                            <div>
                                <span class="block text-[10px] text-gray-400 uppercase">Subtotal</span>
                                <span class="font-bold text-gray-700" x-text="subtotal"></span>
                            </div>
                            <div>
                                <span class="block text-[10px] text-gray-400 uppercase">Tax</span>
                                <input type="number" x-model="tax" name="tax_amount" class="w-20 border rounded p-1 text-right text-xs" placeholder="0.00">
                            </div>
                            <div>
                                <span class="block text-[10px] text-gray-400 uppercase">Discount</span>
                                <input type="number" x-model="discount" name="discount" class="w-20 border rounded p-1 text-right text-xs" placeholder="0.00">
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase">Net Payable</span>
                            <span class="block text-2xl font-bold text-indigo-600" x-text="'$' + netTotal"></span>
                        </div>

                        <div class="h-10 w-px bg-gray-300"></div>

                        <button type="submit" class="px-8 py-3 bg-indigo-600 text-white font-bold rounded shadow hover:bg-indigo-700 transition transform hover:-translate-y-1">
                            <i class="fas fa-save mr-2"></i> Save Bill
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <script>
        function purchaseForm() {
            return {
                rows: [{
                    item_id: '',
                    code: '',
                    name: '',
                    qty: 1,
                    rate: 0
                }],
                tax: 0,
                discount: 0,

                async fetchProduct(index) {
                    const code = this.rows[index].code;
                    if (!code) return;

                    // Fetch logic. Replace with real API call.
                    // await fetch('/api/products/search?q=' + code)
                    // .then(res => res.json())
                    // ...

                    // Mock for now as backend API for fetchProduct wasn't explicitly requested but logic is here
                    // Assuming user will implement real fetch or manually enter if barcode scanner acts as keyboard input
                    // For demo purpose, if code is entered, we'll try to find a matching item from a small JS list or leave empty for now.
                    // The user's snippet hardcoded a mock item.
                    const mock = {
                        id: 1,
                        name: 'Sample Item (Scan Logic Required)',
                        cost: 100.00
                    };

                    if (code === '123') { // Simple test mock
                        this.rows[index].item_id = mock.id;
                        this.rows[index].name = mock.name;
                        this.rows[index].rate = mock.cost;

                        if (index === this.rows.length - 1) this.addRow();
                    } else {
                        // Real implementation would look up via API.
                        console.log('Implement real API fetch here');
                        // Fallback for visual test (Using ID 1 which exists)
                        this.rows[index].item_id = 1;
                        this.rows[index].name = 'Manually Entered Item (ID: 1)';
                    }
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

                get subtotal() {
                    return this.rows.reduce((acc, row) => acc + (row.qty * row.rate), 0).toFixed(2);
                },

                get netTotal() {
                    let sub = parseFloat(this.subtotal);
                    let t = parseFloat(this.tax || 0);
                    let d = parseFloat(this.discount || 0);
                    return (sub + t - d).toFixed(2);
                }
            }
        }
    </script>
</body>

</html>