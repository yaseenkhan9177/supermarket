<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $sale->receipt_no }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: 80mm;
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

        .details {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 5px 0;
            margin: 5px 0;
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
        <div>Payment Receipt</div>
    </div>

    <div class="meta">
        <div>Rcpt #: {{ $sale->receipt_no }}</div>
        <div>Date: {{ $sale->receipt_date }}</div>
        <div>Customer: {{ $sale->customer->name ?? 'N/A' }}</div>
    </div>

    <div class="details">
        <div><strong>Amount Received: {{ number_format($sale->amount_received, 2) }}</strong></div>
        @if($sale->discount_given > 0)
        <div>Discount: {{ number_format($sale->discount_given, 2) }}</div>
        @endif
        <div>Total Adjusted: {{ number_format($sale->total_adjusted, 2) }}</div>
        <br>
        <div>Mode: {{ $sale->payment_mode }}</div>
        @if($sale->payment_mode != 'Cash')
        <div>Ref: {{ $sale->cheque_no }}</div>
        <div>Bank: {{ $sale->bank_name }}</div>
        <div>Date: {{ $sale->cheque_date }}</div>
        @endif
    </div>

    <div class="footer">
        <div>Thank you for your payment!</div>
    </div>

    <div style="text-align: center; margin-top: 10px;">
        <a href="{{ route('receipts.create') }}" style="text-decoration: none; font-weight: bold; font-family: sans-serif;">[ New Receipt ]</a>
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