@extends('layouts.app')

@section('title', 'Analisis Perputaran Stok & Klasifikasi ABC - INVENTORY')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Row -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-lg">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    Analisis Perputaran Stok & Klasifikasi ABC
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">Pareto & Velocity</span>
                </h1>
                <p class="text-xs text-slate-400">Klasifikasi nilai kontribusi omset (Aturan Pareto 80/20) dan pemetaan barang Fast-Moving vs Dead Stock.</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('analytics.print', request()->all()) }}"
               target="_blank"
               class="px-3.5 py-2 rounded-xl bg-[#182633] hover:bg-[#203344] text-slate-200 font-semibold text-xs flex items-center gap-2 border border-[#1f3344] transition">
                <i class="fa-solid fa-print text-emerald-400"></i>
                <span>Cetak Laporan (PDF)</span>
            </a>

            <a href="{{ route('analytics.export-csv', request()->all()) }}"
               class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/20 transition hover:scale-[1.02]">
                <i class="fa-solid fa-file-csv"></i>
                <span>Export CSV / Excel</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar: Periode, Gudang, Kategori & Search -->
    <form method="GET" action="{{ route('analytics.index') }}" class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 shadow-lg space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Periode Dropdown -->
                <div class="flex items-center gap-1.5 text-xs text-slate-400">
                    <span class="font-semibold text-white"><i class="fa-solid fa-calendar-days text-emerald-400 mr-1"></i>Periode:</span>
                    <select name="period" onchange="this.form.submit()" class="px-3 py-1.5 bg-[#0e1821] border border-[#1f3344] rounded-lg text-xs text-white focus:outline-none focus:border-emerald-500">
                        <option value="30" {{ ($filters['period'] ?? 90) == 30 ? 'selected' : '' }}>30 Hari Terakhir</option>
                        <option value="60" {{ ($filters['period'] ?? 90) == 60 ? 'selected' : '' }}>60 Hari Terakhir</option>
                        <option value="90" {{ ($filters['period'] ?? 90) == 90 ? 'selected' : '' }}>90 Hari Terakhir (Default)</option>
                        <option value="180" {{ ($filters['period'] ?? 90) == 180 ? 'selected' : '' }}>180 Hari Terakhir</option>
                        <option value="365" {{ ($filters['period'] ?? 90) == 365 ? 'selected' : '' }}>1 Tahun Terakhir</option>
                    </select>
                </div>

                <!-- Gudang Filter -->
                <select name="warehouse_id" onchange="this.form.submit()" class="px-3 py-1.5 bg-[#0e1821] border border-[#1f3344] rounded-lg text-xs text-white focus:outline-none focus:border-emerald-500">
                    <option value="">Semua Lokasi Gudang</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ ($filters['warehouse_id'] ?? '') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>

                <!-- Kategori Filter -->
                <select name="category_id" onchange="this.form.submit()" class="px-3 py-1.5 bg-[#0e1821] border border-[#1f3344] rounded-lg text-xs text-white focus:outline-none focus:border-emerald-500">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Search Field -->
            <div class="flex items-center gap-2">
                <div class="relative w-full md:w-56">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                    <input type="text"
                           name="search"
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Cari SKU, Nama Barang..."
                           class="w-full pl-8 pr-3 py-1.5 bg-[#0e1821] border border-[#1f3344] rounded-lg text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
                </div>
                @if(!empty($filters['search']) || !empty($filters['warehouse_id']) || !empty($filters['category_id']) || !empty($filters['classification']) || !empty($filters['velocity']))
                    <a href="{{ route('analytics.index') }}" class="px-2.5 py-1.5 bg-slate-800 text-slate-400 hover:text-white rounded-lg text-xs" title="Reset semua filter">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>

    <!-- Executive KPI Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Omset -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Omset Penjualan</p>
                <h3 class="text-xl md:text-2xl font-extrabold text-emerald-400 mt-1">
                    Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ number_format($summary['total_sold_units']) }} unit terjual ({{ $periodDays }} hari)</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-xl">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
        </div>

        <!-- Kelas A (Pareto Core) -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Produk Kelas A (High Value)</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-xl md:text-2xl font-extrabold text-white">{{ $summary['class_a_count'] }}</h3>
                    <span class="text-xs text-emerald-400 font-bold font-mono">{{ $summary['class_a_revenue_pct'] }}% Omset</span>
                </div>
                <p class="text-[11px] text-emerald-500/80 mt-0.5">Prioritas tertinggi rak depan</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-xl">
                <i class="fa-solid fa-crown"></i>
            </div>
        </div>

        <!-- Fast Moving -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Fast-Moving Items</p>
                <h3 class="text-xl md:text-2xl font-extrabold text-cyan-400 mt-1">{{ $summary['fast_moving_count'] }} <span class="text-xs font-normal text-slate-400">SKU</span></h3>
                <p class="text-[11px] text-cyan-500/80 mt-0.5">Perputaran sangat cepat</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 text-xl">
                <i class="fa-solid fa-bolt-lightning"></i>
            </div>
        </div>

        <!-- Dead Stock (Non-Moving) -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Dead Stock (Mati &gt; 90 Hari)</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-xl md:text-2xl font-extrabold text-rose-400">{{ $summary['dead_stock_count'] }}</h3>
                    <span class="text-[11px] text-slate-400">SKU</span>
                </div>
                <p class="text-[11px] text-rose-400/90 mt-0.5">
                    Rp {{ number_format($summary['dead_stock_value'], 0, ',', '.') }} modal tertahan
                </p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400 text-xl">
                <i class="fa-solid fa-box-open"></i>
            </div>
        </div>
    </div>

    <!-- Visual Breakdown Cards: ABC Pareto & Velocity Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Card 1: Distribusi Pareto Klasifikasi ABC -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 shadow-lg space-y-4">
            <div class="flex items-center justify-between border-b border-[#1f3344] pb-3">
                <div>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-diagram-project text-emerald-400"></i>
                        <span>Distribusi Nilai Penjualan (Klasifikasi ABC)</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Aturan Pareto: 80% omset disumbang oleh sebagian kecil produk inti</p>
                </div>
            </div>

            <!-- Visual Bar -->
            <div class="space-y-1.5">
                <div class="w-full h-4 rounded-full bg-slate-800 flex overflow-hidden">
                    <div class="h-full bg-emerald-500 transition-all duration-500" style="width: {{ max(4, $summary['class_a_revenue_pct']) }}%" title="Kelas A: {{ $summary['class_a_revenue_pct'] }}%"></div>
                    <div class="h-full bg-cyan-500 transition-all duration-500" style="width: {{ max(4, $summary['class_b_revenue_pct']) }}%" title="Kelas B: {{ $summary['class_b_revenue_pct'] }}%"></div>
                    <div class="h-full bg-slate-600 transition-all duration-500" style="width: {{ max(4, $summary['class_c_revenue_pct']) }}%" title="Kelas C: {{ $summary['class_c_revenue_pct'] }}%"></div>
                </div>
                <div class="flex justify-between text-[10px] text-slate-400 px-1 font-mono">
                    <span class="text-emerald-400 font-bold">Kelas A ({{ $summary['class_a_revenue_pct'] }}%)</span>
                    <span class="text-cyan-400 font-bold">Kelas B ({{ $summary['class_b_revenue_pct'] }}%)</span>
                    <span class="text-slate-400 font-bold">Kelas C ({{ $summary['class_c_revenue_pct'] }}%)</span>
                </div>
            </div>

            <!-- Legend Grid -->
            <div class="grid grid-cols-3 gap-2.5 pt-1 text-xs">
                <!-- Class A -->
                <a href="{{ route('analytics.index', array_merge($filters, ['classification' => 'A'])) }}"
                   class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/25 hover:bg-emerald-500/20 transition group">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-emerald-400 uppercase tracking-wider text-[11px]">Kelas A</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    </div>
                    <div class="text-lg font-black text-white mt-1">{{ $summary['class_a_count'] }} <span class="text-[10px] text-slate-400 font-normal">SKU</span></div>
                    <div class="text-[10px] text-emerald-300 font-mono mt-0.5">Rp {{ number_format($summary['class_a_revenue'], 0, ',', '.') }}</div>
                    <div class="text-[9px] text-slate-400 mt-1">Kontrol ketat, rak depan</div>
                </a>

                <!-- Class B -->
                <a href="{{ route('analytics.index', array_merge($filters, ['classification' => 'B'])) }}"
                   class="p-3 rounded-xl bg-cyan-500/10 border border-cyan-500/25 hover:bg-cyan-500/20 transition group">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-cyan-400 uppercase tracking-wider text-[11px]">Kelas B</span>
                        <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                    </div>
                    <div class="text-lg font-black text-white mt-1">{{ $summary['class_b_count'] }} <span class="text-[10px] text-slate-400 font-normal">SKU</span></div>
                    <div class="text-[10px] text-cyan-300 font-mono mt-0.5">Rp {{ number_format($summary['class_b_revenue'], 0, ',', '.') }}</div>
                    <div class="text-[9px] text-slate-400 mt-1">Reorder periodik normal</div>
                </a>

                <!-- Class C -->
                <a href="{{ route('analytics.index', array_merge($filters, ['classification' => 'C'])) }}"
                   class="p-3 rounded-xl bg-slate-800/60 border border-slate-700/80 hover:bg-slate-800 transition group">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-300 uppercase tracking-wider text-[11px]">Kelas C</span>
                        <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                    </div>
                    <div class="text-lg font-black text-white mt-1">{{ $summary['class_c_count'] }} <span class="text-[10px] text-slate-400 font-normal">SKU</span></div>
                    <div class="text-[10px] text-slate-300 font-mono mt-0.5">Rp {{ number_format($summary['class_c_revenue'], 0, ',', '.') }}</div>
                    <div class="text-[9px] text-slate-400 mt-1">Jangan overstock rak</div>
                </a>
            </div>
        </div>

        <!-- Card 2: Matriks Kecepatan Perputaran (Velocity Breakdown) -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 shadow-lg space-y-4">
            <div class="flex items-center justify-between border-b border-[#1f3344] pb-3">
                <div>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-gauge-high text-cyan-400"></i>
                        <span>Kecepatan Perputaran Barang (Inventory Velocity)</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Identifikasi pergerakan barang untuk efisiensi ruang gudang</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <!-- Fast Moving -->
                <a href="{{ route('analytics.index', array_merge($filters, ['velocity' => 'fast'])) }}"
                   class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/25 hover:bg-emerald-500/20 transition">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-emerald-400 text-[11px]">⚡ Fast-Moving</span>
                        <span class="font-mono text-xs font-black text-white">{{ $summary['fast_moving_count'] }} SKU</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">DOH &lt; 35 hari. Perputaran lancar, sering keluar dermaga.</p>
                </a>

                <!-- Medium Moving -->
                <a href="{{ route('analytics.index', array_merge($filters, ['velocity' => 'medium'])) }}"
                   class="p-3 rounded-xl bg-blue-500/10 border border-blue-500/25 hover:bg-blue-500/20 transition">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-blue-400 text-[11px]">⏱ Medium-Moving</span>
                        <span class="font-mono text-xs font-black text-white">{{ $summary['medium_moving_count'] }} SKU</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">DOH 36-90 hari. Pergerakan stabil sesuai pola pemesanan.</p>
                </a>

                <!-- Slow Moving -->
                <a href="{{ route('analytics.index', array_merge($filters, ['velocity' => 'slow'])) }}"
                   class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/25 hover:bg-amber-500/20 transition">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-amber-400 text-[11px]">🐢 Slow-Moving</span>
                        <span class="font-mono text-xs font-black text-white">{{ $summary['slow_moving_count'] }} SKU</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">DOH &gt; 90 hari. Jarang bergerak, waspadai penumpukan.</p>
                </a>

                <!-- Dead Stock -->
                <a href="{{ route('analytics.index', array_merge($filters, ['velocity' => 'dead'])) }}"
                   class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/25 hover:bg-rose-500/20 transition">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-rose-400 text-[11px]">🛑 Dead Stock</span>
                        <span class="font-mono text-xs font-black text-white">{{ $summary['dead_stock_count'] }} SKU</span>
                    </div>
                    <p class="text-[10px] text-rose-300/80 mt-1">Tidak ada penjualan &gt; 90 hari. Segera clearance / bundling!</p>
                </a>
            </div>
        </div>

    </div>

    <!-- Sub-Filter Pills (Interactive Tabs) -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs font-semibold">
        <a href="{{ route('analytics.index', array_merge($filters, ['classification' => null, 'velocity' => null])) }}"
           class="px-3.5 py-1.5 rounded-lg transition {{ empty($filters['classification']) && empty($filters['velocity']) ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-[#13202b] text-slate-400 hover:text-white border border-[#1f3344]' }}">
            Semua Produk ({{ $summary['total_products'] }})
        </a>

        <a href="{{ route('analytics.index', array_merge($filters, ['classification' => 'A', 'velocity' => null])) }}"
           class="px-3.5 py-1.5 rounded-lg transition {{ ($filters['classification'] ?? '') === 'A' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-[#13202b] text-slate-400 hover:text-white border border-[#1f3344]' }}">
            👑 Kelas A Saja ({{ $summary['class_a_count'] }})
        </a>

        <a href="{{ route('analytics.index', array_merge($filters, ['classification' => 'B', 'velocity' => null])) }}"
           class="px-3.5 py-1.5 rounded-lg transition {{ ($filters['classification'] ?? '') === 'B' ? 'bg-cyan-500/20 text-cyan-400 border border-cyan-500/30' : 'bg-[#13202b] text-slate-400 hover:text-white border border-[#1f3344]' }}">
            Kelas B Saja ({{ $summary['class_b_count'] }})
        </a>

        <a href="{{ route('analytics.index', array_merge($filters, ['classification' => 'C', 'velocity' => null])) }}"
           class="px-3.5 py-1.5 rounded-lg transition {{ ($filters['classification'] ?? '') === 'C' ? 'bg-slate-700/60 text-slate-300 border border-slate-600' : 'bg-[#13202b] text-slate-400 hover:text-white border border-[#1f3344]' }}">
            Kelas C Saja ({{ $summary['class_c_count'] }})
        </a>

        <span class="text-slate-600">|</span>

        <a href="{{ route('analytics.index', array_merge($filters, ['velocity' => 'fast', 'classification' => null])) }}"
           class="px-3.5 py-1.5 rounded-lg transition {{ ($filters['velocity'] ?? '') === 'fast' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-[#13202b] text-slate-400 hover:text-white border border-[#1f3344]' }}">
            ⚡ Fast-Moving ({{ $summary['fast_moving_count'] }})
        </a>

        <a href="{{ route('analytics.index', array_merge($filters, ['velocity' => 'dead', 'classification' => null])) }}"
           class="px-3.5 py-1.5 rounded-lg transition {{ ($filters['velocity'] ?? '') === 'dead' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-[#13202b] text-slate-400 hover:text-white border border-[#1f3344]' }}">
            🛑 Dead Stock ({{ $summary['dead_stock_count'] }})
        </a>
    </div>

    <!-- Detailed Analytics Table -->
    <div class="table-container overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#1f3344] bg-[#0f1b24]/80 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Barang / SKU</th>
                        <th class="py-3 px-4">Gudang & Rak</th>
                        <th class="py-3 px-4 text-center">Stok Fisik</th>
                        <th class="py-3 px-4 text-center">Unit Terjual</th>
                        <th class="py-3 px-4 text-right">Omset (Revenue)</th>
                        <th class="py-3 px-4 text-center">Kelas ABC</th>
                        <th class="py-3 px-4 text-center">Status Kecepatan</th>
                        <th class="py-3 px-4 text-center">Estimasi Habis (DOH)</th>
                        <th class="py-3 px-4">Rekomendasi Tindakan</th>
                        <th class="py-3 px-4 text-center">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1f3344]/50">
                    @forelse($items as $index => $row)
                        @php
                            $prod = $row['product'];
                        @endphp
                        <tr class="hover:bg-[#162532]/70 transition-colors {{ $row['velocity_status'] === 'dead' ? 'bg-rose-500/5' : ($row['abc_class'] === 'A' ? 'bg-emerald-500/5' : '') }}">
                            <!-- Index -->
                            <td class="py-3.5 px-4 text-center font-mono text-slate-500">{{ $index + 1 }}</td>

                            <!-- SKU & Title -->
                            <td class="py-3.5 px-4">
                                <div class="font-mono font-bold text-emerald-400 text-xs">{{ $prod->sku }}</div>
                                <div class="font-bold text-white text-sm mt-0.5">{{ $prod->title }}</div>
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

                            <!-- Stock -->
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-white">
                                {{ $row['current_stock'] }} {{ $prod->unit }}
                                <div class="text-[10px] text-slate-500 font-normal">
                                    Rp {{ number_format($row['stock_holding_value'], 0, ',', '.') }}
                                </div>
                            </td>

                            <!-- Sold Units -->
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-cyan-400">
                                {{ $row['sold_units'] }} {{ $prod->unit }}
                            </td>

                            <!-- Revenue & Share -->
                            <td class="py-3.5 px-4 text-right font-mono">
                                <div class="text-white font-bold text-sm">
                                    Rp {{ number_format($row['revenue_generated'], 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] text-emerald-400">{{ $row['revenue_share_pct'] }}% kontribusi</div>
                            </td>

                            <!-- ABC Classification Badge -->
                            <td class="py-3.5 px-4 text-center">
                                @if($row['abc_class'] === 'A')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-black bg-emerald-500/20 text-emerald-400 border border-emerald-500/40">
                                        <i class="fa-solid fa-crown text-[10px]"></i> KELAS A
                                    </span>
                                @elseif($row['abc_class'] === 'B')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold bg-cyan-500/20 text-cyan-400 border border-cyan-500/40">
                                        KELAS B
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-800 text-slate-400 border border-slate-700">
                                        KELAS C
                                    </span>
                                @endif
                            </td>

                            <!-- Velocity Status Badge -->
                            <td class="py-3.5 px-4 text-center">
                                @if($row['velocity_status'] === 'fast')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                        ⚡ Fast-Moving
                                    </span>
                                @elseif($row['velocity_status'] === 'medium')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/15 text-blue-400 border border-blue-500/30">
                                        ⏱ Medium
                                    </span>
                                @elseif($row['velocity_status'] === 'slow')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                        🐢 Slow-Moving
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30 animate-pulse">
                                        🛑 Dead Stock
                                    </span>
                                @endif

                                <div class="text-[9px] text-slate-500 mt-0.5 font-mono">
                                    {{ $row['days_since_last_sale'] >= 999 ? 'Belum ada jual' : $row['days_since_last_sale'] . ' hari lalu' }}
                                </div>
                            </td>

                            <!-- DOH (Days on Hand) -->
                            <td class="py-3.5 px-4 text-center font-mono">
                                @if($row['days_of_inventory'] >= 999)
                                    <span class="text-rose-400 font-bold">&infin; (Mati)</span>
                                @elseif($row['days_of_inventory'] <= 30)
                                    <span class="text-emerald-400 font-bold">{{ $row['days_of_inventory'] }} Hari</span>
                                @else
                                    <span class="text-slate-300">{{ $row['days_of_inventory'] }} Hari</span>
                                @endif
                            </td>

                            <!-- Recommendation Plan -->
                            <td class="py-3.5 px-4 max-w-xs">
                                <p class="text-[11px] text-slate-300 leading-tight">
                                    {{ $row['action_recommendation'] }}
                                </p>
                            </td>

                            <!-- Action -->
                            <td class="py-3.5 px-4 text-center">
                                @if($row['velocity_status'] === 'fast' || $row['abc_class'] === 'A')
                                    <a href="{{ route('purchase-orders.create') }}"
                                       class="px-2.5 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs inline-flex items-center gap-1 shadow transition"
                                       title="Buat PO Restock ke Supplier">
                                        <i class="fa-solid fa-cart-plus"></i>
                                        <span>Restock</span>
                                    </a>
                                @elseif($row['velocity_status'] === 'dead')
                                    <a href="{{ route('alerts.index', ['tab' => 'stock']) }}"
                                       class="px-2.5 py-1.5 rounded-lg bg-rose-500/20 hover:bg-rose-500/30 text-rose-300 border border-rose-500/30 font-bold text-xs inline-flex items-center gap-1 transition"
                                       title="Tindakan Pembersihan / Karantina">
                                        <i class="fa-solid fa-fire"></i>
                                        <span>Clearance</span>
                                    </a>
                                @else
                                    <a href="{{ route('inventory.index', ['search' => $prod->sku]) }}"
                                       class="p-1.5 rounded-lg bg-[#182633] hover:bg-[#203344] text-slate-300 hover:text-white transition"
                                       title="Lihat Detail Barang">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-chart-pie text-3xl text-slate-600 mb-2"></i>
                                <p class="text-sm">Tidak ada data produk yang cocok dengan filter analitik saat ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
