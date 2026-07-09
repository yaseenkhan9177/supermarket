<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <title>Credit Note #{{ $refund->credit_no }} | OwnStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .receipt-card { box-shadow: none !important; border: none !important; }
            @page { margin: 10mm; }
        }

        .condition-badge-restock { background: #D1FAE5; color: #065F46; }
        .condition-badge-damaged { background: #FEE2E2; color: #991B1B; }
    </style>
</head>

<body class="bg-slate-100 min-h-screen flex flex-col items-center py-8">

    <!-- Action bar -->
    <div class="no-print w-full max-w-2xl flex justify-between items-center mb-6 px-4">
        <a href="{{ route('refunds.create') }}"
           class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 bg-white border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition">
            <i class="fas fa-plus"></i> New Return
        </a>
        <div class="flex gap-3">
            <a href="{{ route('refunds.index') }}"
               class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 bg-white border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition">
                <i class="fas fa-list"></i> All Returns
            </a>
            <button onclick="window.print()"
                    class="inline-flex items-center gap-2 text-sm font-bold bg-red-600 text-white px-5 py-2 rounded-xl hover:bg-red-700 transition shadow-lg">
                <i class="fas fa-print"></i> Print Receipt
            </button>
        </div>
    </div>

    <!-- Success flash -->
    @if(session('success'))
    <div class="no-print w-full max-w-2xl px-4 mb-4">
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle text-green-500 text-lg"></i>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <!-- ====================================================================
         RECEIPT CARD
    ================================================================== -->
    <div class="receipt-card w-full max-w-2xl bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">

        <!-- Header stripe -->
        <div class="bg-gradient-to-r from-red-700 to-red-500 px-8 py-6 text-white">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-red-200 text-xs font-semibold uppercase tracking-widest mb-1">Credit Note</p>
                    <h1 class="text-3xl font-extrabold tracking-tight">{{ $refund->credit_no }}</h1>
                    <p class="text-red-200 text-sm mt-1">
                        Issued: {{ \Carbon\Carbon::parse($refund->refund_date)->format('d M Y') }}
                    </p>
                </div>
                <div class="text-right">
                    <div class="w-14 h-14 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-undo-alt text-white text-2xl"></i>
                    </div>
                    <p class="text-red-100 text-xs mt-2 font-semibold">OwnStore POS</p>
                </div>
            </div>
        </div>

        <!-- Meta row -->
        <div class="grid grid-cols-3 divide-x divide-gray-100 bg-gray-50 border-b border-gray-100">
            <div class="px-6 py-4">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Customer</p>
                <p class="text-sm font-bold text-gray-900">
                    {{ optional($refund->customer)->name ?? 'Walk-in Customer' }}
                </p>
                @if(optional($refund->customer)->phone)
                <p class="text-xs text-gray-500 mt-0.5">{{ $refund->customer->phone }}</p>
                @endif
            </div>
            <div class="px-6 py-4">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Processed By</p>
                <p class="text-sm font-bold text-gray-900">
                    {{ optional($refund->processedBy)->name ?? '—' }}
                </p>
                <p class="text-xs text-gray-500 mt-0.5">{{ \Carbon\Carbon::parse($refund->created_at)->format('h:i A') }}</p>
            </div>
            <div class="px-6 py-4">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Refund Method</p>
                @php
                    $modeLabel = match($refund->refund_mode) {
                        'CASH'          => ['Cash Refund',          'bg-green-100 text-green-700'],
                        'STORE_CREDIT'  => ['Store Credit',         'bg-blue-100 text-blue-700'],
                        'REDUCE_DEBIT'  => ['Reduced Debit Balance','bg-orange-100 text-orange-700'],
                        default         => [$refund->refund_mode,   'bg-gray-100 text-gray-700'],
                    };
                @endphp
                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $modeLabel[1] }}">
                    {{ $modeLabel[0] }}
                </span>
            </div>
        </div>

        <!-- Line Items -->
        <div class="px-8 py-6">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Returned Items</p>

            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left text-xs font-semibold text-gray-500 pb-3">Item</th>
                        <th class="text-center text-xs font-semibold text-gray-500 pb-3">Qty</th>
                        <th class="text-right text-xs font-semibold text-gray-500 pb-3">Rate</th>
                        <th class="text-right text-xs font-semibold text-gray-500 pb-3">Amount</th>
                        <th class="text-center text-xs font-semibold text-gray-500 pb-3">Condition</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($refund->items as $item)
                    <tr>
                        <td class="py-3 pr-4">
                            <p class="font-semibold text-gray-900">{{ $item->item_name }}</p>
                            @if($item->sale_source)
                            <p class="text-xs text-gray-400 mt-0.5">
                                From: {{ ucwords(str_replace('_', ' ', $item->sale_source)) }}
                            </p>
                            @endif
                        </td>
                        <td class="py-3 text-center text-gray-700">{{ $item->quantity }}</td>
                        <td class="py-3 text-right text-gray-700">{{ number_format($item->rate, 2) }}</td>
                        <td class="py-3 text-right font-bold text-gray-900">{{ number_format($item->net_amount, 2) }}</td>
                        <td class="py-3 text-center">
                            @if($item->condition === 'sellable')
                                <span class="text-xs font-bold px-2 py-1 rounded-full condition-badge-restock">
                                    <i class="fas fa-check mr-1"></i>Restocked
                                </span>
                            @else
                                <span class="text-xs font-bold px-2 py-1 rounded-full condition-badge-damaged">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>Damaged
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Total row -->
        <div class="mx-8 mb-6 bg-red-50 rounded-2xl p-5 flex justify-between items-center border border-red-100">
            <div>
                <p class="text-sm font-semibold text-red-700">Total Credit Issued</p>
                @if($refund->memo)
                <p class="text-xs text-gray-500 mt-1 italic">"{{ $refund->memo }}"</p>
                @endif
            </div>
            <p class="text-3xl font-extrabold text-red-700">
                Rs. {{ number_format($refund->total_amount, 2) }}
            </p>
        </div>

        <!-- Footer -->
        <div class="px-8 pb-6 text-center border-t border-gray-100 pt-5">
            <p class="text-xs text-gray-400">This credit note was generated by OwnStore POS.</p>
            <p class="text-xs text-gray-400 mt-0.5">
                Printed on {{ now()->format('d M Y, h:i A') }}
            </p>
        </div>
    </div>

</body>
</html>
