@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Hak Akses')

@section('content')
<div x-data="{
    showAddUser: false,
    showEditUser: false,
    showResetModal: false,
    currentUser: {},
    openEdit(u) {
        this.currentUser = JSON.parse(JSON.stringify(u));
        this.showEditUser = true;
    },
    openReset(u) {
        this.currentUser = JSON.parse(JSON.stringify(u));
        this.showResetModal = true;
    }
}" class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">Manajemen Pengguna & Hak Akses Tim</h1>
            <p class="text-xs text-slate-400 mt-1">Kelola akun staf gudang, penugasan hak akses peran (*RBAC*), dan proteksi data finansial</p>
        </div>

        <button @click="showAddUser = true"
                class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-sm flex items-center gap-2 shadow-lg shadow-emerald-500/10 transition">
            <i class="fa-solid fa-user-plus text-xs"></i>
            <span>Tambah Staf / Akun Baru</span>
        </button>
    </div>

    <!-- Role Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
        <div class="bg-[#13202b] border border-[#1f3344] rounded-xl p-4">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-1">
                <span>Super Admin (Owner)</span>
                <i class="fa-solid fa-crown text-emerald-400"></i>
            </div>
            <div class="text-2xl font-bold text-white">{{ $stats['super_admin'] }}</div>
            <div class="text-[11px] text-emerald-400 mt-0.5">Akses penuh sistem</div>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-xl p-4">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-1">
                <span>Kepala Gudang</span>
                <i class="fa-solid fa-user-tie text-cyan-400"></i>
            </div>
            <div class="text-2xl font-bold text-cyan-400">{{ $stats['warehouse_manager'] }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Supervisor fisik</div>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-xl p-4">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-1">
                <span>Operator Gudang</span>
                <i class="fa-solid fa-boxes-packing text-amber-400"></i>
            </div>
            <div class="text-2xl font-bold text-amber-400">{{ $stats['warehouse_staff'] }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Input & scan barcode</div>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-xl p-4">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-1">
                <span>Admin Penjualan</span>
                <i class="fa-solid fa-shop text-purple-400"></i>
            </div>
            <div class="text-2xl font-bold text-purple-400">{{ $stats['sales_staff'] }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Marketplace & resi</div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="flex flex-wrap items-center justify-between gap-3 bg-[#13202b] border border-[#1f3344] rounded-xl p-3">
        <form action="{{ route('users.index') }}" method="GET" class="flex flex-wrap items-center gap-2 text-xs">
            <span class="text-slate-400 font-semibold px-1">Filter Peran:</span>
            <select name="role" onchange="this.form.submit()" class="bg-[#0e1922] border border-[#1f3344] rounded-lg px-2.5 py-1.5 text-slate-300 focus:outline-none focus:border-emerald-500">
                <option value="">Semua Peran</option>
                <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Owner / Super Admin</option>
                <option value="warehouse_manager" {{ request('role') == 'warehouse_manager' ? 'selected' : '' }}>Kepala Gudang</option>
                <option value="warehouse_staff" {{ request('role') == 'warehouse_staff' ? 'selected' : '' }}>Operator Gudang</option>
                <option value="sales_staff" {{ request('role') == 'sales_staff' ? 'selected' : '' }}>Admin Penjualan</option>
            </select>
        </form>

        <form action="{{ route('users.index') }}" method="GET" class="relative">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Cari nama, email, no. telp..."
                   class="pl-8 pr-3 py-1.5 bg-[#0e1922] border border-[#1f3344] rounded-lg text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-emerald-500">
            <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-slate-500 text-xs"></i>
        </form>
    </div>

    <!-- Users Table Container -->
    <div class="table-container overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-[#1f3344] bg-[#14232e] text-slate-400 font-semibold text-xs tracking-wider">
                        <th class="py-3.5 px-4">Nama Staf & Profil</th>
                        <th class="py-3.5 px-4">Email Login</th>
                        <th class="py-3.5 px-4">Peran (Role)</th>
                        <th class="py-3.5 px-4">Hak Akses Finansial</th>
                        <th class="py-3.5 px-4">No. Telepon</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4">Terakhir Masuk</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1f3344]/80 text-slate-200">
                    @forelse($users as $u)
                        <tr class="hover:bg-[#182835]/70 transition">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-full bg-[#182835] border border-[#22394c] flex items-center justify-center font-bold text-xs text-white">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-white">{{ $u->name }}</div>
                                        @if($u->id === auth()->id())
                                            <span class="text-[10px] text-emerald-400 font-bold">(Akun Anda Saat Ini)</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap font-mono text-xs text-slate-300">
                                {{ $u->email }}
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-md text-xs font-bold {{ $u->role_badge_color }}">
                                    {{ $u->role_badge_label }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap text-xs">
                                @if($u->canViewFinancials())
                                    <span class="text-emerald-400 flex items-center gap-1 font-semibold">
                                        <i class="fa-solid fa-eye text-[11px]"></i> Lihat HPP & Margin
                                    </span>
                                @else
                                    <span class="text-slate-500 flex items-center gap-1">
                                        <i class="fa-solid fa-eye-slash text-[11px]"></i> Harga Modal Rahasia
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-300">
                                {{ $u->phone ?? '-' }}
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($u->is_active)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-400">
                                {{ $u->last_login_at ? $u->last_login_at->diffForHumans() : 'Belum pernah' }}
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <button type="button"
                                            @click="openEdit({{ $u->toJson() }})"
                                            title="Edit Peran Staf"
                                            class="w-7 h-7 rounded-lg bg-[#182835] hover:bg-cyan-500/20 text-cyan-400 border border-[#233b4e] flex items-center justify-center transition">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </button>

                                    <button type="button"
                                            @click="openReset({{ $u->toJson() }})"
                                            title="Reset Password"
                                            class="w-7 h-7 rounded-lg bg-[#182835] hover:bg-amber-500/20 text-amber-400 border border-[#233b4e] flex items-center justify-center transition">
                                        <i class="fa-solid fa-key text-xs"></i>
                                    </button>

                                    @if($u->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $u) }}" method="POST" onsubmit="return confirm('Hapus akun pengguna {{ addslashes($u->name) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    title="Hapus Akun"
                                                    class="w-7 h-7 rounded-lg bg-[#182835] hover:bg-rose-500/20 text-rose-400 border border-[#233b4e] flex items-center justify-center transition">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500">
                                Tidak ada akun staf yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-[#1f3344] bg-[#101b24]">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- ==================== MODAL: ADD USER ==================== -->
    <div x-show="showAddUser" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="showAddUser = false" class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-md w-full p-6 text-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f3344]">
                <h3 class="font-bold text-white text-base">Tambah Akun Staf Gudang Baru</h3>
                <button @click="showAddUser = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="{{ route('users.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Contoh: Dedi Setiawan" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Email Login</label>
                    <input type="email" name="email" required placeholder="dedi@inventory.com" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Peran (Role) & Hak Akses</label>
                    <select name="role" required class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                        <option value="warehouse_staff">Operator Gudang (Input mutasi stok, scan barcode, HPP modal tersembunyi)</option>
                        <option value="warehouse_manager">Kepala Gudang (Bisa lihat modal, stock opname & laporan)</option>
                        <option value="sales_staff">Admin Penjualan / Marketplace (Kelola pesanan & pengiriman)</option>
                        <option value="super_admin">Owner / Super Admin (Akses penuh seluruh data)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone" placeholder="+62 812-xxxx" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Kata Sandi Awal</label>
                    <input type="password" name="password" required minlength="6" value="password123" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    <p class="text-[10px] text-slate-500 mt-1">Minimal 6 karakter.</p>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-[#1f3344]">
                    <button type="button" @click="showAddUser = false" class="px-3 py-1.5 rounded-lg bg-[#162532] text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-emerald-500 text-slate-950 font-bold text-xs">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL: EDIT USER ==================== -->
    <div x-show="showEditUser" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="showEditUser = false" class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-md w-full p-6 text-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f3344]">
                <h3 class="font-bold text-white text-base">Edit Peran Staf: <span class="text-cyan-400" x-text="currentUser.name"></span></h3>
                <button @click="showEditUser = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form :action="'{{ url('users') }}/' + currentUser.id" method="POST" class="space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required x-model="currentUser.name" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Email Login</label>
                    <input type="email" name="email" required x-model="currentUser.email" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Peran (Role)</label>
                    <select name="role" x-model="currentUser.role" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                        <option value="warehouse_staff">Operator Gudang</option>
                        <option value="warehouse_manager">Kepala Gudang</option>
                        <option value="sales_staff">Admin Penjualan / Marketplace</option>
                        <option value="super_admin">Owner / Super Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nomor Telepon</label>
                    <input type="text" name="phone" x-model="currentUser.phone" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div class="flex items-center space-x-2 pt-1">
                    <input type="checkbox" id="user_is_active_edit" name="is_active" value="1" :checked="currentUser.is_active" class="w-4 h-4 rounded bg-[#0e1922] border-[#294255] text-emerald-500">
                    <label for="user_is_active_edit" class="text-xs text-slate-300">Akun Aktif (Dapat Login)</label>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-[#1f3344]">
                    <button type="button" @click="showEditUser = false" class="px-3 py-1.5 rounded-lg bg-[#162532] text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-cyan-500 text-slate-950 font-bold text-xs">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL: RESET PASSWORD ==================== -->
    <div x-show="showResetModal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div @click.away="showResetModal = false" class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-sm w-full p-6 text-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f3344]">
                <h3 class="font-bold text-white text-base">Reset Kata Sandi</h3>
                <button @click="showResetModal = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form :action="'{{ url('users') }}/' + currentUser.id + '/reset-password'" method="POST" class="space-y-3">
                @csrf
                <p class="text-xs text-slate-400">Masukkan kata sandi baru untuk <strong class="text-white" x-text="currentUser.name"></strong>:</p>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Kata Sandi Baru</label>
                    <input type="password" name="new_password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-[#1f3344]">
                    <button type="button" @click="showResetModal = false" class="px-3 py-1.5 rounded-lg bg-[#162532] text-xs font-semibold">Batal</button>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-amber-500 text-slate-950 font-bold text-xs">Reset Kata Sandi</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
