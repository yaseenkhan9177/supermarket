<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $receipt->receipt_no }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            max-width: 80mm;
            margin: 0 auto;
            color: #000;
            padding: 10px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        .border-b {
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }

        .flex {
            display: flex;
            justify-content: space-between;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }

        th,
        td {
            text-align: left;
            padding: 2px 0;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="text-center bold mb-2" style="font-size: 16px;">
        {{ config('app.name', 'OWN STORE POS') }}
    </div>
    <div class="text-center border-b">
        PAYMENT RECEIPT
    </div>

    <div class="flex">
        <span>No: {{ $receipt->receipt_no }}</span>
        <span>Date: {{ $receipt->receipt_date->format('d/m/Y') }}</span>
    </div>
    <div class="border-b">
        Customer: <b>{{ $receipt->customer->name }}</b>
    </div>

    <div class="border-b">
        <div class="flex">
            <span>Amount Received:</span>
            <span class="bold">{{ number_format($receipt->amount_received, 2) }}</span>
        </div>
        @if($receipt->discount_given > 0)
        <div class="flex">
            <span>Discount:</span>
            <span>{{ number_format($receipt->discount_given, 2) }}</span>
        </div>
        @endif
        <div class="flex" style="margin-top: 5px;">
            <span>Mode: {{ $receipt->payment_mode }}</span>
        </div>
        @if($receipt->memo)
        <div style="font-size: 11px; margin-top:2px;">Memo: {{ $receipt->memo }}</div>
        @endif
    </div>

    <div class="bold" style="margin-top: 5px;">Allocated Against:</div>
    <table>
        @foreach($receipt->allocations as $alloc)
        <tr>
            <td>{{ $alloc->debitSale->invoice_no }}</td>
            <td class="text-right">{{ number_format($alloc->allocated_amount, 2) }}</td>
        </tr>
        @endforeach
    </table>

    <div class="border-b" style="margin-top: 5px;"></div>

    <div class="flex bold" style="font-size: 14px;">
        <span>TOTAL ADJUSTED:</span>
        <span>{{ number_format($receipt->allocations->sum('allocated_amount'), 2) }}</span>
    </div>

    <div class="text-center" style="margin-top: 20px; font-size: 11px;">
        Received By: {{ $receipt->creator->name ?? 'System' }}<br>
        Thank you for your business!
    </div>

    <div class="no-print text-center" style="margin-top: 20px;">
        <button onclick="window.print()" style="padding: 8px 16px; cursor: pointer;">Print Again</button>
        <button onclick="window.close()" style="padding: 8px 16px; cursor: pointer;">Close</button>
        <a href="{{ route('receipts.create') }}" style="padding: 8px 16px; text-decoration: none; border: 1px solid #ccc; background: #eee; color: #000; display: inline-block;">New Receipt</a>
    </div>

</body>

</html>