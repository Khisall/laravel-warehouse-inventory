@extends('layouts.app')

@section('title', 'Detail Penerimaan Barang - ' . $goodsReceipt->receipt_number)

@section('content')
<div class="space-y-6">

    <!-- Header Navigation & Action Bar -->
    <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 shadow-lg">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Left Info -->
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <a href="{{ route('goods-receipts.index') }}" class="text-slate-400 hover:text-emerald-400 text-xs flex items-center gap-1 transition">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Daftar Penerimaan</span>
                    </a>
                    <span class="text-slate-600">/</span>
                    <span class="font-mono text-xs text-emerald-400 font-bold">{{ $goodsReceipt->receipt_number }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                        <i class="fa-solid fa-check mr-1 text-[9px]"></i> Sukses Masuk Gudang
                    </span>
                </div>

                <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    <span>Bukti Penerimaan Barang (GRN)</span>
                </h1>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400 mt-2">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-file-invoice text-emerald-400"></i>
                        <span>Ref PO: </span>
                        <a href="{{ route('purchase-orders.show', $goodsReceipt->purchase_order_id) }}" class="font-mono font-bold text-cyan-400 hover:underline">
                            {{ $goodsReceipt->purchaseOrder->po_number }}
                        </a>
                    </span>
                    <span class="text-slate-600">•</span>
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-truck text-slate-500"></i>
                        <span>SJ Vendor: <strong class="text-white font-mono">{{ $goodsReceipt->vendor_do_number }}</strong></span>
                    </span>
                    <span class="text-slate-600">•</span>
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-warehouse text-slate-500"></i>
                        <span>{{ $goodsReceipt->warehouse ? $goodsReceipt->warehouse->name : 'Gudang Pusat' }}</span>
                    </span>
                    <span class="text-slate-600">•</span>
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-calendar text-slate-500"></i>
                        <span>{{ $goodsReceipt->received_date->format('d M Y, H:i') }} WIB</span>
                    </span>
                </div>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-2.5">
                <a href="{{ route('purchase-orders.show', $goodsReceipt->purchase_order_id) }}"
                   class="px-3.5 py-2.5 rounded-xl bg-[#182633] hover:bg-[#203344] text-slate-200 font-semibold text-xs flex items-center gap-2 border border-[#1f3344] transition">
                    <i class="fa-solid fa-file-invoice-dollar text-cyan-400"></i>
                    <span>Buka PO Terkait</span>
                </a>

                <a href="{{ route('goods-receipts.print', $goodsReceipt->id) }}"
                   target="_blank"
                   class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/20 transition hover:scale-[1.02]">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak Bukti Penerimaan (GRN)</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Items Received Table -->
    <div class="table-container overflow-hidden">
        <div class="px-5 py-4 border-b border-[#1f3344] flex items-center justify-between">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-emerald-400"></i>
                <span>Rincian Barang yang Diterima</span>
            </h3>
            <span class="text-xs text-slate-400 font-mono">
                Total: <strong class="text-emerald-400">{{ $goodsReceipt->items->sum('quantity_received') }}</strong> Unit Diterima
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#1f3344] bg-[#0f1b24]/80 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Barang & SKU</th>
                        <th class="py-3 px-4 text-center">Diterima (Masuk Stok)</th>
                        <th class="py-3 px-4 text-center">Ditolak QC</th>
                        <th class="py-3 px-4">No. Batch Masuk</th>
                        <th class="py-3 px-4">Tanggal Expired</th>
                        <th class="py-3 px-4">Lokasi Rak Putaway</th>
                        <th class="py-3 px-4">Alasan Cacat / Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1f3344]/50">
                    @foreach($goodsReceipt->items as $index => $item)
                        <tr class="hover:bg-[#162532]/70 transition-colors">
                            <td class="py-3.5 px-4 text-center font-mono text-slate-500">{{ $index + 1 }}</td>
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-emerald-400 text-xs">{{ $item->product->sku }}</span>
                                <div class="font-bold text-white text-sm mt-0.5">{{ $item->product->title }}</div>
                                <span class="text-[10px] text-slate-400">{{ $item->product->category?->name ?? 'Tanpa Kategori' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-bold text-emerald-400 text-sm">
                                +{{ $item->quantity_received }} {{ $item->product->unit }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono">
                                @if($item->quantity_rejected > 0)
                                    <span class="px-2 py-0.5 rounded bg-rose-500/15 text-rose-400 font-bold text-[11px] border border-rose-500/30">
                                        {{ $item->quantity_rejected }} {{ $item->product->unit }}
                                    </span>
                                @else
                                    <span class="text-slate-500">0</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono text-cyan-400 font-bold">
                                {{ $item->batch_number ?: '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-amber-400">
                                {{ $item->expiry_date ? $item->expiry_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5 text-slate-200">
                                    <i class="fa-solid fa-map-pin text-emerald-400 text-[11px]"></i>
                                    <span>{{ $item->shelf_location ?: 'Rak Default' }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 italic">
                                {{ $item->rejection_reason ?: '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Metadata Card Footer -->
    <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 shadow-lg grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-slate-400">
        <div>
            <span class="text-slate-500 block mb-0.5">Diterima & Diverifikasi Oleh:</span>
            <strong class="text-white text-sm">{{ $goodsReceipt->receiver?->name ?? 'Staff Gudang' }}</strong>
            <p class="text-[11px] text-emerald-400 mt-0.5">Tercatat ke Kartu Mutasi Stok (Inbound)</p>
        </div>
        <div>
            <span class="text-slate-500 block mb-0.5">Pemasok / Supplier:</span>
            <strong class="text-white text-sm">{{ $goodsReceipt->purchaseOrder->supplier->name }}</strong>
            <p class="text-[11px] text-slate-400 mt-0.5">PIC: {{ $goodsReceipt->purchaseOrder->supplier->contact_person ?? '-' }}</p>
        </div>
        <div>
            <span class="text-slate-500 block mb-0.5">Catatan Khusus Kedatangan:</span>
            <p class="text-slate-300 italic">{{ $goodsReceipt->notes ?: 'Tidak ada catatan tambahan.' }}</p>
        </div>
    </div>

</div>
@endsection
