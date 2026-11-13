<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Penerimaan Barang - {{ $goodsReceipt->receipt_number }}</title>
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
        <a href="{{ route('goods-receipts.show', $goodsReceipt->id) }}"
           class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold flex items-center gap-2 transition shadow">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Detail GRN</span>
        </a>

        <button onclick="window.print()"
                class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-2 shadow-lg transition">
            <i class="fa-solid fa-print"></i>
            <span>Cetak / Simpan PDF (Ctrl+P)</span>
        </button>
    </div>

    <!-- Official Document Paper -->
    <div class="max-w-4xl mx-auto bg-white border border-slate-200 rounded-2xl p-8 md:p-12 shadow-xl print-shadow-none print-border">

        <!-- Kop Surat -->
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
                <h2 class="text-lg font-black text-slate-900 uppercase tracking-wider">BUKTI PENERIMAAN BARANG</h2>
                <div class="inline-block px-3 py-1 bg-slate-100 rounded text-xs font-mono font-bold text-slate-800 mt-1">
                    NO: {{ $goodsReceipt->receipt_number }}
                </div>
            </div>
        </div>

        <!-- Metadata Penerimaan -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs mb-6">
            <div>
                <span class="text-slate-500 text-[10px] uppercase font-bold block mb-0.5">Surat Jalan Vendor:</span>
                <strong class="text-slate-900 font-mono text-sm block">{{ $goodsReceipt->vendor_do_number }}</strong>
            </div>
            <div>
                <span class="text-slate-500 text-[10px] uppercase font-bold block mb-0.5">No. Referensi PO:</span>
                <strong class="text-slate-900 font-mono text-sm block">{{ $goodsReceipt->purchaseOrder->po_number }}</strong>
            </div>
            <div>
                <span class="text-slate-500 text-[10px] uppercase font-bold block mb-0.5">Pemasok / Vendor:</span>
                <strong class="text-slate-900 block">{{ $goodsReceipt->purchaseOrder->supplier->name }}</strong>
            </div>
            <div>
                <span class="text-slate-500 text-[10px] uppercase font-bold block mb-0.5">Waktu Masuk Gudang:</span>
                <span class="font-bold text-slate-900 block">{{ $goodsReceipt->received_date->format('d/m/Y H:i') }} WIB</span>
            </div>
        </div>

        <!-- Items Table -->
        <div class="mb-6">
            <table class="w-full text-left text-xs border border-slate-200 rounded-lg overflow-hidden">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 uppercase text-[10px]">
                    <tr>
                        <th class="py-2.5 px-3 w-8 text-center">No</th>
                        <th class="py-2.5 px-3">Kode SKU</th>
                        <th class="py-2.5 px-3">Nama Barang</th>
                        <th class="py-2.5 px-3">Batch & Exp</th>
                        <th class="py-2.5 px-3">Lokasi Rak</th>
                        <th class="py-2.5 px-3 text-center">Diterima (Pcs)</th>
                        <th class="py-2.5 px-3 text-center">Tolak QC</th>
                        <th class="py-2.5 px-3">Keterangan / Alasan Cacat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($goodsReceipt->items as $idx => $it)
                        <tr>
                            <td class="py-2.5 px-3 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-2.5 px-3 font-mono font-bold text-slate-900">{{ $it->product->sku }}</td>
                            <td class="py-2.5 px-3 font-bold text-slate-800">{{ $it->product->title }}</td>
                            <td class="py-2.5 px-3 font-mono text-[11px]">
                                <div>{{ $it->batch_number ?: '-' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $it->expiry_date ? $it->expiry_date->format('d/m/Y') : '-' }}</div>
                            </td>
                            <td class="py-2.5 px-3 text-slate-700">{{ $it->shelf_location ?: '-' }}</td>
                            <td class="py-2.5 px-3 text-center font-mono font-bold text-slate-900">
                                {{ $it->quantity_received }} {{ $it->product->unit }}
                            </td>
                            <td class="py-2.5 px-3 text-center font-mono">
                                @if($it->quantity_rejected > 0)
                                    <span class="text-rose-600 font-bold">{{ $it->quantity_rejected }} {{ $it->product->unit }}</span>
                                @else
                                    <span class="text-slate-400">0</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-slate-600 italic">
                                {{ $it->rejection_reason ?: '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 font-bold border-t-2 border-slate-300">
                    <tr>
                        <td colspan="5" class="py-2 px-3 text-right uppercase text-slate-600">Total Fisik Diterima Masuk Stok:</td>
                        <td class="py-2 px-3 text-center font-mono text-slate-900 text-sm">
                            {{ $goodsReceipt->items->sum('quantity_received') }} Unit
                        </td>
                        <td class="py-2 px-3 text-center font-mono text-rose-600">
                            {{ $goodsReceipt->items->sum('quantity_rejected') }} Unit
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Catatan Lapangan -->
        @if($goodsReceipt->notes)
            <div class="mb-6 p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                <span class="text-slate-500 font-bold block mb-0.5">Catatan Khusus Lapangan:</span>
                <p class="text-slate-700 italic">{{ $goodsReceipt->notes }}</p>
            </div>
        @endif

        <!-- Lembar Tanda Tangan 3 Pihak -->
        <div class="grid grid-cols-3 gap-6 text-center text-xs mt-10 pt-4 border-t border-slate-200">
            <div>
                <p class="text-slate-500 mb-1">Diserahkan Oleh (Supir / Ekspedisi):</p>
                <div class="h-16 flex items-center justify-center text-slate-300 text-xs italic">
                    ( Tanda Tangan )
                </div>
                <div class="font-bold text-slate-900 underline">Pengemudi / Vendor</div>
                <div class="text-[10px] text-slate-500">{{ $goodsReceipt->purchaseOrder->supplier->name }}</div>
            </div>

            <div>
                <p class="text-slate-500 mb-1">Diterima & Dicek QC (Petugas Gudang):</p>
                <div class="h-16 flex items-center justify-center text-slate-300 text-xs italic">
                    ( Tanda Tangan )
                </div>
                <div class="font-bold text-slate-900 underline">{{ $goodsReceipt->receiver?->name ?? 'Staff Gudang' }}</div>
                <div class="text-[10px] text-slate-500">Staff Inbound & Putaway</div>
            </div>

            <div>
                <p class="text-slate-500 mb-1">Diketahui Oleh (Supervisor Gudang):</p>
                <div class="h-16 flex items-center justify-center text-slate-300 text-xs italic">
                    ( Tanda Tangan )
                </div>
                <div class="font-bold text-slate-900 underline">Kepala / Supervisor Gudang</div>
                <div class="text-[10px] text-slate-500">Warehouse Operations</div>
            </div>
        </div>

    </div>

</body>
</html>
