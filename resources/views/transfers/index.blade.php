@extends('layouts.app')

@section('title', 'Transfer Stok Antar Gudang')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <i class="fa-solid fa-truck-ramp-box text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-100">Transfer Stok Antar Gudang</h1>
                    <p class="text-xs text-slate-400">Manajemen pergerakan barang antar gudang fisik, pelacakan armada ekspedisi & penerbitan Surat Jalan resmi.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('transfers.create') }}"
               class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-semibold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/10 transition">
                <i class="fa-solid fa-plus"></i>
                <span>Buat Mutasi Baru</span>
            </a>
        </div>
    </div>

    <!-- Alert Success / Error Flash -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Total Mutasi -->
        <div class="bg-[#13202b] border border-slate-800 rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Total Mutasi</p>
                <h3 class="text-2xl font-black text-slate-100 mt-1">{{ number_format($stats['total']) }}</h3>
                <span class="text-[10px] text-slate-500">Semua riwayat transfer</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-slate-800/80 border border-slate-700/60 flex items-center justify-center text-slate-300">
                <i class="fa-solid fa-arrows-split-up-and-left text-lg"></i>
            </div>
        </div>

        <!-- In-Transit -->
        <div class="bg-[#13202b] border border-amber-500/20 rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-amber-400 uppercase tracking-wider">Dalam Perjalanan</p>
                <h3 class="text-2xl font-black text-amber-300 mt-1">{{ number_format($stats['in_transit']) }}</h3>
                <span class="text-[10px] text-amber-400/70">Floating stock on delivery</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400">
                <i class="fa-solid fa-truck-fast text-lg animate-pulse"></i>
            </div>
        </div>

        <!-- Received -->
        <div class="bg-[#13202b] border border-emerald-500/20 rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-emerald-400 uppercase tracking-wider">Selesai Diterima</p>
                <h3 class="text-2xl font-black text-emerald-300 mt-1">{{ number_format($stats['received']) }}</h3>
                <span class="text-[10px] text-emerald-400/70">Telah diverifikasi tujuan</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                <i class="fa-solid fa-circle-check text-lg"></i>
            </div>
        </div>

        <!-- Draft -->
        <div class="bg-[#13202b] border border-slate-800 rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Draft Pengajuan</p>
                <h3 class="text-2xl font-black text-slate-200 mt-1">{{ number_format($stats['draft']) }}</h3>
                <span class="text-[10px] text-slate-500">Menunggu keberangkatan</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-slate-800/80 border border-slate-700/60 flex items-center justify-center text-slate-400">
                <i class="fa-solid fa-file-pen text-lg"></i>
            </div>
        </div>
    </div>

    <!-- Filters & Search Form -->
    <div class="bg-[#13202b] border border-slate-800 rounded-2xl p-5">
        <form method="GET" action="{{ route('transfers.index') }}" class="space-y-4">

            <!-- Status Tabs -->
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-800/80 pb-4">
                <a href="{{ route('transfers.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ !request()->filled('status') || request('status') === 'all' ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20' : 'bg-slate-800/80 text-slate-400 hover:text-slate-200 hover:bg-slate-800' }}">
                    Semua ({{ $stats['total'] }})
                </a>
                <a href="{{ route('transfers.index', array_merge(request()->except('status', 'page'), ['status' => 'in_transit'])) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ request('status') === 'in_transit' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20' : 'bg-slate-800/80 text-amber-400 hover:bg-amber-500/10' }}">
                    <i class="fa-solid fa-truck-fast text-[10px]"></i>
                    <span>Dalam Pengiriman ({{ $stats['in_transit'] }})</span>
                </a>
                <a href="{{ route('transfers.index', array_merge(request()->except('status', 'page'), ['status' => 'received'])) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ request('status') === 'received' ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20' : 'bg-slate-800/80 text-emerald-400 hover:bg-emerald-500/10' }}">
                    <i class="fa-solid fa-circle-check text-[10px]"></i>
                    <span>Selesai ({{ $stats['received'] }})</span>
                </a>
                <a href="{{ route('transfers.index', array_merge(request()->except('status', 'page'), ['status' => 'draft'])) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ request('status') === 'draft' ? 'bg-slate-200 text-slate-950 shadow-md' : 'bg-slate-800/80 text-slate-400 hover:bg-slate-800' }}">
                    <i class="fa-solid fa-file-pen text-[10px]"></i>
                    <span>Draft ({{ $stats['draft'] }})</span>
                </a>
                <a href="{{ route('transfers.index', array_merge(request()->except('status', 'page'), ['status' => 'cancelled'])) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'cancelled' ? 'bg-rose-500 text-white shadow-md' : 'bg-slate-800/80 text-rose-400 hover:bg-rose-500/10' }}">
                    Dibatalkan
                </a>
            </div>

            <!-- Inputs Grid -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3 pt-1">
                <!-- Search -->
                <div class="md:col-span-2 relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="Cari No. Transfer, Driver, No Plat, Resi..."
                           class="w-full pl-9 pr-4 py-2 bg-[#0b141b] border border-slate-700/80 rounded-xl text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-emerald-500">
                </div>

                <!-- Source Warehouse -->
                <div>
                    <select name="source_warehouse_id"
                            class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700/80 rounded-xl text-xs text-slate-200 focus:outline-none focus:border-emerald-500">
                        <option value="">-- Gudang Asal (Semua) --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ request('source_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                Dari: {{ $wh->name }} ({{ $wh->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Destination Warehouse -->
                <div>
                    <select name="destination_warehouse_id"
                            class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700/80 rounded-xl text-xs text-slate-200 focus:outline-none focus:border-emerald-500">
                        <option value="">-- Gudang Tujuan (Semua) --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ request('destination_warehouse_id') == $wh->id ? 'selected' : '' }}>
                                Ke: {{ $wh->name }} ({{ $wh->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Filter / Reset -->
                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="flex-1 py-2 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 font-semibold rounded-xl text-xs flex items-center justify-center gap-1.5 transition">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                    @if(request()->hasAny(['q', 'source_warehouse_id', 'destination_warehouse_id', 'status', 'date_from', 'date_to']))
                        <a href="{{ route('transfers.index') }}"
                           class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-slate-200 rounded-xl text-xs transition"
                           title="Reset Filter">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>

        </form>
    </div>

    <!-- Transfers Table Card -->
    <div class="bg-[#13202b] border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#0b141b]/80 border-b border-slate-800 text-[11px] uppercase tracking-wider text-slate-400 font-semibold">
                        <th class="py-3.5 px-4">No. Dokumen & Tanggal</th>
                        <th class="py-3.5 px-4">Rute Mutasi (Asal &rarr; Tujuan)</th>
                        <th class="py-3.5 px-4">Armada & Transporter</th>
                        <th class="py-3.5 px-4 text-center">Muatan</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-xs">
                    @forelse($transfers as $trf)
                        <tr class="hover:bg-[#162532]/60 transition">
                            <!-- No Dokumen & Tanggal -->
                            <td class="py-3.5 px-4">
                                <a href="{{ route('transfers.show', $trf->id) }}"
                                   class="font-mono font-bold text-emerald-400 hover:text-emerald-300 hover:underline flex items-center gap-1.5">
                                    <i class="fa-solid fa-barcode text-[10px] text-slate-500"></i>
                                    <span>{{ $trf->transfer_number }}</span>
                                </a>
                                <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1.5">
                                    <i class="fa-regular fa-calendar text-[10px]"></i>
                                    <span>{{ $trf->transfer_date?->translatedFormat('d M Y') }}</span>
                                    @if($trf->creator)
                                        <span class="text-slate-600">•</span>
                                        <span class="text-slate-500">{{ Str::limit($trf->creator->name, 16) }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Rute Mutasi -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2">
                                    <!-- Source -->
                                    <div class="px-2.5 py-1 rounded-lg bg-slate-800/80 border border-slate-700/60 text-[11px] text-slate-300 flex items-center gap-1.5">
                                        <i class="fa-solid fa-warehouse text-[10px] text-slate-400"></i>
                                        <span class="font-semibold">{{ $trf->sourceWarehouse->name }}</span>
                                    </div>

                                    <i class="fa-solid fa-arrow-right-long text-emerald-500 text-xs"></i>

                                    <!-- Destination -->
                                    <div class="px-2.5 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-[11px] text-emerald-300 flex items-center gap-1.5">
                                        <i class="fa-solid fa-location-dot text-[10px] text-emerald-400"></i>
                                        <span class="font-semibold">{{ $trf->destinationWarehouse->name }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Armada & Driver -->
                            <td class="py-3.5 px-4">
                                @if($trf->driver_name || $trf->vehicle_plate_number)
                                    <div class="text-slate-200 font-medium flex items-center gap-1.5">
                                        <i class="fa-solid fa-user-tag text-[10px] text-slate-500"></i>
                                        <span>{{ $trf->driver_name ?: 'Driver Belum Ditentukan' }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-2">
                                        @if($trf->vehicle_plate_number)
                                            <span class="font-mono bg-slate-800 px-1.5 py-0.5 rounded text-[10px] text-slate-300 border border-slate-700">
                                                {{ $trf->vehicle_plate_number }}
                                            </span>
                                        @endif
                                        <span>{{ $trf->expedition ?: 'Internal' }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-500 italic text-[11px]">- Menunggu penugasan armada -</span>
                                @endif
                            </td>

                            <!-- Muatan -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="font-semibold text-slate-200 font-mono">
                                    {{ $trf->total_quantity }} Unit
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    {{ $trf->total_items_count }} SKU Barang
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $trf->status_badge_class }}">
                                    <i class="fa-solid {{ $trf->status_icon }} text-[10px]"></i>
                                    <span>{{ $trf->status_label }}</span>
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('transfers.show', $trf->id) }}"
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-medium transition flex items-center gap-1"
                                       title="Lihat Detail">
                                        <i class="fa-regular fa-eye"></i>
                                        <span>Detail</span>
                                    </a>

                                    <a href="{{ route('transfers.surat-jalan', $trf->id) }}"
                                       target="_blank"
                                       class="px-2.5 py-1.5 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-xs font-medium transition flex items-center gap-1"
                                       title="Cetak Surat Jalan">
                                        <i class="fa-solid fa-print"></i>
                                        <span>Surat Jalan</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-500">
                                <div class="w-16 h-16 mx-auto rounded-full bg-slate-800/60 flex items-center justify-center text-slate-600 mb-3">
                                    <i class="fa-solid fa-truck-ramp-box text-2xl"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-400">Tidak ada data transfer stok ditemukan.</p>
                                <p class="text-xs text-slate-600 mt-1">Coba sesuaikan kata kunci pencarian atau buat dokumen mutasi baru.</p>
                                <div class="mt-4">
                                    <a href="{{ route('transfers.create') }}"
                                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs transition">
                                        <i class="fa-solid fa-plus"></i> Buat Mutasi Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
            <div class="p-4 border-t border-slate-800 bg-[#0b141b]/60">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
