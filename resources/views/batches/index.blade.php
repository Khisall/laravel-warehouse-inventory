@extends('layouts.app')

@section('title', 'Manajemen Batch & Sistem Picking FEFO')

@section('content')
<div class="space-y-6" x-data="{ showCreateModal: false, showFefoSimulator: false, simProductId: '{{ $products->first()?->id }}', simQty: 10, simResult: null, isSimulating: false }">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <i class="fa-solid fa-boxes-packing text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-100">Manajemen Batch &amp; Sistem Picking FEFO</h1>
                    <p class="text-xs text-slate-400">Pelacakan nomor lot/batch, kontrol kedaluwarsa dinamis, dan algoritma alokasi <em>First Expired, First Out</em> (FEFO).</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="button"
                    @click="showFefoSimulator = true"
                    class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold text-xs flex items-center gap-2 transition shadow-sm">
                <i class="fa-solid fa-wand-magic-sparkles text-emerald-400"></i>
                <span>Simulasi Alokasi FEFO</span>
            </button>

            @if(in_array(auth()->user()?->role, ['super_admin', 'warehouse_manager', 'warehouse_staff']))
                <button type="button"
                        @click="showCreateModal = true"
                        class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-semibold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/10 transition">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tambah Batch Baru</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Alert Flash -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Total Batches -->
        <div class="bg-[#13202b] border border-slate-800 rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Total Lot / Batch</p>
                <h3 class="text-2xl font-black text-slate-100 mt-1">{{ number_format($stats['total']) }}</h3>
                <span class="text-[10px] text-slate-500">Semua batch terdaftar</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-slate-800/80 border border-slate-700/60 flex items-center justify-center text-slate-300">
                <i class="fa-solid fa-layer-group text-lg"></i>
            </div>
        </div>

        <!-- Active Batches -->
        <div class="bg-[#13202b] border border-emerald-500/20 rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-emerald-400 uppercase tracking-wider">Batch Aktif</p>
                <h3 class="text-2xl font-black text-emerald-300 mt-1">{{ number_format($stats['active']) }}</h3>
                <span class="text-[10px] text-emerald-400/70">Siap dialokasikan FEFO</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                <i class="fa-solid fa-circle-check text-lg"></i>
            </div>
        </div>

        <!-- Expiring Soon (Priority 1 FEFO) -->
        <div class="bg-[#13202b] border border-amber-500/20 rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-amber-400 uppercase tracking-wider">Expiring &le; 30 Hari</p>
                <h3 class="text-2xl font-black text-amber-300 mt-1">{{ number_format($stats['expiring_soon']) }}</h3>
                <span class="text-[10px] text-amber-400/70">Prioritas utama keluar</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400">
                <i class="fa-solid fa-stopwatch text-lg animate-pulse"></i>
            </div>
        </div>

        <!-- Quarantine Batches -->
        <div class="bg-[#13202b] border border-slate-800 rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">Karantina / Hold</p>
                <h3 class="text-2xl font-black text-amber-200 mt-1">{{ number_format($stats['quarantine']) }}</h3>
                <span class="text-[10px] text-slate-500">Terkunci dari picking</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-300">
                <i class="fa-solid fa-shield-halved text-lg"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="bg-[#13202b] border border-slate-800 rounded-2xl p-5 space-y-4">
        <form method="GET" action="{{ route('batches.index') }}" class="space-y-4">

            <!-- Status Tabs -->
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-800/80 pb-4">
                <a href="{{ route('batches.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ !request()->filled('status') || request('status') === 'all' ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20' : 'bg-slate-800/80 text-slate-400 hover:text-slate-200 hover:bg-slate-800' }}">
                    Semua ({{ $stats['total'] }})
                </a>
                <a href="{{ route('batches.index', array_merge(request()->except('status', 'page'), ['status' => 'active'])) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ request('status') === 'active' ? 'bg-emerald-500 text-slate-950 shadow-md shadow-emerald-500/20' : 'bg-slate-800/80 text-emerald-400 hover:bg-emerald-500/10' }}">
                    <i class="fa-solid fa-circle-check text-[10px]"></i>
                    <span>Aktif ({{ $stats['active'] }})</span>
                </a>
                <a href="{{ route('batches.index', array_merge(request()->except('status', 'page'), ['status' => 'expiring_soon'])) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ request('status') === 'expiring_soon' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20' : 'bg-slate-800/80 text-amber-400 hover:bg-amber-500/10' }}">
                    <i class="fa-solid fa-stopwatch text-[10px]"></i>
                    <span>Segera Expired ({{ $stats['expiring_soon'] }})</span>
                </a>
                <a href="{{ route('batches.index', array_merge(request()->except('status', 'page'), ['status' => 'quarantine'])) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ request('status') === 'quarantine' ? 'bg-amber-500 text-slate-950 shadow-md' : 'bg-slate-800/80 text-amber-300 hover:bg-amber-500/10' }}">
                    <i class="fa-solid fa-shield-halved text-[10px]"></i>
                    <span>Karantina ({{ $stats['quarantine'] }})</span>
                </a>
                <a href="{{ route('batches.index', array_merge(request()->except('status', 'page'), ['status' => 'depleted'])) }}"
                   class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'depleted' ? 'bg-slate-600 text-white shadow-md' : 'bg-slate-800/80 text-slate-400 hover:bg-slate-800' }}">
                    Habis Terjual
                </a>
            </div>

            <!-- Inputs Grid -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3 pt-1">
                <div class="md:col-span-2 relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                    <input type="text"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="Cari Nomor Lot / Batch, SKU, Nama Barang, Lokasi Rak..."
                           class="w-full pl-9 pr-4 py-2 bg-[#0b141b] border border-slate-700/80 rounded-xl text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <select name="product_id"
                            class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700/80 rounded-xl text-xs text-slate-200 focus:outline-none focus:border-emerald-500">
                        <option value="">-- Semua Produk --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                                {{ Str::limit($p->title, 25) }} ({{ $p->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select name="warehouse_id"
                            class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700/80 rounded-xl text-xs text-slate-200 focus:outline-none focus:border-emerald-500">
                        <option value="">-- Semua Gudang --</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="flex-1 py-2 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 font-semibold rounded-xl text-xs flex items-center justify-center gap-1.5 transition">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                    @if(request()->hasAny(['q', 'product_id', 'warehouse_id', 'status']))
                        <a href="{{ route('batches.index') }}"
                           class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-slate-200 rounded-xl text-xs transition">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </div>

        </form>
    </div>

    <!-- Batches Table Card -->
    <div class="bg-[#13202b] border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#0b141b]/80 border-b border-slate-800 text-[11px] uppercase tracking-wider text-slate-400 font-semibold">
                        <th class="py-3.5 px-4">Nomor Batch / Lot</th>
                        <th class="py-3.5 px-4">Produk &amp; Lokasi Rak</th>
                        <th class="py-3.5 px-4 text-center">Gudang</th>
                        <th class="py-3.5 px-4 text-center">Sisa Stok</th>
                        <th class="py-3.5 px-4 text-center">Kedaluwarsa (Exp)</th>
                        <th class="py-3.5 px-4 text-center">Prioritas FEFO</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-xs">
                    @forelse($batches as $batch)
                        <tr class="hover:bg-[#162532]/60 transition">
                            <!-- Batch Number -->
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-slate-100 flex items-center gap-1.5">
                                    <i class="fa-solid fa-barcode text-[10px] text-emerald-400"></i>
                                    <span>{{ $batch->batch_number }}</span>
                                </span>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    Prod: {{ $batch->manufacturing_date ? $batch->manufacturing_date->translatedFormat('d M Y') : '-' }}
                                </div>
                            </td>

                            <!-- Product & Rack -->
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-200">{{ $batch->product?->title }}</div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="font-mono text-[11px] text-emerald-400 font-bold">{{ $batch->product?->sku }}</span>
                                    @if($batch->location_code)
                                        <span class="px-1.5 py-0.5 rounded bg-slate-800 border border-slate-700 text-[10px] font-mono text-cyan-300">
                                            <i class="fa-solid fa-map-pin text-[8px] mr-1"></i>{{ $batch->location_code }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Warehouse -->
                            <td class="py-3.5 px-4 text-center text-slate-300 text-[11px]">
                                {{ $batch->warehouse?->name }}
                            </td>

                            <!-- Sisa Stok -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="font-mono font-bold text-slate-100">
                                    {{ $batch->current_quantity }} / {{ $batch->initial_quantity }} {{ $batch->product?->unit ?: 'pcs' }}
                                </div>
                                <div class="w-20 mx-auto bg-slate-800 rounded-full h-1.5 mt-1 overflow-hidden">
                                    @php
                                        $pct = $batch->initial_quantity > 0 ? ($batch->current_quantity / $batch->initial_quantity) * 100 : 0;
                                    @endphp
                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                            </td>

                            <!-- Expiry Countdown -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] border {{ $batch->expiry_badge_class }}">
                                    <i class="fa-regular fa-clock text-[9px]"></i>
                                    <span>{{ $batch->expiry_date?->translatedFormat('d M Y') }}</span>
                                </span>
                                <div class="text-[10px] text-slate-400 mt-0.5 font-mono">
                                    @if($batch->days_until_expiry < 0)
                                        <span class="text-rose-400 font-bold">Lewat {{ abs($batch->days_until_expiry) }} hari</span>
                                    @else
                                        <span>{{ $batch->days_until_expiry }} hari lagi</span>
                                    @endif
                                </div>
                            </td>

                            <!-- FEFO Priority Badge -->
                            <td class="py-3.5 px-4 text-center">
                                @if($batch->status === 'quarantine')
                                    <span class="px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-300 text-[10px] border border-amber-500/30">
                                        <i class="fa-solid fa-ban text-[8px] mr-1"></i>Hold
                                    </span>
                                @elseif($batch->current_quantity <= 0)
                                    <span class="text-slate-500 text-[10px] italic">Habis</span>
                                @elseif($batch->days_until_expiry <= 30)
                                    <span class="px-2 py-0.5 rounded-md bg-rose-500/15 text-rose-300 text-[10px] border border-rose-500/30 font-bold animate-pulse">
                                        Prioritas #1 (Pick Segera)
                                    </span>
                                @elseif($batch->days_until_expiry <= 90)
                                    <span class="px-2 py-0.5 rounded-md bg-yellow-500/15 text-yellow-300 text-[10px] border border-yellow-500/30">
                                        Prioritas #2
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-400 text-[10px] border border-emerald-500/20">
                                        Prioritas Normal
                                    </span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $batch->status_badge_class }}">
                                    {{ $batch->status_label }}
                                </span>
                            </td>

                            <!-- Action -->
                            <td class="py-3.5 px-4 text-right">
                                @if(in_array(auth()->user()?->role, ['super_admin', 'warehouse_manager']))
                                    <form method="POST" action="{{ route('batches.toggle-quarantine', $batch->id) }}" class="inline-block">
                                        @csrf
                                        @if($batch->status === 'quarantine')
                                            <button type="submit"
                                                    class="px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-xs font-medium transition"
                                                    title="Lepas Karantina">
                                                <i class="fa-solid fa-lock-open text-[10px] mr-1"></i> Lepas
                                            </button>
                                        @else
                                            <button type="submit"
                                                    class="px-2.5 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/30 text-xs font-medium transition"
                                                    title="Karantina / Hold Batch Ini">
                                                <i class="fa-solid fa-shield-halved text-[10px] mr-1"></i> Hold
                                            </button>
                                        @endif
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-500">
                                <i class="fa-solid fa-boxes-packing text-3xl mb-2 block"></i>
                                Tidak ada data batch ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($batches->hasPages())
            <div class="p-4 border-t border-slate-800 bg-[#0b141b]/60">
                {{ $batches->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Batch Baru -->
    <div x-show="showCreateModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         x-cloak>
        <div class="bg-[#13202b] border border-slate-700 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2.5 text-emerald-400">
                    <i class="fa-solid fa-boxes-packing text-lg"></i>
                    <h3 class="text-base font-bold text-slate-100">Tambah Batch / Lot Baru</h3>
                </div>
                <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-200">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('batches.store') }}" class="space-y-3.5">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Pilih Produk <span class="text-rose-400">*</span></label>
                    <select name="product_id" required class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs text-slate-100 focus:outline-none focus:border-emerald-500">
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->title }} ({{ $p->sku }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Gudang Penyimpanan <span class="text-rose-400">*</span></label>
                        <select name="warehouse_id" required class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs text-slate-100 focus:outline-none focus:border-emerald-500">
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nomor Batch / Lot <span class="text-rose-400">*</span></label>
                        <input type="text" name="batch_number" required placeholder="Contoh: LOT-202611-B01" class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs font-mono uppercase text-slate-100 focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kuantitas Masuk <span class="text-rose-400">*</span></label>
                        <input type="number" name="initial_quantity" min="1" required placeholder="10" class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs font-mono font-bold text-slate-100 focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Lokasi Rak / Bin</label>
                        <input type="text" name="location_code" placeholder="Contoh: RAK-B-04" class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs font-mono uppercase text-slate-100 focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Tgl Manufaktur / Produksi</label>
                        <input type="date" name="manufacturing_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs text-slate-100 focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Tgl Kedaluwarsa (Exp) <span class="text-rose-400">*</span></label>
                        <input type="date" name="expiry_date" required value="{{ date('Y-m-d', strtotime('+1 year')) }}" class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs text-slate-100 focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan Tambahan</label>
                    <input type="text" name="notes" placeholder="Catatan sertifikat COA, kondisi fisik dll..." class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs text-slate-100 focus:outline-none focus:border-emerald-500">
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-500 hover:bg-emerald-600 text-slate-950 text-xs font-bold rounded-xl transition">Simpan Batch</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Simulasi Alokasi FEFO -->
    <div x-show="showFefoSimulator"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         x-cloak>
        <div class="bg-[#13202b] border border-slate-700 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2.5 text-emerald-400">
                    <i class="fa-solid fa-wand-magic-sparkles text-lg"></i>
                    <div>
                        <h3 class="text-base font-bold text-slate-100">Simulasi Algoritma Picking FEFO</h3>
                        <p class="text-xs text-slate-400">Uji coba simulasi pengambilan barang berbasis tanggal expired terdekat</p>
                    </div>
                </div>
                <button type="button" @click="showFefoSimulator = false" class="text-slate-400 hover:text-slate-200">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Pilih Produk</label>
                        <select x-model="simProductId" class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs text-slate-100 focus:outline-none focus:border-emerald-500">
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->title }} ({{ $p->sku }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kuantitas Order</label>
                        <input type="number" x-model.number="simQty" min="1" class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs font-mono font-bold text-slate-100 focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <button type="button"
                        @click="
                            isSimulating = true;
                            fetch('{{ route('batches.fefo-recommend') }}?product_id=' + simProductId + '&quantity=' + simQty)
                                .then(res => res.json())
                                .then(data => { simResult = data; isSimulating = false; })
                                .catch(err => { console.error(err); isSimulating = false; });
                        "
                        class="w-full py-2 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition">
                    <i class="fa-solid fa-play" x-show="!isSimulating"></i>
                    <i class="fa-solid fa-circle-notch fa-spin" x-show="isSimulating"></i>
                    <span>Jalankan Rekomendasi FEFO</span>
                </button>
            </div>

            <!-- Simulation Result Preview -->
            <div x-show="simResult" class="pt-3 border-t border-slate-800 space-y-3">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-400">Hasil Rekomendasi Alokasi:</span>
                    <span class="font-mono font-bold text-emerald-400" x-text="'Total Tersedia: ' + simResult?.total_batch_stock + ' Unit'"></span>
                </div>

                <template x-if="simResult?.batches_to_pick?.length > 0">
                    <div class="space-y-2">
                        <template x-for="(pick, idx) in simResult.batches_to_pick" :key="idx">
                            <div class="p-3 rounded-xl bg-[#0b141b] border border-slate-800 flex items-center justify-between text-xs">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-mono font-bold text-[10px]" x-text="idx + 1"></span>
                                        <strong class="font-mono text-slate-100" x-text="pick.batch_number"></strong>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-cyan-300" x-text="'Rak: ' + pick.location_code"></span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 ml-7" x-text="'Exp: ' + pick.expiry_display + ' (' + pick.days_until_expiry + ' hari lagi)'"></div>
                                </div>
                                <div class="text-right">
                                    <span class="font-mono font-black text-emerald-400 text-sm" x-text="'Ambil ' + pick.pick_qty + ' Unit'"></span>
                                    <div class="text-[10px] text-slate-500" x-text="'Sisa: ' + pick.remaining_after"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="simResult?.batches_to_pick?.length === 0">
                    <div class="p-3 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs text-center">
                        Tidak ada batch aktif yang siap dipick untuk produk ini.
                    </div>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection
