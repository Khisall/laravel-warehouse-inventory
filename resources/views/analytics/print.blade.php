<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Analisis Perputaran Stok & ABC - {{ date('d M Y') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @media print {
            body { background: white !important; color: black !important; font-size: 10pt; }
            .no-print { display: none !important; }
            .print-border { border-color: #333 !important; }
            .print-shadow-none { box-shadow: none !important; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans p-4 md:p-8 min-h-screen">

    <!-- Top Action Bar (Screen Only) -->
    <div class="max-w-5xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('analytics.index', request()->all()) }}"
           class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold flex items-center gap-2 transition shadow">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Dashboard Analitik</span>
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
                <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">LAPORAN EKSEKUTIF ANALITIK GUDANG</h2>
                <div class="text-xs text-slate-500 mt-1">
                    Tanggal: {{ now()->translatedFormat('d F Y, H:i') }} WIB
                </div>
                <div class="text-[11px] font-mono text-slate-400">
                    Periode: {{ $periodDays }} Hari Terakhir
                </div>
            </div>
        </div>

        <!-- Title -->
        <div class="text-center my-6">
            <h2 class="text-lg font-black uppercase tracking-wider text-slate-900 underline">
                ANALISIS PERPUTARAN STOK & KLASIFIKASI ABC (PARETO 80/20)
            </h2>
            <p class="text-xs text-slate-600 mt-1">
                Laporan Evaluasi Perputaran Barang, Klasifikasi Nilai Omset, dan Identifikasi Dead Stock
            </p>
        </div>

        <!-- Executive KPI Overview -->
        <div class="grid grid-cols-4 gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs mb-6">
            <div class="p-2 bg-white rounded border border-slate-200 text-center">
                <span class="text-[10px] text-slate-500 uppercase font-semibold block">Total Omset Penjualan</span>
                <strong class="text-slate-900 text-sm block mt-0.5">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</strong>
                <span class="text-[9px] text-slate-500">{{ number_format($summary['total_sold_units']) }} unit terjual</span>
            </div>

            <div class="p-2 bg-emerald-50 rounded border border-emerald-200 text-center">
                <span class="text-[10px] text-emerald-700 uppercase font-semibold block">Produk Kelas A (Core)</span>
                <strong class="text-emerald-800 text-sm block mt-0.5">{{ $summary['class_a_count'] }} SKU ({{ $summary['class_a_revenue_pct'] }}%)</strong>
                <span class="text-[9px] text-emerald-600">Rp {{ number_format($summary['class_a_revenue'], 0, ',', '.') }}</span>
            </div>

            <div class="p-2 bg-cyan-50 rounded border border-cyan-200 text-center">
                <span class="text-[10px] text-cyan-700 uppercase font-semibold block">Fast-Moving Items</span>
                <strong class="text-cyan-800 text-sm block mt-0.5">{{ $summary['fast_moving_count'] }} SKU</strong>
                <span class="text-[9px] text-cyan-600">Perputaran sangat lancar</span>
            </div>

            <div class="p-2 bg-rose-50 rounded border border-rose-200 text-center">
                <span class="text-[10px] text-rose-700 uppercase font-semibold block">Dead Stock (&gt; 90 Hari)</span>
                <strong class="text-rose-800 text-sm block mt-0.5">{{ $summary['dead_stock_count'] }} SKU</strong>
                <span class="text-[9px] text-rose-600">Rp {{ number_format($summary['dead_stock_value'], 0, ',', '.') }} modal tertahan</span>
            </div>
        </div>

        <!-- Items Table -->
        <div class="mb-6">
            <table class="w-full text-left text-[11px] border border-slate-200 rounded-lg overflow-hidden">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200 uppercase text-[10px]">
                    <tr>
                        <th class="py-2.5 px-3 w-8 text-center">No</th>
                        <th class="py-2.5 px-3">Kode SKU & Nama Barang</th>
                        <th class="py-2.5 px-3">Gudang / Rak</th>
                        <th class="py-2.5 px-3 text-center">Stok</th>
                        <th class="py-2.5 px-3 text-right">Nilai Modal (Rp)</th>
                        <th class="py-2.5 px-3 text-center">Terjual</th>
                        <th class="py-2.5 px-3 text-right">Omset (Rp)</th>
                        <th class="py-2.5 px-3 text-center">Kelas</th>
                        <th class="py-2.5 px-3 text-center">Kecepatan</th>
                        <th class="py-2.5 px-3 text-center">Sisa (DOH)</th>
                        <th class="py-2.5 px-3">Rekomendasi Manajerial</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($items as $idx => $it)
                        @php
                            $prod = $it['product'];
                        @endphp
                        <tr class="{{ $it['velocity_status'] === 'dead' ? 'bg-rose-50/40' : ($it['abc_class'] === 'A' ? 'bg-emerald-50/30' : '') }}">
                            <td class="py-2 px-3 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-2 px-3">
                                <div class="font-mono font-bold text-slate-900">{{ $prod->sku }}</div>
                                <div class="text-slate-700">{{ $prod->title }}</div>
                            </td>
                            <td class="py-2 px-3">
                                <div class="font-semibold text-slate-800">{{ $prod->warehouse_location ?? '-' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $prod->warehouse?->name ?? '-' }}</div>
                            </td>
                            <td class="py-2 px-3 text-center font-mono font-bold text-slate-900">
                                {{ $it['current_stock'] }} {{ $prod->unit }}
                            </td>
                            <td class="py-2 px-3 text-right font-mono text-slate-700">
                                Rp {{ number_format($it['stock_holding_value'], 0, ',', '.') }}
                            </td>
                            <td class="py-2 px-3 text-center font-mono font-bold text-slate-900">
                                {{ $it['sold_units'] }} {{ $prod->unit }}
                            </td>
                            <td class="py-2 px-3 text-right font-mono font-bold text-slate-900">
                                Rp {{ number_format($it['revenue_generated'], 0, ',', '.') }}
                                <div class="text-[9px] text-slate-500 font-normal">({{ $it['revenue_share_pct'] }}%)</div>
                            </td>
                            <td class="py-2 px-3 text-center font-mono font-bold">
                                @if($it['abc_class'] === 'A')
                                    <span class="text-emerald-700">KELAS A</span>
                                @elseif($it['abc_class'] === 'B')
                                    <span class="text-cyan-700">KELAS B</span>
                                @else
                                    <span class="text-slate-600">KELAS C</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 text-center font-mono font-semibold">
                                @if($it['velocity_status'] === 'fast')
                                    <span class="text-emerald-700">Fast</span>
                                @elseif($it['velocity_status'] === 'medium')
                                    <span class="text-blue-700">Medium</span>
                                @elseif($it['velocity_status'] === 'slow')
                                    <span class="text-amber-700">Slow</span>
                                @else
                                    <span class="text-rose-700 font-bold">DEAD STOCK</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 text-center font-mono text-slate-700">
                                {{ $it['days_of_inventory'] >= 999 ? 'Non-Moving' : $it['days_of_inventory'] . ' Hari' }}
                            </td>
                            <td class="py-2 px-3 text-slate-600 italic">
                                {{ $it['action_recommendation'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-100 font-bold border-t-2 border-slate-300">
                    <tr>
                        <td colspan="4" class="py-2.5 px-3 text-right text-slate-700 uppercase">Total Akumulasi:</td>
                        <td class="py-2.5 px-3 text-right font-mono">
                            Rp {{ number_format($summary['total_stock_value'], 0, ',', '.') }}
                        </td>
                        <td class="py-2.5 px-3 text-center font-mono">
                            {{ $summary['total_sold_units'] }} Unit
                        </td>
                        <td class="py-2.5 px-3 text-right font-mono text-emerald-700">
                            Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                        </td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Strategi Manajemen Logistik -->
        <div class="mb-8 text-xs text-slate-600 bg-slate-50 p-4 rounded-xl border border-slate-200">
            <p class="font-bold text-slate-900 mb-1">Rekomendasi Tindakan Strategis Manajemen Logistik:</p>
            <ul class="list-disc pl-4 space-y-1 text-[11px]">
                <li><strong>Barang Kelas A:</strong> Alokasikan pada rak paling depan dekat dermaga pengiriman (*dock*). Pertahankan *safety stock* tinggi untuk mencegah kekosongan barang (*stockout*).</li>
                <li><strong>Barang Fast-Moving:</strong> Jadwalkan pesanan pembelian (*Purchase Order*) berulang ke supplier utama secara teratur.</li>
                <li><strong>Barang Dead Stock:</strong> Lakukan tindakan pelepasan aset segera melalui program diskon/flash sale cuci gudang atau *bundling promo* dengan produk Kelas A agar modal tertahan dapat dicairkan kembali.</li>
            </ul>
        </div>

        <!-- Lembar Tanda Tangan 3 Pihak -->
        <div class="grid grid-cols-3 gap-6 text-center text-xs mt-10 pt-4 border-t border-slate-200">
            <div>
                <p class="text-slate-500 mb-1">Dianalisis Oleh (Staff Pengendali Stok):</p>
                <div class="h-16 flex items-center justify-center text-slate-300 text-xs italic">
                    ( Tanda Tangan )
                </div>
                <div class="font-bold text-slate-900 underline">{{ auth()->user()->name ?? 'Inventory Controller' }}</div>
                <div class="text-[10px] text-slate-500">Inventory & Supply Chain Analyst</div>
            </div>

            <div>
                <p class="text-slate-500 mb-1">Diperiksa Oleh (Kepala Gudang):</p>
                <div class="h-16 flex items-center justify-center text-slate-300 text-xs italic">
                    ( Tanda Tangan )
                </div>
                <div class="font-bold text-slate-900 underline">Budi Santoso</div>
                <div class="text-[10px] text-slate-500">Warehouse Operations Manager</div>
            </div>

            <div>
                <p class="text-slate-500 mb-1">Disetujui Oleh (Direktur / Owner):</p>
                <div class="h-16 flex items-center justify-center text-slate-300 text-xs italic">
                    ( Tanda Tangan )
                </div>
                <div class="font-bold text-slate-900 underline">Khisal</div>
                <div class="text-[10px] text-slate-500">Managing Director</div>
            </div>
        </div>

    </div>

</body>
</html>
