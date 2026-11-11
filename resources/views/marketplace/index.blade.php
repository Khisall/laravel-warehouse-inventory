@extends('layouts.app')

@section('title', 'Marketplace Integrations & Listings')

@section('content')
<div x-data="{
    showAddListing: false
}" class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">Marketplace Integrations</h1>
            <p class="text-xs text-slate-400 mt-1">Sinkronisasi stok gudang real-time ke toko Shopee, Tokopedia, TikTok Shop & Lazada</p>
        </div>

        <div class="flex items-center gap-2.5">
            <form action="{{ route('marketplace.sync-all') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#14222e] hover:bg-[#192b3b] border border-[#1f3344] text-slate-200 text-sm font-semibold flex items-center gap-2 transition">
                    <i class="fa-solid fa-rotate text-emerald-400"></i>
                    <span>Sync Semua Channel</span>
                </button>
            </form>

            <button @click="showAddListing = true" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-slate-950 font-bold text-sm flex items-center gap-2 shadow-lg shadow-orange-500/10">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Hubungkan Produk Baru</span>
            </button>
        </div>
    </div>

    <!-- Active Channel Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center text-orange-400 text-lg">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
                <div>
                    <h4 class="font-bold text-white text-sm">Shopee Official</h4>
                    <span class="text-[11px] text-emerald-400 font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Connected
                    </span>
                </div>
            </div>
            <span class="text-xs px-2 py-1 rounded bg-[#162532] text-slate-300 font-bold border border-[#22394c]">ACCO</span>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-lg">
                    <i class="fa-solid fa-shop"></i>
                </div>
                <div>
                    <h4 class="font-bold text-white text-sm">Tokopedia Mall</h4>
                    <span class="text-[11px] text-emerald-400 font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Connected
                    </span>
                </div>
            </div>
            <span class="text-xs px-2 py-1 rounded bg-[#162532] text-slate-300 font-bold border border-[#22394c]">11SE</span>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-cyan-500/10 border border-cyan-500/30 flex items-center justify-center text-cyan-400 text-lg">
                    <i class="fa-brands fa-tiktok"></i>
                </div>
                <div>
                    <h4 class="font-bold text-white text-sm">TikTok Shop</h4>
                    <span class="text-[11px] text-emerald-400 font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Connected
                    </span>
                </div>
            </div>
            <span class="text-xs px-2 py-1 rounded bg-[#162532] text-slate-300 font-bold border border-[#22394c]">CACO</span>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center text-indigo-400 text-lg">
                    <i class="fa-solid fa-store"></i>
                </div>
                <div>
                    <h4 class="font-bold text-white text-sm">Lazada Flagship</h4>
                    <span class="text-[11px] text-slate-400 font-semibold">Ready to link</span>
                </div>
            </div>
            <span class="text-xs px-2 py-1 rounded bg-[#162532] text-slate-400 font-bold border border-[#22394c]">INAC</span>
        </div>
    </div>

    <!-- Listings Table -->
    <div class="table-container overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-[#1f3344] bg-[#14232e] text-slate-400 font-semibold text-xs tracking-wider">
                        <th class="py-3 px-4">Marketplace Channel</th>
                        <th class="py-3 px-4">SKU Gudang</th>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4">Channel SKU</th>
                        <th class="py-3 px-4">Channel Code</th>
                        <th class="py-3 px-4">Harga Channel</th>
                        <th class="py-3 px-4 text-center">Stok Tersinkron</th>
                        <th class="py-3 px-4 text-center">Status Sync</th>
                        <th class="py-3 px-4">Terakhir Sync</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1f3344]/80 text-slate-200">
                    @forelse($listings as $item)
                        <tr class="hover:bg-[#182835]/70 transition">
                            <td class="py-3.5 px-4 whitespace-nowrap font-bold text-white">
                                <i class="fa-solid fa-globe text-emerald-400 mr-1.5"></i> {{ $item->channel }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap font-mono text-cyan-400 font-semibold">
                                {{ $item->product?->sku ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-200">{{ $item->product?->title ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-400 font-mono">
                                {{ $item->channel_sku }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="text-xs font-bold text-slate-300 bg-[#162532] px-2 py-0.5 rounded border border-[#223849]">
                                    {{ $item->channel_code }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap font-semibold text-slate-200">
                                ${{ number_format($item->channel_price, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 font-bold border border-emerald-500/20 text-xs">
                                    {{ $item->synced_stock }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-400">
                                    <i class="fa-solid fa-circle-check"></i> Synced
                                </span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-400">
                                {{ $item->last_synced_at ? $item->last_synced_at->diffForHumans() : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-500">
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

    <!-- Modal Hubungkan -->
    <div x-show="showAddListing" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="showAddListing = false" class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-md w-full p-6 text-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f3344]">
                <h3 class="font-bold text-white text-base">Hubungkan Produk ke Marketplace</h3>
                <button @click="showAddListing = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('marketplace.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Pilih Produk</label>
                    <select name="product_id" required class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->sku }} - {{ $p->title }} (Stok: {{ $p->quantity }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Target Marketplace</label>
                    <select name="channel" required class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                        <option value="Shopee Official Store">Shopee Official Store</option>
                        <option value="Tokopedia Mall">Tokopedia Mall</option>
                        <option value="TikTok Shop">TikTok Shop</option>
                        <option value="Lazada Flagship">Lazada Flagship</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Channel Code</label>
                        <input type="text" name="channel_code" required value="ACCO" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500 uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Harga Jual ($)</label>
                        <input type="number" step="0.01" name="channel_price" required value="223.20" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-[#1f3344]">
                    <button type="button" @click="showAddListing = false" class="px-3 py-1.5 rounded-lg bg-[#162532] text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-orange-500 text-slate-950 font-bold text-xs">Publish</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
