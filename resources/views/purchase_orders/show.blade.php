@extends('layouts.app')

@section('title', 'Detail Purchase Order - ' . $purchaseOrder->po_number)

@section('content')
<div class="space-y-6">

    <!-- Header Navigation & Action Bar -->
    <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 shadow-lg">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Left Info -->
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <a href="{{ route('purchase-orders.index') }}" class="text-slate-400 hover:text-emerald-400 text-xs flex items-center gap-1 transition">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Daftar PO</span>
                    </a>
                    <span class="text-slate-600">/</span>
                    <span class="font-mono text-xs text-emerald-400 font-bold">{{ $purchaseOrder->po_number }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $purchaseOrder->status_badge }}">
                        {{ $purchaseOrder->status_label }}
                    </span>
                </div>

                <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    <span>Pesanan Pembelian: {{ $purchaseOrder->supplier->name }}</span>
                </h1>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400 mt-2">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-calendar text-slate-500"></i>
                        <span>Tanggal Pesan: {{ $purchaseOrder->order_date->format('d M Y') }}</span>
                    </span>
                    <span class="text-slate-600">•</span>
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-clock text-slate-500"></i>
                        <span>Estimasi Tiba: {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('d M Y') : '-' }}</span>
                    </span>
                    <span class="text-slate-600">•</span>
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-warehouse text-emerald-400"></i>
                        <span>Tujuan: {{ $purchaseOrder->warehouse ? $purchaseOrder->warehouse->name : 'Gudang Pusat' }}</span>
                    </span>
                </div>
            </div>

            <!-- Right Actions -->
            <div class="flex flex-wrap items-center gap-2.5">
                @if(in_array($purchaseOrder->status, ['ordered', 'partial']))
                    <a href="{{ route('goods-receipts.create', $purchaseOrder->id) }}"
                       class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/25 transition hover:scale-[1.02]">
                        <i class="fa-solid fa-dolly"></i>
                        <span>Terima Barang / Inbound Receiving</span>
                    </a>
                @endif

                <a href="{{ route('purchase-orders.print', $purchaseOrder->id) }}"
                   target="_blank"
                   class="px-3.5 py-2.5 rounded-xl bg-[#182633] hover:bg-[#203344] text-slate-200 font-semibold text-xs flex items-center gap-2 border border-[#1f3344] transition">
                    <i class="fa-solid fa-print text-emerald-400"></i>
                    <span>Cetak PO</span>
                </a>

                @if(!in_array($purchaseOrder->status, ['received', 'cancelled']) && $totalReceived === 0)
                    <form action="{{ route('purchase-orders.cancel', $purchaseOrder->id) }}" method="POST"
                          onsubmit="return confirm('Apakah Anda yakin ingin membatalkan Purchase Order ini?');">
                        @csrf
                        <button type="submit"
                                class="p-2.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 text-xs transition"
                                title="Batalkan PO">
                            <i class="fa-solid fa-ban"></i>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Lifecycle Stepper -->
    <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4">
        <div class="grid grid-cols-4 gap-2 text-center text-xs">
            <!-- Step 1: Draft -->
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs mb-1.5 bg-emerald-500/20 text-emerald-400 border border-emerald-500/40">
                    <i class="fa-solid fa-check"></i>
                </div>
                <span class="font-bold text-white">1. Dibuat</span>
                <span class="text-[10px] text-slate-500">{{ $purchaseOrder->created_at->format('d/m/Y') }}</span>
            </div>

            <!-- Step 2: Ordered -->
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs mb-1.5 {{ in_array($purchaseOrder->status, ['ordered', 'partial', 'received']) ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' : 'bg-slate-800 text-slate-500' }}">
                    <i class="fa-solid fa-paper-plane"></i>
                </div>
                <span class="font-bold text-white">2. PO Terbit</span>
                <span class="text-[10px] text-slate-500">Kirim ke Vendor</span>
            </div>

            <!-- Step 3: Partial / Receiving -->
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs mb-1.5 {{ in_array($purchaseOrder->status, ['partial', 'received']) ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' : ($purchaseOrder->status === 'ordered' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/40 animate-pulse' : 'bg-slate-800 text-slate-500') }}">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                </div>
                <span class="font-bold text-white">3. Masuk Gudang</span>
                <span class="text-[10px] text-slate-500">{{ $totalReceived }}/{{ $totalOrdered }} unit</span>
            </div>

            <!-- Step 4: Completed -->
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs mb-1.5 {{ $purchaseOrder->status === 'received' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40' : 'bg-slate-800 text-slate-500' }}">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <span class="font-bold text-white">4. Selesai</span>
                <span class="text-[10px] text-slate-500">Stok Lengkap</span>
            </div>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left 2 Cols: Items & Receiving History -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Items Table -->
            <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 shadow-lg space-y-4">
                <div class="flex items-center justify-between border-b border-[#1f3344] pb-3">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-boxes-stacked text-emerald-400"></i>
                        <span>Barang yang Dipesan & Status Kedatangan</span>
                    </h3>
                    <div class="text-xs text-slate-400 font-mono">
                        Diterima: <strong class="text-emerald-400">{{ $totalReceived }}</strong> / {{ $totalOrdered }} Unit
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-[#1f3344] text-slate-400 uppercase tracking-wider font-semibold">
                                <th class="py-2.5 px-3">No</th>
                                <th class="py-2.5 px-3">Barang & SKU</th>
                                <th class="py-2.5 px-3 text-center">Dipesan</th>
                                <th class="py-2.5 px-3 text-center">Diterima</th>
                                <th class="py-2.5 px-3 text-center">Sisa</th>
                                @if(auth()->user()?->canViewFinancials())
                                    <th class="py-2.5 px-3 text-right">Harga Pokok</th>
                                    <th class="py-2.5 px-3 text-right">Subtotal</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#1f3344]/50">
                            @foreach($purchaseOrder->items as $idx => $it)
                                <tr class="hover:bg-[#162532]/50">
                                    <td class="py-3 px-3 font-mono text-slate-500">{{ $idx + 1 }}</td>
                                    <td class="py-3 px-3">
                                        <div class="font-mono font-bold text-emerald-400 text-xs">{{ $it->product->sku }}</div>
                                        <div class="font-semibold text-white mt-0.5">{{ $it->product->title }}</div>
                                        @if($it->notes)
                                            <div class="text-[11px] text-slate-400 italic mt-0.5">{{ $it->notes }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-center font-mono font-bold text-white">
                                        {{ $it->quantity_ordered }} {{ $it->product->unit }}
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="font-mono font-bold text-emerald-400">{{ $it->quantity_received }}</span>
                                        <span class="text-[10px] text-slate-500">/ {{ $it->quantity_ordered }}</span>
                                    </td>
                                    <td class="py-3 px-3 text-center font-mono">
                                        @if($it->remaining_quantity === 0)
                                            <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 font-bold text-[10px]">Lengkap</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 font-bold text-[10px]">-{{ $it->remaining_quantity }}</span>
                                        @endif
                                    </td>
                                    @if(auth()->user()?->canViewFinancials())
                                        <td class="py-3 px-3 text-right font-mono text-slate-300">
                                            Rp {{ number_format($it->unit_cost, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-3 text-right font-mono font-bold text-white">
                                            Rp {{ number_format($it->subtotal, 0, ',', '.') }}
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Goods Receipt Notes (GRN History) -->
            <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 shadow-lg space-y-4">
                <div class="flex items-center justify-between border-b border-[#1f3344] pb-3">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-dolly text-emerald-400"></i>
                        <span>Riwayat Penerimaan Fisik Gudang (GRN)</span>
                    </h3>
                    @if(in_array($purchaseOrder->status, ['ordered', 'partial']))
                        <a href="{{ route('goods-receipts.create', $purchaseOrder->id) }}"
                           class="px-3 py-1.5 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition">
                            <i class="fa-solid fa-plus"></i>
                            <span>Catat Penerimaan Baru</span>
                        </a>
                    @endif
                </div>

                @if($purchaseOrder->goodsReceipts->isEmpty())
                    <div class="py-8 text-center text-slate-500 text-xs">
                        <i class="fa-solid fa-dolly text-2xl text-slate-600 mb-2"></i>
                        <p>Belum ada penerimaan barang yang dicatat untuk PO ini.</p>
                        @if(in_array($purchaseOrder->status, ['ordered', 'partial']))
                            <a href="{{ route('goods-receipts.create', $purchaseOrder->id) }}"
                               class="inline-block mt-2 text-emerald-400 font-bold hover:underline">
                                Mulai Terima Barang Sekarang →
                            </a>
                        @endif
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($purchaseOrder->goodsReceipts as $grn)
                            <div class="bg-[#0e1821] border border-[#1f3344] rounded-xl p-3.5 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                                        <i class="fa-solid fa-receipt"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('goods-receipts.show', $grn->id) }}" class="font-mono font-bold text-emerald-400 hover:underline">
                                                {{ $grn->receipt_number }}
                                            </a>
                                            <span class="text-slate-500">•</span>
                                            <span class="text-xs text-white">No. SJ Vendor: <strong>{{ $grn->vendor_do_number }}</strong></span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                            Diterima pada {{ $grn->received_date->format('d M Y, H:i') }} oleh {{ $grn->receiver?->name ?? 'Staff Gudang' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <a href="{{ route('goods-receipts.show', $grn->id) }}"
                                       class="px-3 py-1.5 rounded-lg bg-[#182633] hover:bg-[#203344] text-slate-200 text-xs font-semibold">
                                        Lihat GRN
                                    </a>
                                    <a href="{{ route('goods-receipts.print', $grn->id) }}"
                                       target="_blank"
                                       class="p-1.5 rounded-lg bg-[#182633] text-emerald-400 hover:bg-[#203344] text-xs"
                                       title="Cetak Bukti Penerimaan">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        <!-- Right 1 Col: Supplier, Warehouse & Financial Cards -->
        <div class="space-y-6">

            <!-- Supplier Card -->
            <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 shadow-lg space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2 border-b border-[#1f3344] pb-2.5">
                    <i class="fa-solid fa-building text-emerald-400"></i>
                    <span>Informasi Pemasok</span>
                </h3>

                <div>
                    <span class="font-mono text-xs text-emerald-400 font-bold">{{ $purchaseOrder->supplier->code }}</span>
                    <h4 class="text-base font-bold text-white mt-0.5">{{ $purchaseOrder->supplier->name }}</h4>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between text-slate-300">
                        <span class="text-slate-500">Kontak Person:</span>
                        <span class="font-semibold">{{ $purchaseOrder->supplier->contact_person ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-300">
                        <span class="text-slate-500">Telepon / WA:</span>
                        <span class="font-mono">{{ $purchaseOrder->supplier->phone ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-300">
                        <span class="text-slate-500">Email:</span>
                        <span>{{ $purchaseOrder->supplier->email ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-300">
                        <span class="text-slate-500">Term Pembayaran:</span>
                        <span class="font-mono font-bold text-amber-400">{{ $purchaseOrder->supplier->payment_terms }}</span>
                    </div>
                    @if($purchaseOrder->supplier->address)
                        <div class="pt-2 border-t border-[#1f3344] text-slate-400 text-[11px]">
                            <p class="text-slate-500 mb-0.5">Alamat Kantor/Gudang:</p>
                            <p>{{ $purchaseOrder->supplier->address }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Financial Summary Card (Protected) -->
            @if(auth()->user()?->canViewFinancials())
                <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 shadow-lg space-y-3">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2 border-b border-[#1f3344] pb-2.5">
                        <i class="fa-solid fa-receipt text-emerald-400"></i>
                        <span>Ringkasan Finansial PO</span>
                    </h3>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between text-slate-300">
                            <span class="text-slate-400">Subtotal Pesanan:</span>
                            <span class="font-mono font-bold">Rp {{ number_format($purchaseOrder->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-300">
                            <span class="text-slate-400">PPN (Pajak):</span>
                            <span class="font-mono">Rp {{ number_format($purchaseOrder->tax_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-300">
                            <span class="text-slate-400">Ongkos Kirim:</span>
                            <span class="font-mono">Rp {{ number_format($purchaseOrder->shipping_cost, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-base font-bold text-emerald-400 pt-2 border-t border-[#1f3344]">
                            <span>Total Tagihan:</span>
                            <span class="font-mono">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="pt-2">
                        <span class="text-slate-500 text-[11px] block mb-1">Status Pembayaran:</span>
                        <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider {{ $purchaseOrder->payment_status === 'paid' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400' }}">
                            {{ $purchaseOrder->payment_status }}
                        </span>
                    </div>
                </div>
            @endif

            <!-- Author & Approver Card -->
            <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 shadow-lg text-xs space-y-2 text-slate-400">
                <div class="flex items-center justify-between">
                    <span>Dibuat Oleh:</span>
                    <strong class="text-white">{{ $purchaseOrder->creator?->name ?? 'User' }}</strong>
                </div>
                <div class="flex items-center justify-between">
                    <span>Disetujui Oleh:</span>
                    <strong class="text-emerald-400">{{ $purchaseOrder->approver?->name ?? 'Manager' }}</strong>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
