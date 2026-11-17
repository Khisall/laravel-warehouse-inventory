@extends('layouts.app')

@section('title', 'Marketplace Integrations & Multi-Channel Sync')

@section('content')
<div x-data="{
    showAddListing: false,
    showSimulateOrder: false,
    simChannel: 'Shopee',
    simProductId: '{{ $allProducts->first()?->id }}',
    simQuantity: 2,
    simCustomer: 'Budi Kurniawan'
}" class="space-y-6">

    <!-- Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-orange-500/10 border border-orange-500/20 flex items-center justify-center text-orange-400">
                    <i class="fa-solid fa-store text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl md:text-2xl font-black text-white tracking-tight">Marketplace Multi-Channel Sync</h1>
                    <p class="text-xs text-slate-400 mt-0.5">Sinkronisasi stok real-time, perlindungan Safety Buffer &amp; simulator pesanan Shopee, Tokopedia, TikTok Shop</p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Simulate Marketplace Order Button -->
            <button @click="showSimulateOrder = true"
                    class="px-4 py-2 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 border border-emerald-500/30 text-emerald-300 text-xs font-bold flex items-center gap-2 transition shadow-sm">
                <i class="fa-solid fa-cart-shopping text-emerald-400 animate-bounce"></i>
                <span>Simulasi Order Masuk</span>
            </button>

            <!-- Sync All -->
            <form action="{{ route('marketplace.sync-all') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#14222e] hover:bg-[#192b3b] border border-[#1f3344] text-slate-200 text-xs font-semibold flex items-center gap-2 transition">
                    <i class="fa-solid fa-rotate text-emerald-400"></i>
                    <span>Sync Semua Channel</span>
                </button>
            </form>

            <!-- Connect Product -->
            <button @click="showAddListing = true" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-lg shadow-orange-500/15 transition">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Hubungkan Produk</span>
            </button>
        </div>
    </div>

    <!-- Alert Flash -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Channel Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Shopee -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center text-orange-400 text-lg">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
                <div>
                    <h4 class="font-bold text-white text-sm">Shopee Official</h4>
                    <span class="text-[11px] text-emerald-400 font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> {{ $stats['shopee_count'] }} Terhubung
                    </span>
                </div>
            </div>
            <span class="text-xs px-2 py-0.5 rounded bg-[#162532] text-orange-400 font-mono font-bold border border-orange-500/20">SHP</span>
        </div>

        <!-- Tokopedia -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-lg">
                    <i class="fa-solid fa-store"></i>
                </div>
                <div>
                    <h4 class="font-bold text-white text-sm">Tokopedia Mall</h4>
                    <span class="text-[11px] text-emerald-400 font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> {{ $stats['tokopedia_count'] }} Terhubung
                    </span>
                </div>
            </div>
            <span class="text-xs px-2 py-0.5 rounded bg-[#162532] text-emerald-400 font-mono font-bold border border-emerald-500/20">TKP</span>
        </div>

        <!-- TikTok Shop -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400 text-lg">
                    <i class="fa-brands fa-tiktok"></i>
                </div>
                <div>
                    <h4 class="font-bold text-white text-sm">TikTok Shop</h4>
                    <span class="text-[11px] text-cyan-400 font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span> {{ $stats['tiktok_count'] }} Terhubung
                    </span>
                </div>
            </div>
            <span class="text-xs px-2 py-0.5 rounded bg-[#162532] text-cyan-400 font-mono font-bold border border-cyan-500/20">TTK</span>
        </div>

        <!-- Lazada -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/30 flex items-center justify-center text-blue-400 text-lg">
                    <i class="fa-solid fa-heart"></i>
                </div>
                <div>
                    <h4 class="font-bold text-white text-sm">Lazada Flagship</h4>
                    <span class="text-[11px] text-blue-400 font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span> Siap Sinkron
                    </span>
                </div>
            </div>
            <span class="text-xs px-2 py-0.5 rounded bg-[#162532] text-blue-400 font-mono font-bold border border-blue-500/20">LZD</span>
        </div>
    </div>

    <!-- Safety Buffer Info Banner -->
    <div class="bg-[#13202b] border border-slate-800 rounded-2xl p-4 flex items-start gap-3 text-xs text-slate-300">
        <i class="fa-solid fa-shield-halved text-emerald-400 text-lg mt-0.5"></i>
        <div>
            <strong class="text-slate-100">Mekanisme Perlindungan Safety Stock Buffer:</strong>
            <p class="text-slate-400 mt-0.5">
                Stok yang ditayangkan ke pembeli di marketplace dihitung otomatis dari <code>Stok Gudang - Safety Buffer</code>. Hal ini menjamin gudang tidak pernah mengalami <em>overselling</em> (stok habis di toko fisik/cabang lain namun masih terbeli online).
            </p>
        </div>
    </div>

    <!-- Listings Table Card -->
    <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-[#1f3344] bg-[#14232e] text-slate-400 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Channel &amp; Toko</th>
                        <th class="py-3 px-4">SKU Gudang &amp; Produk</th>
                        <th class="py-3 px-4">Harga Jual</th>
                        <th class="py-3 px-4 text-center">Stok Fisik Gudang</th>
                        <th class="py-3 px-4 text-center">Safety Buffer</th>
                        <th class="py-3 px-4 text-center">Stok Tayang Marketplace</th>
                        <th class="py-3 px-4 text-center">Status Sync</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1f3344]/80 text-slate-200">
                    @forelse($listings as $item)
                        <tr class="hover:bg-[#182835]/70 transition">
                            <!-- Channel -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $item->channel_color_class }}">
                                    <i class="fa-solid {{ $item->channel_icon }}"></i>
                                    <span>{{ $item->channel }}</span>
                                </span>
                                <div class="text-[10px] text-slate-400 mt-1 font-semibold">{{ $item->store_name }}</div>
                            </td>

                            <!-- SKU & Title -->
                            <td class="py-3.5 px-4">
                                <div class="font-mono text-cyan-400 font-bold text-xs">{{ $item->product?->sku ?? '-' }}</div>
                                <div class="text-slate-100 font-medium text-xs mt-0.5">{{ Str::limit($item->product?->title, 32) }}</div>
                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">MKT SKU: {{ $item->channel_sku }}</div>
                            </td>

                            <!-- Price -->
                            <td class="py-3.5 px-4 whitespace-nowrap font-mono font-bold text-slate-200">
                                Rp {{ number_format($item->channel_price, 0, ',', '.') }}
                            </td>

                            <!-- Physical Stock -->
                            <td class="py-3.5 px-4 text-center font-mono font-semibold text-slate-300">
                                {{ $item->product?->quantity ?? 0 }} Unit
                            </td>

                            <!-- Safety Buffer (Editable Form) -->
                            <td class="py-3.5 px-4 text-center">
                                <form method="POST" action="{{ route('marketplace.update-buffer', $item->id) }}" class="inline-flex items-center gap-1">
                                    @csrf
                                    <input type="number"
                                           name="safety_stock_buffer"
                                           value="{{ $item->safety_stock_buffer }}"
                                           min="0"
                                           max="99"
                                           class="w-14 px-1.5 py-1 bg-[#0b141b] border border-slate-700 rounded text-center text-xs font-mono font-bold text-amber-400 focus:outline-none focus:border-amber-500">
                                    <button type="submit"
                                            class="p-1 text-slate-400 hover:text-emerald-400 transition"
                                            title="Simpan Perubahan Buffer">
                                        <i class="fa-solid fa-check text-xs"></i>
                                    </button>
                                </form>
                            </td>

                            <!-- Synced Sellable Stock -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full bg-emerald-500/15 text-emerald-300 font-mono font-bold border border-emerald-500/30 text-xs">
                                    {{ $item->available_stock }} Unit
                                </span>
                            </td>

                            <!-- Sync Status -->
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-400">
                                    <i class="fa-solid fa-circle-check"></i> Synced
                                </span>
                                <div class="text-[10px] text-slate-500 mt-0.5">
                                    {{ $item->last_synced_at ? $item->last_synced_at->diffForHumans() : '-' }}
                                </div>
                            </td>

                            <!-- Quick Action: Direct Test Simulation -->
                            <td class="py-3.5 px-4 text-right">
                                <button type="button"
                                        @click="
                                            simChannel = '{{ $item->channel }}';
                                            simProductId = '{{ $item->product_id }}';
                                            showSimulateOrder = true;
                                        "
                                        class="px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[11px] font-semibold transition"
                                        title="Simulasi Pesanan untuk Produk Ini">
                                    <i class="fa-solid fa-play text-[9px] mr-1"></i> Tes Order
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-500">
                                Belum ada produk yang didaftarkan ke marketplace.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($listings->hasPages())
            <div class="px-6 py-4 border-t border-[#1f3344] bg-[#101b24]">
                {{ $listings->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Hubungkan Produk Baru -->
    <div x-show="showAddListing" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" x-cloak>
        <div @click.away="showAddListing = false" class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-md w-full p-6 text-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f3344]">
                <h3 class="font-bold text-white text-base">Hubungkan Produk ke Marketplace</h3>
                <button @click="showAddListing = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('marketplace.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Pilih Produk</label>
                    <select name="product_id" required class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                        @foreach($allProducts as $p)
                            <option value="{{ $p->id }}">{{ $p->sku }} - {{ $p->title }} (Stok: {{ $p->quantity }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Target Marketplace Channel</label>
                    <select name="channel" required class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                        <option value="Shopee">Shopee Official Store</option>
                        <option value="Tokopedia">Tokopedia Mall</option>
                        <option value="TikTok Shop">TikTok Shop Official</option>
                        <option value="Lazada">Lazada Flagship Store</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Safety Stock Buffer</label>
                        <input type="number" name="safety_stock_buffer" required value="3" min="0" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Harga Jual (Rp)</label>
                        <input type="number" name="channel_price" required value="150000" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-[#1f3344]">
                    <button type="button" @click="showAddListing = false" class="px-4 py-2 rounded-xl bg-[#162532] text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-orange-500 text-slate-950 font-bold text-xs">Publish ke Channel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Simulasi Pesanan Marketplace -->
    <div x-show="showSimulateOrder" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" x-cloak>
        <div @click.away="showSimulateOrder = false" class="bg-[#13202b] border border-slate-700 rounded-2xl max-w-lg w-full p-6 text-slate-200 space-y-4 shadow-2xl">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2.5 text-emerald-400">
                    <i class="fa-solid fa-cart-shopping text-lg"></i>
                    <div>
                        <h3 class="font-bold text-white text-base">Simulator Pesanan Marketplace Masuk</h3>
                        <p class="text-xs text-slate-400">Simulasikan checkout pembeli &amp; periksa sinkronisasi antar channel</p>
                    </div>
                </div>
                <button @click="showSimulateOrder = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form action="{{ route('marketplace.simulate-order') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Marketplace Asal Order <span class="text-rose-400">*</span></label>
                    <select name="channel" x-model="simChannel" required class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                        <option value="Shopee">Shopee Official Store</option>
                        <option value="Tokopedia">Tokopedia Mall</option>
                        <option value="TikTok Shop">TikTok Shop Official</option>
                        <option value="Lazada">Lazada Flagship Store</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Produk yang Dibeli <span class="text-rose-400">*</span></label>
                    <select name="product_id" x-model="simProductId" required class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                        @foreach($allProducts as $p)
                            <option value="{{ $p->id }}">{{ $p->title }} ({{ $p->sku }}) &bull; Sisa Gudang: {{ $p->quantity }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Jumlah Dibeli (Qty) <span class="text-rose-400">*</span></label>
                        <input type="number" name="quantity" x-model.number="simQuantity" min="1" required class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs font-mono font-bold text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Pembeli (Customer)</label>
                        <input type="text" name="customer_name" x-model="simCustomer" class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-[#0b141b] border border-slate-800 text-[11px] text-slate-400 space-y-1">
                    <p class="font-bold text-slate-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-gear text-emerald-400"></i> Alur Kerja Otomatis yang Dijalankan:
                    </p>
                    <ul class="list-disc pl-4 space-y-0.5 text-[10px]">
                        <li>Stok fisik gudang langsung dipotong menggunakan algoritma <strong>FEFO</strong> (batch terdekat kedaluwarsa).</li>
                        <li>Membuat entri pesanan baru di modul Orders.</li>
                        <li>Menghitung ulang stok tayang di seluruh channel marketplace lain secara otomatis.</li>
                    </ul>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
                    <button type="button" @click="showSimulateOrder = false" class="px-4 py-2 rounded-xl bg-slate-800 text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs flex items-center gap-2">
                        <i class="fa-solid fa-play"></i>
                        <span>Eksekusi Simulasi Pesanan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
