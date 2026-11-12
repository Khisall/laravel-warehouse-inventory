<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan - {{ $order->order_number }}</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Libre+Barcode+128+Text&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- JsBarcode -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-paper {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans p-6">

    <!-- Top Action Toolbar (No print) -->
    <div class="no-print max-w-4xl mx-auto mb-6 bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <a href="{{ route('orders.index') }}" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-base font-bold text-slate-900">Dokumen Surat Jalan / Delivery Order</h1>
                <p class="text-xs text-slate-500">Order Ref: {{ $order->order_number }}</p>
            </div>
        </div>

        <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-600/20 transition">
            <i class="fa-solid fa-print"></i>
            <span>Cetak Surat Jalan (Print / PDF)</span>
        </button>
    </div>

    <!-- Official Printable Paper (A4 size format) -->
    <div class="print-paper max-w-4xl mx-auto bg-white p-8 rounded-2xl shadow-sm border border-slate-200 text-slate-900 space-y-6">

        <!-- Header Company & Document Title -->
        <div class="flex items-start justify-between border-b-2 border-slate-800 pb-4">
            <div class="space-y-1">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-extrabold text-lg">
                        N
                    </div>
                    <span class="text-xl font-extrabold tracking-wider text-slate-900">INVENTORY LOGISTIC</span>
                </div>
                <p class="text-xs text-slate-600 leading-tight">
                    Kawasan Industri Pergudangan Sentral Blok B No. 12, Jakarta Timur<br>
                    Telp: (021) 8899-1000 | Email: dispatch@inventory.com
                </p>
            </div>

            <div class="text-right space-y-1">
                <h2 class="text-xl font-extrabold uppercase tracking-wide text-slate-900">SURAT JALAN</h2>
                <div class="text-xs font-mono font-bold text-slate-700">DO/{{ date('Y/m') }}/{{ $order->order_number }}</div>
                <div class="text-xs text-slate-500">Tanggal: {{ $order->created_at->format('d F Y') }}</div>
            </div>
        </div>

        <!-- Order & Delivery Info Barcode -->
        <div class="grid grid-cols-2 gap-6 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs">
            <!-- Left: Recipient Details -->
            <div class="space-y-1">
                <div class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">Tujuan Pengiriman (Penerima):</div>
                <div class="font-extrabold text-sm text-slate-900">{{ $order->customer_name }}</div>
                <div class="text-slate-700">{{ $order->customer_phone ?? '-' }}</div>
                <div class="text-slate-600 leading-relaxed mt-1">
                    {{ $order->shipping_address ?? 'Alamat diambil di gudang (Self-Pickup)' }}
                </div>
            </div>

            <!-- Right: Order & Courier Info -->
            <div class="space-y-1 text-right flex flex-col justify-between items-end">
                <div>
                    <div class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">Ekspedisi / Channel:</div>
                    <div class="font-bold text-slate-800 text-xs">{{ $order->courier ?? 'Standard Courier' }} ({{ $order->marketplace }})</div>
                    @if($order->tracking_number)
                        <div class="font-mono font-bold text-emerald-700 text-xs mt-0.5">Resi: {{ $order->tracking_number }}</div>
                    @endif
                </div>

                <!-- Barcode of Order Number -->
                <div class="pt-2">
                    <svg id="order-barcode"></svg>
                </div>
            </div>
        </div>

        <!-- Table Items -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-y-2 border-slate-800 bg-slate-100 text-slate-800 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-2.5 px-3 w-10 text-center">No</th>
                        <th class="py-2.5 px-3 w-28">SKU</th>
                        <th class="py-2.5 px-3">Nama Barang / Deskripsi</th>
                        <th class="py-2.5 px-3">Lokasi Rak</th>
                        <th class="py-2.5 px-3 text-center w-20">Jumlah</th>
                        <th class="py-2.5 px-3 w-16 text-center">Satuan</th>
                        <th class="py-2.5 px-3 w-16 text-center">QC Check</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($order->items as $index => $item)
                        <tr>
                            <td class="py-3 px-3 text-center font-medium">{{ $index + 1 }}</td>
                            <td class="py-3 px-3 font-mono font-bold text-slate-900">{{ $item->sku }}</td>
                            <td class="py-3 px-3">
                                <div class="font-bold text-slate-900">{{ $item->product_title }}</div>
                                @if($item->product?->batch_number)
                                    <div class="text-[10px] text-slate-500 font-mono">Batch: {{ $item->product->batch_number }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-3 font-semibold text-slate-700">{{ $item->product?->warehouse_location ?? '-' }}</td>
                            <td class="py-3 px-3 text-center font-extrabold text-sm text-slate-900">{{ $item->quantity }}</td>
                            <td class="py-3 px-3 text-center text-slate-600">Pcs</td>
                            <td class="py-3 px-3 text-center">
                                <span class="inline-block w-4 h-4 border border-slate-400 rounded"></span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-4 text-center text-slate-500">Tidak ada item dalam pesanan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-800 font-bold">
                        <td colspan="4" class="py-2.5 px-3 text-right uppercase">Total Kuantiti Barang:</td>
                        <td class="py-2.5 px-3 text-center text-sm font-extrabold text-slate-900">
                            {{ $order->items->sum('quantity') }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Notes -->
        <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-200">
            <strong>Catatan Petugas:</strong>
            <p class="mt-0.5 text-slate-500">{{ $order->notes ?? 'Mohon periksa segel dan kuantiti fisik barang saat serah terima. Kerusakan setelah serah terima tanpa bukti video unboxing menjadi tanggung jawab pihak kurir/penerima.' }}</p>
        </div>

        <!-- 3 Signature Boxes -->
        <div class="grid grid-cols-3 gap-6 pt-6 text-center text-xs">
            <div>
                <div class="text-slate-500 text-[11px] mb-1">Diserahkan Oleh:</div>
                <div class="font-bold text-slate-800 uppercase tracking-wide">Petugas Gudang</div>
                <div class="h-16 border-b border-dashed border-slate-400 mt-2"></div>
                <div class="text-[10px] text-slate-400 mt-1">( Tanda Tangan & Nama Terang )</div>
            </div>

            <div>
                <div class="text-slate-500 text-[11px] mb-1">Dibawa Oleh:</div>
                <div class="font-bold text-slate-800 uppercase tracking-wide">Sopir / Kurir Ekspedisi</div>
                <div class="h-16 border-b border-dashed border-slate-400 mt-2"></div>
                <div class="text-[10px] text-slate-400 mt-1">( Tanda Tangan & Nama Terang )</div>
            </div>

            <div>
                <div class="text-slate-500 text-[11px] mb-1">Diterima Oleh:</div>
                <div class="font-bold text-slate-800 uppercase tracking-wide">Customer / Penerima</div>
                <div class="h-16 border-b border-dashed border-slate-400 mt-2"></div>
                <div class="text-[10px] text-slate-400 mt-1">( Tanda Tangan & Nama Terang )</div>
            </div>
        </div>

    </div>

    <!-- Script to render Order Barcode -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            try {
                JsBarcode("#order-barcode", "{{ $order->order_number }}", {
                    format: "CODE128",
                    lineColor: "#000",
                    width: 1.3,
                    height: 28,
                    displayValue: true,
                    fontSize: 9,
                    margin: 0
                });
            } catch(e) {
                console.error("Barcode generation error", e);
            }
        });
    </script>
</body>
</html>
