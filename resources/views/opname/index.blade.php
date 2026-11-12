@extends('layouts.app')

@section('title', 'Stock Opname Digital - Audit Fisik & Rekonsiliasi')

@section('content')
<div class="space-y-6" x-data="{ newOpnameModal: false }">

    <!-- Header & Action Row -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-lg">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                        Stock Opname Digital
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">Audit Fisik & Rekonsiliasi</span>
                    </h1>
                    <p class="text-xs text-slate-400">Verifikasi fisik barang di gudang dengan scanner barcode, hitung selisih, dan rekonsiliasi stok otomatis.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <button @click="newOpnameModal = true"
                    class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/20 transition-all hover:scale-[1.02] active:scale-95">
                <i class="fa-solid fa-plus-circle text-sm"></i>
                <span>Mulai Stock Opname Baru</span>
            </button>
        </div>
    </div>

    <!-- Metric Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Sesi -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Sesi Audit</p>
                <h3 class="text-2xl font-extrabold text-white mt-1">{{ number_format($totalSessions) }}</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Sepanjang waktu</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 text-xl">
                <i class="fa-solid fa-folder-open"></i>
            </div>
        </div>

        <!-- Sesi Berjalan -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Sedang Dihitung</p>
                <h3 class="text-2xl font-extrabold text-amber-400 mt-1">{{ number_format($inProgressSessions) }}</h3>
                <p class="text-[11px] text-amber-500/80 mt-0.5">Memerlukan input fisik</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 text-xl">
                <i class="fa-solid fa-barcode"></i>
            </div>
        </div>

        <!-- Selesai Rekonsiliasi -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Terekonsiliasi</p>
                <h3 class="text-2xl font-extrabold text-emerald-400 mt-1">{{ number_format($completedSessions) }}</h3>
                <p class="text-[11px] text-emerald-500/80 mt-0.5">Stok kartu disesuaikan</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <!-- Akumulasi Finansial Selisih (Role-masked) -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Valuasi Selisih Netto</p>
                @if(auth()->user()?->canViewFinancials())
                    <h3 class="text-xl font-extrabold {{ $totalVarianceValue < 0 ? 'text-rose-400' : 'text-emerald-400' }} mt-1">
                        {{ $totalVarianceValue < 0 ? '-' : '+' }}Rp {{ number_format(abs($totalVarianceValue), 0, ',', '.') }}
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Dampak pada neraca stok</p>
                @else
                    <div class="flex items-center gap-1.5 mt-2 text-slate-500 text-xs">
                        <i class="fa-solid fa-lock text-[10px]"></i>
                        <span>Privasi Finansial</span>
                    </div>
                @endif
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 text-xl">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Status Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
            <a href="{{ route('opname.index', array_merge(request()->except('status'), ['status' => 'all'])) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ (!request()->filled('status') || request('status') === 'all') ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:bg-[#182633] hover:text-white' }}">
                Semua Sesi
            </a>
            <a href="{{ route('opname.index', array_merge(request()->except('status'), ['status' => 'in_progress'])) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'in_progress' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'text-slate-400 hover:bg-[#182633] hover:text-white' }}">
                <span class="inline-block w-2 h-2 rounded-full bg-amber-400 mr-1.5 animate-pulse"></span>
                Sedang Berjalan ({{ $inProgressSessions }})
            </a>
            <a href="{{ route('opname.index', array_merge(request()->except('status'), ['status' => 'completed'])) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'completed' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:bg-[#182633] hover:text-white' }}">
                Selesai / Rekonsiliasi ({{ $completedSessions }})
            </a>
            <a href="{{ route('opname.index', array_merge(request()->except('status'), ['status' => 'cancelled'])) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'cancelled' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'text-slate-400 hover:bg-[#182633] hover:text-white' }}">
                Dibatalkan
            </a>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('opname.index') }}" class="flex items-center gap-2">
            @if(request()->filled('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="relative w-full md:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari No. Opname / Judul..."
                       class="w-full pl-8 pr-3 py-1.5 bg-[#0e1821] border border-[#1f3344] rounded-lg text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
            </div>
            @if(request()->filled('search'))
                <a href="{{ route('opname.index', request()->except('search')) }}"
                   class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-400 rounded-lg text-xs"
                   title="Reset Filter">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- Opname Sessions Table / Cards -->
    <div class="table-container overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#1f3344] bg-[#0f1b24]/80 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No. Opname / Sesi</th>
                        <th class="py-3 px-4">Cakupan Audit</th>
                        <th class="py-3 px-4">Status & Waktu</th>
                        <th class="py-3 px-4 text-center">Item Diaudit</th>
                        <th class="py-3 px-4 text-center">Hasil Fisik</th>
                        @if(auth()->user()?->canViewFinancials())
                            <th class="py-3 px-4 text-right">Nilai Selisih</th>
                        @endif
                        <th class="py-3 px-4">Auditor</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1f3344]/50">
                    @forelse($opnames as $opname)
                        <tr class="hover:bg-[#162532]/70 transition-colors">
                            <!-- No & Judul -->
                            <td class="py-3.5 px-4">
                                <div class="font-mono font-bold text-emerald-400 text-[13px] flex items-center gap-1.5">
                                    <i class="fa-solid fa-barcode text-slate-500"></i>
                                    <span>{{ $opname->opname_number }}</span>
                                </div>
                                <div class="font-semibold text-white mt-0.5">{{ $opname->title }}</div>
                                @if($opname->notes)
                                    <div class="text-[11px] text-slate-400 italic truncate max-w-xs mt-0.5">{{ $opname->notes }}</div>
                                @endif
                            </td>

                            <!-- Cakupan -->
                            <td class="py-3.5 px-4">
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex items-center gap-1.5 text-slate-300">
                                        <i class="fa-solid fa-warehouse text-emerald-400 text-[11px]"></i>
                                        <span>{{ $opname->warehouse ? $opname->warehouse->name : 'Semua Gudang' }}</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 text-slate-400 text-[11px]">
                                        <i class="fa-solid fa-tags text-slate-500 text-[10px]"></i>
                                        <span>{{ $opname->category ? $opname->category->name : 'Semua Kategori' }}</span>
                                    </span>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold border {{ $opname->status_badge }}">
                                    @if($opname->status === 'in_progress')
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-1.5 animate-ping"></span>
                                    @elseif($opname->status === 'completed')
                                        <i class="fa-solid fa-check mr-1.5 text-emerald-400"></i>
                                    @endif
                                    {{ $opname->status_label }}
                                </span>
                                <div class="text-[11px] text-slate-400 mt-1">
                                    {{ $opname->created_at->format('d M Y, H:i') }}
                                </div>
                            </td>

                            <!-- Total Items -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="text-sm font-bold text-white">{{ number_format($opname->total_items) }}</div>
                                <div class="text-[10px] text-slate-400">SKU Barang</div>
                            </td>

                            <!-- Hasil Fisik -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-mono font-bold text-[11px] border border-emerald-500/20" title="Jumlah barang fisik pas sesuai sistem">
                                        {{ $opname->matched_items }} Pas
                                    </span>
                                    <span class="px-2 py-0.5 rounded {{ $opname->variance_items > 0 ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-slate-800 text-slate-400' }} font-mono font-bold text-[11px]" title="Jumlah barang terdapat selisih">
                                        {{ $opname->variance_items }} Selisih
                                    </span>
                                </div>
                            </td>

                            <!-- Financial (Protected) -->
                            @if(auth()->user()?->canViewFinancials())
                                <td class="py-3.5 px-4 text-right">
                                    <div class="font-mono font-bold text-xs {{ $opname->total_variance_value < 0 ? 'text-rose-400' : ($opname->total_variance_value > 0 ? 'text-emerald-400' : 'text-slate-400') }}">
                                        {{ $opname->total_variance_value < 0 ? '-' : ($opname->total_variance_value > 0 ? '+' : '') }}Rp {{ number_format(abs($opname->total_variance_value), 0, ',', '.') }}
                                    </div>
                                    <div class="text-[10px] text-slate-500">Nilai Selisih</div>
                                </td>
                            @endif

                            <!-- Auditor -->
                            <td class="py-3.5 px-4">
                                <div class="text-white font-medium text-xs">{{ $opname->creator?->name ?? 'System' }}</div>
                                @if($opname->completedByUser)
                                    <div class="text-[10px] text-emerald-400/80">ACC: {{ $opname->completedByUser->name }}</div>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if($opname->status === 'in_progress')
                                        <a href="{{ route('opname.show', $opname->id) }}"
                                           class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs flex items-center gap-1.5 shadow-sm transition">
                                            <i class="fa-solid fa-barcode"></i>
                                            <span>Hitung Fisik</span>
                                        </a>
                                    @else
                                        <a href="{{ route('opname.show', $opname->id) }}"
                                           class="px-2.5 py-1.5 rounded-lg bg-[#182633] hover:bg-[#203344] text-slate-300 text-xs flex items-center gap-1 transition"
                                           title="Rincian Sesi">
                                            <i class="fa-solid fa-eye"></i>
                                            <span>Rincian</span>
                                        </a>
                                    @endif

                                    <a href="{{ route('opname.report', $opname->id) }}"
                                       target="_blank"
                                       class="p-1.5 rounded-lg bg-[#182633] hover:bg-[#203344] text-emerald-400 text-xs transition"
                                       title="Cetak Berita Acara Stock Opname">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()?->canViewFinancials() ? 8 : 7 }}" class="py-12 text-center text-slate-400">
                                <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-slate-800/60 flex items-center justify-center text-slate-600 text-2xl">
                                    <i class="fa-solid fa-clipboard-question"></i>
                                </div>
                                <h4 class="font-bold text-white text-base">Belum Ada Sesi Stock Opname</h4>
                                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                                    Mulai audit fisik gudang secara berkala untuk menjaga keakuratan stok dan meminimalkan kerugian selisih barang.
                                </p>
                                <button @click="newOpnameModal = true"
                                        class="mt-4 px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs inline-flex items-center gap-2">
                                    <i class="fa-solid fa-plus"></i>
                                    <span>Mulai Sesi Pertama</span>
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($opnames->hasPages())
            <div class="p-4 border-t border-[#1f3344]">
                {{ $opnames->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Buat Stock Opname Baru -->
    <div x-show="newOpnameModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         style="display: none;">

        <div @click.away="newOpnameModal = false"
             class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-[#1f3344] flex items-center justify-between bg-[#0e1821]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                        <i class="fa-solid fa-clipboard-list"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Mulai Sesi Stock Opname Baru</h3>
                        <p class="text-[11px] text-slate-400">Snapshot data stok sistem untuk perhitungan fisik</p>
                    </div>
                </div>
                <button @click="newOpnameModal = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('opname.store') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Judul / Nama Acara Audit <span class="text-rose-400">*</span>
                    </label>
                    <input type="text"
                           name="title"
                           required
                           placeholder="Contoh: Stock Opname Bulanan November 2026"
                           value="Stock Opname {{ date('F Y') }}"
                           class="w-full px-3.5 py-2.5 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                    <!-- Warehouse Scope -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Cakupan Gudang
                        </label>
                        <select name="warehouse_id"
                                class="w-full px-3.5 py-2.5 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                            <option value="">Semua Gudang (Menyeluruh)</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-500 mt-1">Pilih jika ingin audit parsial per gudang</p>
                    </div>

                    <!-- Category Scope -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Cakupan Kategori
                        </label>
                        <select name="category_id"
                                class="w-full px-3.5 py-2.5 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-500 mt-1">Pilih jika ingin audit parsial per kategori</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Instruksi & Catatan untuk Tim Lapangan
                    </label>
                    <textarea name="notes"
                              rows="3"
                              placeholder="Contoh: Fokus pada rak elektronik dan cek tanggal kadaluarsa batch..."
                              class="w-full px-3.5 py-2 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-3 flex items-start gap-2.5 text-xs text-amber-300">
                    <i class="fa-solid fa-circle-info text-amber-400 mt-0.5"></i>
                    <div>
                        <p class="font-bold">Info Audit:</p>
                        <p class="text-[11px] text-amber-300/80 mt-0.5">
                            Sistem akan mengambil foto cuplikan (snapshot) stok buku saat ini. Tim dapat langsung menghitung barang menggunakan scanner barcode atau input manual.
                        </p>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-[#1f3344]">
                    <button type="button"
                            @click="newOpnameModal = false"
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold text-xs transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/20 transition">
                        <i class="fa-solid fa-play"></i>
                        <span>Buat & Buka Ruang Audit</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
