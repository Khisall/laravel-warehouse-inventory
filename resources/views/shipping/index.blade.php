@extends('layouts.app')

@section('title', 'Shipping & Delivery')

@section('content')
<div x-data="{
    showShipModal: false,
    shipOrder: {},
    openShip(s) {
        this.shipOrder = JSON.parse(JSON.stringify(s));
        this.showShipModal = true;
    }
}" class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">Shipping & Dispatch</h1>
            <p class="text-xs text-slate-400 mt-1">Pantau proses packing, penjemputan kurir & nomor resi paket barang dari gudang</p>
        </div>
    </div>

    <!-- Shipping Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-box"></i>
            </div>
            <div>
                <div class="text-xs text-slate-400">Siap Packing</div>
                <div class="text-xl font-bold text-white">{{ $shipments->where('shipping_status', 'packing')->count() }} Paket</div>
            </div>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-truck-fast"></i>
            </div>
            <div>
                <div class="text-xs text-slate-400">Dalam Pengiriman (In Transit)</div>
                <div class="text-xl font-bold text-amber-400">{{ $shipments->where('shipping_status', 'in_transit')->count() }} Paket</div>
            </div>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="text-xs text-slate-400">Telah Diterima (Delivered)</div>
                <div class="text-xl font-bold text-emerald-400">{{ $shipments->where('shipping_status', 'delivered')->count() }} Paket</div>
            </div>
        </div>
    </div>

    <!-- Shipments Table -->
    <div class="table-container overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-[#1f3344] bg-[#14232e] text-slate-400 font-semibold text-xs tracking-wider">
                        <th class="py-3 px-4">No. Order & Tanggal</th>
                        <th class="py-3 px-4">Penerima & Alamat</th>
                        <th class="py-3 px-4">Ekspedisi / Kurir</th>
                        <th class="py-3 px-4">Nomor Resi (AWB)</th>
                        <th class="py-3 px-4 text-center">Status Pengiriman</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1f3344]/80 text-slate-200">
                    @forelse($shipments as $s)
                        <tr class="hover:bg-[#182835]/70 transition">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-bold text-white font-mono">{{ $s->order_number }}</div>
                                <div class="text-[11px] text-slate-500">{{ $s->created_at->format('d M Y, H:i') }}</div>
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-200">{{ $s->customer_name }}</div>
                                <div class="text-xs text-slate-400 truncate max-w-sm">{{ $s->shipping_address ?? 'Alamat pickup gudang' }}</div>
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap font-medium text-slate-300">
                                <i class="fa-solid fa-truck text-emerald-400 mr-1.5"></i> {{ $s->courier ?? 'Standard' }}
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap font-mono text-cyan-400 font-bold text-xs">
                                {{ $s->tracking_number ?? 'Belum terbit' }}
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($s->shipping_status === 'delivered')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                        <i class="fa-solid fa-check mr-1"></i> Delivered
                                    </span>
                                @elseif($s->shipping_status === 'in_transit')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                        <i class="fa-solid fa-road mr-1"></i> In Transit
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/30">
                                        <i class="fa-solid fa-box mr-1"></i> Packing
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <button type="button"
                                        @click="openShip({{ $s->toJson() }})"
                                        class="px-2.5 py-1 rounded-lg bg-[#182835] hover:bg-emerald-500/20 text-emerald-400 text-xs font-bold border border-[#233b4e] transition">
                                    Update Resi
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                Belum ada riwayat pengiriman.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shipments->hasPages())
            <div class="px-6 py-4 border-t border-[#1f3344] bg-[#101b24]">
                {{ $shipments->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Update Shipping -->
    <div x-show="showShipModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="showShipModal = false" class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-sm w-full p-6 text-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f3344]">
                <h3 class="font-bold text-white text-base">Update Status Pengiriman</h3>
                <button @click="showShipModal = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form :action="'{{ url('shipping') }}/' + shipOrder.id" method="POST" class="space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Status Pengiriman</label>
                    <select name="shipping_status" x-model="shipOrder.shipping_status" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                        <option value="packing">Packing Selesai (Menunggu Pick Up)</option>
                        <option value="in_transit">Dalam Pengiriman (In Transit)</option>
                        <option value="delivered">Telah Diterima Penerima (Delivered)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Kurir</label>
                    <input type="text" name="courier" x-model="shipOrder.courier" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nomor Resi (AWB)</label>
                    <input type="text" name="tracking_number" x-model="shipOrder.tracking_number" placeholder="JNE99281928" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500 font-mono">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-[#1f3344]">
                    <button type="button" @click="showShipModal = false" class="px-3 py-1.5 rounded-lg bg-[#162532] text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-emerald-500 text-slate-950 font-bold text-xs">Simpan Pengiriman</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
