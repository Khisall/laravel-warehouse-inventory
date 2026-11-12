<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'INVENTORY') - Warehouse Management System</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            DEFAULT: '#00df81',
                        },
                        darkbg: '#0c161d',
                        darkcard: '#13202b',
                        darkcard2: '#162532',
                        darkborder: '#1f3344',
                        darkhover: '#192b39',
                        darkinput: '#0e1922',
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <!-- Html5-Qrcode Barcode & QR Camera Scanner -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <!-- JsBarcode -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

    <style>
        body {
            background-color: #0b141b;
            color: #e2e8f0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Custom subtle scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0c161d;
        }
        ::-webkit-scrollbar-thumb {
            background: #233b4e;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #34d399;
        }

        .table-container {
            border: 1px solid #1f3344;
            border-radius: 14px;
            background: #13202b;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4);
        }

        .nav-item-active {
            background: rgba(16, 185, 129, 0.12);
            color: #34d399;
            border-left: 3px solid #00df81;
        }
    </style>
    @stack('styles')
</head>
<body x-data="warehouseApp()">
    <div class="min-h-screen flex flex-col bg-[#0b141b] text-slate-200">

        <!-- Top Header Navigation -->
        <header class="h-16 border-b border-[#1f3344] bg-[#0d1821] px-4 md:px-6 flex items-center justify-between sticky top-0 z-40">
            <!-- Brand & Sidebar Toggle -->
            <div class="flex items-center space-x-3">
                <button @click="mobileSidebarOpen = !mobileSidebarOpen" class="md:hidden text-slate-400 hover:text-white p-2">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>

                <a href="{{ route('inventory.index') }}" class="flex items-center space-x-2.5 group">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center p-1 group-hover:scale-105 transition-transform">
                        <img src="{{ asset('images/logo.svg') }}" alt="INVENTORY Logo" class="w-6 h-6 object-contain">
                    </div>
                    <span class="font-extrabold text-lg tracking-wider text-white uppercase flex items-center gap-1.5">
                        <span class="text-emerald-400">INVENTORY</span>
                    </span>
                </a>

                <button @click="sidebarOpen = !sidebarOpen" class="hidden md:flex items-center justify-center w-7 h-7 rounded-full bg-[#162532] border border-[#1f3344] text-slate-400 hover:text-emerald-400 hover:border-emerald-500/40 transition ml-2">
                    <i class="fa-solid fa-chevron-left text-xs" :class="sidebarOpen ? '' : 'rotate-180 transition-transform'"></i>
                </button>
            </div>

            <!-- Top Search Bar -->
            <div class="flex-1 max-w-xl mx-4 hidden sm:block">
                <form action="{{ route('inventory.index') }}" method="GET" class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </span>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search SKU, item title, or location..."
                           class="w-full pl-10 pr-4 py-2 bg-[#0e1922] border border-[#1f3344] rounded-full text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-emerald-500/80 focus:ring-1 focus:ring-emerald-500/40 transition">
                    @if(request('search'))
                        <a href="{{ route('inventory.index') }}" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </a>
                    @endif
                </form>
            </div>

            <!-- Top Right Profile, Scanner & Notifications -->
            <div class="flex items-center space-x-3 md:space-x-4">
                <!-- Scan Barcode Button -->
                <button type="button"
                        @click="openScanner()"
                        title="Scan Barcode / QR Code Barang"
                        class="px-3 py-1.5 rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 hover:bg-emerald-500/30 transition text-xs font-bold flex items-center gap-1.5 shadow-sm shadow-emerald-500/10">
                    <i class="fa-solid fa-barcode text-sm text-emerald-400"></i>
                    <span class="hidden sm:inline">Scan Barcode</span>
                </button>

                <!-- Quick Sync Button -->
                <form action="{{ route('marketplace.sync-all') }}" method="POST" class="hidden lg:block">
                    @csrf
                    <button type="submit" title="Sync All Stock" class="text-xs px-3 py-1.5 rounded-full bg-[#162532] border border-[#1f3344] text-slate-300 hover:text-emerald-400 hover:border-emerald-500/40 transition flex items-center gap-1.5">
                        <i class="fa-solid fa-rotate text-emerald-400"></i>
                        <span>Sync Stock</span>
                    </button>
                </form>

                <!-- Notifications with Badge -->
                <a href="{{ route('activity.index') }}" title="Notifikasi Mutasi" class="relative p-2 text-slate-400 hover:text-slate-200 transition">
                    <i class="fa-regular fa-bell text-lg"></i>
                    <span class="absolute top-1 right-1 w-4 h-4 rounded-full bg-emerald-500 text-[10px] font-bold text-slate-950 flex items-center justify-center">
                        3
                    </span>
                </a>

                <!-- User Profile Avatar & Dropdown -->
                <div class="relative flex items-center space-x-3 pl-2 border-l border-[#1f3344]" x-data="{ profileOpen: false }" @click.away="profileOpen = false">
                    <div class="flex items-center space-x-3 cursor-pointer" @click="profileOpen = !profileOpen">
                        <div class="relative">
                            <div class="w-9 h-9 rounded-full ring-2 ring-emerald-500/40 overflow-hidden bg-slate-800 flex items-center justify-center font-bold text-xs text-white">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                            </div>
                            <div class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-400 ring-2 ring-[#0d1821]"></div>
                        </div>
                        <div class="hidden xl:block text-left">
                            <div class="text-xs font-semibold text-white truncate max-w-[130px]">{{ auth()->user()->name ?? 'Pengguna' }}</div>
                            <div class="text-[11px] text-emerald-400 font-semibold">{{ auth()->user()->role_badge_label ?? 'Staff' }}</div>
                        </div>
                    </div>

                    <!-- Profile Dropdown Menu -->
                    <div x-show="profileOpen"
                         x-transition
                         style="display: none;"
                         class="absolute right-0 top-12 w-64 rounded-xl bg-[#13202b] border border-[#1f3344] shadow-2xl py-2 z-50 text-xs text-slate-200">
                        <div class="px-3.5 py-2 border-b border-[#1f3344]">
                            <div class="font-bold text-white">{{ auth()->user()->name ?? 'User' }}</div>
                            <div class="text-[11px] text-slate-400 font-mono truncate">{{ auth()->user()->email ?? '' }}</div>
                            <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold {{ auth()->user()->role_badge_color ?? '' }}">
                                {{ auth()->user()->role_badge_label ?? 'Staff' }}
                            </span>
                        </div>

                        <div class="px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1">Coba Demo Switch Peran:</div>
                        <a href="{{ route('auth.demo', 'super_admin') }}" class="block px-3.5 py-1.5 text-slate-300 hover:bg-[#162532] hover:text-emerald-400">
                            👑 Owner / Super Admin
                        </a>
                        <a href="{{ route('auth.demo', 'warehouse_manager') }}" class="block px-3.5 py-1.5 text-slate-300 hover:bg-[#162532] hover:text-cyan-400">
                            🏭 Kepala Gudang
                        </a>
                        <a href="{{ route('auth.demo', 'warehouse_staff') }}" class="block px-3.5 py-1.5 text-slate-300 hover:bg-[#162532] hover:text-amber-400">
                            📦 Operator Gudang
                        </a>
                        <a href="{{ route('auth.demo', 'sales_staff') }}" class="block px-3.5 py-1.5 text-slate-300 hover:bg-[#162532] hover:text-purple-400">
                            🛒 Admin Penjualan
                        </a>

                        <div class="border-t border-[#1f3344] my-1"></div>
                        @if(auth()->user()?->canManageUsers())
                            <a href="{{ route('users.index') }}" class="block px-3.5 py-2 text-slate-300 hover:bg-[#162532] hover:text-emerald-400 font-semibold">
                                <i class="fa-solid fa-users-gear mr-1.5 text-emerald-400"></i> Kelola Tim & Hak Akses
                            </a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left px-3.5 py-2 text-rose-400 hover:bg-rose-500/10 font-bold flex items-center gap-2">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                <span>Keluar (Log Out)</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Body Wrapper -->
        <div class="flex-1 flex overflow-hidden">

            <!-- Sidebar Desktop -->
            <aside :class="sidebarOpen ? 'w-60' : 'w-16'"
                   class="hidden md:flex flex-col bg-[#0e1922] border-r border-[#1f3344] transition-all duration-200 select-none justify-between py-4">

                <!-- Main Menu -->
                <div class="space-y-6">
                    <nav class="space-y-1 px-2.5">
                        <!-- Dashboard -->
                        <a href="{{ route('inventory.index') }}"
                           class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition group {{ request()->routeIs('inventory.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532] hover:text-slate-200' }}">
                            <div class="w-5 text-center mr-3 text-base {{ request()->routeIs('inventory.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">
                                <i class="fa-solid fa-box"></i>
                            </div>
                            <span :class="sidebarOpen ? 'block' : 'hidden'">Dashboard</span>
                        </a>

                        <!-- Warehouses -->
                        <a href="{{ route('warehouses.index') }}"
                           class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition group {{ request()->routeIs('warehouses.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532] hover:text-slate-200' }}">
                            <div class="w-5 text-center mr-3 text-base {{ request()->routeIs('warehouses.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <span :class="sidebarOpen ? 'block' : 'hidden'">Warehouses</span>
                        </a>

                        <!-- Marketplace -->
                        <a href="{{ route('marketplace.index') }}"
                           class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition group {{ request()->routeIs('marketplace.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532] hover:text-slate-200' }}">
                            <div class="w-5 text-center mr-3 text-base {{ request()->routeIs('marketplace.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">
                                <i class="fa-solid fa-store"></i>
                            </div>
                            <span :class="sidebarOpen ? 'block' : 'hidden'">Marketplace</span>
                        </a>

                        <!-- Orders -->
                        <a href="{{ route('orders.index') }}"
                           class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition group {{ request()->routeIs('orders.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532] hover:text-slate-200' }}">
                            <div class="w-5 text-center mr-3 text-base {{ request()->routeIs('orders.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">
                                <i class="fa-solid fa-basket-shopping"></i>
                            </div>
                            <span :class="sidebarOpen ? 'block' : 'hidden'">Orders</span>
                        </a>

                        <!-- Shipping -->
                        <a href="{{ route('shipping.index') }}"
                           class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition group {{ request()->routeIs('shipping.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532] hover:text-slate-200' }}">
                            <div class="w-5 text-center mr-3 text-base {{ request()->routeIs('shipping.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">
                                <i class="fa-solid fa-truck-fast"></i>
                            </div>
                            <span :class="sidebarOpen ? 'block' : 'hidden'">Shipping</span>
                        </a>

                        <!-- Reports (Managers & Super Admin) -->
                        @if(auth()->user()?->canViewFinancials())
                            <a href="{{ route('reports.index') }}"
                               class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition group {{ request()->routeIs('reports.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532] hover:text-slate-200' }}">
                                <div class="w-5 text-center mr-3 text-base {{ request()->routeIs('reports.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">
                                    <i class="fa-solid fa-chart-pie"></i>
                                </div>
                                <span :class="sidebarOpen ? 'block' : 'hidden'">Reports</span>
                            </a>
                        @endif
                    </nav>
                </div>

                <!-- Secondary Menu (Bottom) -->
                <div class="space-y-1 px-2.5 pt-4 border-t border-[#1f3344]/80">
                    <!-- Kelola Tim & Hak Akses (Super Admin only) -->
                    @if(auth()->user()?->canManageUsers())
                        <a href="{{ route('users.index') }}"
                           class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition group {{ request()->routeIs('users.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532] hover:text-slate-200' }}">
                            <div class="w-5 text-center mr-3 text-base {{ request()->routeIs('users.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">
                                <i class="fa-solid fa-users-gear"></i>
                            </div>
                            <span :class="sidebarOpen ? 'block' : 'hidden'">Tim & Akses</span>
                        </a>
                    @endif

                    <!-- Settings (Super Admin only) -->
                    @if(auth()->user()?->isSuperAdmin())
                        <a href="{{ route('settings.index') }}"
                           class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition group {{ request()->routeIs('settings.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532] hover:text-slate-200' }}">
                            <div class="w-5 text-center mr-3 text-base {{ request()->routeIs('settings.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">
                                <i class="fa-solid fa-gear"></i>
                            </div>
                            <span :class="sidebarOpen ? 'block' : 'hidden'">Settings</span>
                        </a>
                    @endif

                    <!-- Activity & Inventory History -->
                    <a href="{{ route('activity.index') }}"
                       class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition group {{ request()->routeIs('activity.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532] hover:text-slate-200' }}">
                        <div class="w-5 text-center mr-3 text-base {{ request()->routeIs('activity.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <span :class="sidebarOpen ? 'block' : 'hidden'">Activity & Inve...</span>
                    </a>

                    <!-- What's New -->
                    <a href="{{ route('whats-new.index') }}"
                       class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition group {{ request()->routeIs('whats-new.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532] hover:text-slate-200' }}">
                        <div class="w-5 text-center mr-3 text-base {{ request()->routeIs('whats-new.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                        </div>
                        <span :class="sidebarOpen ? 'block' : 'hidden'">What's New</span>
                    </a>

                    <!-- Help & Support -->
                    <a href="{{ route('help.index') }}"
                       class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition group {{ request()->routeIs('help.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532] hover:text-slate-200' }}">
                        <div class="w-5 text-center mr-3 text-base {{ request()->routeIs('help.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">
                            <i class="fa-solid fa-headset"></i>
                        </div>
                        <span :class="sidebarOpen ? 'block' : 'hidden'">Help & Support</span>
                    </a>

                    <!-- Log Out -->
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:bg-rose-500/10 hover:text-rose-400 transition group">
                            <div class="w-5 text-center mr-3 text-base text-slate-400 group-hover:text-rose-400">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            </div>
                            <span :class="sidebarOpen ? 'block' : 'hidden'">Log Out</span>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Sidebar Mobile Drawer -->
            <div x-show="mobileSidebarOpen"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 flex md:hidden"
                 style="display: none;">

                <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" @click="mobileSidebarOpen = false"></div>

                <div class="relative flex-1 flex flex-col max-w-xs w-full bg-[#0e1922] border-r border-[#1f3344] py-4 px-3 justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-[#1f3344] mb-4">
                            <div class="flex items-center space-x-2">
                                <img src="{{ asset('images/logo.svg') }}" alt="INVENTORY Logo" class="w-7 h-7">
                                <span class="font-extrabold text-emerald-400 tracking-wider">INVENTORY</span>
                            </div>
                            <button @click="mobileSidebarOpen = false" class="text-slate-400 hover:text-white p-1">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <nav class="space-y-1">
                            <a href="{{ route('inventory.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('inventory.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532]' }}">
                                <i class="fa-solid fa-box w-6 text-emerald-400"></i> Dashboard
                            </a>
                            <a href="{{ route('warehouses.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('warehouses.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532]' }}">
                                <i class="fa-solid fa-layer-group w-6 text-emerald-400"></i> Warehouses
                            </a>
                            <a href="{{ route('marketplace.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('marketplace.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532]' }}">
                                <i class="fa-solid fa-store w-6 text-emerald-400"></i> Marketplace
                            </a>
                            <a href="{{ route('orders.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('orders.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532]' }}">
                                <i class="fa-solid fa-basket-shopping w-6 text-emerald-400"></i> Orders
                            </a>
                            <a href="{{ route('shipping.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('shipping.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532]' }}">
                                <i class="fa-solid fa-truck-fast w-6 text-emerald-400"></i> Shipping
                            </a>
                            @if(auth()->user()?->canViewFinancials())
                                <a href="{{ route('reports.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('reports.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532]' }}">
                                    <i class="fa-solid fa-chart-pie w-6 text-emerald-400"></i> Reports
                                </a>
                            @endif
                        </nav>
                    </div>

                    <div class="pt-4 border-t border-[#1f3344] space-y-1">
                        @if(auth()->user()?->canManageUsers())
                            <a href="{{ route('users.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('users.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532]' }}">
                                <i class="fa-solid fa-users-gear w-6 text-emerald-400"></i> Tim & Hak Akses
                            </a>
                        @endif
                        <a href="{{ route('activity.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-[#162532]">
                            <i class="fa-solid fa-clock-rotate-left w-6"></i> Activity & Inventory
                        </a>
                        @if(auth()->user()?->isSuperAdmin())
                            <a href="{{ route('settings.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-[#162532]">
                                <i class="fa-solid fa-gear w-6"></i> Settings
                            </a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center px-3 py-2 rounded-lg text-sm font-medium text-rose-400 hover:bg-rose-500/10">
                                <i class="fa-solid fa-arrow-right-from-bracket w-6"></i> Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8 bg-[#0b141b]">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" class="mb-5 flex items-center justify-between p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
                            <span class="text-sm font-medium">{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-emerald-400/70 hover:text-emerald-300">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" class="mb-5 flex items-center justify-between p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-circle-exclamation text-rose-400 text-lg"></i>
                            <span class="text-sm font-medium">{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-rose-400/70 hover:text-rose-300">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div x-data="{ show: true }" x-show="show" class="mb-5 p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-sm">
                        <div class="font-semibold flex items-center gap-2 mb-1">
                            <i class="fa-solid fa-triangle-exclamation"></i> Terjadi kesalahan validasi:
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Dynamic Content Slot -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- ==================== MODAL: BARCODE & QR SCANNER ==================== -->
    <div x-show="scannerOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-md flex items-center justify-center p-4"
         style="display: none;">

        <div @click.away="closeScanner()" class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-lg w-full p-5 sm:p-6 shadow-2xl space-y-4 text-slate-200">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-[#1f3344]">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                        <i class="fa-solid fa-barcode"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-white">Scanner Barcode & QR Code Gudang</h3>
                        <p class="text-xs text-slate-400">Dukungan Kamera HP / Webcam & USB Handheld Gun</p>
                    </div>
                </div>
                <button @click="closeScanner()" class="text-slate-400 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- View 1: Camera Scanner Active -->
            <div x-show="!scannedProduct" class="space-y-4">
                <!-- Camera Viewport Box -->
                <div class="relative bg-slate-950 rounded-xl overflow-hidden border border-[#1f3344] min-h-[240px] flex items-center justify-center">
                    <div id="qr-reader" class="w-full"></div>
                    <div x-show="scanLoading" class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm flex flex-col items-center justify-center space-y-2 z-10">
                        <i class="fa-solid fa-circle-notch fa-spin text-emerald-400 text-2xl"></i>
                        <span class="text-xs text-emerald-400 font-semibold">Mencari data SKU di gudang...</span>
                    </div>
                </div>

                <!-- Scanner Status / Error Alert -->
                <div x-show="scannerError" class="p-3 bg-rose-500/10 border border-rose-500/30 rounded-xl text-xs text-rose-300 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-400"></i>
                    <span x-text="scannerError"></span>
                </div>

                <!-- Manual Input / USB Gun Input Fallback -->
                <div class="pt-2 border-t border-[#1f3344]/80">
                    <label class="block text-xs font-semibold text-slate-300 mb-1">
                        Input Manual atau Tembak Barcode Gun USB:
                    </label>
                    <form @submit.prevent="if(scannerManualInput) lookupBarcode(scannerManualInput)" class="flex gap-2">
                        <input type="text"
                               x-model="scannerManualInput"
                               placeholder="Scan / ketik SKU (contoh: MRP400)..."
                               class="flex-1 px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                        <button type="submit" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs rounded-xl transition">
                            Cari
                        </button>
                    </form>
                    <p class="text-[11px] text-slate-500 mt-1">
                        <i class="fa-solid fa-lightbulb text-amber-400"></i> Tip: Alat Barcode Scanner USB otomatis terdeteksi saat tombol ditekan.
                    </p>
                </div>
            </div>

            <!-- View 2: Scanned Product Found Card -->
            <div x-show="scannedProduct" class="space-y-4" style="display: none;">
                <!-- Product Overview Card -->
                <div class="p-4 bg-[#0e1922] border border-emerald-500/40 rounded-xl flex items-start gap-4">
                    <div class="w-16 h-16 rounded-lg bg-slate-900 border border-[#1f3344] p-1 flex-shrink-0 flex items-center justify-center">
                        <img :src="scannedProduct?.image_url" alt="" class="w-full h-full object-contain">
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-base text-white" x-text="scannedProduct?.sku"></span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#162532] text-slate-300 border border-[#22394c]" x-text="scannedProduct?.status_code"></span>
                        </div>
                        <div class="text-xs font-semibold text-cyan-400 truncate mt-0.5" x-text="scannedProduct?.title"></div>
                        <div class="text-[11px] text-slate-400 mt-1 flex flex-wrap gap-x-3">
                            <span>Kategori: <strong class="text-slate-300" x-text="scannedProduct?.category"></strong></span>
                            <span>Lokasi Rak: <strong class="text-emerald-400" x-text="scannedProduct?.warehouse_location"></strong></span>
                        </div>
                    </div>
                </div>

                <!-- Stock Counter Box -->
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="p-2.5 bg-[#162532] border border-[#1f3344] rounded-xl">
                        <div class="text-[10px] text-slate-400">Stok Saat Ini</div>
                        <div class="text-lg font-bold text-white mt-0.5" x-text="scannedProduct?.quantity + ' ' + (scannedProduct?.unit || 'pcs')"></div>
                    </div>
                    <div class="p-2.5 bg-[#162532] border border-[#1f3344] rounded-xl">
                        <div class="text-[10px] text-slate-400">Batch No.</div>
                        <div class="text-xs font-mono font-bold text-cyan-400 mt-1" x-text="scannedProduct?.batch_number"></div>
                    </div>
                    <div class="p-2.5 bg-[#162532] border border-[#1f3344] rounded-xl">
                        <div class="text-[10px] text-slate-400">Harga Satuan</div>
                        <div class="text-xs font-bold text-emerald-400 mt-1" x-text="scannedProduct?.formatted_price"></div>
                    </div>
                </div>

                <!-- Fast Stock In/Out Form for Staff -->
                <form :action="'{{ url('inventory') }}/' + scannedProduct?.id + '/adjust-stock'" method="POST" class="p-4 bg-[#162532] border border-[#1f3344] rounded-xl space-y-3">
                    @csrf
                    <div class="text-xs font-bold text-white flex items-center justify-between">
                        <span>Aksi Cepat Mutasi Fisik:</span>
                        <div class="flex items-center gap-2">
                            <label class="cursor-pointer text-[11px]">
                                <input type="radio" name="type" value="in" checked class="text-emerald-500">
                                <span class="text-emerald-400 font-bold ml-1">+ Masuk (In)</span>
                            </label>
                            <label class="cursor-pointer text-[11px]">
                                <input type="radio" name="type" value="out" class="text-rose-500">
                                <span class="text-rose-400 font-bold ml-1">- Keluar (Out)</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <input type="number" name="quantity" min="1" value="1" required class="w-24 px-3 py-1.5 bg-[#0e1922] border border-[#1f3344] rounded-xl text-center font-bold text-sm text-white focus:outline-none focus:border-emerald-500">
                        <input type="text" name="notes" placeholder="Catatan mutasi (contoh: restock rak)..." class="flex-1 px-3 py-1.5 bg-[#0e1922] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                        <button type="submit" class="px-4 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs rounded-xl shadow transition">
                            Simpan
                        </button>
                    </div>
                </form>

                <!-- Footer Buttons: Print Label & Scan Another -->
                <div class="flex items-center justify-between pt-2 border-t border-[#1f3344]">
                    <a :href="'{{ url('inventory') }}/' + scannedProduct?.id + '/print-label'" target="_blank" class="text-xs text-emerald-400 hover:text-emerald-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-print"></i>
                        <span>Cetak Label Stiker Barang Ini</span>
                    </a>

                    <button type="button" @click="resetScanView()" class="px-3.5 py-1.5 rounded-xl bg-[#162532] hover:bg-[#1f3344] text-xs font-bold text-slate-200 flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-rotate-right"></i>
                        <span>Scan Barang Lain</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Warehouse App Controller & Barcode Listener -->
    <script>
        function warehouseApp() {
            return {
                sidebarOpen: true,
                mobileSidebarOpen: false,
                scannerOpen: false,
                html5QrCode: null,
                scannedProduct: null,
                scanLoading: false,
                scannerManualInput: '',
                scannerError: '',

                init() {
                    // USB Hardware Barcode Gun Keystroke Buffer Listener
                    let buffer = '';
                    let lastKeyTime = Date.now();

                    window.addEventListener('keydown', (e) => {
                        // If typing inside an input/textarea, don't hijack unless it's an Enter from quick buffer
                        const isInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName);
                        const currentTime = Date.now();

                        // Barcode scanners type very rapidly (under 50ms per key)
                        if (currentTime - lastKeyTime > 100) {
                            buffer = '';
                        }
                        lastKeyTime = currentTime;

                        if (e.key === 'Enter') {
                            if (buffer.length >= 3 && !isInput) {
                                e.preventDefault();
                                this.openScanner();
                                this.lookupBarcode(buffer.trim());
                                buffer = '';
                            }
                        } else if (e.key.length === 1) {
                            buffer += e.key;
                        }
                    });
                },

                playBeep() {
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'sine';
                        osc.frequency.value = 920; // Scanner beep pitch
                        gain.gain.setValueAtTime(0.2, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.14);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.14);
                    } catch(e) {}
                },

                openScanner() {
                    this.scannerOpen = true;
                    this.scannedProduct = null;
                    this.scannerError = '';
                    this.scannerManualInput = '';

                    // Initialize Camera Scanner via html5-qrcode
                    this.$nextTick(() => {
                        if (!this.html5QrCode) {
                            this.html5QrCode = new Html5Qrcode("qr-reader");
                        }

                        const config = { fps: 10, qrbox: { width: 250, height: 180 } };
                        this.html5QrCode.start(
                            { facingMode: "environment" },
                            config,
                            (decodedText) => {
                                // Successful scan
                                this.playBeep();
                                this.lookupBarcode(decodedText);
                            },
                            (errorMessage) => {
                                // Frame scan fail (normal when scanning empty air)
                            }
                        ).catch(err => {
                            console.warn("Camera start failed, fallback to USB/manual mode", err);
                            this.scannerError = "Kamera tidak aktif atau tidak diizinkan. Anda tetap bisa menggunakan Barcode Gun USB atau input manual.";
                        });
                    });
                },

                closeScanner() {
                    this.scannerOpen = false;
                    if (this.html5QrCode && this.html5QrCode.isScanning) {
                        this.html5QrCode.stop().then(() => {
                            this.html5QrCode.clear();
                        }).catch(err => console.error(err));
                    }
                },

                resetScanView() {
                    this.scannedProduct = null;
                    this.scannerError = '';
                    this.scannerManualInput = '';
                    if (this.html5QrCode && !this.html5QrCode.isScanning) {
                        this.openScanner();
                    }
                },

                lookupBarcode(code) {
                    if (!code) return;
                    this.scanLoading = true;
                    this.scannerError = '';

                    fetch(`{{ url('inventory/lookup') }}/${encodeURIComponent(code)}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Barang dengan SKU/Barcode "' + code + '" tidak ditemukan.');
                        }
                        return response.json();
                    })
                    .then(data => {
                        this.scanLoading = false;
                        if (data.found) {
                            this.scannedProduct = data.product;
                            // Pause camera while reviewing product
                            if (this.html5QrCode && this.html5QrCode.isScanning) {
                                this.html5QrCode.stop().catch(e => console.error(e));
                            }
                        }
                    })
                    .catch(err => {
                        this.scanLoading = false;
                        this.scannerError = err.message;
                    });
                }
            }
        }
    </script>

    @stack('scripts')
</body>
</html>
