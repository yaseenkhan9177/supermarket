<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Refund Receipt - {{ $refund->credit_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            max-width: 300px;
            margin: 20px auto;
            padding: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 14px;
            margin-bottom: 3px;
        }

        .section {
            margin: 10px 0;
        }

        .section-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }

        .row {
            display: flex;
            justify-between;
            margin: 3px 0;
        }

        .row.bold {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        table th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 3px 0;
            font-weight: bold;
        }

        table td {
            padding: 3px 0;
        }

        .total-row {
            border-top: 2px solid #000;
            border-bottom: 2px double #000;
            font-weight: bold;
            font-size: 14px;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            border-top: 2px dashed #000;
            padding-top: 10px;
            font-size: 10px;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border: 1px solid #000;
            font-size: 10px;
            margin-left: 5px;
        }

        @media print {
            body {
                margin: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>🔄 REFUND RECEIPT</h1>
        <h2>{{ config('app.name', 'OwnStore POS') }}</h2>
        <div>{{ now()->format('d M Y, h:i A') }}</div>
    </div>

    <div class="section">
        <div class="row bold">
            <span>Refund ID:</span>
            <span>{{ $refund->credit_no }}</span>
        </div>
        <div class="row">
            <span>Original Invoice:</span>
            <span>{{ $refund->originalSale->invoice_no ?? 'N/A' }}</span>
        </div>
        <div class="row">
            <span>Customer:</span>
            <span>{{ $refund->customer->name ?? 'Walk-in' }}</span>
        </div>
        <div class="row">
            <span>Refund Date:</span>
            <span>{{ \Carbon\Carbon::parse($refund->refund_date)->format('d M Y') }}</span>
        </div>
        <div class="row">
            <span>Refund Mode:</span>
            <span>{{ str_replace('_', ' ', $refund->refund_mode) }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">REFUNDED ITEMS</div>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($refund->items as $item)
                <tr>
                    <td>
                        {{ $item->item_name }}
                        @if($item->reason)
                        <br><small style="font-size: 10px;">{{ $item->reason }}</small>
                        @endif
                        @if($item->condition === 'waste')
                        <span class="badge">WASTE</span>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ number_format($item->quantity, 2) }}</td>
                    <td style="text-align: right;">Rs {{ number_format($item->net_amount, 2) }}</td>
                </tr>
                @if($item->note)
                <tr>
                    <td colspan="3" style="font-size: 10px; font-style: italic; padding-left: 10px;">
                        Note: {{ $item->note }}
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="row total-row">
            <span>TOTAL REFUND:</span>
            <span>Rs {{ number_format($refund->total_amount, 2) }}</span>
        </div>
    </div>

    @if($refund->authorizedBy)
    <div class="section">
        <div class="row">
            <span>Approved By:</span>
            <span>{{ $refund->authorizedBy->full_name ?? 'Manager' }}</span>
        </div>
        <div class="row">
            <span>Approval Time:</span>
            <span>{{ $refund->approval_timestamp ? \Carbon\Carbon::parse($refund->approval_timestamp)->format('h:i A') : 'N/A' }}</span>
        </div>
    </div>
    @endif

    <div class="section">
        <div class="row">
            <span>Processed By:</span>
            <span>{{ $refund->processedBy->name ?? $refund->salesman->name ?? 'Cashier' }}</span>
        </div>
    </div>

    <div class="footer">
        <div style="margin-bottom: 5px;">*** REFUND TRANSACTION ***</div>
        <div>This is a computer-generated receipt.</div>
        <div>No signature required.</div>
        <div style="margin-top: 10px;">Thank you for your business!</div>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">
            🖨️ Print Receipt
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; cursor: pointer; margin-left: 10px;">
            ❌ Close
        </button>
    </div>

    <script>
        // Auto-print on load (optional)
        // window.onload = function() { window.print(); };
    </script>
</body>

</html>