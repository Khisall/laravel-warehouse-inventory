@extends('layouts.app')

@section('title', 'Ruang Audit Fisik - ' . $opname->opname_number)

@section('content')
<div class="space-y-6" x-data="opnameAuditRoom()">

    <!-- Header Navigation & Status Bar -->
    <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 shadow-lg">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Left Info -->
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <a href="{{ route('opname.index') }}" class="text-slate-400 hover:text-emerald-400 text-xs flex items-center gap-1 transition">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Daftar Sesi</span>
                    </a>
                    <span class="text-slate-600">/</span>
                    <span class="font-mono text-xs text-emerald-400 font-bold">{{ $opname->opname_number }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $opname->status_badge }}">
                        {{ $opname->status_label }}
                    </span>
                </div>

                <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    <span>{{ $opname->title }}</span>
                </h1>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400 mt-2">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-warehouse text-emerald-400"></i>
                        <span class="text-slate-300 font-medium">{{ $opname->warehouse ? $opname->warehouse->name : 'Semua Gudang' }}</span>
                    </span>
                    <span class="text-slate-600">•</span>
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-tags text-slate-500"></i>
                        <span>{{ $opname->category ? $opname->category->name : 'Semua Kategori' }}</span>
                    </span>
                    <span class="text-slate-600">•</span>
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-calendar text-slate-500"></i>
                        <span>Dibuat {{ $opname->created_at->format('d/m/Y H:i') }} oleh {{ $opname->creator?->name ?? 'User' }}</span>
                    </span>
                    @if($opname->completed_at)
                        <span class="text-slate-600">•</span>
                        <span class="text-emerald-400 flex items-center gap-1">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>Selesai {{ $opname->completed_at->format('d/m/Y H:i') }}</span>
                        </span>
                    @endif
                </div>
            </div>

            <!-- Right Actions -->
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('opname.report', $opname->id) }}"
                   target="_blank"
                   class="px-3.5 py-2 rounded-xl bg-[#182633] hover:bg-[#203344] text-slate-200 font-semibold text-xs flex items-center gap-2 border border-[#1f3344] transition">
                    <i class="fa-solid fa-print text-emerald-400"></i>
                    <span>Cetak Berita Acara</span>
                </a>

                @if($opname->status === 'in_progress')
                    <!-- Quick Autofill Action -->
                    <form action="{{ route('opname.quick-fill', $opname->id) }}" method="POST"
                          onsubmit="return confirm('Samakan semua barang yang belum dihitung agar persis dengan stok sistem?');">
                        @csrf
                        <button type="submit"
                                class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold flex items-center gap-1.5 border border-slate-700 transition"
                                title="Set semua barang belum terhitung = stok sistem">
                            <i class="fa-solid fa-wand-magic-sparkles text-amber-400"></i>
                            <span class="hidden sm:inline">Samakan Sisa ke Sistem</span>
                        </button>
                    </form>

                    <!-- Reconcile Button (Managers & Super Admin) -->
                    @if(auth()->user()?->isSuperAdmin() || auth()->user()?->isManager())
                        <button @click="reconcileModal = true"
                                class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/25 transition hover:scale-[1.02]">
                            <i class="fa-solid fa-scale-balanced"></i>
                            <span>Rekonsiliasi Stok</span>
                        </button>
                    @endif

                    <!-- Cancel Button -->
                    <form action="{{ route('opname.cancel', $opname->id) }}" method="POST"
                          onsubmit="return confirm('Apakah Anda yakin ingin membatalkan sesi Stock Opname ini?');">
                        @csrf
                        <button type="submit"
                                class="p-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 text-xs transition"
                                title="Batalkan Sesi">
                            <i class="fa-solid fa-ban"></i>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Live Barcode Scanning Command Bar (Only for in_progress) -->
    @if($opname->status === 'in_progress')
        <div class="bg-gradient-to-r from-[#12232f] to-[#102924] border-2 border-emerald-500/40 rounded-2xl p-4 md:p-5 shadow-xl relative overflow-hidden">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- Barcode Input Box -->
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <span>Pemindai Barcode / QR Cepat (Aktif)</span>
                            <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 font-mono font-normal">Auto-focus Ready</span>
                        </h3>
                    </div>
                    <p class="text-xs text-slate-300 mb-3">
                        Gunakan <strong>Handheld Scanner USB</strong> langsung tembak ke barcode barang, atau ketik SKU lalu tekan <strong>Enter</strong>. Tiap scan otomatis menambah hitungan fisik (+1).
                    </p>

                    <form @submit.prevent="submitScanBarcode()" class="flex items-center gap-2 max-w-xl">
                        <div class="relative flex-1">
                            <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-emerald-400">
                                <i class="fa-solid fa-barcode text-base"></i>
                            </div>
                            <input type="text"
                                   x-ref="barcodeInput"
                                   x-model="scannedCode"
                                   placeholder="Scan Barcode / Masukkan SKU lalu tekan Enter..."
                                   class="w-full pl-11 pr-4 py-2.5 bg-[#0a1218] border-2 border-emerald-500/50 rounded-xl text-sm font-mono text-emerald-300 placeholder-slate-500 focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/30">
                        </div>

                        <button type="submit"
                                :disabled="isScanning || !scannedCode"
                                class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 disabled:opacity-50 text-slate-950 font-bold text-xs flex items-center gap-2 transition">
                            <template x-if="!isScanning">
                                <span class="flex items-center gap-1.5">
                                    <i class="fa-solid fa-plus-circle"></i>
                                    <span>Hitung (+1)</span>
                                </span>
                            </template>
                            <template x-if="isScanning">
                                <i class="fa-solid fa-circle-notch fa-spin"></i>
                            </template>
                        </button>

                        <!-- Camera Scanner Toggle Button -->
                        <button type="button"
                                @click="toggleCameraScanner()"
                                class="px-3.5 py-2.5 rounded-xl bg-[#182633] hover:bg-[#203344] text-slate-200 border border-[#1f3344] font-semibold text-xs flex items-center gap-2 transition"
                                title="Buka Kamera HP / Laptop">
                            <i class="fa-solid fa-camera text-emerald-400"></i>
                            <span class="hidden md:inline" x-text="cameraOpen ? 'Tutup Kamera' : 'Buka Kamera'"></span>
                        </button>
                    </form>

                    <!-- Scan Feedback Banner -->
                    <div x-show="lastScanMessage"
                         x-transition
                         class="mt-3 px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center justify-between"
                         :class="lastScanSuccess ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30'"
                         style="display: none;">
                        <div class="flex items-center gap-2">
                            <i :class="lastScanSuccess ? 'fa-solid fa-circle-check text-emerald-400' : 'fa-solid fa-triangle-exclamation text-rose-400'"></i>
                            <span x-text="lastScanMessage"></span>
                        </div>
                        <button @click="lastScanMessage = ''" class="text-slate-400 hover:text-white">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <!-- Live Camera Viewport Container -->
                <div x-show="cameraOpen"
                     x-transition
                     class="w-full lg:w-80 bg-black rounded-2xl border-2 border-emerald-500/50 p-2 overflow-hidden"
                     style="display: none;">
                    <div class="flex items-center justify-between px-2 py-1 text-xs text-slate-400 border-b border-slate-800 mb-2">
                        <span class="flex items-center gap-1.5 text-emerald-400 font-bold">
                            <i class="fa-solid fa-video"></i> Kamera Aktif
                        </span>
                        <button @click="stopCameraScanner()" class="hover:text-white">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div id="opname-reader" class="w-full rounded-xl overflow-hidden min-h-[180px] bg-slate-900 flex items-center justify-center text-slate-500 text-xs">
                        Menginisialisasi kamera...
                    </div>
                    <p class="text-[10px] text-center text-slate-400 mt-2">Arahkan kamera ke Barcode / QR Code produk</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Audit Progress & KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Progress Hitung -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Progres Fisik</span>
                <span class="text-xs font-mono font-bold text-emerald-400">
                    {{ $opname->total_items > 0 ? round(($totalCounted / $opname->total_items) * 100) : 0 }}%
                </span>
            </div>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-2xl font-extrabold text-white">{{ $totalCounted }}</h3>
                <span class="text-xs text-slate-400">/ {{ $opname->total_items }} SKU</span>
            </div>
            <!-- Progress Bar -->
            <div class="w-full h-2 bg-slate-800 rounded-full mt-3 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full transition-all duration-300"
                     style="width: {{ $opname->total_items > 0 ? ($totalCounted / $opname->total_items) * 100 : 0 }}%"></div>
            </div>
            <div class="flex justify-between text-[10px] text-slate-500 mt-1.5">
                <span>{{ $totalUncounted }} belum dihitung</span>
                <span>{{ $totalCounted }} selesai</span>
            </div>
        </div>

        <!-- Stok Pas (Sesuai) -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Stok Cocok (Pas)</span>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-2xl font-extrabold text-emerald-400">{{ $matchedCount }}</h3>
                <span class="text-xs text-slate-400">SKU</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-2 flex items-center gap-1.5">
                <i class="fa-solid fa-circle-check text-emerald-400"></i>
                <span>Fisik persis sama dengan buku</span>
            </p>
        </div>

        <!-- Terdapat Selisih (Selisih Fisik) -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Terdapat Selisih</span>
            <div class="flex items-baseline gap-2 mt-1">
                <h3 class="text-2xl font-extrabold {{ $varianceCount > 0 ? 'text-amber-400' : 'text-slate-400' }}">{{ $varianceCount }}</h3>
                <span class="text-xs text-slate-400">SKU</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-2 flex items-center gap-1.5">
                <i class="fa-solid fa-triangle-exclamation {{ $varianceCount > 0 ? 'text-amber-400' : 'text-slate-500' }}"></i>
                <span>Netto: {{ $netVarianceUnits > 0 ? '+' : '' }}{{ $netVarianceUnits }} Unit</span>
            </p>
        </div>

        <!-- Dampak Finansial (Protected) -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Valuasi Selisih</span>
            @if(auth()->user()?->canViewFinancials())
                <div class="flex items-baseline gap-1 mt-1">
                    <h3 class="text-xl font-extrabold {{ $netVarianceValue < 0 ? 'text-rose-400' : ($netVarianceValue > 0 ? 'text-emerald-400' : 'text-slate-300') }}">
                        {{ $netVarianceValue < 0 ? '-' : ($netVarianceValue > 0 ? '+' : '') }}Rp {{ number_format(abs($netVarianceValue), 0, ',', '.') }}
                    </h3>
                </div>
                <p class="text-[11px] text-slate-400 mt-2">
                    {{ $netVarianceValue < 0 ? 'Defisit kerugian barang' : ($netVarianceValue > 0 ? 'Surplus kelebihan fisik' : 'Seimbang') }}
                </p>
            @else
                <div class="flex items-center gap-1.5 mt-2 text-slate-500 text-xs">
                    <i class="fa-solid fa-lock text-[10px]"></i>
                    <span>Terkunci untuk Staff</span>
                </div>
                <p class="text-[10px] text-slate-500 mt-1">Hanya Manager / Owner</p>
            @endif
        </div>
    </div>

    <!-- Filter Bar inside Opname Room -->
    <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-3.5 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <!-- Quick Filter Tabs -->
        <div class="flex items-center gap-1 overflow-x-auto pb-1 md:pb-0">
            <a href="{{ route('opname.show', array_merge(['opname' => $opname->id], request()->except('filter'))) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ !request()->filled('filter') ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:bg-[#182633] hover:text-white' }}">
                Semua Barang ({{ $opname->total_items }})
            </a>
            <a href="{{ route('opname.show', array_merge(['opname' => $opname->id], request()->except('filter'), ['filter' => 'uncounted'])) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('filter') === 'uncounted' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'text-slate-400 hover:bg-[#182633] hover:text-white' }}">
                Belum Dihitung ({{ $totalUncounted }})
            </a>
            <a href="{{ route('opname.show', array_merge(['opname' => $opname->id], request()->except('filter'), ['filter' => 'counted'])) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('filter') === 'counted' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:bg-[#182633] hover:text-white' }}">
                Sudah Dihitung ({{ $totalCounted }})
            </a>
            <a href="{{ route('opname.show', array_merge(['opname' => $opname->id], request()->except('filter'), ['filter' => 'variance'])) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('filter') === 'variance' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'text-slate-400 hover:bg-[#182633] hover:text-white' }}">
                Ada Selisih ({{ $varianceCount }})
            </a>
            <a href="{{ route('opname.show', array_merge(['opname' => $opname->id], request()->except('filter'), ['filter' => 'matched'])) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('filter') === 'matched' ? 'bg-teal-500/20 text-teal-400 border border-teal-500/30' : 'text-slate-400 hover:bg-[#182633] hover:text-white' }}">
                Pas / Sesuai ({{ $matchedCount }})
            </a>
        </div>

        <!-- Search in Session -->
        <form method="GET" action="{{ route('opname.show', $opname->id) }}" class="flex items-center gap-2">
            @if(request()->filled('filter'))
                <input type="hidden" name="filter" value="{{ request('filter') }}">
            @endif
            <div class="relative w-full md:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari SKU, Nama, Lokasi Rak..."
                       class="w-full pl-8 pr-3 py-1.5 bg-[#0e1821] border border-[#1f3344] rounded-lg text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
            </div>
            @if(request()->filled('search'))
                <a href="{{ route('opname.show', array_merge(['opname' => $opname->id], request()->except('search'))) }}"
                   class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-400 rounded-lg text-xs"
                   title="Reset">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- Items Audit Table -->
    <div class="table-container overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#1f3344] bg-[#0f1b24]/80 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Barang / SKU / Batch</th>
                        <th class="py-3 px-4">Lokasi Rak</th>
                        <th class="py-3 px-4 text-center">Stok Sistem</th>
                        <th class="py-3 px-4 text-center w-56">Stok Fisik (Aktual)</th>
                        <th class="py-3 px-4 text-center">Selisih</th>
                        @if(auth()->user()?->canViewFinancials())
                            <th class="py-3 px-4 text-right">Nilai Selisih</th>
                        @endif
                        <th class="py-3 px-4">Catatan / Keterangan</th>
                        @if($opname->status === 'in_progress')
                            <th class="py-3 px-4 text-center w-20">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1f3344]/50">
                    @forelse($items as $index => $item)
                        @php
                            $rowBg = '';
                            if ($item->is_counted) {
                                if ($item->variance === 0) {
                                    $rowBg = 'bg-emerald-500/5 hover:bg-emerald-500/10';
                                } else {
                                    $rowBg = 'bg-rose-500/5 hover:bg-rose-500/10';
                                }
                            } else {
                                $rowBg = 'hover:bg-[#162532]/70';
                            }
                        @endphp
                        <tr class="{{ $rowBg }} transition-colors" id="item-row-{{ $item->id }}">
                            <!-- Index -->
                            <td class="py-3.5 px-4 text-center font-mono text-slate-500">
                                {{ $items->firstItem() + $index }}
                            </td>

                            <!-- Product Info -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-emerald-400 text-xs">{{ $item->product->sku }}</span>
                                    @if($item->is_counted)
                                        <span class="px-1.5 py-0.2 rounded bg-emerald-500/20 text-emerald-300 text-[10px] font-bold">
                                            <i class="fa-solid fa-check"></i>
                                        </span>
                                    @endif
                                </div>
                                <div class="font-semibold text-white mt-0.5">{{ $item->product->title }}</div>
                                <div class="flex items-center gap-2 text-[10px] text-slate-400 mt-1">
                                    <span>{{ $item->product->category?->name ?? 'Tanpa Kategori' }}</span>
                                    @if($item->product->batch_number)
                                        <span class="text-slate-600">•</span>
                                        <span class="font-mono text-cyan-400">Batch: {{ $item->product->batch_number }}</span>
                                    @endif
                                    @if($item->product->expiry_date)
                                        <span class="text-slate-600">•</span>
                                        <span class="text-amber-400">Exp: {{ $item->product->expiry_date->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Lokasi Rak -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5 text-slate-300">
                                    <i class="fa-solid fa-map-pin text-emerald-400 text-[11px]"></i>
                                    <span class="font-medium">{{ $item->product->warehouse_location ?? 'Belum Diatur' }}</span>
                                </div>
                                <div class="text-[10px] text-slate-500 mt-0.5">{{ $item->product->warehouse?->name ?? 'Gudang' }}</div>
                            </td>

                            <!-- Stok Sistem -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="text-sm font-extrabold text-white font-mono">{{ $item->system_stock }}</div>
                                <div class="text-[10px] text-slate-400">{{ $item->product->unit }}</div>
                            </td>

                            <!-- Stok Fisik (Aktual) Counter -->
                            <td class="py-3.5 px-4 text-center">
                                @if($opname->status === 'in_progress')
                                    <form action="{{ route('opname.update-item', [$opname->id, $item->id]) }}" method="POST"
                                          class="flex items-center justify-center gap-1.5"
                                          x-data="{
                                              qty: {{ $item->physical_stock }},
                                              counted: {{ $item->is_counted ? 'true' : 'false' }},
                                              save() {
                                                  $el.submit();
                                              }
                                          }">
                                        @csrf
                                        @method('PUT')

                                        <!-- Minus button -->
                                        <button type="button"
                                                @click="if (qty > 0) { qty--; save(); }"
                                                class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 flex items-center justify-center text-xs font-bold border border-slate-700 transition">
                                            <i class="fa-solid fa-minus"></i>
                                        </button>

                                        <!-- Direct Number Input -->
                                        <input type="number"
                                               name="physical_stock"
                                               x-model.number="qty"
                                               @change="save()"
                                               min="0"
                                               class="w-16 py-1 text-center font-mono font-bold text-sm bg-[#0a1218] border border-[#1f3344] focus:border-emerald-500 rounded-lg text-emerald-300 focus:outline-none">

                                        <!-- Plus button -->
                                        <button type="button"
                                                @click="qty++; save();"
                                                class="w-7 h-7 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 flex items-center justify-center text-xs font-bold border border-emerald-500/30 transition">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>

                                        <!-- Quick match with system -->
                                        <button type="button"
                                                @click="qty = {{ $item->system_stock }}; save();"
                                                class="px-1.5 py-1 rounded bg-[#182633] hover:bg-emerald-500/20 text-slate-400 hover:text-emerald-400 text-[10px] font-semibold border border-[#1f3344] transition"
                                                title="Samakan dengan Stok Sistem ({{ $item->system_stock }})">
                                            =
                                        </button>
                                    </form>
                                @else
                                    <div class="text-sm font-extrabold text-white font-mono">{{ $item->physical_stock }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $item->product->unit }}</div>
                                @endif

                                @if($item->scanned_at)
                                    <div class="text-[9px] text-slate-500 mt-1">
                                        Scan: {{ $item->scanned_at->format('H:i') }}
                                    </div>
                                @endif
                            </td>

                            <!-- Selisih -->
                            <td class="py-3.5 px-4 text-center">
                                @if(!$item->is_counted)
                                    <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-400 text-[10px] font-semibold">
                                        Belum Dihitung
                                    </span>
                                @elseif($item->variance === 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 text-[11px] font-bold font-mono">
                                        <i class="fa-solid fa-check text-[10px]"></i> Sesuai (0)
                                    </span>
                                @elseif($item->variance > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-teal-500/15 text-teal-300 border border-teal-500/30 text-[11px] font-bold font-mono">
                                        <i class="fa-solid fa-arrow-up text-[10px]"></i> +{{ $item->variance }} Surplus
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-rose-500/15 text-rose-400 border border-rose-500/30 text-[11px] font-bold font-mono">
                                        <i class="fa-solid fa-arrow-down text-[10px]"></i> {{ $item->variance }} Defisit
                                    </span>
                                @endif
                            </td>

                            <!-- Nilai Selisih (Protected) -->
                            @if(auth()->user()?->canViewFinancials())
                                <td class="py-3.5 px-4 text-right">
                                    @if($item->is_counted)
                                        <div class="font-mono font-bold text-xs {{ $item->variance_value < 0 ? 'text-rose-400' : ($item->variance_value > 0 ? 'text-emerald-400' : 'text-slate-400') }}">
                                            {{ $item->variance_value < 0 ? '-' : ($item->variance_value > 0 ? '+' : '') }}Rp {{ number_format(abs($item->variance_value), 0, ',', '.') }}
                                        </div>
                                        <div class="text-[10px] text-slate-500">@ Rp {{ number_format($item->cost_price, 0, ',', '.') }}</div>
                                    @else
                                        <span class="text-slate-500 text-[11px]">-</span>
                                    @endif
                                </td>
                            @endif

                            <!-- Catatan -->
                            <td class="py-3.5 px-4">
                                @if($opname->status === 'in_progress')
                                    <form action="{{ route('opname.update-item', [$opname->id, $item->id]) }}" method="POST"
                                          x-data="{ note: '{{ addslashes($item->notes ?? '') }}' }"
                                          class="flex items-center gap-1">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="physical_stock" value="{{ $item->physical_stock }}">
                                        <input type="text"
                                               name="notes"
                                               x-model="note"
                                               @change="$el.form.submit()"
                                               placeholder="Tambah alasan selisih..."
                                               class="w-full px-2.5 py-1 bg-[#0a1218] border border-[#1f3344] focus:border-emerald-500 rounded text-[11px] text-slate-200 placeholder-slate-600 focus:outline-none">
                                    </form>
                                @else
                                    <div class="text-[11px] text-slate-300 italic">{{ $item->notes ?? '-' }}</div>
                                @endif
                            </td>

                            <!-- Aksi Per Row -->
                            @if($opname->status === 'in_progress')
                                <td class="py-3.5 px-4 text-center">
                                    <button type="button"
                                            @click="quickScanItem('{{ $item->product->sku }}')"
                                            class="px-2 py-1 rounded bg-slate-800 hover:bg-emerald-500/20 text-slate-400 hover:text-emerald-400 text-[10px] font-bold border border-slate-700 transition"
                                            title="Scan +1 barang ini">
                                        <i class="fa-solid fa-barcode"></i> +1
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()?->canViewFinancials() ? 9 : 8 }}" class="py-10 text-center text-slate-400">
                                <i class="fa-solid fa-inbox text-3xl text-slate-600 mb-2"></i>
                                <p class="text-xs">Tidak ada barang yang cocok dengan filter pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="p-4 border-t border-[#1f3344]">
                {{ $items->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Konfirmasi Rekonsiliasi Stok -->
    @if($opname->status === 'in_progress')
        <div x-show="reconcileModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             style="display: none;">

            <div @click.away="reconcileModal = false"
                 class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-[#1f3344] flex items-center justify-between bg-[#0e1821]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                            <i class="fa-solid fa-scale-balanced"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-white text-base">Konfirmasi Rekonsiliasi Stok</h3>
                            <p class="text-[11px] text-slate-400">Penyesuaian stok fisik ke kartu inventaris</p>
                        </div>
                    </div>
                    <button @click="reconcileModal = false" class="text-slate-400 hover:text-white">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">
                    <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-4 text-xs text-amber-300 space-y-2">
                        <div class="flex items-center gap-2 font-bold text-amber-400">
                            <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                            <span>Peringatan Penting:</span>
                        </div>
                        <p class="leading-relaxed text-amber-200/90">
                            Tindakan ini akan <strong>mengubah stok aktual produk di sistem</strong> sesuai hasil hitungan fisik pada sesi ini, dan secara otomatis mencatat riwayat <strong>Stock Movement (Penyesuaian Stok)</strong>.
                        </p>
                        <p class="text-[11px] text-amber-300/80">
                            Setelah disetujui, sesi Stock Opname akan <strong>ditutup dan dikunci secara permanen</strong>.
                        </p>
                    </div>

                    <!-- Summary Impact -->
                    <div class="grid grid-cols-2 gap-3 bg-[#0e1821] p-3.5 rounded-xl border border-[#1f3344] text-xs">
                        <div>
                            <span class="text-slate-400 text-[11px]">Total Item Diaudit:</span>
                            <div class="text-white font-bold text-base mt-0.5">{{ $opname->total_items }} SKU</div>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px]">Barang Akan Disesuaikan:</span>
                            <div class="text-amber-400 font-bold text-base mt-0.5">{{ $varianceCount }} SKU</div>
                        </div>
                        <div>
                            <span class="text-slate-400 text-[11px]">Net Selisih Fisik:</span>
                            <div class="text-white font-bold text-sm mt-0.5">{{ $netVarianceUnits > 0 ? '+' : '' }}{{ $netVarianceUnits }} Unit</div>
                        </div>
                        @if(auth()->user()?->canViewFinancials())
                            <div>
                                <span class="text-slate-400 text-[11px]">Valuasi Selisih Netto:</span>
                                <div class="font-bold text-sm mt-0.5 {{ $netVarianceValue < 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                                    {{ $netVarianceValue < 0 ? '-' : '+' }}Rp {{ number_format(abs($netVarianceValue), 0, ',', '.') }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="flex items-center justify-end gap-2.5 px-6 py-4 border-t border-[#1f3344] bg-[#0e1821]">
                    <button type="button"
                            @click="reconcileModal = false"
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition">
                        Batal
                    </button>
                    <form action="{{ route('opname.reconcile', $opname->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/25 transition">
                            <i class="fa-solid fa-check-double"></i>
                            <span>Ya, Setujui & Rekonsiliasi Sekarang</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>

<!-- Alpine & Scanner Script -->
@push('scripts')
<script>
    function opnameAuditRoom() {
        return {
            scannedCode: '',
            isScanning: false,
            lastScanMessage: '',
            lastScanSuccess: true,
            cameraOpen: false,
            html5QrCode: null,
            reconcileModal: false,

            init() {
                // Focus scanner input automatically if session in progress
                @if($opname->status === 'in_progress')
                    this.$nextTick(() => {
                        if (this.$refs.barcodeInput) {
                            this.$refs.barcodeInput.focus();
                        }
                    });
                @endif
            },

            // Play pleasant audio beep on successful scan
            playBeep(success = true) {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    if (success) {
                        osc.frequency.setValueAtTime(880, ctx.currentTime); // High pitch A5
                        gain.gain.setValueAtTime(0.15, ctx.currentTime);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.12);
                    } else {
                        osc.frequency.setValueAtTime(220, ctx.currentTime); // Low pitch error tone
                        gain.gain.setValueAtTime(0.2, ctx.currentTime);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.25);
                    }
                } catch (e) {
                    console.log('Audio feedback not supported:', e);
                }
            },

            // Submit barcode scan via AJAX
            async submitScanBarcode() {
                const code = this.scannedCode.trim();
                if (!code) return;

                this.isScanning = true;
                try {
                    const response = await fetch("{{ route('opname.scan', $opname->id) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ barcode: code })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        this.playBeep(true);
                        this.lastScanSuccess = true;
                        this.lastScanMessage = data.message;
                        this.scannedCode = '';

                        // Reload page to reflect updated counts and table immediately
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        this.playBeep(false);
                        this.lastScanSuccess = false;
                        this.lastScanMessage = data.message || 'Barang tidak ditemukan dalam sesi opname ini.';
                        this.scannedCode = '';
                    }
                } catch (err) {
                    this.playBeep(false);
                    this.lastScanSuccess = false;
                    this.lastScanMessage = 'Terjadi kesalahan jaringan saat memindai.';
                } finally {
                    this.isScanning = false;
                    if (this.$refs.barcodeInput) {
                        this.$refs.barcodeInput.focus();
                    }
                }
            },

            // Quick scan from row button
            quickScanItem(sku) {
                this.scannedCode = sku;
                this.submitScanBarcode();
            },

            // Toggle Camera Scanner using Html5Qrcode
            toggleCameraScanner() {
                if (this.cameraOpen) {
                    this.stopCameraScanner();
                } else {
                    this.startCameraScanner();
                }
            },

            startCameraScanner() {
                this.cameraOpen = true;
                this.$nextTick(() => {
                    if (typeof Html5Qrcode === 'undefined') {
                        alert('Library Html5Qrcode belum dimuat.');
                        this.cameraOpen = false;
                        return;
                    }

                    this.html5QrCode = new Html5Qrcode("opname-reader");
                    const config = { fps: 10, qrbox: { width: 250, height: 160 } };

                    this.html5QrCode.start(
                        { facingMode: "environment" },
                        config,
                        (decodedText) => {
                            // On successful camera scan
                            this.scannedCode = decodedText;
                            this.submitScanBarcode();
                        },
                        (errorMessage) => {
                            // ignore parse error frames
                        }
                    ).catch(err => {
                        console.error('Camera start failed:', err);
                        alert('Tidak dapat mengakses kamera: ' + err);
                        this.cameraOpen = false;
                    });
                });
            },

            stopCameraScanner() {
                if (this.html5QrCode) {
                    this.html5QrCode.stop().then(() => {
                        this.html5QrCode.clear();
                        this.html5QrCode = null;
                        this.cameraOpen = false;
                    }).catch(err => {
                        console.error('Failed to stop camera:', err);
                        this.cameraOpen = false;
                    });
                } else {
                    this.cameraOpen = false;
                }
            }
        };
    }
</script>
@endpush
@endsection
