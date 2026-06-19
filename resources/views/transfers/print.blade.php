<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $transfer->transfer_no }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 80mm;
            margin: 0 auto;
            color: #000;
        }

        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
        }

        .details {
            font-size: 12px;
            margin-bottom: 10px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .amount {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 10px 0;
        }

        .signatures {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .sig-line {
            border-top: 1px solid #000;
            width: 45%;
            text-align: center;
            font-size: 10px;
            padding-top: 5px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }

        .btn-print {
            display: block;
            width: 100%;
            padding: 10px;
            background: #9333ea;
            color: white;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>

<body>

    <a href="#" onclick="window.print()" class="btn-print no-print">PRINT RECEIPT</a>

    <div class="header">
        <div class="title">OwnStore PRO</div>
        <div>Internal Transfer Slip</div>
        <div>{{ $transfer->transfer_date }}</div>
    </div>

    <div class="details">
        <div class="row">
            <span>Ticket #:</span>
            <span>{{ $transfer->transfer_no }}</span>
        </div>
        <div class="row">
            <span>Operator:</span>
            <span>{{ $transfer->user->name ?? 'N/A' }}</span>
        </div>
    </div>

    <div class="details">
        <div><strong>FROM (Withdraw):</strong></div>
        <div>{{ $transfer->from_account }}</div>
        <br>
        <div><strong>TO (Deposit):</strong></div>
        <div>{{ $transfer->to_account }}</div>
    </div>

    <div class="amount">
        PKR {{ number_format($transfer->amount, 2) }}
    </div>

    <div class="details">
        <div><strong>Purpose:</strong></div>
        <div>{{ $transfer->purpose ?? 'N/A' }}</div>
    </div>

    <div class="signatures">
        <div class="sig-line">
            Handed Over By
        </div>
        <div class="sig-line">
            Received By
        </div>
    </div>

</body>

</html>