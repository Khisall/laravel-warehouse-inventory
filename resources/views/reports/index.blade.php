@extends('layouts.app')

@section('title', 'Inventory & Warehouse Reports')

@section('content')
<div class="space-y-6">

    <!-- Header with Print Button -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">Inventory & Financial Reports</h1>
            <p class="text-xs text-slate-400 mt-1">Laporan valuasi aset barang, sebaran stok antar gudang & status stok minimum</p>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-[#14222e] hover:bg-[#192b3b] border border-[#1f3344] text-slate-200 text-sm font-semibold flex items-center gap-2 transition">
                <i class="fa-solid fa-print text-emerald-400"></i>
                <span>Cetak Laporan (Print / PDF)</span>
            </button>
            <a href="{{ route('inventory.export-csv') }}" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-sm flex items-center gap-2 transition shadow-lg shadow-emerald-500/10">
                <i class="fa-solid fa-file-csv"></i>
                <span>Download CSV / Excel</span>
            </a>
        </div>
    </div>

    <!-- Financial KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 space-y-1">
            <div class="text-xs text-slate-400 font-medium">Total Nilai Valuasi (Harga Jual)</div>
            <div class="text-2xl font-extrabold text-emerald-400">${{ number_format($totalInventoryValue, 2) }}</div>
            <div class="text-[11px] text-slate-500">Estimasi total omset jika terjual seluruhnya</div>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 space-y-1">
            <div class="text-xs text-slate-400 font-medium">Total Nilai Modal (Cost Value)</div>
            <div class="text-2xl font-extrabold text-white">${{ number_format($totalCostValue, 2) }}</div>
            <div class="text-[11px] text-slate-500">Total modal aset tertahan di gudang</div>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 space-y-1">
            <div class="text-xs text-slate-400 font-medium">Potensi Laba Kotor (Gross Profit)</div>
            <div class="text-2xl font-extrabold text-cyan-400">${{ number_format($estimatedProfitPotential, 2) }}</div>
            <div class="text-[11px] text-cyan-400/80">Margin selisih harga jual dan modal</div>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 space-y-1">
            <div class="text-xs text-slate-400 font-medium">Total Unit Fisik Tersimpan</div>
            <div class="text-2xl font-extrabold text-amber-400">{{ number_format($totalStockQty) }} <span class="text-sm font-normal text-slate-400">Pcs</span></div>
            <div class="text-[11px] text-slate-500">Tersebar di {{ $warehouseStats->count() }} lokasi gudang</div>
        </div>
    </div>

    <!-- Breakdown Tables: Category & Warehouse -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Distribution by Category -->
        <div class="table-container p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f3344]">
                <h3 class="font-bold text-white text-base flex items-center gap-2">
                    <i class="fa-solid fa-shapes text-emerald-400"></i> Sebaran Stok Berdasarkan Kategori
                </h3>
            </div>
            <div class="divide-y divide-[#1f3344]/70">
                @foreach($categoryStats as $cat)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-white text-sm">{{ $cat->name }}</div>
                            <div class="text-xs text-slate-400">{{ $cat->products_count }} jenis barang (SKU)</div>
                        </div>
                        <div class="text-right">
                            <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 font-bold text-xs border border-emerald-500/20">
                                {{ $cat->products_sum_quantity ?? 0 }} pcs
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Distribution by Warehouse -->
        <div class="table-container p-5 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f3344]">
                <h3 class="font-bold text-white text-base flex items-center gap-2">
                    <i class="fa-solid fa-warehouse text-emerald-400"></i> Kapasitas & Sebaran per Gudang
                </h3>
            </div>
            <div class="divide-y divide-[#1f3344]/70">
                @foreach($warehouseStats as $wh)
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-white text-sm">{{ $wh->name }}</div>
                            <div class="text-xs text-slate-400">Kode Rak: <span class="text-cyan-400">{{ $wh->location }}</span></div>
                        </div>
                        <div class="text-right">
                            <span class="px-2.5 py-1 rounded-full bg-[#182835] text-white font-bold text-xs border border-[#22394c]">
                                {{ $wh->products_sum_quantity ?? 0 }} pcs
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Low Stock Alert Warning Box -->
    @if($lowStockItems->count() > 0)
        <div class="table-container p-5 space-y-3 border-amber-500/40">
            <div class="flex items-center justify-between pb-2 border-b border-[#1f3344]">
                <h3 class="font-bold text-amber-400 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> Peringatan Barang Menipis & Habis (Perlu Restock)
                </h3>
                <span class="text-xs text-amber-400/80">{{ $lowStockItems->count() }} barang</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($lowStockItems as $item)
                    <div class="p-3 bg-[#0e1922] border border-[#1f3344] rounded-xl flex items-center justify-between">
                        <div>
                            <div class="font-bold text-white text-xs">{{ $item->sku }}</div>
                            <div class="text-xs text-slate-300 truncate max-w-[180px]">{{ $item->title }}</div>
                            <div class="text-[11px] text-slate-500">{{ $item->warehouse_location }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-extrabold {{ $item->quantity == 0 ? 'text-rose-400' : 'text-amber-400' }}">
                                {{ $item->quantity }} pcs
                            </div>
                            <div class="text-[10px] text-slate-400">Min: {{ $item->min_stock }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
