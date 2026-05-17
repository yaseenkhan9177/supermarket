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
            width: 80mm;
            /* Standard thermal paper width */
            margin: 0 auto;
            padding: 10px;
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }

        .store-name {
            font-size: 16px;
            font-weight: bold;
        }

        .meta {
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        th,
        td {
            text-align: left;
        }

        .totals {
            text-align: right;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }

        .footer {
            text-align: center;
            margin-top: 10px;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <div class="header">
        <div class="store-name">OwnStore PRO</div>
        <div>Retail Invoice</div>
    </div>

    <div class="meta">
        <div>Inv: {{ $sale->invoice_no }}</div>
        <div>Date: {{ $sale->sale_date }}</div>
        <div>Salesman: {{ $sale->salesman->name ?? 'N/A' }}</div>
        <div>Customer: {{ $sale->customer->name ?? 'Walk-in' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th style="text-align:center">Qty</th>
                <th style="text-align:right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td>{{ $item->item_name }}</td>
                <td style="text-align:center">{{ $item->quantity }}</td>
                <td style="text-align:right">{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div><strong>Total: {{ number_format($sale->grand_total, 2) }}</strong></div>
        <div>Cash: {{ number_format($sale->cash_received, 2) }}</div>
        <div>Change: {{ number_format($sale->change_returned, 2) }}</div>
    </div>

    <div class="footer">
        <div>Thank you for shopping!</div>
        <div>No Refund / No Exchange</div>
    </div>

    <div style="text-align: center; margin-top: 10px;">
        <a href="{{ route('cash-sales.create') }}" style="text-decoration: none; font-weight: bold; font-family: sans-serif;">[ New Sale ]</a>
    </div>

    <div id="flash-message" data-success="{{ session('success') }}" style="display:none;"></div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.getElementById('flash-message').getAttribute('data-success');

            if (successMessage) {
                Swal.fire({
                    title: 'Success!',
                    text: successMessage,
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false
                }).then(() => {
                    window.print();
                });
            } else {
                window.print();
            }
        });
    </script>
</body>

</html>