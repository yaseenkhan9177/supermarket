<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print KOT #{{ $kot->kot_no }}</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            @page {
                margin: 0;
            }
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            /* Monospace for alignment */
            width: 80mm;
            /* Standard Thermal Width */
            margin: 0 auto;
            padding: 10px;
            background: #fff;
            color: #000;
        }

        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .meta {
            font-size: 12px;
            margin-top: 5px;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .items th {
            text-align: left;
            border-bottom: 1px solid #000;
        }

        .items td {
            padding: 4px 0;
        }

        .qty {
            width: 15%;
            font-weight: bold;
        }

        .name {
            width: 60%;
        }

        .price {
            width: 25%;
            text-align: right;
        }

        .total {
            margin-top: 10px;
            border-top: 2px dashed #000;
            padding-top: 10px;
            text-align: right;
            font-weight: bold;
            font-size: 14px;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>

<body onload="window.print()">

    <div class="header">
        <div class="title">{{ isset($total) ? 'GUEST BILL' : 'KITCHEN ORDER' }}</div>
        <div class="meta">
            #{{ $kot->kot_no }}<br>
            {{ $kot->created_at->format('d-M-Y H:i A') }}<br>
            <strong>{{ $kot->table_or_room }}</strong>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th class="qty">Qty</th>
                <th class="name">Item</th>
                @if(isset($total)) <th class="price">Amt</th> @endif
            </tr>
        </thead>
        <tbody>
            @foreach($kot->items as $item)
            <tr>
                <td class="qty">{{ $item->qty }}</td>
                <td class="name">{{ $item->item_name }}</td>
                @if(isset($total))
                <td class="price">{{ number_format($item->qty * $item->price, 2) }}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    @if(isset($total))
    <div class="total">
        TOTAL: {{ number_format($total, 2) }}
    </div>
    @endif

    <div class="footer">
        {{ isset($total) ? 'Thank you for dining with us!' : '-- KITCHEN COPY --' }}
    </div>

</body>

</html>