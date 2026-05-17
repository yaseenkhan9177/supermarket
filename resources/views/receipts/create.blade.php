<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <title>Receive Payment</title>
</head>

<body class="bg-gray-900 font-sans text-gray-200" x-data="receiptForm()">

    <nav class="bg-white border-b border-gray-200 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-hand-holding-usd text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-gray-900 leading-none tracking-tight">
                        OwnStore <span class="text-blue-600">PRO</span>
                    </h1>
                    <span class="text-xs text-gray-500 font-medium mt-0.5">Receive Payment / Receipt</span>
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

        <form action="{{ route('receipts.store') }}" method="POST">
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

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-lg text-gray-800 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>

                    <div class="flex items-center gap-2 mb-6 border-b pb-2">
                        <i class="fas fa-file-invoice text-blue-600"></i>
                        <h3 class="font-bold text-gray-900">Receipt Information</h3>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Receipt #</label>
                                <input type="text" name="receipt_no" value="REC-{{ date('Y') }}-{{ rand(100,999) }}" readonly class="w-full bg-gray-100 border border-gray-300 rounded p-2 text-sm font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Date</label>
                                <input type="date" name="receipt_date" value="{{ date('Y-m-d') }}" class="w-full bg-white border border-gray-300 rounded p-2 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Customer / Party *</label>
                            <select x-model="selectedCustomer" @change="fetchPendingInvoices()" name="customer_id" class="w-full bg-blue-50 border border-blue-200 rounded p-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="">-- Select Customer --</option>
                                @foreach($customers as $cust)
                                <option value="{{ $cust->id }}">{{ $cust->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Amount Received</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-400">$</span>
                                    <input type="number" step="0.01" name="amount_received" x-model="amount" class="w-full pl-6 p-2 border border-gray-300 rounded text-right font-bold text-blue-600 focus:border-blue-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Discount Given</label>
                                <input type="number" step="0.01" name="discount_given" x-model="discount" class="w-full p-2 border border-gray-300 rounded text-right">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg text-white relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-green-500"></div>

                    <div class="flex items-center gap-2 mb-6 border-b border-gray-600 pb-2">
                        <i class="fas fa-wallet text-green-400"></i>
                        <h3 class="font-bold">Deposit & Bank Info</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Debit AC (Deposit To)</label>
                            <select name="deposit_account" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm text-gray-300">
                                <option>Cash Account / Drawer</option>
                                <option>Meezan Bank</option>
                                <option>HBL Bank</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Payment Mode</label>
                                <select x-model="paymentMode" name="payment_mode" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm text-gray-300">
                                    <option value="Cash">Cash</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Online">Online Transfer</option>
                                </select>
                            </div>
                            <div x-show="paymentMode !== 'Cash'">
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Cheque/Ref #</label>
                                <input type="text" name="cheque_no" placeholder="Check No" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm">
                            </div>
                        </div>

                        <!-- Conditional Bank Fields -->
                        <div x-show="paymentMode !== 'Cash'" class="grid grid-cols-2 gap-4 transition-all">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Cheque Date</label>
                                <input type="date" name="cheque_date" value="{{ date('Y-m-d') }}" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm text-gray-300">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Bank Name</label>
                                <input type="text" name="bank_name" placeholder="e.g. HBL" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm text-gray-300">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Salesman / Agent</label>
                            <select name="salesman_id" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm text-gray-300">
                                <option value="">-- Select Salesman --</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden text-gray-800">
                <div class="p-4 bg-gray-50 border-b flex justify-between items-center">
                    <h3 class="font-bold text-gray-900"><i class="fas fa-list-alt mr-2 text-gray-400"></i>Pending Invoices (Ledger)</h3>
                    <span class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded font-bold" x-show="invoices.length > 0">
                        Total Due: <span x-text="totalDue"></span>
                    </span>
                </div>

                <div class="overflow-x-auto max-h-60 overflow-y-auto">
                    <div x-show="!selectedCustomer" class="p-8 text-center text-gray-400">
                        <i class="fas fa-search text-4xl mb-3"></i>
                        <p>Select a customer above to view pending invoices.</p>
                    </div>

                    <table x-show="selectedCustomer" class="w-full text-left border-collapse">
                        <thead class="bg-gray-100 sticky top-0">
                            <tr class="text-xs font-bold text-gray-500 uppercase">
                                <th class="p-3">Date</th>
                                <th class="p-3">Voucher #</th>
                                <th class="p-3 text-right">Invoice Total</th>
                                <th class="p-3 text-right">Paid</th>
                                <th class="p-3 text-right">Balance</th>
                                <th class="p-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="inv in invoices" :key="inv.id">
                                <tr class="border-b hover:bg-blue-50 transition cursor-pointer">
                                    <td class="p-3 text-sm text-gray-600" x-text="inv.date"></td>
                                    <td class="p-3 text-sm font-bold text-blue-600" x-text="inv.no"></td>
                                    <td class="p-3 text-sm text-right font-medium" x-text="inv.total"></td>
                                    <td class="p-3 text-sm text-right text-green-600" x-text="inv.paid"></td>
                                    <td class="p-3 text-sm text-right font-bold text-red-500" x-text="inv.balance"></td>
                                    <td class="p-3 text-center">
                                        <span class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded uppercase font-bold">Unpaid</span>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="invoices.length === 0 && selectedCustomer">
                                <td colspan="6" class="p-4 text-center text-gray-500">No pending invoices found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="fixed bottom-0 left-0 w-full bg-white border-t p-4 shadow-[0_-5px_15px_rgba(0,0,0,0.1)] z-40">
                <div class="container mx-auto max-w-[1400px] flex justify-between items-center">

                    <div class="w-1/3">
                        <input type="text" name="memo" placeholder="Memo / Remarks..." class="w-full border-b border-gray-300 focus:border-blue-500 outline-none text-sm py-2">
                    </div>

                    <div class="flex items-center gap-6">
                        <div class="text-right">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase">Total Adjusted</span>
                            <span class="block text-2xl font-bold text-blue-600" x-text="'Rs. ' + (parseFloat(amount || 0) + parseFloat(discount || 0)).toFixed(2)"></span>
                        </div>

                        <div class="h-10 w-px bg-gray-300"></div>

                        <a href="/admin" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded hover:bg-gray-300">
                            Close
                        </a>
                        <button type="submit" class="px-8 py-3 bg-blue-600 text-white font-bold rounded shadow hover:bg-blue-700 transition transform hover:-translate-y-1">
                            <i class="fas fa-save mr-2"></i> Save Receipt
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <script>
        function receiptForm() {
            return {
                selectedCustomer: '',
                amount: 0,
                discount: 0,
                paymentMode: 'Cash',
                invoices: [],

                fetchPendingInvoices() {
                    if (!this.selectedCustomer) {
                        this.invoices = [];
                        return;
                    }

                    fetch(`/receipts/pending-invoices/${this.selectedCustomer}`)
                        .then(res => res.json())
                        .then(data => {
                            this.invoices = data;
                        })
                        .catch(err => {
                            console.error(err);
                            this.invoices = [];
                        });
                },

                get totalDue() {
                    let sum = this.invoices.reduce((acc, inv) => acc + parseFloat(inv.balance.replace(/,/g, '')), 0);
                    return 'Rs. ' + sum.toFixed(2);
                }
            }
        }
    </script>
</body>

</html>