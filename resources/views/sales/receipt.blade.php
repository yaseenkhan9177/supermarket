@php
    $format = request('format', '80mm');
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $sale->invoice_no }} ({{ strtoupper($format) }})</title>
    <style>
        body {
            font-family: {{ in_array($format, ['a4', 'customer']) ? "'Segoe UI', Roboto, Helvetica, sans-serif" : "'Courier New', Courier, monospace" }};
            font-size: {{ $format === '58mm' ? '10px' : ($format === 'a4' || $format === 'customer' ? '13px' : '12px') }};
            margin: 0 auto;
            padding: {{ in_array($format, ['a4', 'customer']) ? '25px' : '10px' }};
            width: {{ $format === '58mm' ? '58mm' : ($format === 'a4' || $format === 'customer' ? '100%' : '80mm') }};
            max-width: {{ in_array($format, ['a4', 'customer']) ? '800px' : ($format === '58mm' ? '58mm' : '80mm') }};
            box-sizing: border-box;
            background: #fff;
            color: #000;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }

        .divider {
            border-top: {{ in_array($format, ['a4', 'customer']) ? '1px solid #cbd5e1' : '1px dashed #000' }};
            margin: {{ in_array($format, ['a4', 'customer']) ? '12px 0' : '5px 0' }};
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }

        .format-badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 4px;
            background: #e2e8f0;
            color: #334155;
            margin-left: 4px;
        }

        table.invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table.invoice-table th, table.invoice-table td {
            padding: {{ in_array($format, ['a4', 'customer']) ? '8px 10px' : '4px 2px' }};
        }
        table.invoice-table th {
            border-bottom: {{ in_array($format, ['a4', 'customer']) ? '2px solid #0f172a' : '1px solid #000' }};
        }
        table.invoice-table tr.border-b td {
            border-bottom: 1px solid #f1f5f9;
        }

        @media print {
            .no-print { display: none !important; }
            body { width: 100% !important; max-width: none !important; padding: 0 !important; }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 12px; text-align: center; background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="margin-bottom: 8px; font-size: 11px; font-weight: bold; color: #64748b;">
            Select Format:
            <a href="?format=80mm" style="padding: 3px 8px; margin: 0 2px; border-radius: 4px; text-decoration: none; {{ $format==='80mm' ? 'background:#2563eb;color:#fff;' : 'background:#e2e8f0;color:#334155;' }}">80mm</a>
            <a href="?format=58mm" style="padding: 3px 8px; margin: 0 2px; border-radius: 4px; text-decoration: none; {{ $format==='58mm' ? 'background:#2563eb;color:#fff;' : 'background:#e2e8f0;color:#334155;' }}">58mm</a>
            <a href="?format=a4" style="padding: 3px 8px; margin: 0 2px; border-radius: 4px; text-decoration: none; {{ $format==='a4' ? 'background:#2563eb;color:#fff;' : 'background:#e2e8f0;color:#334155;' }}">A4 Invoice</a>
            <a href="?format=simple" style="padding: 3px 8px; margin: 0 2px; border-radius: 4px; text-decoration: none; {{ $format==='simple' ? 'background:#2563eb;color:#fff;' : 'background:#e2e8f0;color:#334155;' }}">Simple Slip</a>
            <a href="?format=customer" style="padding: 3px 8px; margin: 0 2px; border-radius: 4px; text-decoration: none; {{ $format==='customer' ? 'background:#2563eb;color:#fff;' : 'background:#e2e8f0;color:#334155;' }}">Customer Invoice</a>
        </div>
        <div>
            @if(($sale->status ?? '') !== 'cancelled')
            <a href="{{ route('sales.edit', $sale->id) }}" style="display: inline-block; text-decoration: none; padding: 5px 12px; font-size: 11px; font-weight: bold; background: #2563eb; color: white; border-radius: 4px; margin-right: 4px;">
                ✏️ Edit Invoice
            </a>
            @endif
            <a href="{{ route('sales.versions', $sale->id) }}" style="display: inline-block; text-decoration: none; padding: 5px 12px; font-size: 11px; font-weight: bold; background: #4f46e5; color: white; border-radius: 4px; margin-right: 4px;">
                📜 History ({{ $sale->versions()->count() }})
            </a>
            <button onclick="window.print()" style="padding: 5px 14px; font-size: 11px; font-weight: bold; cursor: pointer; background: #0f172a; color: white; border: none; border-radius: 4px;">
                🖨️ Print
            </button>
        </div>
    </div>

    @php
        $companySetting = \App\Models\CompanySetting::first();
        $store = \App\Models\Store::first();
        $appName = config('app.name');
        $fallbackName = ($appName && strtolower($appName) !== 'laravel') ? $appName : 'Supermarket';
        $storeName = $companySetting?->business_name ?: ($store?->name ?: $fallbackName);
    @endphp

    <div class="text-center">
        <h2 style="margin:0; font-weight: bold; text-transform: uppercase; font-size: {{ in_array($format, ['a4', 'customer']) ? '22px' : '16px' }};">{{ $storeName }}</h2>
        @if(!empty($companySetting?->address))
            <p style="margin:2px 0; font-size: {{ in_array($format, ['a4', 'customer']) ? '12px' : '11px' }};">{{ $companySetting->address }}</p>
        @endif
        @if(!empty($companySetting?->phone))
            <p style="margin:1px 0; font-size: {{ in_array($format, ['a4', 'customer']) ? '12px' : '10px' }};">Ph: {{ $companySetting->phone }}</p>
        @endif
        <p class="divider"></p>
    </div>

    <div style="margin-bottom: 5px; padding-bottom: 5px;">
        <div style="display: flex; justify-content: space-between;">
            <span>Invoice: <strong>{{ $sale->invoice_no }}</strong></span>
            @if(in_array($format, ['a4', 'customer']))
                <span>Type: <strong>{{ $sale->payment_mode ?? 'Cash' }} Sale</strong></span>
            @endif
        </div>
        Date: {{ is_a($sale->created_at ?? null, 'DateTimeInterface') ? $sale->created_at->format('d-M-Y h:i A') : \Carbon\Carbon::parse($sale->created_at ?? $sale->sale_date ?? now())->format('d-M-Y h:i A') }}<br>

        Customer: <strong>{{ $sale->customer->name ?? 'Walk-in Customer' }}</strong><br>
        Payment Method: <strong>{{ $sale->payment_mode ?? 'Cash' }}</strong><br>
        Payment Status: <strong>{{ ($sale->paid_amount ?? 0) >= ($sale->grand_total ?? 0) ? 'Paid' : (($sale->paid_amount ?? 0) > 0 ? 'Partial' : 'Unpaid') }}</strong><br>

        Salesman: {{ $sale->user->name ?? 'Staff' }}

        @if(!empty($sale->note))
        <div style="margin-top: 4px; padding: 4px; background: #f8fafc; border-left: 3px solid #64748b; font-style: italic; font-size: 11px;">
            <strong>Invoice Note:</strong> {{ $sale->note }}
        </div>
        @endif
    </div>

    <p class="divider"></p>

    <table class="invoice-table">
        <thead>
            <tr>
                <th style="text-align: left;">Item</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Rate</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr class="border-b">
                <td style="padding-top: 4px;">
                    <span class="font-bold">{{ $item->item_name }}</span>
                    @if(($item->tax_rate ?? 0) > 0)
                        <span style="font-size: 9px; color: #444;">(T: {{ number_format($item->tax_rate, 0) }}%)</span>
                    @endif
                    @if(!empty($item->note))
                        <div style="font-size: 10px; color: #475569; font-style: italic;">Note: {{ $item->note }}</div>
                    @endif
                </td>
                <td style="text-align: center;">{{ $item->qty }}</td>
                <td style="text-align: right;">{{ number_format($item->rate, 2) }}</td>
                <td style="text-align: right;" class="font-bold">{{ number_format($item->total, 2) }}</td>
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
        <span>Tax ({{ number_format($sale->tax_rate ?? 0, 0) }}%)</span>
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

    <div class="item-row font-bold" style="font-size: {{ in_array($format, ['a4', 'customer']) ? '16px' : '13px' }}; margin-top: 4px;">
        <span>TOTAL</span>
        <span>Rs. {{ number_format($sale->grand_total, 2) }}</span>
    </div>

    <div class="item-row">
        <span>Paid</span>
        <span>Rs. {{ number_format($sale->paid_amount, 2) }}</span>
    </div>

    <div class="item-row">
        <span>Change / Due</span>
        <span>Rs. {{ number_format($sale->change_amount ?? max(0, $sale->grand_total - $sale->paid_amount), 2) }}</span>
    </div>

    <p class="divider"></p>

    <div class="text-center footer" style="margin-top: 10px; font-size: 11px;">
        Thank You for Shopping!<br>
        No Returns without Receipt.
    </div>

</body>

</html>