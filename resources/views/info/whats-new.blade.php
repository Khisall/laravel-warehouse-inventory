@extends('layouts.app')

@section('title', "What's New")

@section('content')
<div class="space-y-6 max-w-3xl">
    <div>
        <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">What's New in INVENTORY</h1>
        <p class="text-xs text-slate-400 mt-1">Pembaruan fitur sistem pergudangan & sinkronisasi otomatis</p>
    </div>

    <div class="space-y-4">
        <div class="bg-[#13202b] border border-emerald-500/30 rounded-2xl p-6 space-y-2">
            <div class="flex items-center justify-between">
                <span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-bold">Versi 2.0 - Laravel 12</span>
                <span class="text-xs text-slate-400">Rilis Terbaru</span>
            </div>
            <h3 class="font-bold text-white text-base">Desain Antarmuka Premium Figma Match</h3>
            <p class="text-xs text-slate-300 leading-relaxed">
                Aplikasi inventaris kini hadir dengan tampilan Dark Mode modern terinspirasi langsung dari rancangan antarmuka Figma, dilengkapi manajemen SKU, foto produk, sebaran lokasi rak gudang, dan sinkronisasi marketplace.
            </p>
            <ul class="text-xs text-slate-300 list-disc list-inside space-y-1 pt-2">
                <li>Manajemen SKU lengkap dengan variasi produk (Variation badge).</li>
                <li>Quick Stock In & Out langsung dari tabel inventaris.</li>
                <li>Audit trail lengkap mutasi barang (Activity Log).</li>
                <li>Ekspor data ke format CSV / Excel sekali klik.</li>
                <li>Modul Pesanan dan Pengiriman (Orders & Shipping).</li>
            </ul>
        </div>
    </div>
</div>
@endsection
