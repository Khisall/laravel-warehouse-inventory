@extends('layouts.app')

@section('title', 'Manajemen Supplier & Pemasok - INVENTORY')

@section('content')
<div class="space-y-6" x-data="{ newSupplierModal: false, editSupplierModal: false, activeSupplier: {} }">

    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 text-lg">
                <i class="fa-solid fa-handshake"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    Pemasok / Vendor (Suppliers)
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">Mitra Pengadaan</span>
                </h1>
                <p class="text-xs text-slate-400">Kelola direktori supplier, syarat pembayaran, dan riwayat pesanan pengadaan barang.</p>
            </div>
        </div>

        <button @click="newSupplierModal = true"
                class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/20 transition hover:scale-[1.02]">
            <i class="fa-solid fa-plus-circle text-sm"></i>
            <span>Tambah Supplier Baru</span>
        </button>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Supplier</p>
                <h3 class="text-2xl font-extrabold text-white mt-1">{{ number_format($totalSuppliers) }}</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Mitra terdaftar</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 text-xl">
                <i class="fa-solid fa-building"></i>
            </div>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Supplier Aktif</p>
                <h3 class="text-2xl font-extrabold text-emerald-400 mt-1">{{ number_format($activeSuppliers) }}</h3>
                <p class="text-[11px] text-emerald-500/80 mt-0.5">Dapat menerbitkan PO</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Alur Inbound</p>
                <h3 class="text-base font-bold text-teal-300 mt-1">PO → Kirim → GRN</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Siklus otomatis gudang</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400 text-xl">
                <i class="fa-solid fa-truck-ramp-box"></i>
            </div>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Aksi Cepat</p>
                <a href="{{ route('purchase-orders.create') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-400 hover:text-emerald-300 mt-2">
                    <span>Buat PO Baru</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 text-xl">
                <i class="fa-solid fa-file-invoice"></i>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-3.5 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
            <a href="{{ route('suppliers.index') }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ !request()->filled('status') ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:bg-[#182633]' }}">
                Semua Supplier
            </a>
            <a href="{{ route('suppliers.index', ['status' => 'active']) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'active' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:bg-[#182633]' }}">
                Aktif ({{ $activeSuppliers }})
            </a>
            <a href="{{ route('suppliers.index', ['status' => 'inactive']) }}"
               class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition {{ request('status') === 'inactive' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'text-slate-400 hover:bg-[#182633]' }}">
                Nonaktif
            </a>
        </div>

        <form method="GET" action="{{ route('suppliers.index') }}" class="flex items-center gap-2">
            <div class="relative w-full md:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Cari Nama, Kontak, Telp..."
                       class="w-full pl-8 pr-3 py-1.5 bg-[#0e1821] border border-[#1f3344] rounded-lg text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
            </div>
            @if(request()->filled('search'))
                <a href="{{ route('suppliers.index') }}" class="px-2.5 py-1.5 bg-slate-800 text-slate-400 rounded-lg text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- Suppliers Table -->
    <div class="table-container overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-[#1f3344] bg-[#0f1b24]/80 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Kode & Nama Supplier</th>
                        <th class="py-3 px-4">Kontak Person</th>
                        <th class="py-3 px-4">Telepon & Email</th>
                        <th class="py-3 px-4">Alamat Gudang / Kantor</th>
                        <th class="py-3 px-4 text-center">Term Pembayaran</th>
                        <th class="py-3 px-4 text-center">Riwayat PO</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1f3344]/50">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-[#162532]/70 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-emerald-400 text-xs">{{ $supplier->code }}</span>
                                <div class="font-bold text-white text-sm mt-0.5">{{ $supplier->name }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-200">{{ $supplier->contact_person ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-slate-300 font-mono text-[11px]">{{ $supplier->phone ?? '-' }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $supplier->email ?? '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="text-slate-400 text-[11px] truncate" title="{{ $supplier->address }}">
                                    {{ $supplier->address ?? '-' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-mono font-semibold text-[11px] border border-slate-700">
                                    {{ $supplier->payment_terms }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('purchase-orders.index', ['supplier_id' => $supplier->id]) }}"
                                   class="font-mono font-bold text-emerald-400 hover:underline">
                                    {{ $supplier->purchase_orders_count }} PO
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($supplier->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                        <i class="fa-solid fa-check mr-1 text-[9px]"></i> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button @click="activeSupplier = {{ json_encode($supplier) }}; editSupplierModal = true"
                                            class="p-1.5 rounded-lg bg-[#182633] hover:bg-[#203344] text-slate-300 hover:text-white transition"
                                            title="Edit Data Supplier">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <a href="{{ route('purchase-orders.create', ['supplier_id' => $supplier->id]) }}"
                                       class="p-1.5 rounded-lg bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-400 transition"
                                       title="Buat PO untuk Supplier Ini">
                                        <i class="fa-solid fa-cart-plus"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-handshake-slash text-3xl text-slate-600 mb-2"></i>
                                <p class="text-sm">Belum ada data supplier yang tersimpan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
            <div class="p-4 border-t border-[#1f3344]">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Supplier -->
    <div x-show="newSupplierModal"
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         style="display: none;">
        <div @click.away="newSupplierModal = false"
             class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl">
            <div class="px-6 py-4 border-b border-[#1f3344] flex items-center justify-between bg-[#0e1821]">
                <h3 class="font-bold text-white text-base flex items-center gap-2">
                    <i class="fa-solid fa-building text-emerald-400"></i>
                    <span>Tambah Supplier / Vendor Baru</span>
                </h3>
                <button @click="newSupplierModal = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form action="{{ route('suppliers.store') }}" method="POST" class="p-6 space-y-3.5">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kode Supplier</label>
                        <input type="text" name="code" placeholder="Otomatis (e.g. SUP-005)" class="w-full px-3 py-2 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Term Pembayaran <span class="text-rose-400">*</span></label>
                        <select name="payment_terms" required class="w-full px-3 py-2 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                            <option value="Cash">Cash On Delivery (COD)</option>
                            <option value="NET 14">NET 14 Hari</option>
                            <option value="NET 30" selected>NET 30 Hari</option>
                            <option value="NET 60">NET 60 Hari</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Perusahaan / Supplier <span class="text-rose-400">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: PT. Sumber Elektronik Sejahtera" class="w-full px-3 py-2 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kontak Person (PIC)</label>
                        <input type="text" name="contact_person" placeholder="Nama Marketing / PIC" class="w-full px-3 py-2 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nomor Telepon / WA</label>
                        <input type="text" name="phone" placeholder="+62 812-xxxx-xxxx" class="w-full px-3 py-2 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500 font-mono">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Email Supplier</label>
                    <input type="email" name="email" placeholder="sales@perusahaan.com" class="w-full px-3 py-2 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Alamat Lengkap Kantor / Gudang</label>
                    <textarea name="address" rows="2" placeholder="Alamat lengkap..." class="w-full px-3 py-2 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-[#1f3344]">
                    <button type="button" @click="newSupplierModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs">Simpan Supplier</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Supplier -->
    <div x-show="editSupplierModal"
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         style="display: none;">
        <div @click.away="editSupplierModal = false"
             class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl">
            <div class="px-6 py-4 border-b border-[#1f3344] flex items-center justify-between bg-[#0e1821]">
                <h3 class="font-bold text-white text-base">Edit Supplier</h3>
                <button @click="editSupplierModal = false" class="text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form :action="'/suppliers/' + activeSupplier.id" method="POST" class="p-6 space-y-3.5">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kode Supplier</label>
                        <input type="text" name="code" x-model="activeSupplier.code" required class="w-full px-3 py-2 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white font-mono focus:border-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Status</label>
                        <select name="is_active" x-model="activeSupplier.is_active" class="w-full px-3 py-2 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white">
                            <option :value="1">Aktif</option>
                            <option :value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Perusahaan / Supplier</label>
                    <input type="text" name="name" x-model="activeSupplier.name" required class="w-full px-3 py-2 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white focus:border-emerald-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Kontak Person (PIC)</label>
                        <input type="text" name="contact_person" x-model="activeSupplier.contact_person" class="w-full px-3 py-2 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nomor Telepon / WA</label>
                        <input type="text" name="phone" x-model="activeSupplier.phone" class="w-full px-3 py-2 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white font-mono">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Email</label>
                        <input type="email" name="email" x-model="activeSupplier.email" class="w-full px-3 py-2 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Term Pembayaran</label>
                        <select name="payment_terms" x-model="activeSupplier.payment_terms" class="w-full px-3 py-2 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white">
                            <option value="Cash">Cash On Delivery (COD)</option>
                            <option value="NET 14">NET 14 Hari</option>
                            <option value="NET 30">NET 30 Hari</option>
                            <option value="NET 60">NET 60 Hari</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Alamat Lengkap</label>
                    <textarea name="address" x-model="activeSupplier.address" rows="2" class="w-full px-3 py-2 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-[#1f3344]">
                    <button type="button" @click="editSupplierModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-xs">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
