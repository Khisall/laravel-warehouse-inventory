<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan Mutasi {{ $transfer->transfer_number }} - PT. Inventory Logistik Nusantara</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts Inter & JetBrains Mono -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- JsBarcode -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        @media print {
            body {
                background: white !important;
                color: black !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-border {
                border-color: #94a3b8 !important;
            }
            .print-shadow-none {
                box-shadow: none !important;
            }
            @page {
                size: A4;
                margin: 12mm 15mm;
            }
        }
    </style>
</head>
<body class="p-4 md:p-8">

    <!-- Action Bar (Hidden on Print) -->
    <div class="max-w-4xl mx-auto mb-6 no-print flex items-center justify-between bg-slate-900 text-white p-4 rounded-2xl shadow-xl">
        <div class="flex items-center gap-3">
            <a href="{{ route('transfers.show', $transfer->id) }}"
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold flex items-center gap-2 transition">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Detail Mutasi</span>
            </a>
            <div class="text-xs text-slate-400">
                Mode Cetak Dokumen Resmi: <strong class="text-white">Surat Jalan Transfer Antar Gudang</strong>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()"
                    class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-2 shadow-lg transition">
                <i class="fa-solid fa-print"></i>
                <span>Cetak / Simpan PDF (Ctrl+P)</span>
            </button>
        </div>
    </div>

    <!-- Official Document Paper -->
    <div class="max-w-4xl mx-auto bg-white border border-slate-200 rounded-2xl p-8 md:p-10 shadow-xl print-shadow-none print-border">

        <!-- Kop Surat -->
        <div class="border-b-2 border-slate-900 pb-5 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-slate-900 text-white flex items-center justify-center text-2xl font-black">
                    IN
                </div>
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight uppercase text-slate-900">PT. INVENTORY LOGISTIK NUSANTARA</h1>
                    <p class="text-xs text-slate-600">Warehouse Storage & Modern Distribution Center</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Kawasan Industri Modern Kav. 88, Cikarang, Jawa Barat | Telp: (021) 8989-1234</p>
                </div>
            </div>

            <!-- Barcode & Document Metadata -->
            <div class="text-left md:text-right border-t md:border-t-0 pt-2 md:pt-0">
                <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">SURAT JALAN TRANSFER</h2>
                <div class="text-xs text-slate-600 mt-0.5 font-mono">
                    NO: <span class="font-bold text-slate-900">{{ $transfer->transfer_number }}</span>
                </div>
                <div class="flex justify-end mt-1">
                    <svg id="transfer-barcode" class="max-h-12"></svg>
                </div>
            </div>
        </div>

        <!-- Document Subtitle -->
        <div class="text-center my-4 pb-3 border-b border-slate-200">
            <h3 class="text-base font-black uppercase tracking-wider text-slate-900">
                SURAT JALAN PENGIRIMAN &amp; SERAH TERIMA BARANG ANTAR CABANG
            </h3>
            <p class="text-[11px] text-slate-500 mt-0.5">
                Dokumen resmi bukti pengeluaran, pengangkutan, dan penerimaan fisik barang antar lokasi gudang
            </p>
        </div>

        <!-- Shipping & Warehouse Metadata Grid -->
        <div class="grid grid-cols-2 gap-4 mb-6 text-xs">
            <!-- Gudang Asal -->
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Gudang Asal (Pengirim):</p>
                <h4 class="font-bold text-slate-900 text-sm">{{ $transfer->sourceWarehouse->name }} ({{ $transfer->sourceWarehouse->code }})</h4>
                <p class="text-[11px] text-slate-600 mt-1">
                    {{ $transfer->sourceWarehouse->address ?: $transfer->sourceWarehouse->location ?: 'Alamat Pusat Operasional' }}
                </p>
                <p class="text-[10px] text-slate-500 mt-1">
                    Petugas Dispatch: <strong>{{ $transfer->dispatcher?->name ?: $transfer->creator?->name ?: 'Staff Gudang' }}</strong>
                </p>
            </div>

            <!-- Gudang Tujuan -->
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Gudang Tujuan (Penerima):</p>
                <h4 class="font-bold text-slate-900 text-sm">{{ $transfer->destinationWarehouse->name }} ({{ $transfer->destinationWarehouse->code }})</h4>
                <p class="text-[11px] text-slate-600 mt-1">
                    {{ $transfer->destinationWarehouse->address ?: $transfer->destinationWarehouse->location ?: 'Alamat Cabang Distribusi' }}
                </p>
                <p class="text-[10px] text-slate-500 mt-1">
                    Kepala Gudang Penerima: <strong>{{ $transfer->destinationWarehouse->manager_name ?: '-' }}</strong>
                </p>
            </div>
        </div>

        <!-- Logistics & Transporter Details -->
        <div class="grid grid-cols-4 gap-3 bg-slate-100/80 p-3 rounded-xl border border-slate-200 text-xs mb-6">
            <div>
                <span class="text-[10px] text-slate-500 uppercase block font-semibold">Tgl Pengiriman:</span>
                <span class="font-bold text-slate-900">{{ $transfer->transfer_date?->translatedFormat('d F Y') }}</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-500 uppercase block font-semibold">Estimasi Tiba:</span>
                <span class="font-bold text-slate-900">{{ $transfer->expected_arrival_date ? $transfer->expected_arrival_date->translatedFormat('d F Y') : '-' }}</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-500 uppercase block font-semibold">Pengemudi / No Plat:</span>
                <span class="font-bold text-slate-900">{{ $transfer->driver_name ?: '-' }} ({{ $transfer->vehicle_plate_number ?: '-' }})</span>
            </div>
            <div>
                <span class="text-[10px] text-slate-500 uppercase block font-semibold">Ekspedisi / Resi:</span>
                <span class="font-bold text-slate-900">{{ $transfer->expedition ?: 'Internal' }} {{ $transfer->tracking_number ? '• ' . $transfer->tracking_number : '' }}</span>
            </div>
        </div>

        <!-- Items Table -->
        <div class="border border-slate-300 rounded-xl overflow-hidden mb-6">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-200/80 text-slate-800 font-bold border-b border-slate-300 text-[11px] uppercase tracking-wider">
                        <th class="py-2.5 px-3 w-10 text-center">No</th>
                        <th class="py-2.5 px-3">Kode SKU</th>
                        <th class="py-2.5 px-3">Deskripsi Barang</th>
                        <th class="py-2.5 px-3 text-center">Batch / Exp</th>
                        <th class="py-2.5 px-3 text-center w-24">Jumlah Kirim</th>
                        <th class="py-2.5 px-3 text-center w-28">Kondisi Cek Fisik</th>
                        <th class="py-2.5 px-3">Catatan Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($transfer->items as $i => $item)
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 px-3 text-center font-mono text-slate-600">{{ $i + 1 }}</td>
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-900">{{ $item->sku }}</td>
                            <td class="py-2.5 px-3 font-semibold text-slate-800">{{ $item->product_title }}</td>
                            <td class="py-2.5 px-3 text-center font-mono text-[10px] text-slate-600">
                                {{ $item->batch_number ?: '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-900">
                                {{ $item->quantity }} {{ $item->unit }}
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                <div class="inline-flex items-center gap-2 text-[10px] text-slate-600">
                                    <span>[ &nbsp; ] Baik</span>
                                    <span>[ &nbsp; ] Rusak</span>
                                </div>
                            </td>
                            <td class="py-2.5 px-3 text-[11px] text-slate-600 italic">
                                {{ $item->notes ?: '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300">
                    <tr>
                        <td colspan="4" class="py-2.5 px-3 text-right uppercase text-slate-700">Total Muatan Fisik:</td>
                        <td class="py-2.5 px-3 text-center font-mono text-slate-900">{{ $transfer->total_quantity }} Unit</td>
                        <td colspan="2" class="py-2.5 px-3 text-[11px] text-slate-500">{{ $transfer->total_items_count }} SKU Terdaftar</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Syarat & Ketentuan Logistik -->
        <div class="mb-6 text-[10px] text-slate-500 bg-slate-50 p-3 rounded-xl border border-slate-200 space-y-1">
            <p class="font-bold text-slate-700">Ketentuan &amp; Tata Tertib Pengiriman Antar Gudang:</p>
            <ol class="list-decimal pl-4 space-y-0.5">
                <li>Barang telah diserahkan dari gudang asal dalam kondisi baik, jumlah sesuai, dan tersegel rapi.</li>
                <li>Pengemudi/Transporter bertanggung jawab penuh atas keutuhan muatan fisik selama dalam perjalanan hingga tiba di gudang tujuan.</li>
                <li>Petugas penerima di gudang tujuan wajib melakukan pemeriksaan fisik (jumlah &amp; mutu) secara langsung sebelum menandatangani lembar serah terima ini.</li>
                <li>Segala bentuk selisih kuantitas atau kerusakan wajib dicatat pada surat jalan dan dilaporkan kepada Manajer Operasional dalam tempo 1x24 jam.</li>
            </ol>
        </div>

        <!-- Lembar Tanda Tangan 4 Pihak -->
        <div class="grid grid-cols-4 gap-4 text-center text-xs mt-8 pt-4 border-t border-slate-200">
            <div>
                <p class="text-slate-500 mb-1 text-[11px]">Yang Menyerahkan (Gudang Asal):</p>
                <div class="h-14 flex items-center justify-center text-slate-300 text-[10px] italic">
                    ( Tanda Tangan &amp; Cap )
                </div>
                <div class="font-bold text-slate-900 underline">{{ $transfer->dispatcher?->name ?: $transfer->creator?->name ?: 'Staff Dispatch' }}</div>
                <div class="text-[10px] text-slate-500">Inventory Dispatcher</div>
            </div>

            <div>
                <p class="text-slate-500 mb-1 text-[11px]">Yang Mengangkut (Transporter):</p>
                <div class="h-14 flex items-center justify-center text-slate-300 text-[10px] italic">
                    ( Tanda Tangan )
                </div>
                <div class="font-bold text-slate-900 underline">{{ $transfer->driver_name ?: 'Driver / Ekspedisi' }}</div>
                <div class="text-[10px] text-slate-500">{{ $transfer->vehicle_plate_number ?: 'Kurir Armada' }}</div>
            </div>

            <div>
                <p class="text-slate-500 mb-1 text-[11px]">Yang Menerima (Gudang Tujuan):</p>
                <div class="h-14 flex items-center justify-center text-slate-300 text-[10px] italic">
                    ( Tanda Tangan &amp; Cap )
                </div>
                <div class="font-bold text-slate-900 underline">{{ $transfer->receiver?->name ?: '(...........................)' }}</div>
                <div class="text-[10px] text-slate-500">Staff Penerima Tujuan</div>
            </div>

            <div>
                <p class="text-slate-500 mb-1 text-[11px]">Mengetahui (Kepala Gudang):</p>
                <div class="h-14 flex items-center justify-center text-slate-300 text-[10px] italic">
                    ( Tanda Tangan &amp; Cap )
                </div>
                <div class="font-bold text-slate-900 underline">Budi Santoso</div>
                <div class="text-[10px] text-slate-500">Warehouse Operations Manager</div>
            </div>
        </div>

    </div>

    <!-- Generate Vector Barcode with JsBarcode -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            JsBarcode("#transfer-barcode", "{{ $transfer->transfer_number }}", {
                format: "CODE128",
                width: 1.4,
                height: 38,
                displayValue: true,
                fontSize: 11,
                font: "JetBrains Mono",
                lineColor: "#0f172a",
                margin: 2
            });
        });
    </script>

</body>
</html>
