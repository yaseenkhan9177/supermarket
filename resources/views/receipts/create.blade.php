<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <title>Receive Payment</title>
</head>

<body class="bg-gray-900 font-sans text-gray-200 antialiased min-h-screen" x-data="receiptForm()">

    <nav class="bg-white border-b border-gray-200 px-4 sm:px-6 py-3 shadow-sm sticky top-0 z-50 mb-6 sm:mb-8">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center gap-2">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-blue-600 flex items-center justify-center text-white shadow-md flex-shrink-0">
                    <i class="fas fa-hand-holding-usd text-base sm:text-lg"></i>
                </div>
                <div class="flex flex-col min-w-0">
                    <h1 class="text-lg sm:text-xl font-extrabold text-gray-900 leading-none tracking-tight truncate">
                        OwnStore <span class="text-blue-600">PRO</span>
                    </h1>
                    <span class="text-[11px] sm:text-xs text-gray-500 font-medium mt-0.5 truncate">Receive Payment / Receipt</span>
                </div>
            </div>
            <div>
                <a href="/admin" class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-5 py-2 sm:py-2.5 bg-gray-800 hover:bg-black text-white text-xs sm:text-sm font-bold rounded-lg shadow-sm transition transform active:scale-95 whitespace-nowrap">
                    <i class="fas fa-home"></i> <span class="hidden xs:inline">Dashboard</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 sm:px-6 max-w-[1400px] pb-48 md:pb-36">

        <form action="{{ route('receipts.store') }}" method="POST" @submit="if(isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true;">
            @csrf

            @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-md" role="alert">
                <p class="font-bold flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> Error</p>
                <p class="mt-1 text-sm">{{ session('error') }}</p>
            </div>
            @endif

            @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-md" role="alert">
                <p class="font-bold flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> Please check the following errors:</p>
                <ul class="list-disc pl-5 mt-2 text-sm">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                <!-- Left Column: Receipt & Customer Information -->
                <div class="bg-white p-5 sm:p-6 rounded-xl border border-gray-200 shadow-lg text-gray-800 relative overflow-hidden flex flex-col justify-between">
                    <div class="absolute top-0 left-0 w-1.5 h-full bg-blue-500"></div>

                    <div>
                        <div class="flex items-center gap-2 mb-5 sm:mb-6 border-b pb-2">
                            <i class="fas fa-file-invoice text-blue-600"></i>
                            <h3 class="font-bold text-gray-900 text-base sm:text-lg">Receipt Information</h3>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Receipt #</label>
                                    <input type="text" name="receipt_no" value="REC-{{ date('Y') }}-{{ rand(100,999) }}" readonly class="w-full bg-gray-100 border border-gray-300 rounded p-2.5 sm:p-2 text-sm font-mono focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Date</label>
                                    <input type="date" name="receipt_date" value="{{ date('Y-m-d') }}" class="w-full bg-white border border-gray-300 rounded p-2.5 sm:p-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Customer / Party *</label>
                                <select x-model="selectedCustomer" @change="fetchPendingInvoices()" name="customer_id" class="w-full bg-blue-50 border border-blue-200 rounded p-2.5 sm:p-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                    <option value="">-- Select Customer --</option>
                                    @foreach($customers as $cust)
                                    <option value="{{ $cust->id }}">{{ $cust->name }} {{ $cust->phone ? '('.$cust->phone.')' : '' }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Amount Received</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 sm:top-2 text-gray-400 font-bold text-sm">Rs.</span>
                                        <input type="number" step="0.01" name="amount_received" x-model="amount" placeholder="0.00" class="w-full pl-10 p-2.5 sm:p-2 border border-gray-300 rounded text-right font-bold text-blue-600 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 outline-none text-base sm:text-sm">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Discount Given</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 sm:top-2 text-gray-400 font-bold text-sm">Rs.</span>
                                        <input type="number" step="0.01" name="discount_given" x-model="discount" placeholder="0.00" class="w-full pl-10 p-2.5 sm:p-2 border border-gray-300 rounded text-right focus:border-blue-500 focus:ring-2 focus:ring-blue-500 outline-none text-base sm:text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Settlement Quick Preview Badge -->
                    <div x-show="selectedCustomer && totalSettlement > 0" class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs bg-blue-50/70 p-2.5 rounded-lg border border-blue-100">
                        <span class="text-blue-900 font-semibold flex items-center gap-1.5">
                            <i class="fas fa-calculator text-blue-600"></i> Total Settling:
                        </span>
                        <span class="font-extrabold text-blue-700 text-sm" x-text="formattedTotalSettlement"></span>
                    </div>
                </div>

                <!-- Right Column: Deposit & Bank Info -->
                <div class="bg-gray-800 p-5 sm:p-6 rounded-xl border border-gray-700 shadow-lg text-white relative overflow-hidden flex flex-col justify-between">
                    <div class="absolute top-0 left-0 w-1.5 h-full bg-green-500"></div>

                    <div>
                        <div class="flex items-center gap-2 mb-5 sm:mb-6 border-b border-gray-600 pb-2">
                            <i class="fas fa-wallet text-green-400"></i>
                            <h3 class="font-bold text-base sm:text-lg">Deposit & Bank Info</h3>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Debit AC (Deposit To)</label>
                                <select name="deposit_account" class="w-full bg-gray-900 border border-gray-600 rounded p-2.5 sm:p-2 text-sm text-gray-300 focus:ring-2 focus:ring-green-500 outline-none">
                                    <option>Cash Account / Drawer</option>
                                    <option>Meezan Bank</option>
                                    <option>HBL Bank</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Payment Mode</label>
                                    <select x-model="paymentMode" name="payment_mode" class="w-full bg-gray-900 border border-gray-600 rounded p-2.5 sm:p-2 text-sm text-gray-300 focus:ring-2 focus:ring-green-500 outline-none">
                                        <option value="Cash">Cash</option>
                                        <option value="Cheque">Cheque</option>
                                        <option value="Online">Online Transfer</option>
                                    </select>
                                </div>
                                <div x-show="paymentMode !== 'Cash'">
                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Cheque/Ref #</label>
                                    <input type="text" name="cheque_no" placeholder="Check No" class="w-full bg-gray-900 border border-gray-600 rounded p-2.5 sm:p-2 text-sm text-white focus:ring-2 focus:ring-green-500 outline-none">
                                </div>
                            </div>

                            <!-- Conditional Bank Fields -->
                            <div x-show="paymentMode !== 'Cash'" class="grid grid-cols-1 sm:grid-cols-2 gap-4 transition-all">
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Cheque Date</label>
                                    <input type="date" name="cheque_date" value="{{ date('Y-m-d') }}" class="w-full bg-gray-900 border border-gray-600 rounded p-2.5 sm:p-2 text-sm text-gray-300 focus:ring-2 focus:ring-green-500 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Bank Name</label>
                                    <input type="text" name="bank_name" placeholder="e.g. HBL" class="w-full bg-gray-900 border border-gray-600 rounded p-2.5 sm:p-2 text-sm text-gray-300 focus:ring-2 focus:ring-green-500 outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Salesman / Agent</label>
                                <select name="salesman_id" class="w-full bg-gray-900 border border-gray-600 rounded p-2.5 sm:p-2 text-sm text-gray-300 focus:ring-2 focus:ring-green-500 outline-none">
                                    <option value="">-- Select Salesman --</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Account Summary Section (Shows when customer is selected) -->
            <div x-show="selectedCustomer" x-cloak class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden text-gray-800 mb-6 transition-all">
                <div class="p-4 bg-gradient-to-r from-slate-900 to-gray-800 text-white flex flex-wrap justify-between items-center gap-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold">
                            <i class="fas fa-user-tag text-sm"></i>
                        </div>
                        <div>
                            <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block">Customer Account Summary</span>
                            <h4 class="font-extrabold text-base text-white leading-tight" x-text="customerInfo ? customerInfo.name : 'Selected Customer'"></h4>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs px-2.5 py-1 rounded-full font-bold"
                              :class="(summary && summary.account_balance > 0) ? 'bg-red-500/20 text-red-300 border border-red-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'">
                            <i class="fas fa-circle text-[8px] mr-1"></i>
                            <span x-text="(summary && summary.account_balance > 0) ? 'Outstanding Due' : 'Account Settled'"></span>
                        </span>
                    </div>
                </div>

                <!-- KPI Metric Grid -->
                <div class="p-4 sm:p-5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 bg-gray-50 border-b border-gray-200">
                    <!-- Current Outstanding Balance -->
                    <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Account Balance</span>
                        <div class="text-base sm:text-lg font-black text-red-600 mt-0.5 truncate" x-text="summary ? summary.formatted_account_balance : 'Rs. 0.00'"></div>
                        <span class="text-[10px] text-gray-500">Current ledger debt</span>
                    </div>

                    <!-- Total Invoice Outstanding -->
                    <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Invoice Due</span>
                        <div class="text-base sm:text-lg font-black text-amber-600 mt-0.5 truncate" x-text="summary ? summary.formatted_invoice_outstanding : 'Rs. 0.00'"></div>
                        <span class="text-[10px] text-gray-500">Unpaid invoices total</span>
                    </div>

                    <!-- Total Invoices Generated -->
                    <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Invoices</span>
                        <div class="text-base sm:text-lg font-black text-gray-800 mt-0.5 truncate" x-text="summary ? summary.formatted_invoices_total : 'Rs. 0.00'"></div>
                        <span class="text-[10px] text-gray-500">Gross invoice total</span>
                    </div>

                    <!-- Total Paid Amount -->
                    <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Previously Paid</span>
                        <div class="text-base sm:text-lg font-black text-emerald-600 mt-0.5 truncate" x-text="summary ? summary.formatted_paid_total : 'Rs. 0.00'"></div>
                        <span class="text-[10px] text-gray-500">Paid against invoices</span>
                    </div>

                    <!-- Number of Pending Invoices -->
                    <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Pending Invoices</span>
                        <div class="text-base sm:text-lg font-black text-blue-600 mt-0.5" x-text="summary ? summary.pending_count : '0'"></div>
                        <span class="text-[10px] text-gray-500">Awaiting settlement</span>
                    </div>

                    <!-- Oldest Pending Invoice Date -->
                    <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-sm">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Oldest Due Date</span>
                        <div class="text-xs sm:text-sm font-black text-gray-700 mt-1 truncate" x-text="summary ? summary.oldest_invoice_date : 'None'"></div>
                        <span class="text-[10px] text-gray-500">Earliest unpaid invoice</span>
                    </div>
                </div>

                <!-- Informative Notice if Account Balance and Invoices Due Differ -->
                <div x-show="summary && summary.has_diff" class="px-4 py-2.5 bg-amber-50 border-b border-amber-200 text-xs text-amber-800 flex items-center justify-between gap-2 flex-wrap">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-info-circle text-amber-600 text-sm"></i>
                        <span>
                            <strong>Accounting Note:</strong> Customer Ledger Account Balance (<span class="font-bold" x-text="summary.formatted_account_balance"></span>) differs from Pending Invoices Total (<span class="font-bold" x-text="summary.formatted_invoice_outstanding"></span>) by <strong x-text="summary.formatted_balance_diff"></strong>.
                        </span>
                    </div>
                    <span class="text-[11px] text-amber-700 italic">Differences may represent manual adjustments, opening balances, or non-invoiced debit/credit entries.</span>
                </div>

                <!-- Live Receipt Impact Preview Banner -->
                <div x-show="totalSettlement > 0" class="p-3 sm:p-4 bg-blue-50/90 border-b border-blue-200 text-blue-900 flex flex-wrap justify-between items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">
                            <i class="fas fa-bolt"></i>
                        </span>
                        <span class="text-xs font-bold uppercase tracking-wider">Live Payment Impact Preview:</span>
                    </div>
                    <div class="flex items-center gap-4 sm:gap-6 flex-wrap text-xs sm:text-sm">
                        <div>
                            <span class="text-gray-500 block text-[10px] uppercase font-bold">Received:</span>
                            <span class="font-bold text-blue-700" x-text="'Rs. ' + parseFloat(amount || 0).toFixed(2)"></span>
                        </div>
                        <div x-show="parseFloat(discount || 0) > 0">
                            <span class="text-gray-500 block text-[10px] uppercase font-bold">+ Discount:</span>
                            <span class="font-bold text-purple-700" x-text="'Rs. ' + parseFloat(discount || 0).toFixed(2)"></span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-[10px] uppercase font-bold">= Total Settlement:</span>
                            <span class="font-extrabold text-blue-800" x-text="formattedTotalSettlement"></span>
                        </div>
                        <div class="h-6 w-px bg-blue-200 hidden sm:block"></div>
                        <div>
                            <span class="text-gray-500 block text-[10px] uppercase font-bold">Customer Balance After Receipt:</span>
                            <span class="font-extrabold text-emerald-700 text-base" x-text="formattedRemainingCustomerBalance"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Invoices Breakdown Table -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden text-gray-800 mb-6">
                <div class="p-4 bg-gray-50 border-b flex flex-wrap justify-between items-center gap-2">
                    <h3 class="font-bold text-gray-900 text-sm sm:text-base flex items-center">
                        <i class="fas fa-list-alt mr-2 text-gray-400"></i>Pending Invoices (Ledger Breakdown)
                    </h3>
                    <div class="flex items-center gap-3">
                        <span class="text-xs bg-blue-100 text-blue-700 px-2.5 py-1 rounded font-bold" x-show="invoices.length > 0">
                            <span x-text="invoices.length"></span> Pending
                        </span>
                        <span class="text-xs bg-red-100 text-red-600 px-2.5 py-1 rounded font-bold" x-show="invoices.length > 0">
                            Total Due: <span x-text="totalDue"></span>
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-72 overflow-y-auto">
                    <!-- Unselected State -->
                    <div x-show="!selectedCustomer" class="p-8 text-center text-gray-400">
                        <i class="fas fa-search text-4xl mb-3 block text-gray-300"></i>
                        <p class="text-sm">Select a customer above to view pending invoices and complete financial breakdown.</p>
                    </div>

                    <!-- Loading State -->
                    <div x-show="isLoadingCustomer" class="p-8 text-center text-blue-600">
                        <i class="fas fa-circle-notch fa-spin text-3xl mb-2"></i>
                        <p class="text-sm font-semibold">Loading customer ledger & pending invoices...</p>
                    </div>

                    <!-- Error State -->
                    <div x-show="customerError && selectedCustomer && !isLoadingCustomer" class="p-6 text-center text-red-600 bg-red-50">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                        <p class="text-sm font-bold" x-text="customerError"></p>
                    </div>

                    <!-- Invoice Table -->
                    <table x-show="selectedCustomer && !isLoadingCustomer && invoices.length > 0" class="w-full min-w-[720px] text-left border-collapse">
                        <thead class="bg-gray-100 sticky top-0 z-10 shadow-sm">
                            <tr class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                                <th class="p-3">Date</th>
                                <th class="p-3">Voucher / Inv #</th>
                                <th class="p-3 text-right">Invoice Total</th>
                                <th class="p-3 text-right">Previously Paid</th>
                                <th class="p-3 text-right">Current Balance</th>
                                <th class="p-3 text-right bg-blue-50 text-blue-900 border-l border-blue-100">Payment Allocated</th>
                                <th class="p-3 text-right bg-blue-50 text-blue-900">Remaining After</th>
                                <th class="p-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="inv in allocatedInvoices" :key="inv.id">
                                <tr class="border-b transition text-sm"
                                    :class="inv.is_affected ? (inv.preview_status === 'paid' ? 'bg-emerald-50/70 hover:bg-emerald-50' : 'bg-blue-50/60 hover:bg-blue-50') : 'hover:bg-gray-50'">
                                    <td class="p-3 text-gray-600 whitespace-nowrap" x-text="inv.date"></td>
                                    <td class="p-3 font-bold text-blue-600 whitespace-nowrap">
                                        <span x-text="inv.no"></span>
                                        <span x-show="inv.is_affected" class="ml-1 text-[10px] text-emerald-600 font-extrabold"><i class="fas fa-check"></i></span>
                                    </td>
                                    <td class="p-3 text-right font-medium whitespace-nowrap" x-text="'Rs. ' + inv.total"></td>
                                    <td class="p-3 text-right text-gray-600 whitespace-nowrap" x-text="'Rs. ' + inv.paid"></td>
                                    <td class="p-3 text-right font-bold text-red-500 whitespace-nowrap" x-text="'Rs. ' + inv.balance"></td>
                                    
                                    <!-- Live Allocation Column -->
                                    <td class="p-3 text-right font-extrabold whitespace-nowrap border-l border-blue-100"
                                        :class="inv.allocated > 0 ? 'text-blue-700 bg-blue-50/80 font-black' : 'text-gray-400 bg-gray-50/50'"
                                        x-text="inv.formatted_allocated">
                                    </td>
                                    
                                    <!-- Live Remaining After Column -->
                                    <td class="p-3 text-right font-bold whitespace-nowrap"
                                        :class="inv.remaining_after === 0 ? 'text-emerald-600 font-black' : (inv.is_affected ? 'text-amber-700' : 'text-gray-700')"
                                        x-text="inv.formatted_remaining_after">
                                    </td>
                                    
                                    <!-- Status Column -->
                                    <td class="p-3 text-center whitespace-nowrap">
                                        <span class="text-[10px] px-2 py-0.5 rounded uppercase font-extrabold tracking-wider"
                                              :class="inv.preview_status === 'paid' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : (inv.preview_status === 'partial' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-red-100 text-red-700 border border-red-200')"
                                              x-text="inv.preview_status_label">
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <!-- Zero Debt / No Invoices Found State -->
                    <div x-show="selectedCustomer && !isLoadingCustomer && invoices.length === 0 && !customerError" class="p-8 text-center">
                        <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center text-xl mb-2">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h4 class="font-bold text-gray-800 text-base">No outstanding pending invoices for this customer.</h4>
                        <p class="text-xs text-gray-500 mt-1">All invoices for this customer have already been settled or no debit sales exist.</p>
                    </div>
                </div>
            </div>

            <!-- Fixed Bottom Action Bar -->
            <div class="fixed bottom-0 left-0 w-full bg-white border-t p-3 sm:p-4 shadow-[0_-5px_15px_rgba(0,0,0,0.1)] z-40">
                <div class="container mx-auto max-w-[1400px] flex flex-col md:flex-row justify-between items-stretch md:items-center gap-3 sm:gap-4">

                    <div class="w-full md:w-1/3">
                        <input type="text" name="memo" placeholder="Memo / Remarks (Optional)..." class="w-full border-b border-gray-300 focus:border-blue-500 outline-none text-sm py-1.5 sm:py-2">
                    </div>

                    <div class="flex items-center justify-between md:justify-end gap-3 sm:gap-6 flex-wrap">
                        <div class="text-left md:text-right">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Settlement</span>
                            <span class="block text-xl sm:text-2xl font-black text-blue-600" x-text="formattedTotalSettlement"></span>
                        </div>

                        <div class="hidden sm:block h-10 w-px bg-gray-300"></div>

                        <div class="flex items-center gap-2 sm:gap-3">
                            <a href="/admin" class="px-4 sm:px-6 py-2.5 sm:py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 text-xs sm:text-sm transition text-center">
                                Close
                            </a>
                            <button type="submit"
                                    :disabled="isSubmitting"
                                    :class="isSubmitting ? 'opacity-75 cursor-not-allowed' : 'hover:bg-blue-700 hover:-translate-y-0.5 active:translate-y-0'"
                                    class="px-5 sm:px-8 py-2.5 sm:py-3 bg-blue-600 text-white font-bold rounded-lg shadow-md transition transform text-xs sm:text-sm flex items-center justify-center min-w-[140px]">
                                <template x-if="!isSubmitting">
                                    <span><i class="fas fa-save mr-1.5 sm:mr-2"></i> Save Receipt</span>
                                </template>
                                <template x-if="isSubmitting">
                                    <span><i class="fas fa-spinner fa-spin mr-1.5 sm:mr-2"></i> Processing...</span>
                                </template>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <script>
        function receiptForm() {
            return {
                selectedCustomer: '',
                amount: '',
                discount: '',
                paymentMode: 'Cash',
                customerInfo: null,
                summary: null,
                invoices: [],
                isLoadingCustomer: false,
                customerError: null,
                isSubmitting: false,

                fetchPendingInvoices() {
                    this.customerError = null;
                    if (!this.selectedCustomer) {
                        this.invoices = [];
                        this.customerInfo = null;
                        this.summary = null;
                        return;
                    }

                    this.isLoadingCustomer = true;
                    fetch(`/receipts/pending-invoices/${this.selectedCustomer}`)
                        .then(res => {
                            if (!res.ok) throw new Error('Could not load customer financial data');
                            return res.json();
                        })
                        .then(data => {
                            this.isLoadingCustomer = false;
                            if (data && data.summary) {
                                this.customerInfo = data.customer;
                                this.summary = data.summary;
                                this.invoices = data.invoices || [];
                            } else if (Array.isArray(data)) {
                                this.invoices = data;
                                this.summary = {
                                    account_balance: this.rawTotalDue,
                                    formatted_account_balance: 'Rs. ' + this.rawTotalDue.toFixed(2),
                                    invoice_outstanding: this.rawTotalDue,
                                    formatted_invoice_outstanding: 'Rs. ' + this.rawTotalDue.toFixed(2),
                                    total_invoices_amount: this.rawTotalDue,
                                    formatted_invoices_total: 'Rs. ' + this.rawTotalDue.toFixed(2),
                                    total_paid_amount: 0,
                                    formatted_paid_total: 'Rs. 0.00',
                                    pending_count: data.length,
                                    oldest_invoice_date: data.length > 0 ? (data[0].date_human || data[0].date) : 'None',
                                    has_diff: false,
                                };
                            } else {
                                this.invoices = [];
                                this.summary = null;
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            this.isLoadingCustomer = false;
                            this.customerError = 'Failed to load customer invoices and financial summary. Please try again.';
                            this.invoices = [];
                            this.summary = null;
                        });
                },

                get totalSettlement() {
                    let amt = parseFloat(this.amount || 0);
                    let disc = parseFloat(this.discount || 0);
                    return Math.max(0, (isNaN(amt) ? 0 : amt) + (isNaN(disc) ? 0 : disc));
                },

                get formattedTotalSettlement() {
                    return 'Rs. ' + this.totalSettlement.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                get remainingCustomerBalance() {
                    let currentDebt = this.summary ? parseFloat(this.summary.account_balance || 0) : this.rawTotalDue;
                    return Math.max(0, currentDebt - this.totalSettlement);
                },

                get formattedRemainingCustomerBalance() {
                    return 'Rs. ' + this.remainingCustomerBalance.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                get rawTotalDue() {
                    return this.invoices.reduce((acc, inv) => {
                        let bal = typeof inv.raw_balance !== 'undefined' ? parseFloat(inv.raw_balance) : parseFloat((inv.balance || '0').toString().replace(/,/g, ''));
                        return acc + (isNaN(bal) ? 0 : bal);
                    }, 0);
                },

                get totalDue() {
                    return 'Rs. ' + this.rawTotalDue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                get allocatedInvoices() {
                    let remainingToAllocate = this.totalSettlement;
                    return this.invoices.map(inv => {
                        let bal = typeof inv.raw_balance !== 'undefined' ? parseFloat(inv.raw_balance) : parseFloat(inv.balance.toString().replace(/,/g, ''));
                        let total = typeof inv.raw_total !== 'undefined' ? parseFloat(inv.raw_total) : parseFloat(inv.total.toString().replace(/,/g, ''));
                        let previouslyPaid = typeof inv.raw_paid !== 'undefined' ? parseFloat(inv.raw_paid) : parseFloat(inv.paid.toString().replace(/,/g, ''));

                        let alloc = 0;
                        if (remainingToAllocate > 0 && bal > 0) {
                            alloc = Math.min(remainingToAllocate, bal);
                            remainingToAllocate = Math.max(0, remainingToAllocate - alloc);
                        }

                        let remainingAfter = Math.max(0, bal - alloc);
                        
                        let previewStatus = inv.status;
                        let previewStatusLabel = inv.status_label;
                        if (alloc > 0) {
                            if (remainingAfter <= 0.009) {
                                previewStatus = 'paid';
                                previewStatusLabel = 'Paid';
                            } else {
                                previewStatus = 'partial';
                                previewStatusLabel = 'Partially Paid';
                            }
                        }

                        return {
                            ...inv,
                            allocated: alloc,
                            formatted_allocated: 'Rs. ' + alloc.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                            remaining_after: remainingAfter,
                            formatted_remaining_after: 'Rs. ' + remainingAfter.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                            preview_status: previewStatus,
                            preview_status_label: previewStatusLabel,
                            is_affected: alloc > 0,
                        };
                    });
                }
            }
        }
    </script>
</body>

</html>