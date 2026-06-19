<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Voucher #{{ $purchase->purchase_no }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none;
            }
        }

        body {
            font-family: 'Courier New', Courier, monospace;
        }
    </style>
</head>

<body class="bg-gray-100 p-8 text-gray-800">

    <div class="max-w-2xl mx-auto bg-white p-8 shadow-md print:shadow-none print:w-full">

        <!-- Header -->
        <div class="text-center border-b-2 border-dashed border-gray-300 pb-4 mb-4">
            <h1 class="text-2xl font-bold uppercase tracking-widest text-gray-900">Purchase Voucher</h1>
            <p class="text-sm text-gray-500">OwnStore PRO - Internal Record</p>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-2 gap-4 text-sm mb-6">
            <div>
                <span class="block text-gray-500 text-xs uppercase">Supplier Details</span>
                <span class="font-bold text-lg text-gray-900">{{ $purchase->supplier->name ?? 'N/A' }}</span>
                <br>
                <span class="text-gray-500 text-xs">Ph: {{ $purchase->supplier->phone ?? '-' }}</span>
            </div>
            <div class="text-right">
                <span class="block text-gray-500 text-xs uppercase">Voucher #</span>
                <span class="font-bold text-gray-900">{{ $purchase->purchase_no }}</span>
                <br>
                <span class="block text-gray-500 text-xs uppercase mt-1">Date</span>
                <span class="font-mono">{{ $purchase->purchase_date }}</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 text-sm mb-6 pb-6 border-b border-gray-200">
            <div>
                <span class="block text-gray-500 text-xs uppercase">Payment Type</span>
                <span class="font-bold px-2 py-0.5 rounded text-xs inline-block mt-1 {{ $purchase->payment_type == 'Credit' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-green-100 text-green-800 border-green-200' }} border print:border-black print:bg-transparent print:text-black">
                    {{ $purchase->payment_type }}
                </span>
                @if($purchase->due_date)
                <div class="mt-1 text-xs text-red-500 font-bold print:text-black">
                    Due: {{ $purchase->due_date }}
                </div>
                @endif
            </div>
            <div class="text-right">
                <span class="block text-gray-500 text-xs uppercase">Vendor Bill Ref</span>
                <span class="font-mono font-bold">{{ $purchase->vendor_bill_no ?? '-' }}</span>
            </div>
        </div>

        <!-- Items Table -->
        <table class="w-full text-sm mb-6">
            <thead>
                <tr class="border-b-2 border-black text-xs uppercase text-gray-600">
                    <th class="text-left py-2">Item Description</th>
                    <th class="text-center py-2">Qty</th>
                    <th class="text-right py-2">Rate</th>
                    <th class="text-right py-2">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $item)
                <tr class="border-b border-dashed border-gray-300">
                    <td class="py-2">
                        <span class="font-bold">{{ $item->item->name ?? 'Unknown Item' }}</span>
                    </td>
                    <td class="text-center py-2">{{ $item->qty }}</td>
                    <td class="text-right py-2">{{ number_format($item->cost_rate, 2) }}</td>
                    <td class="text-right py-2">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Import & Clearing Charges -->
        @if($purchase->charges && $purchase->charges->count() > 0)
        <div class="mb-6 border border-gray-200 rounded p-4 print:border-gray-400">
            <h3 class="text-xs font-bold uppercase text-gray-600 mb-2 border-b border-gray-300 pb-1">
                <i class="fas fa-file-invoice mr-1"></i> Import & Clearing Charges / Taxes
            </h3>
            <table class="w-full text-sm">
                @foreach($purchase->charges as $charge)
                <tr class="border-b border-dashed border-gray-200">
                    <td class="py-1.5 text-gray-700">{{ $charge->taxChargeType->name ?? 'Unknown' }}</td>
                    <td class="py-1.5 text-right font-mono">{{ number_format($charge->amount, 2) }}</td>
                </tr>
                @endforeach
                <tr class="border-t border-black">
                    <td class="py-1.5 font-bold text-gray-800">Total Charges</td>
                    <td class="py-1.5 text-right font-mono font-bold">{{ number_format($purchase->charges->sum('amount'), 2) }}</td>
                </tr>
            </table>
        </div>
        @endif

        <!-- Totals -->
        <div class="flex justify-end mb-8">
            <div class="w-1/2">
                <div class="flex justify-between py-1 text-sm">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="font-mono">{{ number_format($purchase->gross_total, 2) }}</span>
                </div>
                @if($purchase->charges && $purchase->charges->sum('amount') > 0)
                <div class="flex justify-between py-1 text-sm">
                    <span class="text-gray-500">Import Charges / Tax</span>
                    <span class="font-mono">{{ number_format($purchase->charges->sum('amount'), 2) }}</span>
                </div>
                @endif
                @if($purchase->discount > 0)
                <div class="flex justify-between py-1 text-sm text-green-600 print:text-black">
                    <span class="text-gray-500">Discount</span>
                    <span class="font-mono">-{{ number_format($purchase->discount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between py-2 border-t-2 border-black font-bold text-xl mt-2">
                    <span>Total</span>
                    <span>Rs. {{ number_format($purchase->net_total, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Memo -->
        @if($purchase->memo)
        <div class="mb-8 p-3 bg-gray-50 border border-gray-200 rounded text-xs italic print:bg-transparent print:border-gray-400">
            <strong>Note:</strong> {{ $purchase->memo }}
        </div>
        @endif

        <!-- Footer / Signatures -->
        <div class="mt-12 pt-12 border-t border-gray-300 flex justify-between text-xs text-center text-gray-400">
            <div class="w-32">
                <div class="border-t border-gray-400 pt-2">Received By</div>
            </div>
            <div class="w-32">
                <div class="border-t border-gray-400 pt-2">Authorized Sign</div>
            </div>
        </div>

        <div class="text-center mt-8 text-[10px] text-gray-400 uppercase tracking-widest">
            Printed on {{ date('Y-m-d H:i:s') }}
        </div>

        <!-- Print Action -->
        <div class="fixed bottom-8 right-8 no-print">
            <button onclick="window.print()" class="bg-amber-600 text-white px-6 py-3 rounded-full shadow-lg font-bold hover:bg-amber-700 transition flex items-center gap-2">
                <i class="fas fa-print"></i> Print Voucher
            </button>
            <button onclick="window.history.back()" class="bg-gray-600 text-white px-4 py-3 rounded-full shadow-lg font-bold hover:bg-gray-700 transition ml-2">
                <i class="fas fa-arrow-left"></i>
            </button>
        </div>

    </div>

</body>

</html>