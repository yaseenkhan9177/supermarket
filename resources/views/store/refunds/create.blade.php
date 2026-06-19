<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Refund - Web Edition</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 md:bg-gray-200 min-h-screen flex flex-col md:items-center md:justify-center font-sans">

    <!-- Main Container -->
    <div class="w-full md:max-w-6xl bg-gray-100 md:border md:border-gray-400 shadow-none md:shadow-2xl md:rounded-lg flex flex-col h-screen md:h-[90vh] overflow-hidden">

        <!-- Header Bar -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-400 text-white px-4 py-2 flex justify-between items-center shadow-md shrink-0">
            <span class="font-bold text-sm tracking-wide flex items-center gap-2">
                <i class="fas fa-undo"></i> Cash Refund
            </span>
            <a href="{{ route('dashboard') }}" class="hover:bg-red-500 p-1 rounded transition text-white no-underline text-sm">
                <i class="fas fa-times"></i>
            </a>
        </div>

        <form action="{{ route('refunds.store') }}" method="POST" id="refundForm" class="flex-grow flex flex-col overflow-hidden">
            @csrf

            <div class="p-2 md:p-4 flex-grow flex flex-col overflow-hidden space-y-4">

                <!-- Top Form Area -->
                <div class="bg-white border border-gray-300 p-3 rounded shadow-sm shrink-0">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                        <!-- Left Column -->
                        <div class="col-span-1 md:col-span-6 grid grid-cols-12 gap-2 items-center">
                            <label class="col-span-3 text-xs font-bold text-gray-700 text-right">Customer:</label>
                            <div class="col-span-9">
                                <select name="customer_id" id="customer_id" class="w-full border border-gray-300 bg-blue-50 text-sm p-1 rounded focus:ring-2 focus:ring-blue-500 outline-none" required onchange="loadCustomerInfo(this)">
                                    <option value="">Select Customer...</option>
                                    @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" data-address="{{ $customer->address }}" data-phone="{{ $customer->phone }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <label class="col-span-3 text-xs font-bold text-gray-700 text-right self-start mt-1">Address:</label>
                            <div class="col-span-9">
                                <textarea id="customer_address" rows="2" class="w-full border border-gray-300 text-sm p-1 rounded bg-gray-50" readonly></textarea>
                            </div>

                            <label class="col-span-3 text-xs font-bold text-gray-700 text-right">Phone/Fax:</label>
                            <div class="col-span-9 flex gap-2">
                                <input type="text" id="customer_phone" class="w-full border border-gray-300 text-sm p-1 rounded" readonly>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-span-1 md:col-span-6 grid grid-cols-12 gap-2 items-center md:border-l border-gray-200 md:pl-4 pt-4 md:pt-0 border-t md:border-t-0">

                            <label class="col-span-3 text-xs font-bold text-gray-700 text-right">Credit No:</label>
                            <div class="col-span-4">
                                <input type="text" name="credit_no" value="{{ $creditNo }}" class="w-full border border-gray-300 text-sm p-1 rounded bg-gray-50" readonly>
                            </div>

                            <label class="col-span-1 text-xs font-bold text-gray-700 text-right">Date:</label>
                            <div class="col-span-4">
                                <input type="date" name="refund_date" value="{{ date('Y-m-d') }}" class="w-full border border-gray-300 text-sm p-1 rounded">
                            </div>

                            <label class="col-span-3 text-xs font-bold text-gray-700 text-right">Paid From:</label>
                            <div class="col-span-9">
                                <select name="paid_from_account_id" class="w-full border border-gray-300 text-sm p-1 rounded">
                                    @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <label class="col-span-3 text-xs font-bold text-gray-700 text-right">Sale/Rep:</label>
                            <div class="col-span-9">
                                <select name="sales_rep_id" class="w-full border border-gray-300 text-sm p-1 rounded">
                                    @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <label class="col-span-3 text-xs font-bold text-gray-700 text-right">Memo:</label>
                            <div class="col-span-9">
                                <input type="text" name="memo" class="w-full border border-gray-300 text-sm p-1 rounded">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid/Table Area (Scrollable) -->
                <div class="flex-grow bg-white border border-gray-300 shadow-inner overflow-auto relative rounded">
                    <table class="w-full text-left border-collapse min-w-[700px]">
                        <thead class="bg-gray-100 sticky top-0 border-b border-gray-300 z-10">
                            <tr>
                                <th class="p-2 text-xs font-bold text-gray-600 border-r w-10 text-center">#</th>
                                <th class="p-2 text-xs font-bold text-gray-600 border-r w-20">Item ID</th>
                                <th class="p-2 text-xs font-bold text-gray-600 border-r">Description</th>
                                <th class="p-2 text-xs font-bold text-gray-600 border-r w-20">Depart</th>
                                <th class="p-2 text-xs font-bold text-gray-600 border-r w-16 text-center">Qty</th>
                                <th class="p-2 text-xs font-bold text-gray-600 border-r w-20 text-right">Rate</th>
                                <th class="p-2 text-xs font-bold text-gray-600 border-r w-24 text-right">Total</th>
                                <th class="p-2 text-xs font-bold text-gray-600 border-r w-20 text-right">Disc</th>
                                <th class="p-2 text-xs font-bold text-gray-600 w-24 text-right bg-blue-50">Net Amt</th>
                                <th class="p-2 w-8"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody" class="text-sm">
                            <!-- Rows generated by JS -->
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- Footer Controls -->
            <div class="bg-gray-200 border-t border-gray-300 p-2 shrink-0">
                <div class="flex flex-col md:flex-row justify-between items-center gap-2">

                    <div class="flex space-x-1 w-full md:w-auto justify-center md:justify-start">
                        <!-- Navigation (Visible on md+) -->
                        <div class="hidden md:flex bg-white rounded border border-gray-300 mr-4">
                            <button type="button" class="px-3 py-1 hover:bg-gray-100 text-gray-600 text-xs font-bold border-r">|&lt;</button>
                            <button type="button" class="px-3 py-1 hover:bg-gray-100 text-gray-600 text-xs font-bold border-r">&lt;</button>
                            <button type="button" class="px-3 py-1 hover:bg-gray-100 text-gray-600 text-xs font-bold border-r">&gt;</button>
                            <button type="button" class="px-3 py-1 hover:bg-gray-100 text-gray-600 text-xs font-bold">&gt;|</button>
                        </div>

                        <button type="button" onclick="addNewRow()" class="flex flex-col items-center px-4 py-1 bg-white border border-gray-300 rounded hover:bg-blue-50 shadow-sm transition active:scale-95">
                            <span class="text-green-600 text-lg leading-none">+</span>
                            <span class="text-[10px] font-bold text-gray-600">Add</span>
                        </button>
                        <button type="submit" class="flex flex-col items-center px-4 py-1 bg-white border border-gray-300 rounded hover:bg-blue-50 shadow-sm transition active:scale-95">
                            <span class="text-blue-600 text-lg leading-none">💾</span>
                            <span class="text-[10px] font-bold text-gray-600">Save</span>
                        </button>
                        <button type="button" class="flex flex-col items-center px-4 py-1 bg-white border border-gray-300 rounded hover:bg-blue-50 shadow-sm transition active:scale-95">
                            <span class="text-gray-600 text-lg leading-none">🖨</span>
                            <span class="text-[10px] font-bold text-gray-600">Print</span>
                        </button>
                        <a href="{{ route('dashboard') }}" class="flex flex-col items-center px-4 py-1 bg-white border border-gray-300 rounded hover:bg-red-50 shadow-sm transition no-underline active:scale-95">
                            <span class="text-red-500 text-lg leading-none">✖</span>
                            <span class="text-[10px] font-bold text-gray-600">Close</span>
                        </a>
                    </div>

                    <div class="flex items-center justify-between w-full md:w-auto space-x-4 bg-white px-4 py-2 rounded border border-gray-300 shadow-sm">
                        <div class="flex flex-col items-end">
                            <span class="text-[10px] text-gray-500 uppercase font-bold">Total</span>
                            <span class="text-sm font-semibold" id="display_total">0.00</span>
                            <input type="hidden" name="total_amount" id="input_total">
                        </div>
                        <div class="h-8 w-px bg-gray-300"></div>
                        <div class="flex flex-col items-end">
                            <span class="text-[10px] text-gray-500 uppercase font-bold">Discount</span>
                            <span class="text-sm text-red-500" id="display_discount">0.00</span>
                            <input type="hidden" name="discount_total" id="input_discount">
                        </div>
                        <div class="h-8 w-px bg-gray-300"></div>
                        <div class="flex flex-col items-end">
                            <span class="text-[10px] text-blue-600 uppercase font-bold">Net Amt</span>
                            <span class="text-xl font-bold text-blue-700 leading-none" id="display_net">0.00</span>
                            <input type="hidden" name="net_amount" id="input_net">
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <!-- JavaScript remains same -->
    <script>
        let rowCount = 0;

        function addNewRow() {
            rowCount++;
            const timestamp = Date.now();
            const tbody = document.getElementById('itemsBody');
            const tr = document.createElement('tr');
            tr.className = 'border-b border-gray-100 hover:bg-blue-50 transition';
            tr.id = `row_${timestamp}`;

            tr.innerHTML = `
                <td class="p-1 border-r text-center text-gray-400">${rowCount}</td>
                <td class="p-1 border-r"><input type="text" class="w-full outline-none bg-transparent text-center" placeholder="ID"></td>
                <td class="p-1 border-r"><input type="text" name="items[${timestamp}][item_name]" class="w-full outline-none bg-transparent" placeholder="Item Name" required></td>
                <td class="p-1 border-r"><input type="text" class="w-full outline-none bg-transparent text-center" placeholder="-"></td>
                <td class="p-1 border-r"><input type="number" name="items[${timestamp}][quantity]" id="qty_${timestamp}" value="1" class="w-full text-center outline-none bg-transparent border border-blue-100 rounded px-1" oninput="calculateRow(${timestamp})"></td>
                <td class="p-1 border-r"><input type="number" name="items[${timestamp}][rate]" id="rate_${timestamp}" value="0" class="w-full text-right outline-none bg-transparent" oninput="calculateRow(${timestamp})"></td>
                <td class="p-1 border-r text-right text-gray-500"><input type="text" id="total_${timestamp}" class="w-full text-right bg-transparent outline-none" readonly value="0.00"></td>
                <td class="p-1 border-r"><input type="number" name="items[${timestamp}][discount]" id="disc_${timestamp}" value="0" class="w-full text-right outline-none bg-transparent text-red-500" oninput="calculateRow(${timestamp})"></td>
                <td class="p-1 text-right font-bold bg-blue-50"><input type="text" name="items[${timestamp}][net_amount]" id="net_${timestamp}" class="w-full text-right bg-transparent outline-none font-bold" readonly value="0.00"></td>
                <td class="p-1 text-center"><button type="button" onclick="removeRow(${timestamp})" class="text-red-400 hover:text-red-600">×</button></td>
            `;
            tbody.appendChild(tr);
        }

        function removeRow(id) {
            document.getElementById(`row_${id}`).remove();
            calculateTotals();
        }

        function calculateRow(id) {
            const qty = parseFloat(document.getElementById(`qty_${id}`).value) || 0;
            const rate = parseFloat(document.getElementById(`rate_${id}`).value) || 0;
            const disc = parseFloat(document.getElementById(`disc_${id}`).value) || 0;

            const total = qty * rate;
            const net = total - disc;

            document.getElementById(`total_${id}`).value = total.toFixed(2);
            document.getElementById(`net_${id}`).value = net.toFixed(2);
            calculateTotals();
        }

        function calculateTotals() {
            let totalAmt = 0;
            let totalDisc = 0;
            let totalNet = 0;

            const rows = document.querySelectorAll('#itemsBody tr');
            rows.forEach(row => {
                const id = row.id.split('_')[1];
                if (id) {
                    totalAmt += parseFloat(document.getElementById(`total_${id}`).value) || 0;
                    totalDisc += parseFloat(document.getElementById(`disc_${id}`).value) || 0;
                    totalNet += parseFloat(document.getElementById(`net_${id}`).value) || 0;
                }
            });

            document.getElementById('display_total').innerText = totalAmt.toFixed(2);
            document.getElementById('display_discount').innerText = totalDisc.toFixed(2);
            document.getElementById('display_net').innerText = totalNet.toFixed(2);

            document.getElementById('input_total').value = totalAmt.toFixed(2);
            document.getElementById('input_discount').value = totalDisc.toFixed(2);
            document.getElementById('input_net').value = totalNet.toFixed(2);
        }

        function loadCustomerInfo(select) {
            const option = select.options[select.selectedIndex];
            if (option.value) {
                document.getElementById('customer_address').value = option.dataset.address || '';
                document.getElementById('customer_phone').value = option.dataset.phone || '';
            } else {
                document.getElementById('customer_address').value = '';
                document.getElementById('customer_phone').value = '';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            addNewRow(); // Start with one row
        });
    </script>
</body>

</html>