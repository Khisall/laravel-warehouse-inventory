@extends('layouts.app')

@section('title', 'Pusat Peringatan & Notifikasi Otomatis - INVENTORY')

@section('content')
<div class="space-y-6" x-data="{ writeOffModal: false, activeProduct: null, writeOffQty: 1, writeOffReason: 'Kedaluwarsa / Rusak saat penyimpanan' }">

    <!-- Header & Action Row -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 text-lg">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    Pusat Peringatan Stok & Kedaluwarsa
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-rose-500/15 text-rose-400 border border-rose-500/30 font-medium font-mono">
                        {{ $outOfStockCount + $lowStockCount + $expiredCount + $expiringSoonCount }} Kritis
                    </span>
                </h1>
                <p class="text-xs text-slate-400">Deteksi otomatis barang habis/menipis dan jadwal kedaluwarsa batch untuk pencegahan kerugian logistik.</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('purchase-orders.create') }}"
               class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/20 transition hover:scale-[1.02]">
                <i class="fa-solid fa-cart-plus"></i>
                <span>Buat PO Restock Supplier</span>
            </a>
            <a href="{{ route('opname.index') }}"
               class="px-3.5 py-2.5 rounded-xl bg-[#182633] hover:bg-[#203344] text-slate-300 font-semibold text-xs flex items-center gap-2 border border-[#1f3344] transition">
                <i class="fa-solid fa-clipboard-check text-emerald-400"></i>
                <span>Audit Opname</span>
            </a>
        </div>
    </div>

    <!-- Metric Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Out of Stock -->
        <a href="{{ route('alerts.index', ['tab' => 'stock', 'severity' => 'out_of_stock']) }}"
           class="bg-[#13202b] border border-[#1f3344] hover:border-rose-500/50 rounded-2xl p-4 flex items-center justify-between transition group">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Stok Habis (0 Unit)</p>
                <h3 class="text-2xl font-extrabold text-rose-400 mt-1">{{ number_format($outOfStockCount) }}</h3>
                <p class="text-[11px] text-rose-500/80 mt-0.5">Operasional terhenti</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 text-xl group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-ban"></i>
            </div>
        </a>

        <!-- Low Stock -->
        <a href="{{ route('alerts.index', ['tab' => 'stock', 'severity' => 'low_stock']) }}"
           class="bg-[#13202b] border border-[#1f3344] hover:border-amber-500/50 rounded-2xl p-4 flex items-center justify-between transition group">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Stok Menipis (≤ Min)</p>
                <h3 class="text-2xl font-extrabold text-amber-400 mt-1">{{ number_format($lowStockCount) }}</h3>
                <p class="text-[11px] text-amber-500/80 mt-0.5">Perlu reorder segera</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 text-xl group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-battery-quarter"></i>
            </div>
        </a>

        <!-- Expired -->
        <a href="{{ route('alerts.index', ['tab' => 'expiry', 'expiry_window' => 'expired']) }}"
           class="bg-[#13202b] border border-[#1f3344] hover:border-rose-500/50 rounded-2xl p-4 flex items-center justify-between transition group">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Sudah Kedaluwarsa</p>
                <h3 class="text-2xl font-extrabold text-rose-400 mt-1">{{ number_format($expiredCount) }}</h3>
                <p class="text-[11px] text-rose-500/80 mt-0.5">Wajib karantina / musnah</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 text-xl group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-calendar-xmark"></i>
            </div>
        </a>

        <!-- Expiring Soon (< 30 Hari) -->
        <a href="{{ route('alerts.index', ['tab' => 'expiry', 'expiry_window' => '30_days']) }}"
           class="bg-[#13202b] border border-[#1f3344] hover:border-yellow-500/50 rounded-2xl p-4 flex items-center justify-between transition group">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Kritis Expired (≤ 30 Hari)</p>
                <h3 class="text-2xl font-extrabold text-yellow-400 mt-1">{{ number_format($expiringSoonCount) }}</h3>
                <p class="text-[11px] text-yellow-500/80 mt-0.5">Prioritaskan pengeluaran FEFO</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-yellow-500/10 border border-yellow-500/20 flex items-center justify-center text-yellow-400 text-xl group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </a>
    </div>

    <!-- Financial Impact Overview (Role-protected for managers & super_admin) -->
    @if(auth()->user()?->canViewFinancials())
        <div class="bg-gradient-to-r from-[#12232f] via-[#162532] to-[#1a2b25] border border-[#1f3344] rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                    <i class="fa-solid fa-scale-balanced text-base"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300">Estimasi Kebutuhan Modal Restock:</h4>
                    <p class="text-lg font-extrabold text-white mt-0.5">
                        Rp {{ number_format($restockValueRequired, 0, ',', '.') }}
                        <span class="text-xs font-normal text-slate-400">untuk {{ $totalNeedsReorder }} SKU</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 border-t md:border-t-0 md:border-l border-[#1f3344] pt-3 md:pt-0 md:pl-6">
                <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/30 flex items-center justify-center text-rose-400">
                    <i class="fa-solid fa-circle-exclamation text-base"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-300">Valuasi Stok Berisiko Expired:</h4>
                    <p class="text-lg font-extrabold text-rose-400 mt-0.5">
                        Rp {{ number_format($expiredStockValue + $expiringSoonValue, 0, ',', '.') }}
                        <span class="text-xs font-normal text-slate-400">total potensi kerugian</span>
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Navigation Tabs: Stock vs Expiry -->
    <div class="flex items-center border-b border-[#1f3344] gap-6 text-sm">
        <a href="{{ route('alerts.index', ['tab' => 'stock']) }}"
           class="pb-3 font-bold transition flex items-center gap-2 border-b-2 {{ $tab === 'stock' ? 'border-amber-400 text-amber-400' : 'border-transparent text-slate-400 hover:text-white' }}">
            <i class="fa-solid fa-boxes-stacked"></i>
            <span>Peringatan Stok Menipis & Habis</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-mono {{ $tab === 'stock' ? 'bg-amber-400/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                {{ $totalNeedsReorder }}
            </span>
        </a>

        <a href="{{ route('alerts.index', ['tab' => 'expiry']) }}"
           class="pb-3 font-bold transition flex items-center gap-2 border-b-2 {{ $tab === 'expiry' ? 'border-rose-400 text-rose-400' : 'border-transparent text-slate-400 hover:text-white' }}">
            <i class="fa-solid fa-calendar-xmark"></i>
            <span>Monitoring Kedaluwarsa (FEFO)</span>
            <span class="px-2 py-0.5 rounded-full text-xs font-mono {{ $tab === 'expiry' ? 'bg-rose-400/20 text-rose-300' : 'bg-slate-800 text-slate-400' }}">
                {{ $expiredCount + $expiringSoonCount }}
            </span>
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-3.5 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <!-- Sub-filter Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
            @if($tab === 'stock')
                <a href="{{ route('alerts.index', array_merge(request()->except('severity'), ['tab' => 'stock'])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ !request()->filled('severity') ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'text-slate-400 hover:bg-[#182633]' }}">
                    Semua Perlu Restock ({{ $totalNeedsReorder }})
                </a>
                <a href="{{ route('alerts.index', array_merge(request()->except('severity'), ['tab' => 'stock', 'severity' => 'out_of_stock'])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('severity') === 'out_of_stock' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'text-slate-400 hover:bg-[#182633]' }}">
                    Habis (0 Stok)
                </a>
                <a href="{{ route('alerts.index', array_merge(request()->except('severity'), ['tab' => 'stock', 'severity' => 'low_stock'])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('severity') === 'low_stock' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'text-slate-400 hover:bg-[#182633]' }}">
                    Menipis (≤ Min)
                </a>
            @else
                <a href="{{ route('alerts.index', array_merge(request()->except('expiry_window'), ['tab' => 'expiry'])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ !request()->filled('expiry_window') ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'text-slate-400 hover:bg-[#182633]' }}">
                    Semua Barang Berjadwal
                </a>
                <a href="{{ route('alerts.index', array_merge(request()->except('expiry_window'), ['tab' => 'expiry', 'expiry_window' => 'expired'])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('expiry_window') === 'expired' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'text-slate-400 hover:bg-[#182633]' }}">
                    Sudah Expired ({{ $expiredCount }})
                </a>
                <a href="{{ route('alerts.index', array_merge(request()->except('expiry_window'), ['tab' => 'expiry', 'expiry_window' => '30_days'])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('expiry_window') === '30_days' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'text-slate-400 hover:bg-[#182633]' }}">
                    Kritis (≤ 30 Hari)
                </a>
                <a href="{{ route('alerts.index', array_merge(request()->except('expiry_window'), ['tab' => 'expiry', 'expiry_window' => '90_days'])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('expiry_window') === '90_days' ? 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30' : 'text-slate-400 hover:bg-[#182633]' }}">
                    Perhatian (≤ 90 Hari)
                </a>
            @endif
        </div>

        <!-- Search & Filter Form -->
        <form method="GET" action="{{ route('alerts.index') }}" class="flex items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            @if(request()->filled('severity'))
                <input type="hidden" name="severity" value="{{ request('severity') }}">
            @endif
            @if(request()->filled('expiry_window'))
                <input type="hidden" name="expiry_window" value="{{ request('expiry_window') }}">
            @endif

            <select name="warehouse_id" onchange="this.form.submit()" class="px-2.5 py-1.5 bg-[#0e1821] border border-[#1f3344] rounded-lg text-xs text-white">
                <option value="">Semua Gudang</option>
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                @endforeach
            </select>

            <div class="relative w-48 md:w-56">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari SKU, Nama, Batch..."
                       class="w-full pl-8 pr-3 py-1.5 bg-[#0e1821] border border-[#1f3344] rounded-lg text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500">
            </div>
            @if(request()->filled('search') || request()->filled('warehouse_id'))
                <a href="{{ route('alerts.index', ['tab' => $tab]) }}" class="px-2.5 py-1.5 bg-slate-800 text-slate-400 rounded-lg text-xs" title="Reset filter">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- TAB 1: LOW STOCK & OUT OF STOCK TABLE -->
    @if($tab === 'stock')
        <div class="table-container overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-[#1f3344] bg-[#0f1b24]/80 text-slate-400 uppercase tracking-wider font-semibold">
                            <th class="py-3 px-4">Barang / SKU</th>
                            <th class="py-3 px-4">Lokasi & Rak</th>
                            <th class="py-3 px-4 text-center">Stok Saat Ini</th>
                            <th class="py-3 px-4 text-center">Batas Minimum</th>
                            <th class="py-3 px-4 text-center">Tingkat Bahaya</th>
                            <th class="py-3 px-4 text-center">Rekomendasi Restock</th>
                            @if(auth()->user()?->canViewFinancials())
                                <th class="py-3 px-4 text-right">Estimasi Modal</th>
                            @endif
                            <th class="py-3 px-4 text-center">Aksi Cepat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#1f3344]/50">
                        @forelse($products as $prod)
                            <tr class="hover:bg-[#162532]/70 transition-colors">
                                <!-- SKU & Title -->
                                <td class="py-3.5 px-4">
                                    <div class="font-mono font-bold text-amber-400 text-xs">{{ $prod->sku }}</div>
                                    <div class="font-semibold text-white mt-0.5">{{ $prod->title }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $prod->category?->name ?? 'Tanpa Kategori' }}</div>
                                </td>

                                <!-- Location -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5 text-slate-200">
                                        <i class="fa-solid fa-map-pin text-emerald-400 text-[11px]"></i>
                                        <span>{{ $prod->warehouse_location ?? 'Belum Diatur' }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-500">{{ $prod->warehouse?->name ?? 'Gudang' }}</div>
                                </td>

                                <!-- Quantity -->
                                <td class="py-3.5 px-4 text-center font-mono">
                                    <span class="text-sm font-extrabold {{ $prod->quantity <= 0 ? 'text-rose-400' : 'text-amber-400' }}">
                                        {{ $prod->quantity }}
                                    </span>
                                    <span class="text-[10px] text-slate-500">{{ $prod->unit }}</span>
                                </td>

                                <!-- Min Stock -->
                                <td class="py-3.5 px-4 text-center font-mono text-slate-300">
                                    {{ $prod->min_stock }} {{ $prod->unit }}
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($prod->quantity <= 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30 animate-pulse">
                                            <i class="fa-solid fa-triangle-exclamation mr-1"></i> Habis Total
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                            <i class="fa-solid fa-battery-quarter mr-1"></i> Menipis
                                        </span>
                                    @endif
                                </td>

                                <!-- Suggested Restock -->
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-emerald-400 text-sm">
                                    +{{ $prod->suggested_reorder_qty }} {{ $prod->unit }}
                                </td>

                                <!-- Financial Cost -->
                                @if(auth()->user()?->canViewFinancials())
                                    <td class="py-3.5 px-4 text-right font-mono text-xs">
                                        <div class="text-white font-bold">
                                            Rp {{ number_format($prod->suggested_reorder_qty * $prod->cost_price, 0, ',', '.') }}
                                        </div>
                                        <div class="text-[10px] text-slate-500">@ Rp {{ number_format($prod->cost_price, 0, ',', '.') }}</div>
                                    </td>
                                @endif

                                <!-- Action Buttons -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('purchase-orders.create') }}"
                                           class="px-2.5 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs flex items-center gap-1 shadow transition"
                                           title="Buat PO Pemesanan ke Supplier">
                                            <i class="fa-solid fa-cart-plus"></i>
                                            <span>Pesan PO</span>
                                        </a>

                                        <button type="button"
                                                @click="quickAdjustStock({{ $prod->id }}, '{{ $prod->sku }}')"
                                                class="p-1.5 rounded-lg bg-[#182633] hover:bg-[#203344] text-slate-300 hover:text-white transition"
                                                title="Sesuaikan Stok Cepat">
                                            <i class="fa-solid fa-sliders"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()?->canViewFinancials() ? 8 : 7 }}" class="py-12 text-center text-slate-400">
                                    <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-2xl">
                                        <i class="fa-solid fa-circle-check"></i>
                                    </div>
                                    <h4 class="font-bold text-white text-base">Seluruh Stok Barang Aman!</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">Tidak ada barang yang habis atau di bawah batas minimum stok saat ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div class="p-4 border-t border-[#1f3344]">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- TAB 2: EXPIRY MONITORING & FEFO TABLE -->
    @if($tab === 'expiry')
        <div class="table-container overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-[#1f3344] bg-[#0f1b24]/80 text-slate-400 uppercase tracking-wider font-semibold">
                            <th class="py-3 px-4">Barang & SKU</th>
                            <th class="py-3 px-4">Nomor Batch</th>
                            <th class="py-3 px-4">Tanggal Expired</th>
                            <th class="py-3 px-4 text-center">Sisa Waktu</th>
                            <th class="py-3 px-4 text-center">Status Kelayakan</th>
                            <th class="py-3 px-4 text-center">Stok di Rak</th>
                            @if(auth()->user()?->canViewFinancials())
                                <th class="py-3 px-4 text-right">Nilai Barang</th>
                            @endif
                            <th class="py-3 px-4 text-center">Tindakan Lapangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#1f3344]/50">
                        @forelse($products as $prod)
                            @php
                                $days = $prod->days_until_expiry;
                            @endphp
                            <tr class="hover:bg-[#162532]/70 transition-colors {{ $days < 0 ? 'bg-rose-500/5' : ($days <= 30 ? 'bg-amber-500/5' : '') }}">
                                <!-- SKU & Title -->
                                <td class="py-3.5 px-4">
                                    <div class="font-mono font-bold text-cyan-400 text-xs">{{ $prod->sku }}</div>
                                    <div class="font-semibold text-white mt-0.5">{{ $prod->title }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $prod->warehouse_location ?? 'Gudang' }}</div>
                                </td>

                                <!-- Batch Number -->
                                <td class="py-3.5 px-4 font-mono font-bold text-white">
                                    <span class="px-2 py-0.5 rounded bg-slate-800 text-cyan-300 border border-slate-700">
                                        {{ $prod->batch_number ?: 'Tanpa Batch' }}
                                    </span>
                                </td>

                                <!-- Expiry Date -->
                                <td class="py-3.5 px-4 font-mono font-semibold text-slate-200">
                                    {{ $prod->expiry_date ? $prod->expiry_date->format('d M Y') : '-' }}
                                </td>

                                <!-- Sisa Hari -->
                                <td class="py-3.5 px-4 text-center font-mono">
                                    @if($days < 0)
                                        <span class="text-rose-400 font-extrabold">Expired {{ abs($days) }} hari lalu</span>
                                    @elseif($days === 0)
                                        <span class="text-rose-400 font-extrabold animate-ping">Hari Ini!</span>
                                    @elseif($days <= 30)
                                        <span class="text-amber-400 font-bold">{{ $days }} hari lagi</span>
                                    @elseif($days <= 90)
                                        <span class="text-yellow-300">{{ $days }} hari lagi</span>
                                    @else
                                        <span class="text-slate-400">{{ $days }} hari</span>
                                    @endif
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $prod->expiry_badge_class }}">
                                        @if($days < 0)
                                            <i class="fa-solid fa-triangle-exclamation mr-1"></i> Kadaluarsa
                                        @elseif($days <= 30)
                                            <i class="fa-solid fa-fire mr-1"></i> Kritis FEFO
                                        @elseif($days <= 90)
                                            <i class="fa-solid fa-clock mr-1"></i> Peringatan Dini
                                        @else
                                            <i class="fa-solid fa-check mr-1"></i> Aman
                                        @endif
                                    </span>
                                </td>

                                <!-- Stock in Rack -->
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-white">
                                    {{ $prod->quantity }} {{ $prod->unit }}
                                </td>

                                <!-- Financial Value (Protected) -->
                                @if(auth()->user()?->canViewFinancials())
                                    <td class="py-3.5 px-4 text-right font-mono font-bold text-xs {{ $days < 0 ? 'text-rose-400' : 'text-slate-200' }}">
                                        Rp {{ number_format($prod->quantity * $prod->cost_price, 0, ',', '.') }}
                                    </td>
                                @endif

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($days < 0)
                                            <button type="button"
                                                    @click="activeProduct = {{ json_encode($prod) }}; writeOffQty = {{ $prod->quantity }}; writeOffModal = true;"
                                                    class="px-2.5 py-1.5 rounded-lg bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/30 font-bold text-xs flex items-center gap-1 transition">
                                                <i class="fa-solid fa-trash-can"></i>
                                                <span>Karantina</span>
                                            </button>
                                        @else
                                            <a href="{{ route('inventory.print-label', $prod->id) }}"
                                               target="_blank"
                                               class="p-1.5 rounded-lg bg-[#182633] hover:bg-[#203344] text-slate-300 hover:text-white transition"
                                               title="Cetak Label Barcode & Batch">
                                                <i class="fa-solid fa-barcode"></i>
                                            </a>
                                            <button type="button"
                                                    @click="activeProduct = {{ json_encode($prod) }}; writeOffQty = 1; writeOffModal = true;"
                                                    class="p-1.5 rounded-lg bg-slate-800 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 transition"
                                                    title="Tandai Rusak/Karantina">
                                                <i class="fa-solid fa-box-archive"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()?->canViewFinancials() ? 8 : 7 }}" class="py-12 text-center text-slate-400">
                                    <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-2xl">
                                        <i class="fa-solid fa-shield-check"></i>
                                    </div>
                                    <h4 class="font-bold text-white text-base">Tidak Ada Barang Mendekati Kedaluwarsa!</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">Semua barang dengan tanggal batch masih dalam kondisi sangat aman.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
                <div class="p-4 border-t border-[#1f3344]">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- Modal Karantina / Pemusnahan Stok Expired -->
    <div x-show="writeOffModal"
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         style="display: none;">
        <div @click.away="writeOffModal = false"
             class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-md w-full overflow-hidden shadow-2xl">
            <div class="px-6 py-4 border-b border-[#1f3344] flex items-center justify-between bg-[#0e1821]">
                <h3 class="font-bold text-white text-base flex items-center gap-2">
                    <i class="fa-solid fa-box-archive text-rose-400"></i>
                    <span>Karantina / Pemusnahan Stok</span>
                </h3>
                <button @click="writeOffModal = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <template x-if="activeProduct">
                <form :action="'/alerts/' + activeProduct.id + '/write-off'" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-xs text-rose-300">
                        <p class="font-bold" x-text="activeProduct.title + ' (' + activeProduct.sku + ')'"></p>
                        <p class="text-[11px] text-rose-300/80 mt-1">
                            Tindakan ini akan mengurangi stok fisik di gudang dan mencatat mutasi barang keluar (*out*) bertipe karantina/pemusnahan barang kedaluwarsa.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">
                            Jumlah Unit yang Dikeluarkan / Dimusnahkan <span class="text-rose-400">*</span>
                        </label>
                        <input type="number"
                               name="quantity"
                               x-model.number="writeOffQty"
                               min="1"
                               :max="activeProduct.quantity"
                               required
                               class="w-full px-3 py-2 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white font-mono focus:border-rose-500">
                        <p class="text-[10px] text-slate-500 mt-1">Stok saat ini: <span x-text="activeProduct.quantity"></span> <span x-text="activeProduct.unit"></span></p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">
                            Alasan Pemusnahan / Karantina <span class="text-rose-400">*</span>
                        </label>
                        <input type="text"
                               name="reason"
                               x-model="writeOffReason"
                               required
                               placeholder="Contoh: Melewati tanggal expired batch..."
                               class="w-full px-3 py-2 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white focus:border-rose-500">
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-[#1f3344]">
                        <button type="button" @click="writeOffModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs flex items-center gap-1.5 shadow-lg shadow-rose-500/20">
                            <i class="fa-solid fa-trash-can"></i>
                            <span>Keluarkan dari Stok</span>
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
