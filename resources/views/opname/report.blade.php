<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Stock Opname - {{ $opname->opname_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
                font-size: 11pt;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
            .print-border {
                border-color: #333 !important;
            }
            .print-shadow-none {
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans p-4 md:p-8 min-h-screen">

    <!-- Top Action Bar (Screen only) -->
    <div class="max-w-5xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('opname.show', $opname->id) }}"
           class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold flex items-center gap-2 transition shadow">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Ruang Audit</span>
        </a>

        <div class="flex items-center gap-3">
            <button onclick="window.print()"
                    class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center gap-2 shadow-lg transition">
                <i class="fa-solid fa-print"></i>
                <span>Cetak / Simpan PDF (Ctrl+P)</span>
            </button>
        </div>
    </div>

    <!-- Official Document Paper -->
    <div class="max-w-5xl mx-auto bg-white border border-slate-200 rounded-2xl p-8 md:p-12 shadow-xl print-shadow-none print-border">

        <!-- Kop Surat Dokumen -->
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
                <div class="inline-block px-3 py-1 bg-slate-100 rounded text-xs font-mono font-bold text-slate-800">
                    NO: BA-{{ $opname->opname_number }}
                </div>
                <div class="text-xs text-slate-500 mt-1">
                    Tanggal Cetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB
                </div>
            </div>
        </div>

        <!-- Judul Berita Acara -->
        <div class="text-center my-6">
            <h2 class="text-lg font-black uppercase tracking-wider text-slate-900 underline">
                BERITA ACARA HASIL STOCK OPNAME FISIK GUDANG
            </h2>
            <p class="text-xs text-slate-600 mt-1">
                Ref. Sesi: <strong class="font-mono text-slate-800">{{ $opname->opname_number }}</strong> — {{ $opname->title }}
            </p>
        </div>

        <!-- Info Metadata Sesi Opname -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs mb-6">
            <div>
                <span class="text-slate-500 text-[11px] block">Lokasi Gudang:</span>
                <strong class="text-slate-800">{{ $opname->warehouse ? $opname->warehouse->name : 'Semua Gudang' }}</strong>
            </div>
            <div>
                <span class="text-slate-500 text-[11px] block">Kategori Audit:</span>
                <strong class="text-slate-800">{{ $opname->category ? $opname->category->name : 'Semua Kategori' }}</strong>
            </div>
            <div>
                <span class="text-slate-500 text-[11px] block">Waktu Pelaksanaan:</span>
                <strong class="text-slate-800">{{ $opname->created_at->format('d/m/Y H:i') }}</strong>
            </div>
            <div>
                <span class="text-slate-500 text-[11px] block">Status Sesi:</span>
                <span class="font-bold {{ $opname->status === 'completed' ? 'text-emerald-700' : 'text-amber-700' }}">
                    {{ $opname->status_label }}
                </span>
            </div>
        </div>

        <!-- Ringkasan Eksekutif (Summary Stats) -->
        <div class="mb-6">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">1. Ringkasan Eksekutif Hasil Audit Fisik</h3>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <div class="p-3 border border-slate-200 rounded-lg text-center bg-white">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Total SKU Diaudit</div>
                    <div class="text-lg font-bold text-slate-900 mt-0.5">{{ $opname->total_items }}</div>
                </div>
                <div class="p-3 border border-emerald-200 rounded-lg text-center bg-emerald-50/50">
                    <div class="text-[10px] text-emerald-700 uppercase font-semibold">Stok Cocok (Pas)</div>
                    <div class="text-lg font-bold text-emerald-800 mt-0.5">{{ $opname->matched_items }} SKU</div>
                </div>
                <div class="p-3 border border-amber-200 rounded-lg text-center bg-amber-50/50">
                    <div class="text-[10px] text-amber-700 uppercase font-semibold">Ada Selisih</div>
                    <div class="text-lg font-bold text-amber-800 mt-0.5">{{ $opname->variance_items }} SKU</div>
                </div>
                <div class="p-3 border border-blue-200 rounded-lg text-center bg-blue-50/50">
                    <div class="text-[10px] text-blue-700 uppercase font-semibold">Tingkat Akurasi</div>
                    <div class="text-lg font-bold text-blue-800 mt-0.5">
                        {{ $opname->total_items > 0 ? round(($opname->matched_items / $opname->total_items) * 100, 1) : 0 }}%
                    </div>
                </div>
                <div class="p-3 border border-slate-200 rounded-lg text-center bg-slate-50">
                    <div class="text-[10px] text-slate-500 uppercase font-semibold">Total Nilai Selisih</div>
                    <div class="text-sm font-bold {{ $opname->total_variance_value < 0 ? 'text-rose-700' : 'text-emerald-700' }} mt-1">
                        {{ $opname->total_variance_value < 0 ? '-' : '+' }}Rp {{ number_format(abs($opname->total_variance_value), 0, ',', '.') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Rincian Selisih Barang -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600">2. Rincian Selisih & Hasil Hitung Fisik</h3>
                <span class="text-[11px] text-slate-400">Total {{ $items->count() }} Item</span>
            </div>

            <div class="overflow-x-auto border border-slate-200 rounded-lg">
                <table class="w-full text-left text-[11px]">
                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3 w-8 text-center">No</th>
                            <th class="py-2.5 px-3">SKU & Nama Barang</th>
                            <th class="py-2.5 px-3">Lokasi / Rak</th>
                            <th class="py-2.5 px-3 text-center">Buku (Sistem)</th>
                            <th class="py-2.5 px-3 text-center">Fisik (Aktual)</th>
                            <th class="py-2.5 px-3 text-center">Selisih</th>
                            <th class="py-2.5 px-3 text-right">Harga Pokok</th>
                            <th class="py-2.5 px-3 text-right">Nilai Selisih</th>
                            <th class="py-2.5 px-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($items as $idx => $it)
                            <tr class="{{ $it->variance != 0 ? ($it->variance < 0 ? 'bg-rose-50/30' : 'bg-emerald-50/30') : '' }}">
                                <td class="py-2 px-3 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                                <td class="py-2 px-3">
                                    <div class="font-mono font-bold text-slate-900">{{ $it->product->sku }}</div>
                                    <div class="text-slate-700 font-medium">{{ $it->product->title }}</div>
                                    @if($it->product->batch_number)
                                        <div class="text-[10px] text-slate-500 font-mono">Batch: {{ $it->product->batch_number }}</div>
                                    @endif
                                </td>
                                <td class="py-2 px-3">
                                    <span class="text-slate-700 font-medium">{{ $it->product->warehouse_location ?? '-' }}</span>
                                    <div class="text-[10px] text-slate-500">{{ $it->product->warehouse?->name ?? 'Gudang' }}</div>
                                </td>
                                <td class="py-2 px-3 text-center font-mono font-semibold">{{ $it->system_stock }} {{ $it->product->unit }}</td>
                                <td class="py-2 px-3 text-center font-mono font-bold">{{ $it->physical_stock }} {{ $it->product->unit }}</td>
                                <td class="py-2 px-3 text-center font-mono font-bold">
                                    @if($it->variance === 0)
                                        <span class="text-emerald-700">0 (Pas)</span>
                                    @elseif($it->variance > 0)
                                        <span class="text-teal-700">+{{ $it->variance }} Lebih</span>
                                    @else
                                        <span class="text-rose-700">{{ $it->variance }} Kurang</span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 text-right font-mono text-slate-600">
                                    Rp {{ number_format($it->cost_price, 0, ',', '.') }}
                                </td>
                                <td class="py-2 px-3 text-right font-mono font-bold {{ $it->variance_value < 0 ? 'text-rose-700' : ($it->variance_value > 0 ? 'text-emerald-700' : 'text-slate-400') }}">
                                    {{ $it->variance_value < 0 ? '-' : ($it->variance_value > 0 ? '+' : '') }}Rp {{ number_format(abs($it->variance_value), 0, ',', '.') }}
                                </td>
                                <td class="py-2 px-3 text-slate-600 italic">
                                    {{ $it->notes ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300">
                        <tr>
                            <td colspan="5" class="py-2.5 px-3 text-right text-slate-700 uppercase">Total Akumulasi Selisih Fisik & Finansial:</td>
                            <td class="py-2.5 px-3 text-center font-mono text-slate-900">
                                {{ $items->sum('variance') > 0 ? '+' : '' }}{{ $items->sum('variance') }} Unit
                            </td>
                            <td></td>
                            <td class="py-2.5 px-3 text-right font-mono {{ $opname->total_variance_value < 0 ? 'text-rose-700' : 'text-emerald-700' }}">
                                {{ $opname->total_variance_value < 0 ? '-' : '+' }}Rp {{ number_format(abs($opname->total_variance_value), 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Pernyataan & Klausul Berita Acara -->
        <div class="mb-8 text-xs text-slate-600 leading-relaxed border-l-4 border-slate-400 pl-4 py-1">
            <p>
                Demikian Berita Acara Stock Opname ini dibuat dengan sebenar-benarnya berdasarkan hasil perhitungan fisik barang secara langsung di gudang. Seluruh data selisih telah dikonfirmasi dan disepakati oleh tim penghitung serta telah dilakukan penyesuaian (rekonsiliasi) pada sistem kartu persediaan.
            </p>
        </div>

        <!-- Lembar Tanda Tangan Resmi (3-Pihak) -->
        <div class="grid grid-cols-3 gap-6 text-center text-xs mt-10 pt-4 border-t border-slate-200">
            <!-- Petugas Dihitung Oleh -->
            <div>
                <p class="text-slate-500 mb-1">Dihitung Oleh (Auditor Fisik):</p>
                <div class="h-20 flex items-center justify-center text-slate-300 text-xs italic">
                    ( Tanda Tangan )
                </div>
                <div class="font-bold text-slate-900 underline">{{ $opname->creator?->name ?? 'Operator Gudang' }}</div>
                <div class="text-[10px] text-slate-500">Staff Operasional Gudang</div>
            </div>

            <!-- Petugas Diperiksa Oleh -->
            <div>
                <p class="text-slate-500 mb-1">Diperiksa / Disaksikan Oleh:</p>
                <div class="h-20 flex items-center justify-center text-slate-300 text-xs italic">
                    ( Tanda Tangan )
                </div>
                <div class="font-bold text-slate-900 underline">Supervisor Administrasi</div>
                <div class="text-[10px] text-slate-500">Supervisor Pengendalian Stok</div>
            </div>

            <!-- Disetujui Oleh -->
            <div>
                <p class="text-slate-500 mb-1">Disetujui Oleh (Kepala Gudang):</p>
                <div class="h-20 flex items-center justify-center text-slate-300 text-xs italic">
                    ( Tanda Tangan )
                </div>
                <div class="font-bold text-slate-900 underline">{{ $opname->completedByUser?->name ?? 'Kepala Gudang' }}</div>
                <div class="text-[10px] text-slate-500">Warehouse Operations Manager</div>
            </div>
        </div>

    </div>

</body>
</html>
