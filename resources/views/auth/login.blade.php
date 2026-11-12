<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - INVENTORY Warehouse Management System</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <style>
        body {
            background-color: #0b141b;
            color: #e2e8f0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 bg-[#0b141b] text-slate-200" x-data="{ showPass: false }">

    <div class="max-w-md w-full space-y-6">

        <!-- Logo & Branding -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 p-2 shadow-lg shadow-emerald-500/10">
                <img src="{{ asset('images/logo.svg') }}" alt="INVENTORY Logo" class="w-10 h-10 object-contain">
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-wider uppercase flex items-center justify-center gap-1.5">
                <span class="text-emerald-400">INVENTORY</span>
            </h1>
            <p class="text-xs text-slate-400">Sistem Pergudangan & Manajemen Stok Multi-User</p>
        </div>

        <!-- Login Card -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-6 sm:p-8 shadow-2xl space-y-5">

            <!-- Flash Alert Messages -->
            @if(session('error'))
                <div class="p-3.5 bg-rose-500/10 border border-rose-500/30 rounded-xl text-xs text-rose-300 flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-rose-400"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="p-3.5 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-xs text-emerald-300 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-400"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Email Input -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Alamat Email Staf</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-500">
                            <i class="fa-solid fa-envelope text-xs"></i>
                        </span>
                        <input type="email"
                               name="email"
                               required
                               value="{{ old('email', 'admin@inventory.com') }}"
                               placeholder="nama@inventory.com"
                               class="w-full pl-10 pr-4 py-2.5 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/40">
                    </div>
                    @error('email')
                        <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Kata Sandi</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-500">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </span>
                        <input :type="showPass ? 'text' : 'password'"
                               name="password"
                               required
                               value="password123"
                               placeholder="••••••••"
                               class="w-full pl-10 pr-10 py-2.5 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/40">
                        <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300">
                            <i :class="showPass ? 'fa-eye-slash' : 'fa-eye'" class="fa-solid text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-[#0e1922] border-[#294255] text-emerald-500 focus:ring-0">
                        <span class="text-slate-400">Ingat Saya</span>
                    </label>
                    <span class="text-slate-500">Default: password123</span>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-sm shadow-lg shadow-emerald-500/20 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                    <span>Masuk ke Sistem</span>
                </button>
            </form>

            <!-- 1-Click Role Switcher for Testing -->
            <div class="pt-4 border-t border-[#1f3344] space-y-2">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center">
                    Coba Akses Cepat Berdasarkan Peran (1-Click Demo)
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <a href="{{ route('auth.demo', 'super_admin') }}" class="p-2 rounded-xl bg-[#0e1922] hover:bg-emerald-500/10 border border-[#1f3344] hover:border-emerald-500/40 text-slate-300 hover:text-emerald-400 transition text-center flex flex-col items-center">
                        <span class="font-bold text-emerald-400"><i class="fa-solid fa-crown mr-1"></i> Owner</span>
                        <span class="text-[10px] text-slate-500">Akses Penuh Finansial</span>
                    </a>

                    <a href="{{ route('auth.demo', 'warehouse_manager') }}" class="p-2 rounded-xl bg-[#0e1922] hover:bg-cyan-500/10 border border-[#1f3344] hover:border-cyan-500/40 text-slate-300 hover:text-cyan-400 transition text-center flex flex-col items-center">
                        <span class="font-bold text-cyan-400"><i class="fa-solid fa-user-tie mr-1"></i> Ka. Gudang</span>
                        <span class="text-[10px] text-slate-500">Supervisor Stok</span>
                    </a>

                    <a href="{{ route('auth.demo', 'warehouse_staff') }}" class="p-2 rounded-xl bg-[#0e1922] hover:bg-amber-500/10 border border-[#1f3344] hover:border-amber-500/40 text-slate-300 hover:text-amber-400 transition text-center flex flex-col items-center">
                        <span class="font-bold text-amber-400"><i class="fa-solid fa-boxes-packing mr-1"></i> Operator</span>
                        <span class="text-[10px] text-slate-500">Harga Modal Rahasia</span>
                    </a>

                    <a href="{{ route('auth.demo', 'sales_staff') }}" class="p-2 rounded-xl bg-[#0e1922] hover:bg-purple-500/10 border border-[#1f3344] hover:border-purple-500/40 text-slate-300 hover:text-purple-400 transition text-center flex flex-col items-center">
                        <span class="font-bold text-purple-400"><i class="fa-solid fa-shop mr-1"></i> Sales</span>
                        <span class="text-[10px] text-slate-500">Pesanan & Resi</span>
                    </a>
                </div>
            </div>

        </div>

        <div class="text-center text-xs text-slate-500">
            INVENTORY WMS &copy; {{ date('Y') }} &bull; Laravel 12 &bull; Multi-Role Engine
        </div>

    </div>

</body>
</html>
