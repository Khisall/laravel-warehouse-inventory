@extends('layouts.app')

@section('title', 'Orders Management')

@section('content')
<div x-data="{
    showNewOrderModal: false,
    showStatusModal: false,
    currentOrder: {},
    openStatus(o) {
        this.currentOrder = JSON.parse(JSON.stringify(o));
        this.showStatusModal = true;
    }
}" class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">Orders Management</h1>
            <p class="text-xs text-slate-400 mt-1">Daftar pesanan keluar dari gudang (Otomatis memotong stok barang secara akurat)</p>
        </div>

        <button @click="showNewOrderModal = true"
                class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-sm flex items-center gap-2 shadow-lg shadow-emerald-500/10">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Buat Order Baru</span>
        </button>
    </div>

    <!-- Orders Filter Tabs -->
    <div class="flex flex-wrap items-center gap-2 text-xs">
        <a href="{{ route('orders.index') }}" class="px-3 py-1.5 rounded-lg border {{ !request('status') ? 'bg-emerald-500/20 border-emerald-500 text-emerald-400 font-bold' : 'bg-[#13202b] border-[#1f3344] text-slate-400 hover:text-white' }}">
            Semua Pesanan
        </a>
        <a href="{{ route('orders.index', ['status' => 'processing']) }}" class="px-3 py-1.5 rounded-lg border {{ request('status') == 'processing' ? 'bg-cyan-500/20 border-cyan-500 text-cyan-400 font-bold' : 'bg-[#13202b] border-[#1f3344] text-slate-400 hover:text-white' }}">
            Diproses (Packing)
        </a>
        <a href="{{ route('orders.index', ['status' => 'shipped']) }}" class="px-3 py-1.5 rounded-lg border {{ request('status') == 'shipped' ? 'bg-amber-500/20 border-amber-500 text-amber-400 font-bold' : 'bg-[#13202b] border-[#1f3344] text-slate-400 hover:text-white' }}">
            Dalam Pengiriman
        </a>
        <a href="{{ route('orders.index', ['status' => 'completed']) }}" class="px-3 py-1.5 rounded-lg border {{ request('status') == 'completed' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-400 font-bold' : 'bg-[#13202b] border-[#1f3344] text-slate-400 hover:text-white' }}">
            Selesai / Terkirim
        </a>
    </div>

    <!-- Orders Table -->
    <div class="table-container overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-[#1f3344] bg-[#14232e] text-slate-400 font-semibold text-xs tracking-wider">
                        <th class="py-3 px-4">No. Order</th>
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Channel / Toko</th>
                        <th class="py-3 px-4">Item Pesanan</th>
                        <th class="py-3 px-4">Total Nilai</th>
                        <th class="py-3 px-4 text-center">Status Pesanan</th>
                        <th class="py-3 px-4 text-center">Status Pengiriman</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1f3344]/80 text-slate-200">
                    @forelse($orders as $o)
                        <tr class="hover:bg-[#182835]/70 transition">
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-white font-mono">{{ $o->order_number }}</div>
                                <div class="text-[11px] text-slate-500">{{ $o->created_at->format('d M Y, H:i') }}</div>
                            </td>

                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-200">{{ $o->customer_name }}</div>
                                <div class="text-xs text-slate-400">{{ $o->customer_phone ?? $o->customer_email ?? '-' }}</div>
                            </td>

                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded bg-[#162532] text-xs font-semibold text-slate-300 border border-[#223849]">
                                    {{ $o->marketplace }}
                                </span>
                            </td>

                            <td class="py-3 px-4 text-xs">
                                @foreach($o->items as $item)
                                    <div class="leading-relaxed">
                                        <span class="text-emerald-400 font-bold">{{ $item->quantity }}x</span>
                                        <span class="text-slate-200">{{ $item->product_title }}</span>
                                        <span class="text-[11px] text-slate-500 font-mono">({{ $item->sku }})</span>
                                    </div>
                                @endforeach
                            </td>

                            <td class="py-3 px-4 whitespace-nowrap font-bold text-white">
                                ${{ number_format($o->total_amount, 2) }}
                            </td>

                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold
                                    {{ $o->status === 'completed' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : ($o->status === 'processing' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/30' : 'bg-slate-800 text-slate-300') }}">
                                    {{ ucfirst($o->status) }}
                                </span>
                            </td>

                            <td class="py-3 px-4 text-center whitespace-nowrap text-xs">
                                <span class="font-medium text-slate-300">{{ ucfirst(str_replace('_', ' ', $o->shipping_status)) }}</span>
                                @if($o->tracking_number)
                                    <div class="text-[11px] text-slate-500 font-mono">{{ $o->courier }}: {{ $o->tracking_number }}</div>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <a href="{{ route('orders.delivery-order', $o) }}"
                                       target="_blank"
                                       title="Cetak Surat Jalan & Packing Slip"
                                       class="px-2.5 py-1 rounded-lg bg-[#182835] hover:bg-emerald-500/20 text-emerald-400 text-xs font-bold border border-[#233b4e] flex items-center gap-1.5 transition">
                                        <i class="fa-solid fa-file-invoice"></i>
                                        <span>Surat Jalan</span>
                                    </a>
                                    <button type="button"
                                            @click="openStatus({{ $o->toJson() }})"
                                            class="px-2.5 py-1 rounded-lg bg-[#182835] hover:bg-cyan-500/20 text-cyan-400 text-xs font-bold border border-[#233b4e] transition">
                                        Update Status
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500">
                                Belum ada pesanan terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-[#1f3344] bg-[#101b24]">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Buat Order Baru -->
    <div x-show="showNewOrderModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="showNewOrderModal = false" class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-lg w-full p-6 text-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f3344]">
                <h3 class="font-bold text-white text-base">Buat Order Pengeluaran Gudang</h3>
                <button @click="showNewOrderModal = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('orders.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Customer</label>
                    <input type="text" name="customer_name" required placeholder="Contoh: Alexander Pratama" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">No. Telp Customer</label>
                        <input type="text" name="customer_phone" placeholder="0812xxxx" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Channel Penjualan</label>
                        <select name="marketplace" required class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                            <option value="Shopee Official">Shopee Official</option>
                            <option value="Tokopedia Mall">Tokopedia Mall</option>
                            <option value="TikTok Shop">TikTok Shop</option>
                            <option value="Direct Order">Direct Order / Gudang</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Pilih Produk</label>
                    <select name="product_id" required class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->sku }} - {{ $p->title }} (Stok Tersedia: {{ $p->quantity }}) - ${{ number_format($p->price, 2) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Jumlah (Qty)</label>
                        <input type="number" name="quantity" min="1" required value="1" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kurir Pengiriman</label>
                        <input type="text" name="courier" value="JNE Regular" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Alamat Pengiriman</label>
                    <textarea name="shipping_address" rows="2" placeholder="Alamat lengkap penerima..." class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-[#1f3344]">
                    <button type="button" @click="showNewOrderModal = false" class="px-3 py-1.5 rounded-lg bg-[#162532] text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-emerald-500 text-slate-950 font-bold text-xs">Proses Order & Potong Stok</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Update Status Order -->
    <div x-show="showStatusModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="showStatusModal = false" class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-sm w-full p-6 text-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f3344]">
                <h3 class="font-bold text-white text-base">Update Status Order</h3>
                <button @click="showStatusModal = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form :action="'{{ url('orders') }}/' + currentOrder.id + '/status'" method="POST" class="space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Status Pesanan</label>
                    <select name="status" x-model="currentOrder.status" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Status Pengiriman</label>
                    <select name="shipping_status" x-model="currentOrder.shipping_status" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                        <option value="packing">Sedang Dipacking</option>
                        <option value="in_transit">Sedang Dalam Pengiriman</option>
                        <option value="delivered">Telah Diterima (Delivered)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nomor Resi / AWB</label>
                    <input type="text" name="tracking_number" x-model="currentOrder.tracking_number" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-[#1f3344]">
                    <button type="button" @click="showStatusModal = false" class="px-3 py-1.5 rounded-lg bg-[#162532] text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-emerald-500 text-slate-950 font-bold text-xs">Simpan Status</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
