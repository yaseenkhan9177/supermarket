<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receive Payment - Web Edition</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 md:bg-gray-200 min-h-screen flex flex-col md:items-center md:justify-center font-sans">

    <!-- Main Container -->
    <!-- Mobile: Full Screen, Desktop: Floating Card with 90vh height -->
    <div class="w-full md:max-w-6xl bg-gray-100 md:border md:border-gray-400 shadow-none md:shadow-2xl md:rounded-lg flex flex-col h-screen md:h-[90vh] overflow-hidden">

        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-400 text-white px-4 py-2 flex justify-between items-center shadow-md shrink-0">
            <span class="font-bold text-sm tracking-wide flex items-center gap-2">
                <i class="fas fa-coins"></i> Receive Payment
            </span>
            <a href="{{ route('dashboard') }}" class="hover:bg-red-500 p-1 rounded transition text-white no-underline text-sm">
                <i class="fas fa-times"></i>
            </a>
        </div>

        <form action="{{ route('receipts.store') }}" method="POST" id="receiptForm" class="p-2 md:p-4 flex-grow flex flex-col space-y-4 overflow-hidden">
            @csrf

            <!-- Top Section: Stack on mobile, Side-by-Side on Desktop -->
            <!-- Allow height to adapt on mobile (auto), fixed on desktop to preserve 'app' feel if content allows, otherwise auto is safer -->
            <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4 h-auto md:h-48 shrink-0">

                <!-- Left: Receipt Info -->
                <div class="w-full md:w-1/2 bg-white border border-gray-300 rounded p-3 shadow-sm relative pt-4">
                    <span class="absolute -top-2.5 left-2 bg-white px-1 text-xs font-bold text-blue-600 border border-blue-100 rounded">Receipt Information</span>

                    <div class="grid grid-cols-12 gap-y-2 gap-x-2 mt-1 items-center">
                        <label class="col-span-3 text-[10px] md:text-xs font-bold text-gray-700">Receipt#</label>
                        <div class="col-span-4"><input type="text" name="receipt_number" class="w-full border p-1 text-xs rounded bg-gray-50" value="{{ $receiptNo }}" readonly></div>
                        <label class="col-span-1 text-[10px] md:text-xs font-bold text-gray-700 text-right">Date</label>
                        <div class="col-span-4"><input type="date" name="receipt_date" class="w-full border p-1 text-xs rounded" value="{{ date('Y-m-d') }}"></div>

                        <label class="col-span-3 text-[10px] md:text-xs font-bold text-gray-700">Credit AC</label>
                        <div class="col-span-9">
                            <select name="customer_id" id="customer_id" class="w-full border p-1 text-xs rounded bg-yellow-50 focus:ring-1 focus:ring-blue-500" onchange="loadPendingInvoices(this.value)">
                                <option value="">Select Customer...</option>
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <label class="col-span-3 text-[10px] md:text-xs font-bold text-gray-700">Amount</label>
                        <div class="col-span-4"><input type="number" name="amount" id="main_amount" step="0.01" class="w-full border p-1 text-xs rounded font-bold text-green-700" placeholder="0.00" oninput="autoAllocate()"></div>
                        <div class="col-span-5"></div>

                        <label class="col-span-3 text-[10px] md:text-xs font-bold text-gray-700">Discount</label>
                        <div class="col-span-4"><input type="number" name="discount" step="0.01" class="w-full border p-1 text-xs rounded" placeholder="0.00"></div>
                        <div class="col-span-5"></div>

                        <label class="col-span-3 text-[10px] md:text-xs font-bold text-gray-700">Party/Name</label>
                        <div class="col-span-9"><input type="text" name="party_name" class="w-full border p-1 text-xs rounded"></div>
                    </div>
                </div>

                <!-- Right: Deposit Info -->
                <div class="w-full md:w-1/2 bg-white border border-gray-300 rounded p-3 shadow-sm relative pt-4">
                    <span class="absolute -top-2.5 left-2 bg-white px-1 text-xs font-bold text-blue-600 border border-blue-100 rounded">Deposit Information</span>

                    <div class="grid grid-cols-12 gap-y-2 gap-x-2 mt-1 items-center">
                        <label class="col-span-3 text-[10px] md:text-xs font-bold text-gray-700">Debit AC</label>
                        <div class="col-span-9">
                            <select name="deposit_account_id" class="w-full border p-1 text-xs rounded">
                                @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ $account->name == 'Cash Account' ? 'selected' : '' }}>{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <label class="col-span-3 text-[10px] md:text-xs font-bold text-gray-700">Cheque#</label>
                        <div class="col-span-9 grid grid-cols-2 gap-2">
                            <input type="text" name="cheque_number" class="w-full border p-1 text-xs rounded" placeholder="Number">
                            <div class="flex items-center space-x-1">
                                <span class="text-[10px] font-bold">ChkDate</span>
                                <input type="date" name="cheque_date" class="w-full border p-1 text-xs rounded">
                            </div>
                        </div>

                        <label class="col-span-3 text-[10px] md:text-xs font-bold text-gray-700">Salesman</label>
                        <div class="col-span-9">
                            <select name="salesman_id" class="w-full border p-1 text-xs rounded">
                                <option value="">Select Salesman...</option>
                                @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-span-12 h-1 md:h-10"></div>
                    </div>
                </div>
            </div>

            <!-- Allocation Grid (Scrollable) -->
            <div class="flex-grow bg-white border border-gray-300 shadow-inner overflow-auto relative rounded">
                <table class="w-full text-left border-collapse min-w-[600px]">
                    <thead class="bg-gray-500 text-white sticky top-0 z-10">
                        <tr>
                            <th class="p-2 text-xs font-normal border-r border-gray-400 w-24">Date</th>
                            <th class="p-2 text-xs font-normal border-r border-gray-400 w-20">Voucher</th>
                            <th class="p-2 text-xs font-normal border-r border-gray-400 w-24">Number</th>
                            <th class="p-2 text-xs font-normal border-r border-gray-400 text-right w-24">Total</th>
                            <th class="p-2 text-xs font-normal border-r border-gray-400 text-right w-24">Paid</th>
                            <th class="p-2 text-xs font-normal border-r border-gray-400 text-right w-24">Balance</th>
                            <th class="p-2 text-xs font-normal border-r border-gray-400 text-right w-24 bg-blue-600">Amount</th>
                            <th class="p-2 text-xs font-normal">DETAILS</th>
                        </tr>
                    </thead>
                    <tbody id="allocationBody" class="text-xs bg-gray-500">
                        <!-- Rows loaded via JS -->
                    </tbody>
                </table>
                <div id="gridPlaceholder" class="h-full w-full bg-gray-200 flex flex-col items-center justify-center text-gray-500 p-4 text-center">
                    <i class="fas fa-search text-3xl mb-2 text-gray-400"></i>
                    Select a customer to view pending invoices.
                </div>
            </div>

            <!-- Footer: Stack buttons on very small screens -->
            <div class="bg-gray-100 border border-gray-300 rounded p-2 flex flex-col md:flex-row md:items-end space-y-2 md:space-y-0 md:space-x-4 relative bg-gray-50">

                <div class="flex-grow w-full md:w-auto">
                    <label class="text-xs font-bold text-gray-700 block mb-1">Memo</label>
                    <input type="text" name="memo" class="w-full border border-gray-300 rounded p-1 text-sm shadow-inner">
                </div>

                <div class="flex justify-between items-center w-full md:w-auto">
                    <!-- Buttons Group -->
                    <div class="flex space-x-1">
                        <button type="button" class="flex flex-col items-center justify-center w-12 h-10 bg-white border border-gray-300 rounded hover:bg-blue-50 shadow-sm cursor-not-allowed opacity-50">
                            <span class="text-green-600 text-lg leading-none font-bold">+</span>
                            <span class="text-[9px] font-bold text-gray-600">Add</span>
                        </button>
                        <button type="submit" class="flex flex-col items-center justify-center w-12 h-10 bg-white border border-gray-300 rounded hover:bg-blue-50 shadow-sm active:scale-95 transition">
                            <span class="text-blue-600 text-lg leading-none">💾</span>
                            <span class="text-[9px] font-bold text-gray-600">Save</span>
                        </button>
                        <button type="button" class="flex flex-col items-center justify-center w-12 h-10 bg-white border border-gray-300 rounded hover:bg-blue-50 shadow-sm active:scale-95 transition">
                            <span class="text-gray-600 text-lg leading-none">🖨</span>
                            <span class="text-[9px] font-bold text-gray-600">Print</span>
                        </button>
                        <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center w-12 h-10 bg-white border border-gray-300 rounded hover:bg-red-50 shadow-sm no-underline active:scale-95 transition">
                            <span class="text-red-500 text-lg leading-none font-bold">✖</span>
                            <span class="text-[9px] font-bold text-gray-600">Close</span>
                        </a>
                    </div>

                    <!-- Totals Group (Right Aligned on Mobile too) -->
                    <div class="flex flex-col items-end space-y-1 ml-auto md:ml-4">
                        <div class="flex items-center space-x-2">
                            <div class="flex flex-col items-end">
                                <label class="text-[9px] font-bold text-gray-600">Adjusted</label>
                                <input type="text" name="adjusted_amount" id="adjusted_amount" class="bg-white border text-right border-gray-300 w-20 md:w-24 h-6 px-1 text-xs font-bold shadow-inner" readonly value="0.00">
                            </div>
                            <div class="flex flex-col items-end">
                                <label class="text-[9px] font-bold text-gray-600">Unadjusted</label>
                                <input type="text" name="unadjusted_amount" id="unadjusted_amount" class="bg-white border text-right border-gray-300 w-20 md:w-24 h-6 px-1 text-xs font-bold shadow-inner text-red-600" readonly value="0.00">
                            </div>
                        </div>

                        <div class="flex items-center space-x-3 mt-1">
                            <label class="flex items-center space-x-1 text-xs font-bold text-gray-600 cursor-pointer select-none">
                                <input type="checkbox" name="is_cleared" class="form-checkbox h-4 w-4 text-blue-600 rounded">
                                <span>Cleared</span>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

        </form>
    </div>

    <!-- JavaScript remains same, just ensuring it's included -->
    <script>
        function loadPendingInvoices(customerId) {
            const tbody = document.getElementById('allocationBody');
            const placeholder = document.getElementById('gridPlaceholder');

            if (!customerId) {
                tbody.innerHTML = '';
                placeholder.style.display = 'flex';
                return;
            }

            placeholder.style.display = 'none';
            tbody.innerHTML = '<tr><td colspan="8" class="p-2 text-center text-white">Loading...</td></tr>';

            fetch(`/receipts/pending-invoices/${customerId}`)
                .then(response => response.json())
                .then(data => {
                    tbody.innerHTML = '';
                    if (data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="8" class="p-2 text-center text-white">No pending invoices found.</td></tr>';
                        return;
                    }

                    data.forEach((inv, index) => {
                        const tr = document.createElement('tr');
                        tr.className = 'bg-white text-gray-800 border-b border-gray-200 hover:bg-yellow-50';
                        tr.innerHTML = `
                            <td class="p-1 border-r">${inv.invoice_date}</td>
                            <td class="p-1 border-r">Sale</td>
                            <td class="p-1 border-r">${inv.invoice_no}</td>
                            <td class="p-1 border-r text-right">${parseFloat(inv.net_total).toFixed(2)}</td>
                            <td class="p-1 border-r text-right text-green-600">${parseFloat(inv.paid_amount).toFixed(2)}</td>
                            <td class="p-1 border-r text-right text-red-600 font-bold">${parseFloat(inv.balance_due).toFixed(2)}</td>
                            <td class="p-1 border-r text-right">
                                <input type="number" step="0.01" name="allocations[${index}][amount]" class="alloc-amount w-full text-right outline-none bg-blue-50 focus:bg-white border border-transparent focus:border-blue-400 rounded px-1" value="0.00" max="${inv.balance_due}" oninput="recalcTotals()">
                                <input type="hidden" name="allocations[${index}][id]" value="${inv.id}">
                                <input type="hidden" name="allocations[${index}][date]" value="${inv.invoice_date}">
                                <input type="hidden" name="allocations[${index}][no]" value="${inv.invoice_no}">
                                <input type="hidden" name="allocations[${index}][total]" value="${inv.net_total}">
                                <input type="hidden" name="allocations[${index}][paid]" value="${inv.paid_amount}">
                                <input type="hidden" name="allocations[${index}][balance]" value="${inv.balance_due}">
                            </td>
                            <td class="p-1"><input type="text" name="allocations[${index}][details]" class="w-full outline-none bg-transparent"></td>
                        `;
                        tbody.appendChild(tr);
                    });

                    // Trigger auto-alloc if amount exists
                    autoAllocate();
                });
        }

        function recalcTotals() {
            let totalAllocated = 0;
            const amounts = document.querySelectorAll('.alloc-amount');
            amounts.forEach(input => {
                totalAllocated += parseFloat(input.value) || 0;
            });

            const mainAmount = parseFloat(document.getElementById('main_amount').value) || 0;

            document.getElementById('adjusted_amount').value = totalAllocated.toFixed(2);
            document.getElementById('unadjusted_amount').value = (mainAmount - totalAllocated).toFixed(2);
        }

        function autoAllocate() {
            let remaining = parseFloat(document.getElementById('main_amount').value) || 0;
            const amounts = document.querySelectorAll('.alloc-amount');

            amounts.forEach(input => {
                const max = parseFloat(input.getAttribute('max'));
                if (remaining > 0) {
                    if (remaining >= max) {
                        input.value = max.toFixed(2);
                        remaining -= max;
                    } else {
                        input.value = remaining.toFixed(2);
                        remaining = 0;
                    }
                } else {
                    input.value = '0.00';
                }
            });
            recalcTotals();
        }
    </script>
</body>

</html>