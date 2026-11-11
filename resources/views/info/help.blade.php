@extends('layouts.app')

@section('title', 'Help & Support')

@section('content')
<div class="space-y-6 max-w-3xl">
    <div>
        <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">Help & Warehouse Support</h1>
        <p class="text-xs text-slate-400 mt-1">Panduan penggunaan & kontak bantuan operasional gudang</p>
    </div>

    <div class="space-y-4">
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 space-y-2">
            <h4 class="font-bold text-white text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-question text-emerald-400"></i> Bagaimana cara menambah barang baru?
            </h4>
            <p class="text-xs text-slate-300 leading-relaxed">
                Klik tombol <strong>"Add New >"</strong> pada bagian kanan atas Dashboard. Isi SKU (atau klik tombol Auto), nama barang, kategori, lokasi rak gudang, kuantiti masuk awal, dan harga. Klik <strong>"Simpan ke Gudang"</strong>.
            </p>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 space-y-2">
            <h4 class="font-bold text-white text-sm flex items-center gap-2">
                <i class="fa-solid fa-arrow-right-arrow-left text-emerald-400"></i> Bagaimana cara mencatat barang masuk / keluar cepat?
            </h4>
            <p class="text-xs text-slate-300 leading-relaxed">
                Pada baris tabel barang, klik ikon <strong>"+/-" (Mutasi Stok Cepat)</strong>. Pilih Barang Masuk (+In) atau Barang Keluar (-Out), isi jumlah dan nomor surat jalan. Stok akan langsung terupdate dan dicatat di tab <strong>Activity Log</strong>.
            </p>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 space-y-2">
            <h4 class="font-bold text-white text-sm flex items-center gap-2">
                <i class="fa-solid fa-file-csv text-emerald-400"></i> Bagaimana cara ekspor data laporan ke Excel?
            </h4>
            <p class="text-xs text-slate-300 leading-relaxed">
                Klik tombol <strong>"Action >"</strong> di atas tabel lalu pilih <strong>"Export ke CSV (Excel)"</strong>, atau buka menu <strong>Reports</strong> di sidebar sebelah kiri.
            </p>
        </div>
    </div>
</div>
@endsection
