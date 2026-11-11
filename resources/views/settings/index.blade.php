@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="space-y-6 max-w-4xl">
    <div>
        <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">System Settings & Warehouse Config</h1>
        <p class="text-xs text-slate-400 mt-1">Konfigurasi umum aplikasi manajemen pergudangan & preferensi akun</p>
    </div>

    <div class="space-y-6">
        <!-- Profil Gudang -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-6 space-y-4">
            <h3 class="font-bold text-base text-white flex items-center gap-2">
                <i class="fa-solid fa-id-card text-emerald-400"></i> Profil Instansi & Gudang Pusat
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Nama Usaha / Gudang</label>
                    <input type="text" value="INVENTORY Central Logistic" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-white text-xs focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Mata Uang Default</label>
                    <input type="text" value="USD ($) / IDR (Rp)" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-white text-xs focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Email Notifikasi Restock</label>
                    <input type="email" value="admin@inventory.com" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-white text-xs focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-300 mb-1">Ambang Batas Minimum Stok Default</label>
                    <input type="number" value="5" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-white text-xs focus:outline-none focus:border-emerald-500">
                </div>
            </div>
        </div>

        <!-- Info Sistem Laravel & Database -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-6 space-y-3">
            <h3 class="font-bold text-base text-white flex items-center gap-2">
                <i class="fa-solid fa-server text-emerald-400"></i> Environment & Status Server
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div class="p-3 bg-[#0e1922] rounded-xl border border-[#1f3344]">
                    <div class="text-slate-400">Framework</div>
                    <div class="font-bold text-white mt-1">Laravel 12.72.0</div>
                </div>
                <div class="p-3 bg-[#0e1922] rounded-xl border border-[#1f3344]">
                    <div class="text-slate-400">PHP Version</div>
                    <div class="font-bold text-white mt-1">PHP 8.2.12</div>
                </div>
                <div class="p-3 bg-[#0e1922] rounded-xl border border-[#1f3344]">
                    <div class="text-slate-400">Database Engine</div>
                    <div class="font-bold text-emerald-400 mt-1">MySQL (XAMPP)</div>
                </div>
                <div class="p-3 bg-[#0e1922] rounded-xl border border-[#1f3344]">
                    <div class="text-slate-400">Database Name</div>
                    <div class="font-bold text-cyan-400 mt-1">inventory_db</div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="button" onclick="alert('Pengaturan sistem berhasil disimpan!')" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/10">
                Simpan Perubahan
            </button>
        </div>
    </div>
</div>
@endsection
