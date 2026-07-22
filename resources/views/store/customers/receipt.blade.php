<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $receipt->receipt_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
            }
            .receipt-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }
        .reversed-stamp {
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            color: rgba(220, 38, 38, 0.22);
            font-size: 5.5rem;
            font-weight: 900;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            border: 8px solid rgba(220, 38, 38, 0.35);
            padding: 0.5rem 2rem;
            border-radius: 1rem;
            pointer-events: none;
            user-select: none;
            z-index: 50;
        }
    </style>
</head>
<body class="bg-slate-100 dark:bg-slate-900 min-h-screen font-sans text-slate-800 dark:text-slate-200 antialiased p-4 md:p-8 flex flex-col items-center">

    {{-- Top Action Bar (hidden when printing) --}}
    <div class="no-print w-full max-w-2xl flex items-center justify-between mb-6">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-4 py-2 rounded-xl shadow-sm transition">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <button onclick="window.print()" class="inline-flex items-center gap-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition">
            <i class="fas fa-print"></i> Print Receipt
        </button>
    </div>

    {{-- Printable Receipt Container --}}
    <div class="receipt-card relative bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-3xl shadow-xl p-8 md:p-10 w-full max-w-2xl overflow-hidden">
        
        {{-- Reversed Watermark Stamp --}}
        @if($isReversed)
            <div class="reversed-stamp">
                REVERSED
            </div>
        @endif

        {{-- Store Branding Header --}}
        <div class="flex flex-col md:flex-row items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-6 mb-8 gap-4 text-center md:text-left">
            <div class="flex items-center gap-4">
                @if(!empty($companySetting->logo_path))
                    <img src="{{ asset('storage/' . $companySetting->logo_path) }}" alt="Logo" class="h-16 w-auto object-contain">
                @else
                    <div class="w-14 h-14 rounded-2xl bg-emerald-600 text-white font-black text-2xl flex items-center justify-center shadow-md">
                        <i class="fas fa-store"></i>
                    </div>
                @endif
                <div>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white uppercase tracking-wider">
                        {{ $receipt->store_name ?: ($companySetting->business_name ?? config('app.name')) }}
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ $companySetting->address ?? 'Main City Market Store' }}
                    </p>
                    @if(!empty($companySetting->phone))
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Phone: {{ $companySetting->phone }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="text-center md:text-right">
                <span class="inline-block px-3.5 py-1 text-xs font-black uppercase tracking-wider rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 mb-1">
                    Official Payment Receipt
                </span>
                <h2 class="text-lg font-mono font-bold text-slate-800 dark:text-slate-200">
                    {{ $receipt->receipt_number }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Date: {{ \Carbon\Carbon::parse($receipt->created_at)->format('d M Y, h:i A') }}
                </p>
            </div>
        </div>

        {{-- Customer Info Banner --}}
        <div class="bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-700/60 rounded-2xl p-5 mb-8 flex flex-col md:flex-row justify-between gap-4">
            <div>
                <span class="text-xs uppercase font-bold text-slate-400 dark:text-slate-500 tracking-wider">Received From</span>
                <h3 class="text-xl font-black text-slate-900 dark:text-white mt-0.5">
                    {{ $receipt->customer->name ?? 'Customer' }}
                </h3>
                @if($receipt->customer && $receipt->customer->phone)
                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        <i class="fas fa-phone text-slate-400 mr-1"></i> {{ $receipt->customer->phone }}
                    </p>
                @endif
            </div>

            <div class="text-left md:text-right">
                <span class="text-xs uppercase font-bold text-slate-400 dark:text-slate-500 tracking-wider">Payment Method</span>
                <p class="text-base font-bold text-slate-800 dark:text-slate-200 capitalize mt-0.5">
                    <i class="fas fa-wallet text-emerald-600 mr-1"></i> {{ str_replace('_', ' ', $receipt->payment_method) }}
                </p>
            </div>
        </div>

        {{-- Main Amount Card --}}
        <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/30 dark:to-teal-950/20 border border-emerald-200/80 dark:border-emerald-800/50 rounded-2xl p-6 mb-8 text-center">
            <span class="text-xs uppercase font-black tracking-widest text-emerald-600 dark:text-emerald-400">
                Amount Received
            </span>
            <div class="text-4xl md:text-5xl font-black text-emerald-700 dark:text-emerald-300 mt-1">
                Rs. {{ number_format($receipt->amount, 2) }}
            </div>
        </div>

        {{-- Ledger & Balance Summary Details --}}
        <div class="space-y-4 mb-8 text-sm border-t border-b border-slate-100 dark:border-slate-700/60 py-6">
            <div class="flex justify-between items-center">
                <span class="text-slate-500 dark:text-slate-400 font-medium">Customer Debt Balance After Payment:</span>
                <span class="font-mono font-bold text-slate-900 dark:text-white text-base">
                    Rs. {{ number_format($receipt->remaining_balance, 2) }}
                </span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-slate-500 dark:text-slate-400 font-medium">Received By:</span>
                <span class="font-semibold text-slate-800 dark:text-slate-200">
                    <i class="fas fa-user-circle text-slate-400 mr-1"></i>
                    {{ $receipt->receivedBy->name ?? 'Store Cashier' }}
                </span>
            </div>

            @if($receipt->ledgerEntry && $receipt->ledgerEntry->note)
                <div class="flex justify-between items-start gap-4">
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Note / Reference:</span>
                    <span class="font-normal text-slate-700 dark:text-slate-300 text-right max-w-xs">
                        {{ $receipt->ledgerEntry->note }}
                    </span>
                </div>
            @endif
        </div>

        {{-- Footer & Signatures --}}
        <div class="pt-6 flex flex-col md:flex-row justify-between items-center gap-6 text-xs text-slate-400 dark:text-slate-500">
            <div>
                <p class="font-medium">Computer generated receipt — valid without stamp.</p>
                <p class="text-[10px] text-slate-400">Thank you for your payment!</p>
            </div>
            <div class="text-center border-t border-slate-300 dark:border-slate-600 pt-2 w-48">
                <span class="font-semibold text-slate-600 dark:text-slate-400">Authorized Signature</span>
            </div>
        </div>
    </div>

</body>
</html>
