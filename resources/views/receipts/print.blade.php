<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Receipt #{{ $sale->receipt_no ?: $sale->receipt_number }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            width: 100%;
            max-width: 80mm;
            margin: 0 auto;
            padding: 12px;
            color: #111;
            background: #fff;
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .store-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .meta {
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .details {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 8px 0;
            margin: 8px 0;
            line-height: 1.5;
        }

        .row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .allocations {
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 8px;
            font-size: 11px;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 8px;
            line-height: 1.4;
        }

        .actions {
            text-align: center;
            margin-top: 16px;
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 6px 14px;
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            font-family: sans-serif;
            font-size: 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }

        .btn-secondary {
            background: #4b5563;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                max-width: 100%;
                width: 100%;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    @php
        $companySetting = \App\Models\CompanySetting::first();
        $store = \App\Models\Store::first();
        $appName = config('app.name');
        $fallbackName = ($appName && strtolower($appName) !== 'laravel') ? $appName : 'Supermarket';
        $storeName = $sale->store_name ?: ($companySetting?->business_name ?: ($store?->name ?: $fallbackName));
    @endphp

    <div class="header">
        <div class="store-name">{{ $storeName }}</div>
        @if(!empty($companySetting?->address))
            <div style="font-size: 11px;">{{ $companySetting->address }}</div>
        @endif
        @if(!empty($companySetting?->phone))
            <div style="font-size: 10px;">Ph: {{ $companySetting->phone }}</div>
        @endif
        <div style="font-weight: bold; margin-top: 4px; font-size: 14px;">PAYMENT RECEIPT</div>
    </div>

    <div class="meta">
        <div class="row"><span>Receipt #:</span> <strong>{{ $sale->receipt_no ?: $sale->receipt_number }}</strong></div>
        <div class="row"><span>Date:</span> <span>{{ $sale->receipt_date ? \Carbon\Carbon::parse($sale->receipt_date)->format('d-M-Y') : now()->format('d-M-Y') }}</span></div>
        <div class="row"><span>Customer:</span> <strong>{{ $sale->customer->name ?? 'N/A' }}</strong></div>
        @if(!empty($sale->customer?->phone))
            <div class="row"><span>Phone:</span> <span>{{ $sale->customer->phone }}</span></div>
        @endif
        @if($sale->salesman)
            <div class="row"><span>Salesman:</span> <span>{{ $sale->salesman->name }}</span></div>
        @endif
        @if($sale->receivedBy)
            <div class="row"><span>Cashier:</span> <span>{{ $sale->receivedBy->name }}</span></div>
        @endif
    </div>

    <div class="details">
        <div class="row">
            <span><strong>Amount Received:</strong></span>
            <strong>Rs. {{ number_format($sale->amount_received ?: $sale->amount, 2) }}</strong>
        </div>
        @if($sale->discount_given > 0)
            <div class="row">
                <span>Discount Given:</span>
                <span>Rs. {{ number_format($sale->discount_given, 2) }}</span>
            </div>
        @endif
        <div class="row" style="border-top: 1px dotted #888; padding-top: 4px; margin-top: 4px;">
            <span><strong>Total Settled:</strong></span>
            <strong>Rs. {{ number_format($sale->total_adjusted ?: ($sale->amount_received + $sale->discount_given), 2) }}</strong>
        </div>
        <div class="row">
            <span>Remaining Balance:</span>
            <span><strong>Rs. {{ number_format($sale->remaining_balance, 2) }}</strong></span>
        </div>
        <div style="margin-top: 6px; font-size: 11px;">
            <div class="row"><span>Payment Mode:</span> <span>{{ $sale->payment_mode ?: $sale->payment_method }}</span></div>
            <div class="row"><span>Deposit To:</span> <span>{{ $sale->deposit_account ?: 'Cash Account' }}</span></div>
            @if($sale->payment_mode && $sale->payment_mode !== 'Cash')
                @if($sale->cheque_no)
                    <div class="row"><span>Ref / Cheque #:</span> <span>{{ $sale->cheque_no }}</span></div>
                @endif
                @if($sale->bank_name)
                    <div class="row"><span>Bank:</span> <span>{{ $sale->bank_name }}</span></div>
                @endif
                @if($sale->cheque_date)
                    <div class="row"><span>Cheque Date:</span> <span>{{ $sale->cheque_date }}</span></div>
                @endif
            @endif
            @if($sale->memo)
                <div class="row" style="margin-top: 3px;"><span>Memo:</span> <span>{{ $sale->memo }}</span></div>
            @endif
        </div>
    </div>

    @if($sale->allocations && $sale->allocations->count() > 0)
        <div class="allocations">
            <div style="font-weight: bold; margin-bottom: 3px;">Settled Invoices:</div>
            @foreach($sale->allocations as $alloc)
                <div class="row">
                    <span>{{ $alloc->debitSale->invoice_no ?? ('Invoice #' . $alloc->debit_sale_id) }}</span>
                    <span>Rs. {{ number_format($alloc->allocated_amount, 2) }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <div class="footer">
        <div>Thank you for your payment!</div>
        <div style="font-size: 10px; margin-top: 3px; color: #555;">Computer generated receipt.</div>
    </div>

    <div class="actions no-print">
        <button onclick="window.print()" class="btn">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="{{ route('receipts.create') }}" class="btn btn-secondary">
            + New Receipt
        </a>
        <a href="/admin" class="btn btn-secondary">
            Dashboard
        </a>
    </div>

    <div id="flash-message" data-success="{{ session('success') }}" style="display:none;"></div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.getElementById('flash-message').getAttribute('data-success');

            if (successMessage) {
                Swal.fire({
                    title: 'Payment Received!',
                    text: successMessage,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    // Window print trigger optional
                });
            }
        });
    </script>
</body>

</html>