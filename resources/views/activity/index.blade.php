@extends('layouts.app')

@section('title', 'Activity & Inventory Log')

@section('content')
<div x-data="{
    showNewLogModal: false
}" class="space-y-6">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">Activity & Inventory Log</h1>
            <p class="text-xs text-slate-400 mt-1">Audit trail lengkap pergerakan stok (Barang Masuk, Barang Keluar, Penyesuaian Fisik)</p>
        </div>

        <button @click="showNewLogModal = true"
                class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-sm flex items-center gap-2 shadow-lg shadow-emerald-500/10">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Catat Mutasi Manual</span>
        </button>
    </div>

    <!-- Filter Pills -->
    <div class="flex flex-wrap items-center gap-2 text-xs">
        <a href="{{ route('activity.index') }}" class="px-3 py-1.5 rounded-lg border {{ !request('type') ? 'bg-emerald-500/20 border-emerald-500 text-emerald-400 font-bold' : 'bg-[#13202b] border-[#1f3344] text-slate-400 hover:text-white' }}">
            Semua Mutasi
        </a>
        <a href="{{ route('activity.index', ['type' => 'in']) }}" class="px-3 py-1.5 rounded-lg border {{ request('type') == 'in' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-400 font-bold' : 'bg-[#13202b] border-[#1f3344] text-slate-400 hover:text-white' }}">
            <i class="fa-solid fa-arrow-down text-emerald-400 mr-1"></i> Barang Masuk (In)
        </a>
        <a href="{{ route('activity.index', ['type' => 'out']) }}" class="px-3 py-1.5 rounded-lg border {{ request('type') == 'out' ? 'bg-rose-500/20 border-rose-500 text-rose-400 font-bold' : 'bg-[#13202b] border-[#1f3344] text-slate-400 hover:text-white' }}">
            <i class="fa-solid fa-arrow-up text-rose-400 mr-1"></i> Barang Keluar (Out)
        </a>
        <a href="{{ route('activity.index', ['type' => 'adjustment']) }}" class="px-3 py-1.5 rounded-lg border {{ request('type') == 'adjustment' ? 'bg-cyan-500/20 border-cyan-500 text-cyan-400 font-bold' : 'bg-[#13202b] border-[#1f3344] text-slate-400 hover:text-white' }}">
            <i class="fa-solid fa-sliders text-cyan-400 mr-1"></i> Penyesuaian (Adjustment)
        </a>
    </div>

    <!-- Movement Timeline Table -->
    <div class="table-container overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-[#1f3344] bg-[#14232e] text-slate-400 font-semibold text-xs tracking-wider">
                        <th class="py-3 px-4">Waktu & Tanggal</th>
                        <th class="py-3 px-4">Barang (SKU / Title)</th>
                        <th class="py-3 px-4">Tipe Mutasi</th>
                        <th class="py-3 px-4 text-center">Jumlah</th>
                        <th class="py-3 px-4 text-center">Stok Awal -> Akhir</th>
                        <th class="py-3 px-4">No. Ref / Surat Jalan</th>
                        <th class="py-3 px-4">Keterangan</th>
                        <th class="py-3 px-4">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1f3344]/80 text-slate-200">
                    @forelse($movements as $m)
                        <tr class="hover:bg-[#182835]/70 transition">
                            <td class="py-3 px-4 whitespace-nowrap text-xs text-slate-400 leading-snug">
                                <div class="font-medium text-slate-300">{{ $m->created_at->format('H:i') }} WIB</div>
                                <div class="text-[11px] text-slate-500">{{ $m->created_at->format('d M Y') }}</div>
                            </td>

                            <td class="py-3 px-4">
                                <div class="font-bold text-white">{{ $m->product?->sku ?? 'Deleted Product' }}</div>
                                <div class="text-xs text-cyan-400 truncate max-w-xs">{{ $m->product?->title ?? '-' }}</div>
                            </td>

                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($m->type === 'in')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-emerald-500/10 text-emerald-400 text-xs font-bold border border-emerald-500/30">
                                        <i class="fa-solid fa-arrow-down text-[10px]"></i> Masuk
                                    </span>
                                @elseif($m->type === 'out')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-rose-500/10 text-rose-400 text-xs font-bold border border-rose-500/30">
                                        <i class="fa-solid fa-arrow-up text-[10px]"></i> Keluar
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-cyan-500/10 text-cyan-400 text-xs font-bold border border-cyan-500/30">
                                        <i class="fa-solid fa-sliders text-[10px]"></i> Penyesuaian
                                    </span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-center whitespace-nowrap font-bold text-sm {{ $m->type === 'in' ? 'text-emerald-400' : 'text-rose-400' }}">
                                {{ $m->type === 'in' ? '+' : '-' }}{{ $m->quantity }}
                            </td>

                            <td class="py-3 px-4 text-center whitespace-nowrap text-xs text-slate-400">
                                <span class="text-slate-300 font-semibold">{{ $m->stock_before }}</span>
                                <i class="fa-solid fa-arrow-right text-[10px] mx-1.5 text-slate-500"></i>
                                <span class="font-bold text-white">{{ $m->stock_after }}</span>
                            </td>

                            <td class="py-3 px-4 whitespace-nowrap text-xs font-mono text-slate-300">
                                {{ $m->reference_no ?? '-' }}
                            </td>

                            <td class="py-3 px-4 text-xs text-slate-300 max-w-xs truncate">
                                {{ $m->notes ?? '-' }}
                            </td>

                            <td class="py-3 px-4 whitespace-nowrap text-xs text-slate-400">
                                {{ $m->created_by }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500">
                                Belum ada aktivitas mutasi yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="px-6 py-4 border-t border-[#1f3344] bg-[#101b24]">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Catat Manual -->
    <div x-show="showNewLogModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="showNewLogModal = false" class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-md w-full p-6 text-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f3344]">
                <h3 class="font-bold text-white text-base">Catat Pergerakan Stok Manual</h3>
                <button @click="showNewLogModal = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('activity.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Pilih Barang</label>
                    <select name="product_id" required class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->sku }} - {{ $p->title }} (Stok: {{ $p->quantity }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Jenis Mutasi</label>
                    <select name="type" required class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                        <option value="in">Barang Masuk (+In)</option>
                        <option value="out">Barang Keluar (-Out)</option>
                        <option value="adjustment">Penyesuaian Stok (Audit Fisik)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Jumlah</label>
                    <input type="number" name="quantity" min="1" required value="1" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nomor Surat Jalan / Ref</label>
                    <input type="text" name="reference_no" placeholder="Contoh: SJ-2026-99" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Keterangan</label>
                    <textarea name="notes" rows="2" placeholder="Alasan mutasi..." class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-[#1f3344]">
                    <button type="button" @click="showNewLogModal = false" class="px-3 py-1.5 rounded-lg bg-[#162532] text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-emerald-500 text-slate-950 font-bold text-xs">Simpan Mutasi</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
