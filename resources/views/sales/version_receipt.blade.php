<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snapshot V{{ $version->version_number }} — Invoice #{{ $sale->invoice_no }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 10px;
            width: 80mm;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
        .item-row { display: flex; justify-content: space-between; }
        .footer { margin-top: 10px; font-size: 10px; }
        .watermark {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 4px;
            border: 1px dashed #ef4444;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        @media print {
            .no-print { display: none; }
        }
    </style>
</head>

<body>
    @php
        $snapshot = $version->new_values ?? [];
        $items = $snapshot['items'] ?? [];
        $companySetting = \App\Models\CompanySetting::first();
        $store = \App\Models\Store::first();
        $appName = config('app.name');
        $fallbackName = ($appName && strtolower($appName) !== 'laravel') ? $appName : 'Supermarket';
        $storeName = $companySetting?->business_name ?: ($store?->name ?: $fallbackName);
    @endphp

    <div class="no-print" style="margin-bottom: 10px; text-align: center;">
        <button onclick="window.print()" style="padding: 6px 12px; font-size: 12px; font-weight: bold; cursor: pointer; background: #2563eb; color: white; border: none; border-radius: 4px;">
            🖨️ Print This Snapshot
        </button>
        <button onclick="window.close()" style="padding: 6px 12px; font-size: 12px; cursor: pointer; background: #64748b; color: white; border: none; border-radius: 4px; margin-left: 4px;">
            Close
        </button>
    </div>

    <div class="watermark">
        Historical Snapshot — Version {{ $version->version_number }}<br>
        (Recorded: {{ $version->created_at->format('d-M-Y h:i A') }})
    </div>

    <div class="text-center">
        <h2 style="margin:0; font-weight: bold; text-transform: uppercase; font-size: 15px;">{{ $storeName }}</h2>
        @if(!empty($companySetting?->address))
            <p style="margin:2px 0; font-size: 10px;">{{ $companySetting->address }}</p>
        @endif
        @if(!empty($companySetting?->phone))
            <p style="margin:1px 0; font-size: 10px;">Ph: {{ $companySetting->phone }}</p>
        @endif
        <p class="divider"></p>
    </div>

    <div style="border-bottom: 1px dashed #000; margin-bottom: 5px; padding-bottom: 5px; font-size: 11px;">
        Invoice #: <strong>{{ $sale->invoice_no }}</strong> (Version {{ $version->version_number }})<br>
        Original Date: <strong>{{ $sale->sale_date ? \Carbon\Carbon::parse($sale->sale_date)->format('d-M-Y h:i A') : $sale->created_at->format('d-M-Y h:i A') }}</strong><br>
        @if($version->version_number > 1)
        Edited At: <strong>{{ $version->created_at->format('d-M-Y h:i A') }}</strong><br>
        Edited By: <strong>{{ $version->user_name ?? ($version->user->name ?? 'Staff') }}</strong><br>
        @else
        Created By: <strong>{{ $version->user_name ?? ($version->user->name ?? 'Staff') }}</strong><br>
        @endif
        Customer: <strong>{{ $snapshot['customer_name'] ?? 'Walk-in Customer' }}</strong><br>
        Status: <strong>{{ strtoupper($version->action_type) }}</strong>
    </div>

    @if($version->reason)
    <div style="background: #f8fafc; border: 1px dotted #94a3b8; padding: 4px; font-size: 10px; margin-bottom: 5px;">
        <strong>Audit Reason:</strong> {{ $version->reason }}
    </div>
    @endif

    <p class="divider"></p>

    <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
        <thead>
            <tr style="border-bottom: 1px solid #000;">
                <th style="text-align: left;">Item</th>
                <th style="text-align: center;">Qty</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            <tr>
                <td style="padding-top: 4px;">{{ \Illuminate\Support\Str::limit($item['item_name'] ?? 'Item', 15) }}</td>
                <td style="text-align: center;">{{ $item['qty'] }}</td>
                <td style="text-align: right;">{{ number_format($item['total'] ?? ($item['qty'] * $item['rate']), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="divider"></p>

    <div class="item-row">
        <span>Subtotal</span>
        <span class="font-bold">{{ number_format($snapshot['subtotal'] ?? 0, 2) }}</span>
    </div>

    @if(($snapshot['discount_total'] ?? 0) > 0)
    <div class="item-row">
        <span>Discount</span>
        <span>-{{ number_format($snapshot['discount_total'], 2) }}</span>
    </div>
    @endif

    @if(($snapshot['tax_total'] ?? 0) > 0 || ($snapshot['tax_rate'] ?? 0) > 0)
    <div class="item-row">
        <span>Tax ({{ number_format($snapshot['tax_rate'] ?? 0, (($snapshot['tax_rate'] ?? 0) == intval($snapshot['tax_rate'] ?? 0) ? 0 : 2)) }}%)</span>
        <span>+{{ number_format($snapshot['tax_total'] ?? 0, 2) }}</span>
    </div>
    @endif

    <div class="item-row font-bold" style="font-size: 13px; margin-top: 4px; border-top: 1px solid #000; padding-top: 4px;">
        <span>Grand Total</span>
        <span>Rs. {{ number_format($snapshot['grand_total'] ?? 0, 2) }}</span>
    </div>

    <div class="item-row" style="font-size: 11px; margin-top: 4px;">
        <span>Payment Mode</span>
        <span>{{ $snapshot['payment_mode'] ?? $sale->payment_mode }}</span>
    </div>

    <p class="divider"></p>

    <div class="text-center footer">
        <p>*** HISTORICAL SNAPSHOT ONLY ***</p>
        <p>Generated for Audit & Verification</p>
    </div>
</body>
</html>
