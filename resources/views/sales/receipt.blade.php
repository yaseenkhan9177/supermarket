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

        .footer {
            margin-top: 10px;
            font-size: 10px;
        }
    </style>
</head>

<body onload="window.print()">

    <div class="text-center">
        <h2 style="margin:0;">OWN STORE</h2>
        <p style="margin:2px 0;">Retail & Wholesale</p>
        <p class="divider"></p>
    </div>

    <div style="border-bottom: 1px dashed #000; margin-bottom: 5px; padding-bottom: 5px;">
        Invoice: <strong>{{ $sale->invoice_no }}</strong><br>
        Date: {{ $sale->created_at->format('d-M-Y h:i A') }}<br>

        Customer: <strong>{{ $sale->customer->name ?? 'Walk-in Customer' }}</strong><br>

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
                <td style="padding-top: 4px;">{{ Str::limit($item->item_name, 15) }}</td>
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

    @if($sale->return_adjustment > 0)
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
        <span>Change</span>
        <span>{{ number_format($sale->change_amount, 2) }}</span>
    </div>

    <p class="divider"></p>

    <div class="text-center footer">
        Thank You for Shopping!<br>
        No Returns without Receipt.
    </div>

</body>

</html>