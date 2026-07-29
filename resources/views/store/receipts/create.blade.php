@extends('layouts.admin')

@section('navbar_subtitle', 'Receive Payment')

@section('content')
<div x-data @customer-selected.window="loadPendingInvoices($event.detail.id)">
    <form action="{{ route('receipts.store') }}" method="POST" id="receiptForm" class="p-2 md:p-4 flex-grow flex flex-col space-y-4">
        @csrf

        <!-- Top Section -->
        <div class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4 h-auto shrink-0">

            <!-- Left: Receipt Info -->
            <div class="w-full md:w-1/2 bg-white border border-gray-300 rounded p-3 shadow-sm relative pt-4 text-gray-800">
                <span class="absolute -top-2.5 left-2 bg-white px-1 text-xs font-bold text-blue-600 border border-blue-100 rounded">Receipt Information</span>

                <div class="grid grid-cols-12 gap-y-2 gap-x-2 mt-1 items-center">
                    <label class="col-span-3 text-[10px] md:text-xs font-bold text-gray-700">Receipt#</label>
                    <div class="col-span-4"><input type="text" name="receipt_number" class="w-full border p-1 text-xs rounded bg-gray-50 font-mono" value="{{ $receiptNo }}" readonly></div>
                    <label class="col-span-1 text-[10px] md:text-xs font-bold text-gray-700 text-right">Date</label>
                    <div class="col-span-4"><input type="date" name="receipt_date" class="w-full border p-1 text-xs rounded" value="{{ date('Y-m-d') }}"></div>

                    <label class="col-span-3 text-[10px] md:text-xs font-bold text-gray-700">Credit AC *</label>
                    <div class="col-span-9">
                        <x-customer-search id="customer_id" name="customer_id" :required="true" />
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
            <div class="w-full md:w-1/2 bg-white border border-gray-300 rounded p-3 shadow-sm relative pt-4 text-gray-800">
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

        <!-- Allocation Grid -->
        <div class="flex-grow min-h-[250px] bg-white border border-gray-300 shadow-inner overflow-auto relative rounded text-gray-800">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead class="bg-gray-700 text-white sticky top-0 z-10">
                    <tr>
                        <th class="p-2 text-xs font-normal border-r border-gray-600 w-24">Date</th>
                        <th class="p-2 text-xs font-normal border-r border-gray-600 w-20">Voucher</th>
                        <th class="p-2 text-xs font-normal border-r border-gray-600 w-24">Number</th>
                        <th class="p-2 text-xs font-normal border-r border-gray-600 text-right w-24">Total</th>
                        <th class="p-2 text-xs font-normal border-r border-gray-600 text-right w-24">Paid</th>
                        <th class="p-2 text-xs font-normal border-r border-gray-600 text-right w-24">Balance</th>
                        <th class="p-2 text-xs font-normal border-r border-gray-600 text-right w-24 bg-blue-600">Amount</th>
                        <th class="p-2 text-xs font-normal">DETAILS</th>
                    </tr>
                </thead>
                <tbody id="allocationBody" class="text-xs">
                    <!-- Rows loaded via JS -->
                </tbody>
            </table>
            <div id="gridPlaceholder" class="h-48 w-full bg-gray-50 flex flex-col items-center justify-center text-gray-500 p-4 text-center">
                <i class="fas fa-search text-3xl mb-2 text-gray-400"></i>
                Select a customer to view pending invoices.
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-100 border border-gray-300 rounded p-2 flex flex-col md:flex-row md:items-end space-y-2 md:space-y-0 md:space-x-4 relative">
            <div class="flex-grow w-full md:w-auto">
                <label class="text-xs font-bold text-gray-700 block mb-1">Memo</label>
                <input type="text" name="memo" class="w-full border border-gray-300 rounded p-1 text-sm shadow-inner text-gray-800">
            </div>

            <div class="flex justify-between items-center w-full md:w-auto">
                <!-- Buttons Group -->
                <div class="flex space-x-1">
                    <button type="submit" class="flex flex-col items-center justify-center px-4 h-10 bg-indigo-600 text-white rounded hover:bg-indigo-700 shadow-sm active:scale-95 transition font-bold text-xs">
                        <i class="fas fa-save mb-0.5"></i>
                        <span>Save</span>
                    </button>
                    <a href="{{ route('receipts.index') }}" class="flex flex-col items-center justify-center px-4 h-10 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 shadow-sm no-underline active:scale-95 transition font-bold text-xs">
                        <i class="fas fa-arrow-left mb-0.5"></i>
                        <span>Cancel</span>
                    </a>
                </div>

                <!-- Totals Group -->
                <div class="flex flex-col items-end space-y-1 ml-auto md:ml-4">
                    <div class="flex items-center space-x-2">
                        <div class="flex flex-col items-end">
                            <label class="text-[9px] font-bold text-gray-600">Adjusted</label>
                            <input type="text" name="adjusted_amount" id="adjusted_amount" class="bg-white border text-right border-gray-300 w-20 md:w-24 h-6 px-1 text-xs font-bold shadow-inner text-gray-800" readonly value="0.00">
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

<script>
    function loadPendingInvoices(customerId) {
        const tbody = document.getElementById('allocationBody');
        const placeholder = document.getElementById('gridPlaceholder');

        if (!customerId) {
            tbody.innerHTML = '';
            if (placeholder) placeholder.style.display = 'flex';
            return;
        }

        if (placeholder) placeholder.style.display = 'none';
        tbody.innerHTML = '<tr><td colspan="8" class="p-4 text-center text-gray-500 font-medium">Loading pending invoices...</td></tr>';

        fetch(`/receipts/pending-invoices/${customerId}`)
            .then(response => response.json())
            .then(data => {
                tbody.innerHTML = '';
                if (!data || data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="p-4 text-center text-gray-500 font-medium">No pending invoices found for this customer.</td></tr>';
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

                autoAllocate();
            })
            .catch(err => {
                console.error('Error loading pending invoices:', err);
                tbody.innerHTML = '<tr><td colspan="8" class="p-4 text-center text-red-500 font-medium">Failed to load invoices.</td></tr>';
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
@endsection