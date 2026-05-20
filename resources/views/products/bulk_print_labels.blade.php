<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print Label QR Code Massal</title>
    <style>
        @page {
            size: 70mm 35mm;
            margin: 0;
        }
        
        html, body {
            margin: 0;
            padding: 0;
            width: 70mm;
            height: 35mm;
            font-family: 'Arial', sans-serif;
            background-color: #fff;
            -webkit-print-color-adjust: exact;
        }

        .label-item {
            width: 70mm;
            height: 35mm;
            box-sizing: border-box;
            padding: 2.5mm;
            display: flex;
            align-items: center;
            justify-content: space-between;
            page-break-after: always;
            overflow: hidden;
        }

        .qr-section {
            width: 28mm;
            height: 28mm;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-section img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .info-section {
            width: 36mm;
            height: 30mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding-left: 1mm;
            box-sizing: border-box;
        }

        .sku-text {
            font-size: 11px;
            font-weight: bold;
            margin: 0 0 2px 0;
            color: #000;
            letter-spacing: 0.5px;
        }

        .name-text {
            font-size: 9px;
            font-weight: bold;
            margin: 0 0 4px 0;
            line-height: 1.1;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .meta-text {
            font-size: 7.5px;
            margin: 1px 0;
            color: #444;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    @foreach($products as $product)
        @php
            $auditUrl = url('/product/audit/' . $product->sku); 
            $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=0&data=" . urlencode($auditUrl);
        @endphp

        <div class="label-item">
            <div class="qr-section">
                <img src="{{ $qrCodeUrl }}" alt="QR Code {{ $product->sku }}">
            </div>

            <div class="info-section">
                <div class="sku-text">{{ $product->sku }}</div>
                <div class="name-text">{{ $product->name }}</div>
                <div class="meta-text">Cat: {{ $product->category->name ?? '-' }}</div>
                <div class="meta-text">Div: {{ $product->division->name ?? '-' }}</div>
                <div class="meta-text">Cond: {{ strtoupper($product->condition) }}</div>
            </div>
        </div>
    @endforeach

    <script>
        window.onload = function() {
            window.print();
            setTimeout(function() {
                window.close();
            }, 500);
        };
    </script>
</body>
</html>