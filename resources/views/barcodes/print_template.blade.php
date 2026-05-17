<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Labels</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>

    <style>
        /* RESET BROWSER DEFAULTS */
        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
        }

        /* GRID LAYOUT */
        .label-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            gap: var(--gap);
            padding: 2mm;
        }

        /* INDIVIDUAL STICKER CARD */
        .sticker {
            width: calc(100% / var(--per-row) - var(--gap));
            height: auto;
            /* Or fixed height like 25mm */
            border: 1px dotted #ccc;
            /* Helper border, remove for production */
            text-align: center;
            padding: 5px;
            box-sizing: border-box;
            page-break-inside: avoid;
            /* Prevent cutting a label in half */
        }

        /* HIDE BORDER ON PRINT */
        @media print {
            .sticker {
                border: none;
            }

            @page {
                margin: 0;
            }

            /* Remove browser headers/footers */
        }

        /* TYPOGRAPHY */
        .store-name {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .item-name {
            font-size: 10px;
            margin: 2px 0;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .price {
            font-size: 12px;
            font-weight: bold;
        }

        .expiry {
            font-size: 8px;
        }

        /* BARCODE CANVAS */
        canvas {
            max-width: 100%;
            height: 40px;
        }
    </style>
</head>

<body style="--gap: {{ $settings['gap'] ?? 2 }}mm; --per-row: {{ $settings['per_row'] ?? 2 }};">

    <div class="label-container">
        @foreach($items as $item)
        @for($i = 0; $i < ($item['qty'] ?? 1); $i++)
            <div class="sticker">
            <div class="store-name">OWNSTORE PRO</div>

            <div class="item-name">{{ $item['name'] }}</div>

            <canvas class="barcode"
                data-code="{{ $item['barcode'] }}"
                data-type="{{ $settings['type'] }}">
            </canvas>

            <div style="display: flex; justify-content: space-between; align-items: center; padding: 0 5px;">
                @if($settings['show_price'])
                <span class="price">Rs. {{ $item['price'] }}</span>
                @endif
                @if($settings['show_expiry'] && !empty($item['expiry']))
                <span class="expiry">Exp: {{ $item['expiry'] }}</span>
                @endif
            </div>
    </div>
    @endfor
    @endforeach
    </div>

    <script>
        // 1. Generate Barcodes
        document.querySelectorAll('.barcode').forEach(function(canvas) {
            JsBarcode(canvas, canvas.getAttribute('data-code'), {
                format: canvas.getAttribute('data-type') === 'QR' ? 'QR' : 'CODE128',
                lineColor: "#000",
                width: 1.5,
                height: 35,
                displayValue: true,
                fontSize: 10,
                margin: 0
            });
        });

        // 2. Auto Print
        window.onload = function() {
            window.print();
        }
    </script>

</body>

</html>