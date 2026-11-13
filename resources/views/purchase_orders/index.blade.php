@extends('layouts.app')

@section('title', 'Purchase Orders (PO) - Pengadaan Inbound')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Row -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-lg">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    Purchase Orders (PO)
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">Inbound Procurement</span>
                </h1>
                <p class="text-xs text-slate-400">Penerbitan pesanan pembelian ke pemasok, monitoring pengiriman, dan penerimaan stok gudang.</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('suppliers.index') }}"
               class="px-3.5 py-2.5 rounded-xl bg-[#182633] hover:bg-[#203344] text-slate-300 font-semibold text-xs flex items-center gap-2 border border-[#1f3344] transition">
                <i class="fa-solid fa-handshake text-emerald-400"></i>
                <span>Kelola Supplier</span>
            </a>

            <a href="{{ route('purchase-orders.create') }}"
               class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/20 transition hover:scale-[1.02]">
                <i class="fa-solid fa-plus-circle text-sm"></i>
                <span>Buat Purchase Order Baru</span>
            </a>
        </div>
    </div>

    <!-- Summary Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total PO -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total PO Diterbitkan</p>
                <h3 class="text-2xl font-extrabold text-white mt-1">{{ number_format($totalPOs) }}</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Semua riwayat pesanan</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 text-xl">
                <i class="fa-solid fa-file-lines"></i>
            </div>
        </div>

        <!-- Menunggu Pengiriman -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Menunggu Barang Tiba</p>
                <h3 class="text-2xl font-extrabold text-amber-400 mt-1">{{ number_format($pendingPOs) }}</h3>
                <p class="text-[11px] text-amber-500/80 mt-0.5">Dalam proses kirim / parsial</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 text-xl">
                <i class="fa-solid fa-truck-moving"></i>
            </div>
        </div>

        <!-- Selesai Diterima -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Selesai Diterima (GRN)</p>
                <h3 class="text-2xl font-extrabold text-emerald-400 mt-1">{{ number_format($completedPOs) }}</h3>
                <p class="text-[11px] text-emerald-500/80 mt-0.5">Stok masuk 100% lengkap</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <!-- Total Valuasi PO (Role-masked) -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Nilai Pembelian</p>
                @if(auth()->user()?->canViewFinancials())
                    <h3 class="text-xl font-extrabold text-emerald-400 mt-1">
                        Rp {{ number_format($totalPurchaseValue, 0, ',', '.') }}
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Akumulasi biaya pengadaan</p>
                @else
                    <div class="flex items-center gap-1.5 mt-2 text-slate-500 text-xs">
                        <i class="fa-solid fa-lock text-[10px]"></i>
                        <span>Privasi Finansial</span>
                    </div>
                @endif
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 text-xl">
                <i class="fa-solid fa-coins"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-3.5 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <!-- Status Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
            <a href="{{ route('purchase-orders.index', array_merge(request()->except('status'), ['status' => 'all'])) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ (!request()->filled('status') || request('status') === 'all') ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:bg-[#182633]' }}">
                Semua PO
            </a>
            <a href="{{ route('purchase-orders.index', array_merge(request()->except('status'), ['status' => 'ordered'])) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'ordered' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : 'text-slate-400 hover:bg-[#182633]' }}">
                Menunggu Kiriman
            </a>
            <a href="{{ route('purchase-orders.index', array_merge(request()->except('status'), ['status' => 'partial'])) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'partial' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'text-slate-400 hover:bg-[#182633]' }}">
                Parsial / Sebagian
            </a>
            <a href="{{ route('purchase-orders.index', array_merge(request()->except('status'), ['status' => 'received'])) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'received' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:bg-[#182633]' }}">
                Lengkap (Received)
            </a>
            <a href="{{ route('purchase-orders.index', array_merge(request()->except('status'), ['status' => 'cancelled'])) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'cancelled' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'text-slate-400 hover:bg-[#182633]' }}">
                Dibatalkan
            </a>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('purchase-orders.index') }}" class="flex items-center gap-2">
            @if(request()->filled('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="relative w-full md:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari No. PO / Supplier..."
                       class="w-full pl-8 pr-3 py-1.5 bg-[#0e1821] border border-[#1f3344] rounded-lg text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
            </div>
            @if(request()->filled('search'))
                <a href="{{ route('purchase-orders.index', request()->except('search')) }}" class="px-2.5 py-1.5 bg-slate-800 text-slate-400 rounded-lg text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- Purchase Orders Table -->
    <div class="table-container overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#1f3344] bg-[#0f1b24]/80 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No. PO & Tanggal</th>
                        <th class="py-3 px-4">Pemasok / Vendor</th>
                        <th class="py-3 px-4">Gudang Tujuan</th>
                        <th class="py-3 px-4">Estimasi Tiba</th>
                        <th class="py-3 px-4 text-center">Status Pesanan</th>
                        <th class="py-3 px-4 text-center">Item</th>
                        @if(auth()->user()?->canViewFinancials())
                            <th class="py-3 px-4 text-right">Total Nilai</th>
                        @endif
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1f3344]/50">
                    @forelse($purchaseOrders as $po)
                        <tr class="hover:bg-[#162532]/70 transition-colors">
                            <!-- No PO & Tanggal -->
                            <td class="py-3.5 px-4">
                                <div class="font-mono font-bold text-emerald-400 text-[13px] flex items-center gap-1.5">
                                    <i class="fa-solid fa-file-invoice text-slate-500"></i>
                                    <span>{{ $po->po_number }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $po->order_date->format('d M Y') }}
                                </div>
                            </td>

                            <!-- Supplier -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white text-xs">{{ $po->supplier->name }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $po->supplier->contact_person ? 'PIC: ' . $po->supplier->contact_person : 'Term: ' . $po->supplier->payment_terms }}
                                </div>
                            </td>

                            <!-- Gudang -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5 text-slate-300">
                                    <i class="fa-solid fa-warehouse text-emerald-400 text-[11px]"></i>
                                    <span>{{ $po->warehouse ? $po->warehouse->name : 'Semua Gudang' }}</span>
                                </div>
                            </td>

                            <!-- Estimasi Tiba -->
                            <td class="py-3.5 px-4">
                                @if($po->expected_delivery_date)
                                    <div class="text-slate-300 font-mono text-[11px]">
                                        {{ $po->expected_delivery_date->format('d M Y') }}
                                    </div>
                                    @if($po->expected_delivery_date->isPast() && in_array($po->status, ['ordered', 'partial']))
                                        <span class="text-[10px] text-rose-400 font-bold">Terlambat</span>
                                    @endif
                                @else
                                    <span class="text-slate-500">-</span>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold border {{ $po->status_badge }}">
                                    @if($po->status === 'ordered')
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400 mr-1.5 animate-pulse"></span>
                                    @elseif($po->status === 'partial')
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-1.5"></span>
                                    @elseif($po->status === 'received')
                                        <i class="fa-solid fa-check mr-1.5 text-emerald-400"></i>
                                    @endif
                                    {{ $po->status_label }}
                                </span>
                            </td>

                            <!-- Item Count -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-mono font-bold text-white">{{ $po->items_count }} SKU</span>
                            </td>

                            <!-- Financial Total (Protected) -->
                            @if(auth()->user()?->canViewFinancials())
                                <td class="py-3.5 px-4 text-right">
                                    <div class="font-mono font-bold text-xs text-white">
                                        Rp {{ number_format($po->total_amount, 0, ',', '.') }}
                                    </div>
                                    <div class="text-[10px] text-slate-500 uppercase">{{ $po->payment_status }}</div>
                                </td>
                            @endif

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if(in_array($po->status, ['ordered', 'partial']))
                                        <a href="{{ route('goods-receipts.create', $po->id) }}"
                                           class="px-2.5 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs flex items-center gap-1 shadow-sm transition"
                                           title="Terima Barang Masuk Gudang">
                                            <i class="fa-solid fa-dolly"></i>
                                            <span>Terima</span>
                                        </a>
                                    @endif

                                    <a href="{{ route('purchase-orders.show', $po->id) }}"
                                       class="p-1.5 rounded-lg bg-[#182633] hover:bg-[#203344] text-slate-300 hover:text-white transition"
                                       title="Rincian PO">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

                                    <a href="{{ route('purchase-orders.print', $po->id) }}"
                                       target="_blank"
                                       class="p-1.5 rounded-lg bg-[#182633] hover:bg-[#203344] text-emerald-400 transition"
                                       title="Cetak Purchase Order">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()?->canViewFinancials() ? 8 : 7 }}" class="py-12 text-center text-slate-400">
                                <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-slate-800/60 flex items-center justify-center text-slate-600 text-2xl">
                                    <i class="fa-solid fa-file-circle-xmark"></i>
                                </div>
                                <h4 class="font-bold text-white text-base">Belum Ada Purchase Order</h4>
                                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                                    Buat PO baru untuk memesan stok barang dari supplier dan mulai proses penerimaan inbound.
                                </p>
                                <a href="{{ route('purchase-orders.create') }}"
                                   class="mt-4 px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs inline-flex items-center gap-2">
                                    <i class="fa-solid fa-plus"></i>
                                    <span>Buat PO Pertama</span>
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchaseOrders->hasPages())
            <div class="p-4 border-t border-[#1f3344]">
                {{ $purchaseOrders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
