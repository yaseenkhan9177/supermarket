<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $sale->invoice_no }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 10px;
            width: 80mm;
            /* Thermal Printer Standard */
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
        }

        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 12px; text-align: center; background: #f1f5f9; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1;">
        @if(($sale->status ?? '') !== 'cancelled')
        <a href="{{ route('sales.edit', $sale->id) }}" style="display: inline-block; text-decoration: none; padding: 5px 10px; font-size: 11px; font-weight: bold; background: #2563eb; color: white; border-radius: 4px; margin-right: 4px;">
            ✏️ Edit Invoice
        </a>
        @endif
        <a href="{{ route('sales.versions', $sale->id) }}" style="display: inline-block; text-decoration: none; padding: 5px 10px; font-size: 11px; font-weight: bold; background: #4f46e5; color: white; border-radius: 4px; margin-right: 4px;">
            📜 History ({{ $sale->versions()->count() }})
        </a>
        <button onclick="window.print()" style="padding: 5px 10px; font-size: 11px; font-weight: bold; cursor: pointer; background: #0f172a; color: white; border: none; border-radius: 4px;">
            🖨️ Print
        </button>
    </div>

    @php
        $companySetting = \App\Models\CompanySetting::first();
        $store = \App\Models\Store::first();
        $appName = config('app.name');
        $fallbackName = ($appName && strtolower($appName) !== 'laravel') ? $appName : 'Supermarket';
        $storeName = $companySetting?->business_name ?: ($store?->name ?: $fallbackName);
    @endphp
    <div class="text-center">
        <h2 style="margin:0; font-weight: bold; text-transform: uppercase; font-size: 16px;">{{ $storeName }}</h2>
        @if(!empty($companySetting?->address))
            <p style="margin:2px 0; font-size: 11px;">{{ $companySetting->address }}</p>
        @endif
        @if(!empty($companySetting?->phone))
            <p style="margin:1px 0; font-size: 10px;">Ph: {{ $companySetting->phone }}</p>
        @endif
        <p class="divider"></p>
    </div>

    <div style="border-bottom: 1px dashed #000; margin-bottom: 5px; padding-bottom: 5px;">
        Invoice: <strong>{{ $sale->invoice_no }}</strong><br>
        Date: {{ is_a($sale->created_at ?? null, 'DateTimeInterface') ? $sale->created_at->format('d-M-Y h:i A') : \Carbon\Carbon::parse($sale->created_at ?? $sale->sale_date ?? now())->format('d-M-Y h:i A') }}<br>

        Customer: <strong>{{ $sale->customer->name ?? 'Walk-in Customer' }}</strong><br>
        Payment Method: <strong>{{ $sale->payment_mode ?? 'Cash' }}</strong><br>
        Payment Status: <strong>{{ ($sale->paid_amount ?? 0) >= ($sale->grand_total ?? 0) ? 'Paid' : (($sale->paid_amount ?? 0) > 0 ? 'Partial' : 'Unpaid') }}</strong><br>

        Salesman: {{ $sale->user->name ?? 'Staff' }}
    </div>

    <p class="divider"></p>

    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 1px solid #000;">
                <th style="text-align: left;">Item</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td style="padding-top: 4px;">
                    {{ Str::limit($item->item_name, 15) }}
                    @if(($item->tax_rate ?? 0) > 0)
                        <span style="font-size: 9px; color: #444;">(T: {{ number_format($item->tax_rate, 0) }}%)</span>
                    @endif
                </td>
                <td style="text-align: center;">{{ $item->qty }}</td>
                <td style="text-align: right;">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="divider"></p>

    <div class="item-row">
        <span>Subtotal</span>
        <span>{{ number_format($sale->subtotal, 2) }}</span>
    </div>

    @if(($sale->discount_total ?? 0) > 0)
    <div class="item-row" style="color: #555;">
        <span>Discount</span>
        <span>-{{ number_format($sale->discount_total, 2) }}</span>
    </div>
    @endif

    @if(($sale->tax_total ?? 0) > 0 || ($sale->tax_rate ?? 0) > 0)
    <div class="item-row">
        <span>Tax</span>
        <span>+{{ number_format($sale->tax_total ?? 0, 2) }}</span>
    </div>
    @endif

    @if(($sale->additional_charges_total ?? 0) > 0)
    <div class="item-row">
        <span>Additional Charges</span>
        <span>+{{ number_format($sale->additional_charges_total, 2) }}</span>
    </div>
    @endif

    @if(($sale->return_adjustment ?? 0) > 0)
    <div class="item-row" style="color: #555;">
        <span>Return Adj</span>
        <span>-{{ number_format($sale->return_adjustment, 2) }}</span>
    </div>
    @endif

    <div class="item-row font-bold">
        <span>TOTAL</span>
        <span>{{ number_format($sale->grand_total, 2) }}</span>
    </div>

    <div class="item-row">
        <span>Paid</span>
        <span>{{ number_format($sale->paid_amount, 2) }}</span>
    </div>

    <div class="item-row">
        <span>Change / Due</span>
        <span>{{ number_format($sale->change_amount ?? max(0, $sale->grand_total - $sale->paid_amount), 2) }}</span>
    </div>

    <p class="divider"></p>

    <div class="text-center footer">
        Thank You for Shopping!<br>
        No Returns without Receipt.
    </div>

</body>

</html>