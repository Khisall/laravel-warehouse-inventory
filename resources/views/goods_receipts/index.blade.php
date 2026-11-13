@extends('layouts.app')

@section('title', 'Penerimaan Barang (Goods Receipts) - INVENTORY')

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-lg">
                <i class="fa-solid fa-dolly"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    Penerimaan Barang Masuk (Goods Receipts)
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">Inbound GRN</span>
                </h1>
                <p class="text-xs text-slate-400">Riwayat barang tiba di dermaga gudang, hasil uji QC, dan penambahan stok kartu persediaan.</p>
            </div>
        </div>

        <a href="{{ route('purchase-orders.index', ['status' => 'ordered']) }}"
           class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/20 transition hover:scale-[1.02]">
            <i class="fa-solid fa-truck-ramp-box"></i>
            <span>Terima Barang dari PO Terbit</span>
        </a>
    </div>

    <!-- Search Bar -->
    <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-3.5 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="text-xs text-slate-400">
            Total Dokumen Penerimaan: <strong class="text-white font-mono">{{ $receipts->total() }}</strong> GRN
        </div>

        <form method="GET" action="{{ route('goods-receipts.index') }}" class="flex items-center gap-2">
            <div class="relative w-full md:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari No. GRN / No. SJ Vendor..."
                       class="w-full pl-8 pr-3 py-1.5 bg-[#0e1821] border border-[#1f3344] rounded-lg text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
            </div>
            @if(request()->filled('search'))
                <a href="{{ route('goods-receipts.index') }}" class="px-2.5 py-1.5 bg-slate-800 text-slate-400 rounded-lg text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- Receipts Table -->
    <div class="table-container overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#1f3344] bg-[#0f1b24]/80 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">No. GRN (Bukti Masuk)</th>
                        <th class="py-3 px-4">Referensi No. PO</th>
                        <th class="py-3 px-4">Surat Jalan Vendor</th>
                        <th class="py-3 px-4">Pemasok / Supplier</th>
                        <th class="py-3 px-4">Gudang Penerima</th>
                        <th class="py-3 px-4">Waktu Diterima</th>
                        <th class="py-3 px-4 text-center">Item Masuk</th>
                        <th class="py-3 px-4">Penerima</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1f3344]/50">
                    @forelse($receipts as $receipt)
                        <tr class="hover:bg-[#162532]/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <a href="{{ route('goods-receipts.show', $receipt->id) }}" class="font-mono font-bold text-emerald-400 hover:underline">
                                    {{ $receipt->receipt_number }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($receipt->purchaseOrder)
                                    <a href="{{ route('purchase-orders.show', $receipt->purchase_order_id) }}" class="font-mono text-cyan-400 hover:underline">
                                        {{ $receipt->purchaseOrder->po_number }}
                                    </a>
                                @else
                                    <span class="text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-white">{{ $receipt->vendor_do_number ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-200">{{ $receipt->purchaseOrder?->supplier?->name ?? 'Vendor' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-300">
                                {{ $receipt->warehouse?->name ?? 'Gudang Pusat' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-slate-200">{{ $receipt->received_date->format('d M Y') }}</div>
                                <div class="text-[10px] text-slate-500">{{ $receipt->received_date->format('H:i') }} WIB</div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-mono font-bold text-[11px] border border-emerald-500/20">
                                    {{ $receipt->items_count }} SKU
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-300">
                                {{ $receipt->receiver?->name ?? 'Operator' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('goods-receipts.show', $receipt->id) }}"
                                       class="p-1.5 rounded-lg bg-[#182633] hover:bg-[#203344] text-slate-300 hover:text-white transition"
                                       title="Lihat Detail GRN">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('goods-receipts.print', $receipt->id) }}"
                                       target="_blank"
                                       class="p-1.5 rounded-lg bg-[#182633] text-emerald-400 hover:bg-[#203344] transition"
                                       title="Cetak Surat Penerimaan Barang">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-inbox text-3xl text-slate-600 mb-2"></i>
                                <p class="text-sm">Belum ada riwayat penerimaan barang masuk.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($receipts->hasPages())
            <div class="p-4 border-t border-[#1f3344]">
                {{ $receipts->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
