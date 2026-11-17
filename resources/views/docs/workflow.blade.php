<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentasi Alur Kerja (Workflow) Sistem Gudang - PT. Inventory Logistik Nusantara</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts Inter & JetBrains Mono -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #0f172a;
            color: #1e293b;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        .page-break {
            page-break-before: always;
            break-before: page;
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
                border-color: #cbd5e1 !important;
            }
            .print-shadow-none {
                box-shadow: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 14mm 16mm;
            }
        }
    </style>
</head>
<body class="py-8 px-4 md:px-8">

    <!-- Top Action Bar (Hidden on Print) -->
    <div class="max-w-5xl mx-auto mb-6 no-print flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-900 border border-slate-800 text-white p-4 rounded-2xl shadow-2xl">
        <div class="flex items-center gap-3">
            <a href="{{ route('inventory.index') }}"
               class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold flex items-center gap-2 transition">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Dashboard</span>
            </a>
            <div>
                <span class="text-xs text-slate-400 block">Dokumen Resmi Sistem:</span>
                <strong class="text-xs text-emerald-400 font-mono">Standard Operating Procedure &amp; Workflow Manual v2.0</strong>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('docs.download-pdf') }}"
               class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold flex items-center gap-2 border border-slate-700 transition">
                <i class="fa-solid fa-download text-cyan-400"></i>
                <span>Unduh File PDF</span>
            </a>
            <button onclick="window.print()"
                    class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center gap-2 shadow-lg shadow-emerald-600/20 transition">
                <i class="fa-solid fa-print"></i>
                <span>Cetak Dokumen / Simpan PDF (Ctrl+P)</span>
            </button>
        </div>
    </div>

    <!-- Main Printable Paper Container -->
    <div class="max-w-5xl mx-auto bg-white rounded-3xl p-8 md:p-14 shadow-2xl print-shadow-none text-slate-800 space-y-10">

        <!-- ================= PAGE 1: COVER & HEADER ================= -->
        <div>
            <!-- Header Kop Surat Perusahaan -->
            <div class="border-b-2 border-slate-900 pb-6 mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-3xl font-black shadow-md">
                        IN
                    </div>
                    <div>
                        <h1 class="text-xl font-extrabold tracking-tight uppercase text-slate-900">PT. INVENTORY LOGISTIK NUSANTARA</h1>
                        <p class="text-xs text-slate-600 font-medium">Warehouse Storage &amp; Modern Distribution Center Systems</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Kawasan Industri Modern Kav. 88, Cikarang, Jawa Barat &bull; Telp: (021) 8989-1234 &bull; www.inventory-logistik.co.id</p>
                    </div>
                </div>

                <div class="text-left sm:text-right border-t sm:border-t-0 pt-3 sm:pt-0">
                    <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-800 font-bold text-[10px] rounded-full uppercase tracking-wider mb-1">
                        Dokumen Operasional Resmi
                    </span>
                    <h2 class="text-xs font-black text-slate-900 uppercase tracking-wider">STANDARD OPERATING PROCEDURE</h2>
                    <div class="text-[11px] font-mono text-slate-500 mt-0.5">No: SOP-WMS-2026/XI-REV2</div>
                    <div class="text-[11px] text-slate-500">Tanggal Terbit: {{ now()->translatedFormat('d F Y') }}</div>
                </div>
            </div>

            <!-- Title Banner -->
            <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-[#13202b] text-white p-8 rounded-2xl mb-8 relative overflow-hidden shadow-lg">
                <div class="relative z-10">
                    <span class="text-emerald-400 font-mono text-xs font-bold uppercase tracking-widest block mb-2">
                        Comprehensive System Guide &bull; Enterprise WMS Edition
                    </span>
                    <h2 class="text-2xl md:text-3xl font-black uppercase tracking-tight leading-tight">
                        PANDUAN ALUR KERJA (WORKFLOW) SISTEM MANAJEMEN GUDANG &amp; INVENTARIS TERPADU
                    </h2>
                    <p class="text-xs text-slate-300 mt-3 max-w-2xl leading-relaxed">
                        Dokumentasi komprehensif alur operasional logistik, mulai dari pengadaan barang masuk (Inbound PO), kontrol batch dan kedaluwarsa (FEFO), mutasi antar gudang cabang, stock opname digital, sinkronisasi multi-toko online, hingga analisis perputaran stok eksekutif.
                    </p>
                </div>
            </div>

            <!-- Ringkasan Eksekutif (Executive Summary) -->
            <div class="space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
                    <div class="w-2.5 h-6 bg-emerald-600 rounded"></div>
                    <h3 class="text-base font-extrabold uppercase tracking-wider text-slate-900">1. Ringkasan Eksekutif &amp; Tujuan Sistem</h3>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed text-justify">
                    Sistem Manajemen Gudang &amp; Inventaris PT. Inventory Logistik Nusantara dibangun untuk mengintegrasikan seluruh rantai pasok logistik fisik dengan sistem digital berbasis web dan perangkat bergerak (*mobile*). Sistem dirancang dengan prinsip <strong>Real-Time Transparency, Zero Stockout, dan Lean Inventory</strong> guna menekan kerugian modal kerja yang tertahan pada stok mati (*dead stock*) serta mencegah barang kadaluarsa terlewat.
                </p>

                <!-- 5 Pilar Inti -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 pt-2 text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                        <i class="fa-solid fa-truck-ramp-box text-emerald-600 text-xl mb-1.5 block"></i>
                        <strong class="font-bold text-slate-900 block text-[11px]">1. Inbound Terkontrol</strong>
                        <span class="text-[10px] text-slate-500">QC ketat &amp; pencatatan GRN</span>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                        <i class="fa-solid fa-boxes-packing text-cyan-600 text-xl mb-1.5 block"></i>
                        <strong class="font-bold text-slate-900 block text-[11px]">2. Sistem FEFO</strong>
                        <span class="text-[10px] text-slate-500">Prioritas batch kedaluwarsa</span>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                        <i class="fa-solid fa-truck-fast text-amber-600 text-xl mb-1.5 block"></i>
                        <strong class="font-bold text-slate-900 block text-[11px]">3. Mutasi Sah</strong>
                        <span class="text-[10px] text-slate-500">Surat Jalan barcode 4 pihak</span>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                        <i class="fa-solid fa-store text-orange-600 text-xl mb-1.5 block"></i>
                        <strong class="font-bold text-slate-900 block text-[11px]">4. Multi-Channel</strong>
                        <span class="text-[10px] text-slate-500">Perlindungan Safety Buffer</span>
                    </div>

                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                        <i class="fa-solid fa-chart-line text-emerald-700 text-xl mb-1.5 block"></i>
                        <strong class="font-bold text-slate-900 block text-[11px]">5. Analitik ABC</strong>
                        <span class="text-[10px] text-slate-500">Pareto 80/20 &amp; Dead Stock</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= PAGE 2: DIAGRAM ALUR MAKRO ================= -->
        <div class="pt-6 page-break">
            <div class="flex items-center gap-2 border-b border-slate-200 pb-2 mb-6">
                <div class="w-2.5 h-6 bg-emerald-600 rounded"></div>
                <h3 class="text-base font-extrabold uppercase tracking-wider text-slate-900">2. Peta Alur Operasional Makro (End-to-End Pipeline)</h3>
            </div>

            <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200 space-y-6">
                <!-- Step Pipeline Steps -->
                <div class="space-y-4">
                    <!-- Step 1 -->
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-900 text-emerald-400 flex items-center justify-center font-mono font-black text-sm shrink-0 shadow">
                            01
                        </div>
                        <div class="flex-1 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Pengadaan &amp; Penerimaan Barang (Inbound Procurement)</h4>
                                <span class="font-mono text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-bold">Modul: /purchase-orders &amp; /goods-receipts</span>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Manajer menerbitkan Purchase Order (PO) kepada Supplier resmi &rarr; Truk pengirim tiba di dermaga bongkar &rarr; Operator melakukan pemeriksaan fisik Quality Control (QC) &rarr; Penerbitan Goods Receipt Note (GRN) &rarr; Input nomor lot batch, tanggal expired, dan alokasi rak penempatan (Putaway).
                            </p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-900 text-cyan-400 flex items-center justify-center font-mono font-black text-sm shrink-0 shadow">
                            02
                        </div>
                        <div class="flex-1 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Manajemen Penyimpanan, Batch &amp; Pengawasan Mutu (Storage &amp; FEFO)</h4>
                                <span class="font-mono text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-bold">Modul: /batches &amp; /alerts</span>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Stok tersimpan di rak gudang fisik &bull; Algoritma FEFO melacak tanggal kedaluwarsa tiap batch &bull; Fitur Karantina / Hold mencegah batch cacat terambil &bull; Alert Center memberi notifikasi otomatis jika stok menipis (&le; min_stock) atau batch expired (&le; 30 hari).
                            </p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-900 text-amber-400 flex items-center justify-center font-mono font-black text-sm shrink-0 shadow">
                            03
                        </div>
                        <div class="flex-1 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Mutasi Antar Gudang &amp; Audit Stok Fisik (Internal Movement &amp; Audit)</h4>
                                <span class="font-mono text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-bold">Modul: /transfers &amp; /opname</span>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Pengiriman barang antar cabang (Gudang Pusat &rarr; Gudang Cabang) menggunakan Surat Jalan Transfer resmi 4 tanda tangan berbarcode &bull; Audit fisik rutin berkala menggunakan modul Stock Opname Digital berbasis pemindai barcode untuk rekonsiliasi selisih sistem vs fisik.
                            </p>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-900 text-orange-400 flex items-center justify-center font-mono font-black text-sm shrink-0 shadow">
                            04
                        </div>
                        <div class="flex-1 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Penjualan Multi-Toko &amp; Pengeluaran Barang (Outbound Sales)</h4>
                                <span class="font-mono text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-bold">Modul: /marketplace &amp; /orders</span>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Stok disinkronkan ke Shopee, Tokopedia, TikTok Shop dengan perlindungan Safety Buffer &bull; Pesanan masuk otomatis memicu pemotongan stok secara FEFO &bull; Sisa stok di seluruh toko online lain langsung terupdate otomatis &bull; Cetak Surat Jalan Pengiriman (Delivery Order).
                            </p>
                        </div>
                    </div>

                    <!-- Step 5 -->
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-900 text-emerald-500 flex items-center justify-center font-mono font-black text-sm shrink-0 shadow">
                            05
                        </div>
                        <div class="flex-1 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Evaluasi Kinerja, Omset &amp; Keputusan Strategis (Executive Analytics)</h4>
                                <span class="font-mono text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-bold">Modul: /analytics &amp; /reports</span>
                            </div>
                            <p class="text-[11px] text-slate-600 leading-relaxed">
                                Analisis Pareto 80/20 mengelompokkan barang Kelas A, B, C &bull; Pemetaan kecepatan perputaran (Fast, Medium, Slow, Dead Stock &ge; 90 hari) &bull; Perhitungan nilai modal kerja tertahan dan rekomendasi tindakan cuci gudang / reorder PO &bull; Cetak Berita Acara Analitik Eksekutif.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= PAGE 3: DETAIL FASE 1 & FASE 2 ================= -->
        <div class="pt-6 page-break space-y-6">
            <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
                <div class="w-2.5 h-6 bg-emerald-600 rounded"></div>
                <h3 class="text-base font-extrabold uppercase tracking-wider text-slate-900">3. Prosedur Standar Operasional (SOP) Tiap Modul</h3>
            </div>

            <!-- Modul Inbound -->
            <div class="border border-slate-200 rounded-2xl p-5 bg-white space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h4 class="font-bold text-slate-900 text-xs flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        Fase 1: Prosedur Pengadaan &amp; Penerimaan Barang Masuk (Inbound Receiving)
                    </h4>
                    <span class="text-[10px] font-mono text-slate-400">URL: /purchase-orders &bull; /goods-receipts</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-[11px] text-slate-600">
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <strong class="text-slate-900 block mb-1">1. Pengajuan PO</strong>
                        Staff/Manajer membuat dokumen PO berisi daftar barang, kuantitas, target gudang, dan harga beli vendor. Status awal: <em>Draft</em> lalu menjadi <em>Ordered</em> saat dikirim ke supplier.
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <strong class="text-slate-900 block mb-1">2. Pemeriksaan QC &amp; GRN</strong>
                        Saat barang tiba, staf mencocokkan Surat Jalan vendor dengan PO. Barang dihitung dan diperiksa kualitas fisiknya. Barang cacat dicatat pada kuantitas *Rejected* disertai alasannya.
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <strong class="text-slate-900 block mb-1">3. Putaway &amp; Batching</strong>
                        Barang yang diterima (*Accepted*) diinput nomor batch/lot produksinya dan tanggal kadaluwarsa, lalu dipindahkan ke rak yang ditentukan (*putaway*). Stok master otomatis bertambah.
                    </div>
                </div>
            </div>

            <!-- Modul Storage, Batch & FEFO -->
            <div class="border border-slate-200 rounded-2xl p-5 bg-white space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h4 class="font-bold text-slate-900 text-xs flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-cyan-600"></span>
                        Fase 2: Prosedur Tata Kelola Batch &amp; Sistem Pengambilan FEFO
                    </h4>
                    <span class="text-[10px] font-mono text-slate-400">URL: /batches &bull; /alerts</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-[11px] text-slate-600">
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <strong class="text-slate-900 block mb-1">1. Mekanisme Algoritma FEFO</strong>
                        Sistem mengurutkan seluruh batch aktif berdasarkan tanggal kadaluwarsa terdekat. Saat terjadi pesanan keluar, barang dari batch terdekat akan dipotong otomatis terlebih dahulu hingga habis (*depleted*).
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <strong class="text-slate-900 block mb-1">2. Fitur Karantina (Hold)</strong>
                        Jika suatu batch dicurigai cacat atau dalam uji sampel laboratorium, Manajer dapat mengaktifkan status *Quarantine*. Batch ini langsung diblokir dan tidak akan pernah dialokasikan untuk dijual.
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <strong class="text-slate-900 block mb-1">3. Peringatan Dini &amp; Write-Off</strong>
                        Sistem memberikan sinyal peringatan berkedip jika batch memasuki &le; 30 hari kadaluwarsa. Jika melewati tanggal expired, tombol *Write-Off* otomatis mencatat pemusnahan barang.
                    </div>
                </div>
            </div>

            <!-- Modul Mutasi Antar Gudang -->
            <div class="border border-slate-200 rounded-2xl p-5 bg-white space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h4 class="font-bold text-slate-900 text-xs flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                        Fase 3: Prosedur Mutasi Antar Gudang &amp; Surat Jalan Konsinyasi
                    </h4>
                    <span class="text-[10px] font-mono text-slate-400">URL: /transfers</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-[11px] text-slate-600">
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <strong class="text-slate-900 block mb-1">1. Pengajuan Mutasi (Draft)</strong>
                        Staf memilih Gudang Asal dan Gudang Tujuan (wajib berbeda). Formulir secara dinamis memuat ketersediaan stok fisik di gudang asal dan memvalidasi batas maksimum muat.
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <strong class="text-slate-900 block mb-1">2. Keberangkatan (In-Transit)</strong>
                        Manajer menyetujui keberangkatan (*Dispatch*). Stok di gudang asal dipotong seketika dan tercatat sebagai barang dalam perjalanan. Dokumen Surat Jalan Transfer dicetak lengkap dengan barcode nomor transfer.
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <strong class="text-slate-900 block mb-1">3. Verifikasi Tiba (Received)</strong>
                        Petugas gudang tujuan memeriksa fisik muatan. Kuantitas yang lolos verifikasi ditambahkan ke katalog gudang tujuan, dan mutasi selesai ditandatangani 4 pihak.
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= PAGE 4: DETAIL FASE 4 & FASE 5 ================= -->
        <div class="pt-6 page-break space-y-6">
            <!-- Modul Marketplace & Safety Buffer -->
            <div class="border border-slate-200 rounded-2xl p-5 bg-white space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h4 class="font-bold text-slate-900 text-xs flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-orange-600"></span>
                        Fase 4: Multi-Channel Marketplace Sync &amp; Safety Stock Buffer
                    </h4>
                    <span class="text-[10px] font-mono text-slate-400">URL: /marketplace &bull; /orders</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-[11px] text-slate-600">
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <strong class="text-slate-900 block mb-1">1. Safety Buffer Anti-Overselling</strong>
                        Setiap listing memiliki buffer pengaman (misal: 3–5 unit). Rumus stok tayang toko online: <code>Stok Marketplace = max(0, Stok Fisik - Safety Buffer)</code>.
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <strong class="text-slate-900 block mb-1">2. Pemotongan Stok FEFO Otomatis</strong>
                        Saat pesanan tiba dari Shopee, Tokopedia, atau TikTok Shop, sistem memotong stok secara real-time dari batch yang paling mendekati kadaluwarsa.
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <strong class="text-slate-900 block mb-1">3. Sinkronisasi Antar Channel</strong>
                        Penjualan di satu channel otomatis memperbarui sisa stok tayang di seluruh channel marketplace lainnya dalam hitungan detik.
                    </div>
                </div>
            </div>

            <!-- Modul Analisis ABC & Velocity -->
            <div class="border border-slate-200 rounded-2xl p-5 bg-white space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h4 class="font-bold text-slate-900 text-xs flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-700"></span>
                        Fase 5: Analisis ABC Pareto (80/20) &amp; Manajemen Dead Stock
                    </h4>
                    <span class="text-[10px] font-mono text-slate-400">URL: /analytics</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-[11px] text-slate-600">
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <strong class="text-slate-900 block mb-1">1. Klasifikasi Pareto 80/20</strong>
                        Barang dikelompokkan: <strong>Kelas A</strong> (Top ~80% omset omzet inti), <strong>Kelas B</strong> (~15% omset), dan <strong>Kelas C</strong> (~5% omset volume rendah).
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <strong class="text-slate-900 block mb-1">2. Kecepatan Stok &amp; Dead Stock</strong>
                        Memetakan *Fast-Moving* (&le; 35 hari DOH), *Medium*, *Slow*, dan **Dead Stock** (tidak bergerak &ge; 90 hari) yang mengunci modal kerja (*capital tie-up*).
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <strong class="text-slate-900 block mb-1">3. Rekomendasi Aksi Cerdas</strong>
                        Sistem memberikan saran otomatis per produk: Jadwalkan reorder PO berkala untuk Kelas A, atau cuci gudang (*flash sale*) dan *bundling promo* untuk Dead Stock.
                    </div>
                </div>
            </div>

            <!-- Matriks Hak Akses RBAC -->
            <div class="border border-slate-200 rounded-2xl p-5 bg-white space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h4 class="font-bold text-slate-900 text-xs flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-slate-900"></span>
                        Matriks Hak Akses &amp; Tanggung Jawab Pengguna (RBAC Matrix)
                    </h4>
                    <span class="text-[10px] font-mono text-slate-400">Role-Based Access Control</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-[11px]">
                        <thead>
                            <tr class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                                <th class="p-2">Modul / Fungsionalitas</th>
                                <th class="p-2 text-center">Super Admin (Owner)</th>
                                <th class="p-2 text-center">Warehouse Manager</th>
                                <th class="p-2 text-center">Warehouse Staff</th>
                                <th class="p-2 text-center">Sales Staff</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600">
                            <tr>
                                <td class="p-2 font-medium text-slate-900">Dashboard &amp; Katalog Stok Fisik</td>
                                <td class="p-2 text-center text-emerald-600 font-bold">Penuh</td>
                                <td class="p-2 text-center text-emerald-600 font-bold">Penuh</td>
                                <td class="p-2 text-center text-emerald-600">Lihat &amp; Sesuaikan</td>
                                <td class="p-2 text-center text-slate-500">Lihat Saja</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-medium text-slate-900">Purchase Order (PO) &amp; Pemasok</td>
                                <td class="p-2 text-center text-emerald-600 font-bold">Penuh / Approve</td>
                                <td class="p-2 text-center text-emerald-600 font-bold">Penuh / Approve</td>
                                <td class="p-2 text-center text-slate-500">Lihat Dokumen</td>
                                <td class="p-2 text-center text-rose-500">&times; Ditolak</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-medium text-slate-900">Penerimaan Barang (GRN &amp; QC)</td>
                                <td class="p-2 text-center text-emerald-600 font-bold">Penuh</td>
                                <td class="p-2 text-center text-emerald-600 font-bold">Penuh</td>
                                <td class="p-2 text-center text-emerald-600 font-bold">Input GRN &amp; QC</td>
                                <td class="p-2 text-center text-rose-500">&times; Ditolak</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-medium text-slate-900">Batch, FEFO &amp; Karantina Mutu</td>
                                <td class="p-2 text-center text-emerald-600 font-bold">Penuh / Karantina</td>
                                <td class="p-2 text-center text-emerald-600 font-bold">Penuh / Karantina</td>
                                <td class="p-2 text-center text-emerald-600">Input Batch Baru</td>
                                <td class="p-2 text-center text-rose-500">&times; Ditolak</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-medium text-slate-900">Mutasi Gudang &amp; Surat Jalan</td>
                                <td class="p-2 text-center text-emerald-600 font-bold">Penuh / Dispatch</td>
                                <td class="p-2 text-center text-emerald-600 font-bold">Penuh / Dispatch</td>
                                <td class="p-2 text-center text-emerald-600">Buat Draft &amp; Terima</td>
                                <td class="p-2 text-center text-rose-500">&times; Ditolak</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-medium text-slate-900">Stock Opname Digital</td>
                                <td class="p-2 text-center text-emerald-600 font-bold">Rekonsiliasi Final</td>
                                <td class="p-2 text-center text-emerald-600 font-bold">Rekonsiliasi Final</td>
                                <td class="p-2 text-center text-emerald-600">Scan &amp; Hitung Fisik</td>
                                <td class="p-2 text-center text-rose-500">&times; Ditolak</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-medium text-slate-900">Marketplace Sync &amp; Safety Buffer</td>
                                <td class="p-2 text-center text-emerald-600 font-bold">Atur Buffer &amp; Sync</td>
                                <td class="p-2 text-center text-emerald-600 font-bold">Atur Buffer &amp; Sync</td>
                                <td class="p-2 text-center text-rose-500">&times; Ditolak</td>
                                <td class="p-2 text-center text-slate-500">Lihat Listing</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-medium text-slate-900">Analisis ABC &amp; Valuasi Finansial</td>
                                <td class="p-2 text-center text-emerald-600 font-bold">Akses Penuh</td>
                                <td class="p-2 text-center text-emerald-600 font-bold">Akses Penuh</td>
                                <td class="p-2 text-center text-rose-500">&times; Disensor / 403</td>
                                <td class="p-2 text-center text-rose-500">&times; Disensor / 403</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-medium text-slate-900">Manajemen Pengguna (/users)</td>
                                <td class="p-2 text-center text-emerald-600 font-bold">Khusus Super Admin</td>
                                <td class="p-2 text-center text-rose-500">&times; Ditolak</td>
                                <td class="p-2 text-center text-rose-500">&times; Ditolak</td>
                                <td class="p-2 text-center text-rose-500">&times; Ditolak</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ================= PAGE 5: DIREKTORI LINK & PENGESAHAN ================= -->
        <div class="pt-6 page-break space-y-6">
            <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
                <div class="w-2.5 h-6 bg-emerald-600 rounded"></div>
                <h3 class="text-base font-extrabold uppercase tracking-wider text-slate-900">4. Direktori Link Navigasi Cepat &amp; Pengesahan Dokumen</h3>
            </div>

            <!-- Table of URLs -->
            <div class="overflow-x-auto border border-slate-200 rounded-2xl bg-white">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100 text-slate-800 font-bold border-b border-slate-200 text-[11px] uppercase tracking-wider">
                            <th class="py-2.5 px-3">Nama Modul</th>
                            <th class="py-2.5 px-3">URL Lokal (PC Utama)</th>
                            <th class="py-2.5 px-3">Akses WiFi LAN (HP / Tablet)</th>
                            <th class="py-2.5 px-3">Tujuan Utama</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-[11px] font-mono">
                        <tr>
                            <td class="py-2.5 px-3 font-bold font-sans text-slate-900">Mobile Camera Scanner</td>
                            <td class="py-2.5 px-3 text-emerald-700 font-bold">/scanner</td>
                            <td class="py-2.5 px-3 text-slate-600">http://192.168.1.24:8000/scanner</td>
                            <td class="py-2.5 px-3 font-sans text-slate-500">Scan barcode via kamera HP / tablet</td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 font-bold font-sans text-slate-900">Manajemen Batch &amp; FEFO</td>
                            <td class="py-2.5 px-3 text-emerald-700 font-bold">/batches</td>
                            <td class="py-2.5 px-3 text-slate-600">http://192.168.1.24:8000/batches</td>
                            <td class="py-2.5 px-3 font-sans text-slate-500">Pelacakan lot exp &amp; simulator alokasi</td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 font-bold font-sans text-slate-900">Marketplace Multi-Channel</td>
                            <td class="py-2.5 px-3 text-emerald-700 font-bold">/marketplace</td>
                            <td class="py-2.5 px-3 text-slate-600">http://192.168.1.24:8000/marketplace</td>
                            <td class="py-2.5 px-3 font-sans text-slate-500">Safety Buffer &amp; simulator order online</td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 font-bold font-sans text-slate-900">Transfer Stok Antar Gudang</td>
                            <td class="py-2.5 px-3 text-emerald-700 font-bold">/transfers</td>
                            <td class="py-2.5 px-3 text-slate-600">http://192.168.1.24:8000/transfers</td>
                            <td class="py-2.5 px-3 font-sans text-slate-500">Mutasi cabang &amp; cetak Surat Jalan</td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 font-bold font-sans text-slate-900">Analisis ABC &amp; Dead Stock</td>
                            <td class="py-2.5 px-3 text-emerald-700 font-bold">/analytics</td>
                            <td class="py-2.5 px-3 text-slate-600">http://192.168.1.24:8000/analytics</td>
                            <td class="py-2.5 px-3 font-sans text-slate-500">Pareto 80/20 &amp; modal kerja tertahan</td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 font-bold font-sans text-slate-900">Stock Opname Digital</td>
                            <td class="py-2.5 px-3 text-emerald-700 font-bold">/opname</td>
                            <td class="py-2.5 px-3 text-slate-600">http://192.168.1.24:8000/opname</td>
                            <td class="py-2.5 px-3 font-sans text-slate-500">Audit fisik &amp; cetak Berita Acara</td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 font-bold font-sans text-slate-900">Penerimaan Barang (Inbound)</td>
                            <td class="py-2.5 px-3 text-emerald-700 font-bold">/goods-receipts</td>
                            <td class="py-2.5 px-3 text-slate-600">http://192.168.1.24:8000/goods-receipts</td>
                            <td class="py-2.5 px-3 font-sans text-slate-500">QC penerimaan &amp; cetak GRN</td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 font-bold font-sans text-slate-900">Purchase Orders (PO)</td>
                            <td class="py-2.5 px-3 text-emerald-700 font-bold">/purchase-orders</td>
                            <td class="py-2.5 px-3 text-slate-600">http://192.168.1.24:8000/purchase-orders</td>
                            <td class="py-2.5 px-3 font-sans text-slate-500">Pengadaan barang ke vendor/supplier</td>
                        </tr>
                        <tr>
                            <td class="py-2.5 px-3 font-bold font-sans text-slate-900">Pusat Peringatan (Alert Center)</td>
                            <td class="py-2.5 px-3 text-emerald-700 font-bold">/alerts</td>
                            <td class="py-2.5 px-3 text-slate-600">http://192.168.1.24:8000/alerts</td>
                            <td class="py-2.5 px-3 font-sans text-slate-500">Low-stock &amp; barang kedaluwarsa</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Syarat dan Komitmen Kepatuhan -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-1">
                <strong class="text-slate-900 block mb-1">Ketentuan Kepatuhan Prosedur Operasional:</strong>
                <ol class="list-decimal pl-4 space-y-0.5 text-[11px]">
                    <li>Seluruh staf operasional wajib mencatatkan setiap perpindahan fisik barang secara real-time ke dalam sistem WMS.</li>
                    <li>Dilarang mengeluarkan barang tanpa adanya dokumen sah (Surat Jalan Pengiriman atau Surat Jalan Mutasi Antar Gudang).</li>
                    <li>Seluruh proses pengambilan barang wajib mematuhi rekomendasi urutan FEFO (*First Expired, First Out*) dari sistem.</li>
                    <li>Perbedaan stok saat audit fisik wajib segera dilaporkan kepada Kepala Gudang untuk dilakukan investigasi.</li>
                </ol>
            </div>

            <!-- Lembar Tanda Tangan 3 Pihak Sah -->
            <div class="grid grid-cols-3 gap-6 text-center text-xs mt-10 pt-4 border-t border-slate-200">
                <div>
                    <p class="text-slate-500 mb-1 text-[11px]">Disusun Oleh (Analis Sistem):</p>
                    <div class="h-16 flex items-center justify-center text-slate-300 text-xs italic">
                        ( Tanda Tangan &amp; Cap )
                    </div>
                    <div class="font-bold text-slate-900 underline">Khisal</div>
                    <div class="text-[10px] text-slate-500 font-mono">Lead Supply Chain &amp; System Architect</div>
                </div>

                <div>
                    <p class="text-slate-500 mb-1 text-[11px]">Diperiksa Oleh (Kepala Gudang):</p>
                    <div class="h-16 flex items-center justify-center text-slate-300 text-xs italic">
                        ( Tanda Tangan &amp; Cap )
                    </div>
                    <div class="font-bold text-slate-900 underline">Budi Santoso</div>
                    <div class="text-[10px] text-slate-500 font-mono">Warehouse Operations Manager</div>
                </div>

                <div>
                    <p class="text-slate-500 mb-1 text-[11px]">Disetujui Oleh (Direktur / Owner):</p>
                    <div class="h-16 flex items-center justify-center text-slate-300 text-xs italic">
                        ( Tanda Tangan &amp; Cap )
                    </div>
                    <div class="font-bold text-slate-900 underline">Khisal (Owner)</div>
                    <div class="text-[10px] text-slate-500 font-mono">Managing Director &amp; Founder</div>
                </div>
            </div>

        </div>

    </div>

</body>
</html>
