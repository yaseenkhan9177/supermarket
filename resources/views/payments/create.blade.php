<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Payment | OwnStore PRO</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-900 font-sans text-gray-200" x-data="paymentForm()">

    <nav class="bg-white border-b border-gray-200 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-orange-500 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-money-bill-wave text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-gray-900 leading-none tracking-tight">
                        OwnStore <span class="text-orange-500">PRO</span>
                    </h1>
                    <span class="text-xs text-gray-500 font-medium mt-0.5">Cash Expenses / Payments</span>
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

        {{-- Error/Success Alerts --}}
        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
            <p class="font-bold">Success</p>
            <p>{{ session('success') }}</p>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
            <p class="font-bold">Error</p>
            <p>{{ session('error') }}</p>
        </div>
        @endif

        @if ($errors->any())
        <div class="bg-orange-100 border-l-4 border-orange-500 text-orange-700 p-4 mb-4" role="alert">
            <p class="font-bold">Validation Error</p>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="/payments/store" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-lg text-gray-800 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-orange-500"></div>

                    <div class="flex items-center gap-2 mb-6 border-b pb-2">
                        <i class="fas fa-file-invoice-dollar text-orange-500"></i>
                        <h3 class="font-bold text-gray-900">Payment Details (Paid To)</h3>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Payment #</label>
                                <input type="text" name="payment_no" value="PAY-{{ date('Y') }}-{{ rand(100,999) }}" readonly class="w-full bg-gray-100 border border-gray-300 rounded p-2 text-sm font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Date</label>
                                <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="w-full bg-white border border-gray-300 rounded p-2 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Debit AC (Expense Head)</label>
                            <select name="paid_to_account" class="w-full bg-orange-50 border border-orange-200 rounded p-2 text-sm focus:ring-2 focus:ring-orange-500 outline-none">
                                <option value="">-- Select Expense --</option>
                                <optgroup label="Operating Expenses">
                                    <option>Electricity Expense</option>
                                    <option>Rent Expense</option>
                                    <option>Salary Expense</option>
                                    <option>Office Supplies</option>
                                </optgroup>
                                <optgroup label="Vendors / Suppliers">
                                    <option>Supplier A</option>
                                    <option>Supplier B</option>
                                </optgroup>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Amount Paid</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-400">PKR</span>
                                    <input type="number" name="amount_paid" step="0.01" x-model="amount" class="w-full pl-6 p-2 border border-gray-300 rounded text-right font-bold text-orange-600 focus:border-orange-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Discount Received</label>
                                <input type="number" step="0.01" name="discount_received" class="w-full p-2 border border-gray-300 rounded text-right">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Details / Description</label>
                            <input type="text" name="memo" placeholder="e.g. Electricity Bill for Dec 2025" class="w-full border border-gray-300 rounded p-2 text-sm">
                        </div>
                    </div>
                </div>

                <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg text-white relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-red-500"></div>

                    <div class="flex items-center gap-2 mb-6 border-b border-gray-600 pb-2">
                        <i class="fas fa-wallet text-red-400"></i>
                        <h3 class="font-bold">Source of Funds (Paid From)</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Credit AC (Source)</label>
                            <select name="paid_from_account" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm text-gray-300">
                                <option>010000: CASH ACCOUNT / DRAWER</option>
                                <option>010001: MAIN SAFE</option>
                                <option>020001: MEEZAN BANK</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Cheque No</label>
                                <input type="text" name="cheque_no" placeholder="Optional" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Cheque Date</label>
                                <input type="date" name="cheque_date" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm text-gray-400">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Authorized By (Salesman)</label>
                            <select name="user_id" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm text-gray-300">
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fixed bottom-0 left-0 w-full bg-white border-t p-4 shadow-[0_-5px_15px_rgba(0,0,0,0.1)] z-40">
                <div class="container mx-auto max-w-[1400px] flex justify-end items-center gap-6">

                    <div class="text-right">
                        <span class="block text-[10px] font-bold text-gray-400 uppercase">Total Paid</span>
                        <span class="block text-2xl font-bold text-orange-600" x-text="'PKR ' + (parseFloat(amount || 0)).toFixed(2)"></span>
                    </div>

                    <div class="h-10 w-px bg-gray-300"></div>

                    <button type="button" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-8 py-3 bg-orange-600 text-white font-bold rounded shadow hover:bg-orange-700 transition transform hover:-translate-y-1">
                        <i class="fas fa-check-circle mr-2"></i> Confirm Payment
                    </button>
                </div>
            </div>

        </form>
    </div>

    <script>
        function paymentForm() {
            return {
                amount: 0
            }
        }
    </script>
</body>

</html>