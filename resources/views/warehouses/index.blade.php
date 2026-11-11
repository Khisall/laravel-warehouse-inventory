@extends('layouts.app')

@section('title', 'Warehouses & Storage Locations')

@section('content')
<div x-data="{
    showAddWarehouse: false,
    showEditWarehouse: false,
    editWh: {},
    openEdit(wh) {
        this.editWh = JSON.parse(JSON.stringify(wh));
        this.showEditWarehouse = true;
    }
}" class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">Warehouses & Storage Locations</h1>
            <p class="text-xs text-slate-400 mt-1">Kelola daftar gudang fisik, pembagian blok rak (Location A, B, C, D) & kapasitas penyimpanan</p>
        </div>

        <button @click="showAddWarehouse = true"
                class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-sm flex items-center gap-2 shadow-lg shadow-emerald-500/10">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Tambah Gudang Baru</span>
        </button>
    </div>

    <!-- Warehouse Grid Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($warehouses as $wh)
            <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 hover:border-emerald-500/50 transition space-y-4 relative group">
                <div class="flex items-start justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-warehouse"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-white text-base">{{ $wh->name }}</h3>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-[#162532] text-emerald-400 border border-[#22394c]">
                                {{ $wh->code }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-1">
                        <button @click="openEdit({{ $wh->toJson() }})" class="w-7 h-7 rounded-lg bg-[#182835] text-slate-400 hover:text-cyan-400 flex items-center justify-center transition">
                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                        </button>
                        <form action="{{ route('warehouses.destroy', $wh) }}" method="POST" onsubmit="return confirm('Hapus gudang {{ $wh->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-7 h-7 rounded-lg bg-[#182835] text-slate-400 hover:text-rose-400 flex items-center justify-center transition">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="space-y-2 text-xs text-slate-300">
                    <div class="flex items-center justify-between py-1 border-b border-[#1f3344]/60">
                        <span class="text-slate-400">Kode Rak / Slot:</span>
                        <span class="font-bold text-emerald-400">{{ $wh->location }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-[#1f3344]/60">
                        <span class="text-slate-400">Penanggung Jawab:</span>
                        <span class="font-medium text-slate-200">{{ $wh->manager_name ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-[#1f3344]/60">
                        <span class="text-slate-400">Telepon Gudang:</span>
                        <span class="text-slate-200">{{ $wh->phone ?? '-' }}</span>
                    </div>
                    <div class="py-1">
                        <span class="text-slate-400 block mb-0.5">Alamat Gudang:</span>
                        <span class="text-slate-300 text-[11px] leading-relaxed">{{ $wh->address ?? '-' }}</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-[#1f3344] flex items-center justify-between text-xs">
                    <span class="text-slate-400">Total Jenis Barang:</span>
                    <a href="{{ route('inventory.index', ['warehouse' => $wh->id]) }}" class="px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 font-bold border border-emerald-500/30 flex items-center gap-1 transition">
                        <span>{{ $wh->products_count }} SKU</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Tambah Gudang -->
    <div x-show="showAddWarehouse" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="showAddWarehouse = false" class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-md w-full p-6 text-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f3344]">
                <h3 class="font-bold text-white text-base">Tambah Lokasi Gudang Baru</h3>
                <button @click="showAddWarehouse = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('warehouses.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Gudang</label>
                    <input type="text" name="name" required placeholder="Contoh: Gudang Sentral Blok E" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kode Gudang</label>
                        <input type="text" name="code" required placeholder="WH-BLOK-E" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500 uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kode Slot / Rak</label>
                        <input type="text" name="location" required placeholder="Location E: 3" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Kepala Gudang (PIC)</label>
                    <input type="text" name="manager_name" placeholder="Nama Supervisor" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">No. Kontak / Telepon</label>
                    <input type="text" name="phone" placeholder="+62 812-xxxx" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Alamat Lengkap</label>
                    <textarea name="address" rows="2" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-[#1f3344]">
                    <button type="button" @click="showAddWarehouse = false" class="px-3 py-1.5 rounded-lg bg-[#162532] text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-emerald-500 text-slate-950 font-bold text-xs">Simpan Gudang</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Gudang -->
    <div x-show="showEditWarehouse" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="showEditWarehouse = false" class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-md w-full p-6 text-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f3344]">
                <h3 class="font-bold text-white text-base">Edit Gudang: <span class="text-cyan-400" x-text="editWh.name"></span></h3>
                <button @click="showEditWarehouse = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form :action="'{{ url('warehouses') }}/' + editWh.id" method="POST" class="space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Gudang</label>
                    <input type="text" name="name" required x-model="editWh.name" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kode Gudang</label>
                        <input type="text" name="code" required x-model="editWh.code" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500 uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kode Slot / Rak</label>
                        <input type="text" name="location" required x-model="editWh.location" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Kepala Gudang (PIC)</label>
                    <input type="text" name="manager_name" x-model="editWh.manager_name" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">No. Kontak / Telepon</label>
                    <input type="text" name="phone" x-model="editWh.phone" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Alamat Lengkap</label>
                    <textarea name="address" rows="2" x-model="editWh.address" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-[#1f3344]">
                    <button type="button" @click="showEditWarehouse = false" class="px-3 py-1.5 rounded-lg bg-[#162532] text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-cyan-500 text-slate-950 font-bold text-xs">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
