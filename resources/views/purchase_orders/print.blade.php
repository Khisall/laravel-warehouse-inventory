<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $purchaseOrder->po_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print {
            body { background: white !important; color: black !important; font-size: 11pt; }
            .no-print { display: none !important; }
            .print-border { border-color: #333 !important; }
            .print-shadow-none { box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans p-4 md:p-8 min-h-screen">

    <!-- Top Action Bar (Screen Only) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('purchase-orders.show', $purchaseOrder->id) }}"
           class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold flex items-center gap-2 transition shadow">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Detail PO</span>
        </a>

        <button onclick="window.print()"
                class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-2 shadow-lg transition">
            <i class="fa-solid fa-print"></i>
            <span>Cetak / Simpan PDF (Ctrl+P)</span>
        </button>
    </div>

    <!-- Official Document Paper -->
    <div class="max-w-4xl mx-auto bg-white border border-slate-200 rounded-2xl p-8 md:p-12 shadow-xl print-shadow-none print-border">

        <!-- Kop Dokumen -->
        <div class="border-b-2 border-slate-900 pb-5 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-slate-900 text-white flex items-center justify-center text-2xl font-black">
                    IN
                </div>
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight uppercase text-slate-900">PT. INVENTORY LOGISTIK NUSANTARA</h1>
                    <p class="text-xs text-slate-500">Warehouse Storage & Modern Distribution Center</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Kawasan Industri Modern Kav. 88, Cikarang, Jawa Barat | Telp: (021) 8989-1234</p>
                </div>
            </div>

            <div class="text-left md:text-right border-t md:border-t-0 pt-2 md:pt-0">
                <h2 class="text-lg font-black text-slate-900 uppercase tracking-wider">PURCHASE ORDER</h2>
                <div class="inline-block px-3 py-1 bg-slate-100 rounded text-xs font-mono font-bold text-slate-800 mt-1">
                    NO: {{ $purchaseOrder->po_number }}
                </div>
            </div>
        </div>

        <!-- Supplier & Delivery Destination Meta -->
        <div class="grid grid-cols-2 gap-6 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs mb-6">
            <div>
                <span class="text-slate-400 text-[10px] uppercase font-bold tracking-wider block mb-1">Kepada Pemasok (Vendor):</span>
                <strong class="text-slate-900 text-sm block">{{ $purchaseOrder->supplier->name }}</strong>
                <p class="text-slate-600 mt-0.5">PIC: {{ $purchaseOrder->supplier->contact_person ?? '-' }} | Telp: {{ $purchaseOrder->supplier->phone ?? '-' }}</p>
                <p class="text-slate-500 mt-0.5">{{ $purchaseOrder->supplier->address ?? '-' }}</p>
                <div class="mt-2 inline-block px-2 py-0.5 bg-slate-200 rounded text-[10px] font-mono font-bold text-slate-700">
                    Syarat Pembayaran: {{ $purchaseOrder->supplier->payment_terms }}
                </div>
            </div>

            <div>
                <span class="text-slate-400 text-[10px] uppercase font-bold tracking-wider block mb-1">Kirim Ke (Gudang Penerima):</span>
                <strong class="text-slate-900 text-sm block">{{ $purchaseOrder->warehouse ? $purchaseOrder->warehouse->name : 'Gudang Utama' }}</strong>
                <p class="text-slate-600 mt-0.5">{{ $purchaseOrder->warehouse ? $purchaseOrder->warehouse->address : 'Kawasan Industri Modern Kav. 88, Cikarang' }}</p>
                <div class="mt-3 grid grid-cols-2 gap-2 text-[11px]">
                    <div>
                        <span class="text-slate-500 block">Tgl Order:</span>
                        <strong>{{ $purchaseOrder->order_date->format('d/m/Y') }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Estimasi Tiba:</span>
                        <strong>{{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('d/m/Y') : '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="mb-6">
            <table class="w-full text-left text-xs border border-slate-200 rounded-lg overflow-hidden">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 uppercase text-[10px]">
                    <tr>
                        <th class="py-2.5 px-3 w-8 text-center">No</th>
                        <th class="py-2.5 px-3">Kode SKU</th>
                        <th class="py-2.5 px-3">Deskripsi Barang</th>
                        <th class="py-2.5 px-3 text-center">Jumlah</th>
                        <th class="py-2.5 px-3 text-right">Harga Satuan</th>
                        <th class="py-2.5 px-3 text-right">Subtotal (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($purchaseOrder->items as $idx => $it)
                        <tr>
                            <td class="py-2.5 px-3 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-900">{{ $it->product->sku }}</td>
                            <td class="py-2.5 px-3">
                                <div class="font-bold text-slate-800">{{ $it->product->title }}</div>
                                @if($it->notes)
                                    <div class="text-[10px] text-slate-500 italic">{{ $it->notes }}</div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono font-bold">{{ $it->quantity_ordered }} {{ $it->product->unit }}</td>
                            <td class="py-2.5 px-3 text-right font-mono">Rp {{ number_format($it->unit_cost, 0, ',', '.') }}</td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format($it->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 border-t-2 border-slate-300 font-semibold text-xs">
                    <tr>
                        <td colspan="5" class="py-2 px-3 text-right text-slate-600">Subtotal Pesanan:</td>
                        <td class="py-2 px-3 text-right font-mono">Rp {{ number_format($purchaseOrder->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if($purchaseOrder->tax_amount > 0)
                        <tr>
                            <td colspan="5" class="py-1.5 px-3 text-right text-slate-600">PPN (11%):</td>
                            <td class="py-1.5 px-3 text-right font-mono">Rp {{ number_format($purchaseOrder->tax_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if($purchaseOrder->shipping_cost > 0)
                        <tr>
                            <td colspan="5" class="py-1.5 px-3 text-right text-slate-600">Biaya Pengiriman:</td>
                            <td class="py-1.5 px-3 text-right font-mono">Rp {{ number_format($purchaseOrder->shipping_cost, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr class="text-sm font-bold bg-slate-100 text-slate-900">
                        <td colspan="5" class="py-2.5 px-3 text-right uppercase">Total Nilai Purchase Order:</td>
                        <td class="py-2.5 px-3 text-right font-mono">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Instruksi Khusus -->
        <div class="mb-8 text-xs text-slate-600 bg-slate-50 p-3.5 rounded-lg border border-slate-200">
            <p class="font-bold text-slate-800 mb-0.5">Syarat & Ketentuan Penerimaan Gudang:</p>
            <ol class="list-decimal pl-4 space-y-0.5 text-[11px]">
                <li>Barang wajib disertai Surat Jalan resmi vendor dengan mencantumkan Nomor PO ini.</li>
                <li>Barang akan diperiksa oleh tim Quality Control (QC) saat bongkar muatan di dock penerimaan.</li>
                <li>Barang yang cacat / tidak sesuai spesifikasi berhak ditolak dan dicatat pada lembar GRN.</li>
            </ol>
        </div>

        <!-- Lembar Tanda Tangan 3 Pihak -->
        <div class="grid grid-cols-3 gap-6 text-center text-xs mt-10 pt-4 border-t border-slate-200">
            <div>
                <p class="text-slate-500 mb-1">Dipesan Oleh (Purchasing):</p>
                <div class="h-16 flex items-center justify-center text-slate-300 text-xs italic">
                    ( Tanda Tangan )
                </div>
                <div class="font-bold text-slate-900 underline">{{ $purchaseOrder->creator?->name ?? 'Staff Pengadaan' }}</div>
                <div class="text-[10px] text-slate-500">Purchasing Officer</div>
            </div>

            <div>
                <p class="text-slate-500 mb-1">Disetujui Oleh (Manajemen):</p>
                <div class="h-16 flex items-center justify-center text-slate-300 text-xs italic">
                    ( Tanda Tangan )
                </div>
                <div class="font-bold text-slate-900 underline">{{ $purchaseOrder->approver?->name ?? 'Direktur / Manager' }}</div>
                <div class="text-[10px] text-slate-500">Operations Director</div>
            </div>

            <div>
                <p class="text-slate-500 mb-1">Diterima & Disetujui Vendor:</p>
                <div class="h-16 flex items-center justify-center text-slate-300 text-xs italic">
                    ( Tanda Tangan & Cap )
                </div>
                <div class="font-bold text-slate-900 underline">{{ $purchaseOrder->supplier->contact_person ?? 'Pemasok / Vendor' }}</div>
                <div class="text-[10px] text-slate-500">{{ $purchaseOrder->supplier->name }}</div>
            </div>
        </div>

    </div>

</body>
</html>
