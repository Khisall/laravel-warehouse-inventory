@extends('layouts.app')

@section('title', 'Penerimaan Barang Masuk (GRN) - ' . $purchaseOrder->po_number)

@section('content')
<div class="space-y-6">

    <!-- Header & Breadcrumb -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchase-orders.show', $purchaseOrder->id) }}" class="p-2 rounded-xl bg-[#13202b] hover:bg-[#182633] text-slate-400 hover:text-white border border-[#1f3344] transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    Penerimaan Barang Masuk Gudang (GRN)
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">Inbound Putaway</span>
                </h1>
                <p class="text-xs text-slate-400">Verifikasi fisik barang tiba dari surat jalan vendor, uji QC, dan penetapan batch/rak gudang.</p>
            </div>
        </div>
    </div>

    <!-- PO Summary Banner -->
    <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 shadow-lg">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block mb-0.5">Referensi Purchase Order:</span>
                <span class="font-mono font-bold text-emerald-400 text-sm">{{ $purchaseOrder->po_number }}</span>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">Pemasok / Vendor:</span>
                <strong class="text-white text-sm block">{{ $purchaseOrder->supplier->name }}</strong>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">Gudang Penerima:</span>
                <span class="text-white font-semibold flex items-center gap-1.5 mt-0.5">
                    <i class="fa-solid fa-warehouse text-emerald-400"></i>
                    <span>{{ $purchaseOrder->warehouse ? $purchaseOrder->warehouse->name : 'Gudang Utama' }}</span>
                </span>
            </div>
            <div>
                <span class="text-slate-400 block mb-0.5">Status PO Saat Ini:</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $purchaseOrder->status_badge }}">
                    {{ $purchaseOrder->status_label }}
                </span>
            </div>
        </div>
    </div>

    <!-- Receiving Form -->
    <form action="{{ route('goods-receipts.store', $purchaseOrder->id) }}" method="POST" class="space-y-6">
        @csrf

        <!-- Vendor DO & Inbound Metadata -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 shadow-lg space-y-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2 border-b border-[#1f3344] pb-3">
                <i class="fa-solid fa-truck-moving text-emerald-400"></i>
                <span>Informasi Dokumen Pengiriman Vendor</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Surat Jalan Vendor -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Nomor Surat Jalan (DO) Vendor <span class="text-rose-400">*</span>
                    </label>
                    <input type="text"
                           name="vendor_do_number"
                           required
                           placeholder="Contoh: SJ-2026/MTR/8891"
                           class="w-full px-3.5 py-2.5 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 font-mono">
                    <p class="text-[10px] text-slate-500 mt-1">Nomor fisik surat jalan yang dibawa supir/ekspedisi</p>
                </div>

                <!-- Received Date -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Waktu Barang Tiba di Gudang <span class="text-rose-400">*</span>
                    </label>
                    <input type="datetime-local"
                           name="received_date"
                           value="{{ date('Y-m-d\TH:i') }}"
                           required
                           class="w-full px-3.5 py-2.5 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500 font-mono">
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Catatan Penerimaan / No. Polisi Kendaraan
                    </label>
                    <input type="text"
                           name="notes"
                           placeholder="Contoh: Truk B 9123 UXT, segel utuh..."
                           class="w-full px-3.5 py-2.5 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
                </div>
            </div>
        </div>

        <!-- Items Receiving & QC Checklist -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 shadow-lg space-y-4">
            <div class="border-b border-[#1f3344] pb-3 flex items-center justify-between">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-check text-emerald-400"></i>
                    <span>Verifikasi Fisik, QC & Lokasi Rak Putaway</span>
                </h3>
                <span class="text-xs text-slate-400">Pastikan hitungan fisik akurat sebelum konfirmasi</span>
            </div>

            <div class="space-y-4">
                @foreach($purchaseOrder->items as $index => $item)
                    @php
                        $remaining = $item->remaining_quantity;
                    @endphp
                    <div class="bg-[#0e1821] border border-[#1f3344] rounded-xl p-4 space-y-3 {{ $remaining === 0 ? 'opacity-60 bg-[#0a1218]' : '' }}">
                        <input type="hidden" name="items[{{ $index }}][purchase_order_item_id]" value="{{ $item->id }}">
                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">

                        <!-- Row Header: Product Info & Order Stats -->
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 border-b border-[#1f3344]/60 pb-2.5">
                            <div>
                                <span class="font-mono font-bold text-emerald-400 text-xs">{{ $item->product->sku }}</span>
                                <h4 class="font-bold text-white text-sm">{{ $item->product->title }}</h4>
                                <span class="text-[11px] text-slate-400">{{ $item->product->category?->name ?? 'Tanpa Kategori' }}</span>
                            </div>

                            <div class="flex items-center gap-4 text-xs font-mono">
                                <div>
                                    <span class="text-slate-500 text-[10px] block">Dipesan:</span>
                                    <strong class="text-white">{{ $item->quantity_ordered }} {{ $item->product->unit }}</strong>
                                </div>
                                <div>
                                    <span class="text-slate-500 text-[10px] block">Telah Masuk:</span>
                                    <span class="text-emerald-400 font-bold">{{ $item->quantity_received }} {{ $item->product->unit }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-500 text-[10px] block">Sisa Menunggu:</span>
                                    <strong class="{{ $remaining > 0 ? 'text-amber-400' : 'text-slate-500' }}">
                                        {{ $remaining }} {{ $item->product->unit }}
                                    </strong>
                                </div>
                            </div>
                        </div>

                        <!-- Row Inputs: Accepted, Rejected, Batch, Expiry, Rack -->
                        @if($remaining > 0)
                            <div class="grid grid-cols-2 md:grid-cols-6 gap-3 text-xs pt-1">
                                <!-- Qty Accepted -->
                                <div>
                                    <label class="block text-[11px] font-bold text-emerald-400 mb-1">
                                        <i class="fa-solid fa-check"></i> Diterima Masuk (Unit)
                                    </label>
                                    <input type="number"
                                           name="items[{{ $index }}][quantity_received]"
                                           value="{{ $remaining }}"
                                           min="0"
                                           max="{{ $remaining }}"
                                           required
                                           class="w-full px-3 py-2 bg-[#0a1218] border-2 border-emerald-500/50 rounded-lg text-xs font-mono font-bold text-emerald-300 focus:outline-none focus:border-emerald-400">
                                </div>

                                <!-- Qty Rejected (QC Fail) -->
                                <div>
                                    <label class="block text-[11px] font-bold text-rose-400 mb-1">
                                        <i class="fa-solid fa-xmark"></i> Cacat / Ditolak (QC)
                                    </label>
                                    <input type="number"
                                           name="items[{{ $index }}][quantity_rejected]"
                                           value="0"
                                           min="0"
                                           class="w-full px-3 py-2 bg-[#0a1218] border border-rose-500/30 rounded-lg text-xs font-mono text-rose-300 focus:outline-none focus:border-rose-400">
                                </div>

                                <!-- Rejection Reason -->
                                <div>
                                    <label class="block text-[11px] text-slate-400 mb-1">Alasan Penolakan</label>
                                    <input type="text"
                                           name="items[{{ $index }}][rejection_reason]"
                                           placeholder="Contoh: Dus sobek / pecah"
                                           class="w-full px-3 py-2 bg-[#0a1218] border border-[#1f3344] rounded-lg text-xs text-slate-300 placeholder-slate-600 focus:outline-none">
                                </div>

                                <!-- Batch Number -->
                                <div>
                                    <label class="block text-[11px] text-cyan-400 mb-1 font-semibold">
                                        <i class="fa-solid fa-layer-group"></i> No. Batch Baru
                                    </label>
                                    <input type="text"
                                           name="items[{{ $index }}][batch_number]"
                                           value="{{ $item->product->batch_number ?: 'BATCH-' . date('Ym') . '-' . str_pad($item->product->id, 2, '0', STR_PAD_LEFT) }}"
                                           class="w-full px-3 py-2 bg-[#0a1218] border border-[#1f3344] rounded-lg text-xs font-mono text-cyan-300 focus:outline-none">
                                </div>

                                <!-- Expiry Date -->
                                <div>
                                    <label class="block text-[11px] text-amber-400 mb-1 font-semibold">
                                        <i class="fa-solid fa-calendar-xmark"></i> Expired Date
                                    </label>
                                    <input type="date"
                                           name="items[{{ $index }}][expiry_date]"
                                           value="{{ $item->product->expiry_date ? $item->product->expiry_date->format('Y-m-d') : '' }}"
                                           class="w-full px-3 py-2 bg-[#0a1218] border border-[#1f3344] rounded-lg text-xs font-mono text-amber-300 focus:outline-none">
                                </div>

                                <!-- Putaway Rack Location -->
                                <div>
                                    <label class="block text-[11px] text-slate-400 mb-1">
                                        <i class="fa-solid fa-map-pin text-emerald-400"></i> Lokasi Rak Putaway
                                    </label>
                                    <input type="text"
                                           name="items[{{ $index }}][shelf_location]"
                                           value="{{ $item->product->warehouse_location ?: 'Rak Inbound' }}"
                                           class="w-full px-3 py-2 bg-[#0a1218] border border-[#1f3344] rounded-lg text-xs text-slate-200 focus:outline-none">
                                </div>
                            </div>
                        @else
                            <div class="text-xs text-emerald-400 flex items-center gap-1.5 py-1">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Barang ini sudah 100% lengkap diterima pada kedatangan sebelumnya.</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('purchase-orders.show', $purchaseOrder->id) }}"
               class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">
                Batal
            </a>
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/25 transition hover:scale-[1.02]">
                <i class="fa-solid fa-check-double"></i>
                <span>Konfirmasi Penerimaan Barang Masuk (Update Stok Fisik)</span>
            </button>
        </div>
    </form>

</div>
@endsection
