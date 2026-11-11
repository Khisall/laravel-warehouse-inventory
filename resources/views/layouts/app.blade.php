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
<body x-data="{ sidebarOpen: true, mobileSidebarOpen: false }">
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

            <!-- Top Right Profile & Notifications -->
            <div class="flex items-center space-x-4">
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

                <!-- User Profile Avatar -->
                <div class="flex items-center space-x-3 pl-2 border-l border-[#1f3344]">
                    <div class="relative group cursor-pointer">
                        <div class="w-9 h-9 rounded-full ring-2 ring-emerald-500/40 overflow-hidden bg-slate-800">
                            <img src="{{ asset('images/avatar.svg') }}" alt="User Profile" class="w-full h-full object-cover">
                        </div>
                        <div class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-400 ring-2 ring-[#0d1821]"></div>
                    </div>
                    <div class="hidden xl:block text-left">
                        <div class="text-xs font-semibold text-white">Gudang Admin</div>
                        <div class="text-[11px] text-emerald-400">Warehouse Lead</div>
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

                        <!-- Reports -->
                        <a href="{{ route('reports.index') }}"
                           class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition group {{ request()->routeIs('reports.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532] hover:text-slate-200' }}">
                            <div class="w-5 text-center mr-3 text-base {{ request()->routeIs('reports.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">
                                <i class="fa-solid fa-chart-pie"></i>
                            </div>
                            <span :class="sidebarOpen ? 'block' : 'hidden'">Reports</span>
                        </a>
                    </nav>
                </div>

                <!-- Secondary Menu (Bottom) -->
                <div class="space-y-1 px-2.5 pt-4 border-t border-[#1f3344]/80">
                    <!-- Settings -->
                    <a href="{{ route('settings.index') }}"
                       class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition group {{ request()->routeIs('settings.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532] hover:text-slate-200' }}">
                        <div class="w-5 text-center mr-3 text-base {{ request()->routeIs('settings.*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">
                            <i class="fa-solid fa-gear"></i>
                        </div>
                        <span :class="sidebarOpen ? 'block' : 'hidden'">Settings</span>
                    </a>

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
                    <a href="#" onclick="alert('Anda login sebagai Demo Administrator'); return false;"
                       class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 hover:bg-rose-500/10 hover:text-rose-400 transition group">
                        <div class="w-5 text-center mr-3 text-base text-slate-400 group-hover:text-rose-400">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </div>
                        <span :class="sidebarOpen ? 'block' : 'hidden'">Log Out</span>
                    </a>
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
                            <a href="{{ route('reports.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('reports.*') ? 'nav-item-active' : 'text-slate-400 hover:bg-[#162532]' }}">
                                <i class="fa-solid fa-chart-pie w-6 text-emerald-400"></i> Reports
                            </a>
                        </nav>
                    </div>

                    <div class="pt-4 border-t border-[#1f3344] space-y-1">
                        <a href="{{ route('activity.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-[#162532]">
                            <i class="fa-solid fa-clock-rotate-left w-6"></i> Activity & Inventory
                        </a>
                        <a href="{{ route('settings.index') }}" class="flex items-center px-3 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-[#162532]">
                            <i class="fa-solid fa-gear w-6"></i> Settings
                        </a>
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

    @stack('scripts')
</body>
</html>
