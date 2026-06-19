@extends('layouts.admin')

@section('navbar_subtitle', 'Debit Sales (Credit Sale)')

@section('content')
<div x-data="debitSalesForm()">


    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    <form action="{{ route('debit-sales.store') }}" method="POST" class="flex flex-col flex-grow overflow-hidden">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6 shrink-0">
            <!-- 1. Customer Section -->
            <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg" x-data="customerQuickAdd()">
                <h3 class="text-white font-bold mb-4 border-b border-gray-700 pb-2 flex items-center">
                    <i class="fas fa-user-circle mr-2 text-indigo-400"></i> 1. Customer
                </h3>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Customer *</label>
                    <div class="flex gap-2">
                        <select id="customer_select" x-model="selectedCustomerId" @change="fetchCustomerDetails()" name="customer_id" class="w-full bg-gray-900 border border-gray-600 rounded-lg p-2.5 text-white focus:ring-2 focus:ring-indigo-500 outline-none transition" required>
                            <option value="">-- Select Customer --</option>
                            @foreach($customers as $cust)
                            <option value="{{ $cust->id }}">{{ $cust->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" @click="isOpen = true" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 rounded-lg shadow-md transition transform hover:scale-105">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Address</label>
                    <textarea x-model="customer.address" readonly class="w-full bg-gray-900 border border-gray-600 rounded-lg p-2 text-gray-400 text-sm h-16 resize-none focus:outline-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Phone</label>
                        <input type="text" x-model="customer.phone" readonly class="w-full bg-gray-900 border border-gray-600 rounded-lg p-2 text-gray-400 text-sm focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Credit Limit</label>
                        <input type="text" x-model="customer.credit_limit" readonly class="w-full bg-gray-900 border border-gray-600 rounded-lg p-2 text-gray-400 text-sm font-bold focus:outline-none">
                    </div>
                </div>
                <div x-show="customer.balance > customer.credit_limit" class="mt-2 text-xs text-red-400 font-bold flex items-center">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Credit Limit Exceeded!
                </div>

                <div x-show="isOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                        <div x-show="isOpen" @click="isOpen = false" class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75"></div>

                        <div x-show="isOpen" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">

                            <div class="bg-indigo-600 px-4 py-3 flex justify-between items-center">
                                <h3 class="text-lg font-bold text-white"><i class="fas fa-user-plus mr-2"></i> Add New Customer</h3>
                                <button type="button" @click="isOpen = false" class="text-white hover:text-gray-200"><i class="fas fa-times"></i></button>
                            </div>

                            <div class="p-6 space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Customer Name *</label>
                                    <input type="text" x-model="form.name" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Phone Number</label>
                                    <input type="text" x-model="form.phone" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 dark:bg-gray-700 dark:text-white outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Address / Area</label>
                                    <textarea x-model="form.address" rows="2" class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 dark:bg-gray-700 dark:text-white outline-none"></textarea>
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:flex sm:flex-row-reverse">
                                <button type="button" @click="saveCustomer" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                                    Save Customer
                                </button>
                                <button type="button" @click="isOpen = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Invoice Details -->
            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-sm border border-gray-100 text-gray-800">
                <h3 class="text-gray-900 font-bold mb-4 border-b pb-2 flex items-center">
                    <i class="fas fa-file-invoice mr-2 text-indigo-600"></i> 2. Invoice Details
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Invoice No</label>
                        <input type="text" name="invoice_no" value="{{ $invoiceNo }}" readonly class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2.5 font-mono text-sm text-gray-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Invoice Date</label>
                        <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}" class="w-full bg-white border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pricing Mode</label>
                        <select name="pricing_type" class="w-full bg-white border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="Retail">Retail</option>
                            <option value="Wholesale">Wholesale</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Due Date *</label>
                        <input type="date" name="due_date" required min="{{ date('Y-m-d') }}" class="w-full bg-white border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Salesman *</label>
                        <select name="salesman_id" class="w-full bg-white border border-gray-200 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                            <option value="">-- Select Salesman --</option>
                            @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Items Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex-grow flex flex-col overflow-hidden mb-6 text-gray-800">
            <div class="p-4 bg-gray-50 border-b flex justify-between items-center shrink-0">
                <h3 class="font-bold text-gray-900 flex items-center">
                    <i class="fas fa-shopping-basket mr-2 text-indigo-600"></i> 3. Items
                </h3>
                <button type="button" @click="addRow()" class="bg-indigo-600 text-white hover:bg-indigo-700 px-4 py-2 rounded-lg text-sm font-bold transition shadow-sm flex items-center">
                    <i class="fas fa-plus mr-1.5"></i> Add Row
                </button>
            </div>

            <div class="flex-grow overflow-auto">
                <table class="w-full text-left border-collapse min-w-[1000px]">
                    <thead class="sticky top-0 z-10">
                        <tr class="text-[10px] font-black text-gray-500 uppercase bg-gray-50 border-b">
                            <th class="p-3 w-12 text-center">#</th>
                            <th class="p-3 w-40">Barcode</th>
                            <th class="p-3">Item Description</th>
                            <th class="p-3 w-24 text-center">Qty</th>
                            <th class="p-3 w-32 text-right">Rate</th>
                            <th class="p-3 w-32 text-right">Total</th>
                            <th class="p-3 w-24 text-center">Disc %</th>
                            <th class="p-3 w-32 text-right bg-indigo-50 text-indigo-700">Net Amount</th>
                            <th class="p-3 w-16 text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in rows" :key="row.id">
                            <tr class="border-b transition hover:bg-gray-50 group">
                                <td class="p-3 text-center text-gray-400 text-xs" x-text="index + 1"></td>

                                <td class="p-3">
                                    <input type="text" x-model="row.barcode" @keydown.enter.prevent="fetchProductByBarcode(index)" class="w-full p-2 border border-gray-200 rounded-lg bg-gray-50 focus:bg-white text-xs outline-none focus:ring-2 focus:ring-indigo-500 transition" placeholder="Scan Barcode...">
                                </td>

                                <td class="p-3">
                                    <div class="relative">
                                        <input type="text" x-model="row.name" class="w-full p-2 border border-gray-200 rounded-lg bg-gray-50 text-xs font-medium text-gray-700" readonly>
                                        <input type="hidden" :name="`items[${index}][product_id]`" x-model="row.product_id">
                                    </div>
                                </td>

                                <td class="p-3">
                                    <input type="number" step="0.01" x-model="row.qty" @input="calculateTotals()" class="w-full p-2 border border-blue-200 rounded-lg text-center text-xs font-bold text-blue-700 bg-blue-50 focus:bg-white transition outline-none" name="`items[${index}][quantity]`">
                                </td>

                                <td class="p-3">
                                    <input type="number" step="0.01" x-model="row.rate" @input="calculateTotals()" class="w-full p-2 border border-gray-200 rounded-lg text-right text-xs outline-none focus:ring-2 focus:ring-indigo-500" name="`items[${index}][rate]`">
                                </td>

                                <td class="p-3 text-right font-medium text-gray-500 text-xs italic">
                                    <span x-text="(row.qty * row.rate).toFixed(2)"></span>
                                </td>

                                <td class="p-3">
                                    <input type="number" step="1" x-model="row.disc" @input="calculateTotals()" class="w-full p-2 border border-red-200 rounded-lg text-center text-xs font-bold text-red-600 bg-red-50 focus:bg-white transition outline-none" name="`items[${index}][discount_percent]`">
                                </td>

                                <td class="p-3 text-right font-bold text-indigo-700 bg-indigo-50/50">
                                    <span x-text="calculateNetRow(row)"></span>
                                </td>

                                <td class="p-3 text-center">
                                    <button type="button" @click="removeRow(index)" class="text-gray-300 hover:text-red-500 transition opacity-0 group-hover:opacity-100">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="shrink-0 bg-gray-900 text-white p-4">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 flex items-center gap-8">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-gray-500 block mb-1">Total Items</span>
                            <span class="text-xl font-bold" x-text="rows.length"></span>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-gray-500 block mb-1">Total Quantity</span>
                            <span class="text-xl font-bold" x-text="totalQty"></span>
                        </div>
                        <div class="ml-auto">
                            <span class="text-[10px] uppercase font-bold text-gray-500 block mb-1">Gross Total</span>
                            <span class="text-xl font-bold text-gray-300" x-text="grossTotal"></span>
                        </div>
                    </div>

                    <div class="bg-indigo-800 rounded-xl p-4 shadow-inner relative overflow-hidden">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-[10px] uppercase font-bold text-indigo-300">Global Discount ($)</span>
                            <input type="number" name="discount" x-model="globalDiscount" @input="calculateTotals()" class="w-24 bg-indigo-900 border border-indigo-700 rounded p-1 text-right text-sm font-bold focus:outline-none">
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs uppercase font-black text-white">Net Invoice Total</span>
                            <span class="text-3xl font-black text-white" x-text="netTotal"></span>
                        </div>
                        <div class="absolute -right-4 -bottom-4 text-indigo-700 text-6xl opacity-20 transform -rotate-12">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center shrink-0">
            <a href="{{ route('debit-sales.index') }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-bold text-sm">
                <i class="fas fa-arrow-left mr-2"></i> Back to List
            </a>
            <div class="flex gap-4">
                <button type="reset" class="px-6 py-3 bg-transparent border border-gray-600 text-gray-600 rounded-lg hover:bg-gray-800 hover:text-white transition font-bold text-sm">
                    Reset Form
                </button>
                <button type="submit" class="px-12 py-3 bg-indigo-600 text-white font-black rounded-lg shadow-xl hover:bg-indigo-700 transform hover:-translate-y-1 transition duration-200 flex items-center">
                    <i class="fas fa-check-double mr-2"></i> POST INVOICE
                </button>
            </div>
        </div>

    </form>
</div>

<script>
    function customerQuickAdd() {
        return {
            isOpen: false,
            form: {
                name: '',
                phone: '',
                address: ''
            },

            saveCustomer() {
                if (!this.form.name) {
                    alert('Customer Name is required!');
                    return;
                }

                // Send data to backend
                fetch('/customers/quick-store', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.form)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // 1. Add new option to dropdown
                            const select = document.getElementById('customer_select');
                            const option = new Option(data.customer.name, data.customer.id, true, true);
                            select.add(option);

                            // 2. Close Modal & Reset
                            this.isOpen = false;
                            this.form = {
                                name: '',
                                phone: '',
                                address: ''
                            };

                            // 3. Trigger change event if you have listeners
                            select.dispatchEvent(new Event('change'));

                            alert('Customer Added Successfully!');
                        } else {
                            alert('Error adding customer');
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        }
    }

    function debitSalesForm() {
        return {
            selectedCustomerId: '',
            customer: {
                address: '',
                phone: '',
                credit_limit: '0.00',
                balance: '0.00'
            },
            rows: [{
                id: Date.now(),
                product_id: '',
                barcode: '',
                name: '',
                qty: 0,
                rate: 0.00,
                disc: 0
            }],
            globalDiscount: 0.00,
            totalQty: 0,
            grossTotal: '0.00',
            netTotal: '0.00',

            fetchCustomerDetails() {
                if (!this.selectedCustomerId) {
                    this.customer = {
                        address: '',
                        phone: '',
                        credit_limit: '0.00',
                        balance: '0.00'
                    };
                    return;
                }
                const url = `/api/customers/${this.selectedCustomerId}`; // Adjust based on your API structure
                // Assuming we can get JSON from the resource route
                fetch(url, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.customer = data;
                    })
                    .catch(err => console.error('Error fetching customer:', err));
            },

            fetchProductByBarcode(index) {
                const barcode = this.rows[index].barcode;
                if (!barcode) return;

                // Adjust the API endpoint to your actual product lookup route
                fetch(`/api/products/search?barcode=${barcode}`) // Using a generic meta route or dedicated barcode route
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.id) {
                            this.rows[index].product_id = data.id;
                            this.rows[index].name = data.description;
                            this.rows[index].rate = data.sale_rate;
                            this.rows[index].qty = 1;
                            this.calculateTotals();
                            this.addRow(); // Auto-add next row
                        } else {
                            alert('Product not found!');
                        }
                    })
                    .catch(err => {
                        // For demo purposes, let's simulate if API fails during implementation
                        console.error('API fail or missing, attempting fallback search logic...');
                    });
            },

            addRow() {
                this.rows.push({
                    id: Date.now(),
                    product_id: '',
                    barcode: '',
                    name: '',
                    qty: 0,
                    rate: 0.00,
                    disc: 0
                });
            },

            removeRow(index) {
                if (this.rows.length > 1) {
                    this.rows.splice(index, 1);
                    this.calculateTotals();
                }
            },

            calculateNetRow(row) {
                let subtotal = row.qty * row.rate;
                let discount = subtotal * (row.disc / 100);
                return (subtotal - discount).toFixed(2);
            },

            calculateTotals() {
                let qty = 0;
                let gross = 0;
                let netRows = 0;

                this.rows.forEach(row => {
                    qty += parseFloat(row.qty) || 0;
                    let rowGross = (parseFloat(row.qty) || 0) * (parseFloat(row.rate) || 0);
                    gross += rowGross;

                    let rowDisc = rowGross * ((parseFloat(row.disc) || 0) / 100);
                    netRows += (rowGross - rowDisc);
                });

                this.totalQty = qty;
                this.grossTotal = gross.toFixed(2);
                this.netTotal = (netRows - (parseFloat(this.globalDiscount) || 0)).toFixed(2);
            }
        }
    }
</script>

<style>
    /* Custom scrollbar for items list */
    .overflow-auto::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .overflow-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .overflow-auto::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 4px;
    }

    .overflow-auto::-webkit-scrollbar-thumb:hover {
        background: #a0aec0;
    }
</style>
@endsection