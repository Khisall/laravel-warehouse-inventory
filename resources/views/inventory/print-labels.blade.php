<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Label Barcode - INVENTORY</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Libre+Barcode+128+Text&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- JsBarcode (Vector SVG Barcode Generator) -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

    <!-- QRCode.js (QR Code Generator) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .label-card {
                box-shadow: none !important;
                border: 1px dashed #bbb !important;
                page-break-inside: avoid;
            }
        }

        .label-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }

        .barcode-svg {
            width: 100%;
            height: 48px;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans p-6">

    <!-- Top Action Toolbar (Hidden when printing) -->
    <div class="no-print max-w-5xl mx-auto mb-6 bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <a href="{{ route('inventory.index') }}" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-lg font-bold text-slate-900">Cetak Label Barcode & QR Code</h1>
                <p class="text-xs text-slate-500">{{ $products->count() }} jenis barang siap dicetak untuk stiker fisik gudang</p>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-600/20 transition">
                <i class="fa-solid fa-print"></i>
                <span>Cetak Label Sekarang (Print / PDF)</span>
            </button>
        </div>
    </div>

    <!-- Sticker Labels Grid Container -->
    <div class="max-w-5xl mx-auto label-grid">
        @foreach($products as $product)
            <div class="label-card bg-white border-2 border-slate-300 rounded-xl p-3.5 flex flex-col justify-between shadow-sm relative overflow-hidden" style="width: 290px; height: 180px;">
                <!-- Header Label -->
                <div class="flex items-center justify-between border-b border-slate-200 pb-1.5 mb-1.5">
                    <span class="text-[10px] font-extrabold tracking-wider uppercase text-emerald-700 flex items-center gap-1">
                        <i class="fa-solid fa-box text-emerald-600"></i> INVENTORY WAREHOUSE
                    </span>
                    <span class="text-[10px] font-bold text-slate-500">
                        {{ $product->category?->name ?? 'GENERAL' }}
                    </span>
                </div>

                <!-- Product Name -->
                <div class="text-xs font-bold text-slate-900 leading-snug truncate" title="{{ $product->title }}">
                    {{ $product->title }}
                </div>

                <!-- Middle: Barcode CODE128 & QR Code -->
                <div class="flex items-center justify-between my-1">
                    <div class="flex-1 pr-2">
                        <!-- SVG Barcode rendered by JsBarcode -->
                        <svg id="barcode-{{ $product->id }}" class="barcode-svg"></svg>
                    </div>

                    <div class="w-12 h-12 flex-shrink-0 flex items-center justify-center border border-slate-200 rounded p-0.5 bg-white">
                        <div id="qrcode-{{ $product->id }}" class="w-full h-full"></div>
                    </div>
                </div>

                <!-- Footer details: Rack Location, Batch, Price -->
                <div class="pt-1.5 border-t border-slate-200 flex items-center justify-between text-[11px] leading-tight">
                    <div>
                        <div class="text-[9px] text-slate-400 font-bold uppercase">Lokasi Rak</div>
                        <div class="font-extrabold text-slate-800">{{ $product->warehouse_location }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-[9px] text-slate-400 font-bold uppercase">Batch No.</div>
                        <div class="font-mono font-semibold text-slate-600 text-[10px]">{{ $product->batch_number ?? '-' }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[9px] text-slate-400 font-bold uppercase">Harga</div>
                        <div class="font-extrabold text-emerald-700 text-xs">${{ number_format($product->price, 2) }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Client-side Barcode & QR Code Initializer -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @foreach($products as $product)
                // Generate Code128 Barcode
                try {
                    JsBarcode("#barcode-{{ $product->id }}", "{{ $product->sku }}", {
                        format: "CODE128",
                        lineColor: "#000",
                        width: 1.5,
                        height: 32,
                        displayValue: true,
                        fontSize: 10,
                        margin: 0,
                        textMargin: 1
                    });
                } catch(e) {
                    console.error("Error generating barcode for {{ $product->sku }}", e);
                }

                // Generate QR Code
                try {
                    new QRCode(document.getElementById("qrcode-{{ $product->id }}"), {
                        text: "{{ $product->sku }}",
                        width: 44,
                        height: 44,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.M
                    });
                } catch(e) {
                    console.error("Error generating QR code for {{ $product->sku }}", e);
                }
            @endforeach
        });
    </script>
</body>
</html>
