@extends('layouts.app')

@section('title', 'Dashboard - Inventory')

@section('content')
<div x-data="{
    showAddModal: false,
    showEditModal: false,
    showStockModal: false,
    showFilterModal: false,
    showMarketplaceModal: false,
    editItem: {},
    stockItem: {},
    selectedIds: [],
    selectAll: false,
    toggleAll() {
        if (this.selectAll) {
            this.selectedIds = [{{ $products->pluck('id')->implode(',') }}];
        } else {
            this.selectedIds = [];
        }
    },
    openEdit(item) {
        this.editItem = JSON.parse(JSON.stringify(item));
        this.showEditModal = true;
    },
    openStock(item, defaultType = 'in') {
        this.stockItem = JSON.parse(JSON.stringify(item));
        this.stockItem.type = defaultType;
        this.stockItem.amount = 1;
        this.stockItem.notes = '';
        this.stockItem.ref = '';
        this.showStockModal = true;
    }
}" class="space-y-6">

    <!-- KPI Summary Row -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
        <div class="bg-[#13202b] border border-[#1f3344] rounded-xl p-4 hover:border-emerald-500/40 transition">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-1.5">
                <span>Total SKU / Items</span>
                <i class="fa-solid fa-boxes-stacked text-emerald-400"></i>
            </div>
            <div class="text-2xl font-bold text-white tracking-tight">{{ number_format($totalItems) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Barang terdaftar</div>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-xl p-4 hover:border-emerald-500/40 transition">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-1.5">
                <span>Total Stok Gudang</span>
                <i class="fa-solid fa-layer-group text-emerald-400"></i>
            </div>
            <div class="text-2xl font-bold text-emerald-400 tracking-tight">{{ number_format($totalStockQty) }} <span class="text-xs font-normal text-slate-400">unit</span></div>
            <div class="text-[11px] text-slate-400 mt-1">Siap dikirim</div>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-xl p-4 hover:border-emerald-500/40 transition">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-1.5">
                <span>Valuasi Inventaris</span>
                <i class="fa-solid fa-vault text-emerald-400"></i>
            </div>
            <div class="text-2xl font-bold text-white tracking-tight">${{ number_format($totalValuation, 2) }}</div>
            <div class="text-[11px] text-emerald-400 mt-1">Nilai aset total</div>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-xl p-4 hover:border-emerald-500/40 transition">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-1.5">
                <span>Stok Menipis</span>
                <i class="fa-solid fa-triangle-exclamation text-amber-400"></i>
            </div>
            <div class="text-2xl font-bold text-amber-400 tracking-tight">{{ number_format($lowStockCount) }}</div>
            <div class="text-[11px] text-amber-400/80 mt-1">Perlu restock segera</div>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-xl p-4 hover:border-emerald-500/40 transition">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-1.5">
                <span>Habis (Out of Stock)</span>
                <i class="fa-solid fa-circle-xmark text-rose-400"></i>
            </div>
            <div class="text-2xl font-bold text-rose-400 tracking-tight">{{ number_format($outOfStockCount) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Stok kosong (0)</div>
        </div>

        <div class="bg-[#13202b] border border-[#1f3344] rounded-xl p-4 hover:border-emerald-500/40 transition">
            <div class="flex items-center justify-between text-slate-400 text-xs mb-1.5">
                <span>Marketplace Terhubung</span>
                <i class="fa-solid fa-globe text-cyan-400"></i>
            </div>
            <div class="text-2xl font-bold text-cyan-400 tracking-tight">{{ number_format($marketplaceActive) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Channel tersinkron</div>
        </div>
    </div>

    <!-- Main Dashboard Bar (Matching the screenshot header) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-2">
        <div class="flex items-center space-x-3">
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">Dashboard</h1>
            @if(request('category') || request('warehouse') || request('status_code') || request('search'))
                <a href="{{ route('inventory.index') }}" class="text-xs px-2.5 py-1 rounded-full bg-[#162532] text-slate-400 hover:text-white border border-[#1f3344] flex items-center gap-1">
                    <span>Clear Filter</span>
                    <i class="fa-solid fa-xmark text-[10px]"></i>
                </a>
            @endif
        </div>

        <!-- Right Action Buttons (Matching Action >, List on Marketplace >, Add New >) -->
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Action Dropdown Button -->
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button @click="open = !open"
                        type="button"
                        class="px-4 py-2 rounded-xl bg-[#14222e] hover:bg-[#192b3b] border border-[#1f3344] text-slate-200 text-sm font-semibold flex items-center gap-2.5 transition">
                    <span>Action</span>
                    <i class="fa-solid fa-chevron-right text-xs text-slate-400 transition-transform" :class="open ? 'rotate-90' : ''"></i>
                </button>

                <div x-show="open"
                     x-transition
                     style="display: none;"
                     class="absolute right-0 mt-2 w-56 rounded-xl bg-[#13202b] border border-[#1f3344] shadow-2xl py-2 z-50">
                    <div class="px-3 py-1.5 text-[11px] font-bold text-slate-400 uppercase tracking-wider">Aksi Massal</div>
                    <button type="button"
                            @click="document.getElementById('bulk-form-action').value = 'print_labels'; document.getElementById('bulk-form').submit();"
                            class="w-full text-left px-3 py-2 text-xs text-slate-300 hover:bg-[#192b3b] hover:text-emerald-400 flex items-center gap-2">
                        <i class="fa-solid fa-barcode text-emerald-400"></i>
                        <span>Cetak Label Barcode Terpilih</span>
                    </button>
                    <button type="button"
                            @click="document.getElementById('bulk-form-action').value = 'marketplace_list'; document.getElementById('bulk-form').submit();"
                            class="w-full text-left px-3 py-2 text-xs text-slate-300 hover:bg-[#192b3b] hover:text-emerald-400 flex items-center gap-2">
                        <i class="fa-solid fa-share-nodes text-emerald-400"></i>
                        <span>Daftarkan ke Marketplace</span>
                    </button>
                    <button type="button"
                            @click="document.getElementById('bulk-form-action').value = 'mark_acco'; document.getElementById('bulk-form').submit();"
                            class="w-full text-left px-3 py-2 text-xs text-slate-300 hover:bg-[#192b3b] hover:text-emerald-400 flex items-center gap-2">
                        <i class="fa-solid fa-tag text-emerald-400"></i>
                        <span>Ubah Status ke ACCO</span>
                    </button>
                    <button type="button"
                            @click="document.getElementById('bulk-form-action').value = 'mark_11se'; document.getElementById('bulk-form').submit();"
                            class="w-full text-left px-3 py-2 text-xs text-slate-300 hover:bg-[#192b3b] hover:text-emerald-400 flex items-center gap-2">
                        <i class="fa-solid fa-tag text-emerald-400"></i>
                        <span>Ubah Status ke 11SE</span>
                    </button>
                    <div class="border-t border-[#1f3344] my-1"></div>
                    <a href="{{ route('inventory.export-csv') }}" class="w-full text-left px-3 py-2 text-xs text-slate-300 hover:bg-[#192b3b] hover:text-emerald-400 flex items-center gap-2">
                        <i class="fa-solid fa-file-csv text-emerald-400"></i>
                        <span>Export ke CSV (Excel)</span>
                    </a>
                    <button type="button"
                            @click="if(confirm('Yakin ingin menghapus barang terpilih?')) { document.getElementById('bulk-form-action').value = 'delete'; document.getElementById('bulk-form').submit(); }"
                            class="w-full text-left px-3 py-2 text-xs text-rose-400 hover:bg-rose-500/10 flex items-center gap-2">
                        <i class="fa-solid fa-trash-can"></i>
                        <span>Hapus Terpilih</span>
                    </button>
                </div>
            </div>

            <!-- List on Marketplace Button -->
            <button @click="showMarketplaceModal = true"
                    type="button"
                    class="px-4 py-2 rounded-xl bg-[#14222e] hover:bg-[#192b3b] border border-[#1f3344] text-slate-200 text-sm font-semibold flex items-center gap-2.5 transition">
                <span>List on Marketplace</span>
                <i class="fa-solid fa-chevron-right text-xs text-slate-400"></i>
            </button>

            <!-- Add New Button -->
            <button @click="showAddModal = true"
                    type="button"
                    class="px-4 py-2 rounded-xl bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 border border-emerald-500/40 text-sm font-bold flex items-center gap-2.5 transition shadow-lg shadow-emerald-500/10">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Add New</span>
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </button>
        </div>
    </div>

    <!-- Quick Filter Bar -->
    <div class="flex flex-wrap items-center justify-between gap-3 bg-[#13202b] border border-[#1f3344] rounded-xl p-3">
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="text-slate-400 font-semibold px-2">Filter:</span>
            
            <!-- Category Filter -->
            <form action="{{ route('inventory.index') }}" method="GET" class="inline">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="warehouse" value="{{ request('warehouse') }}">
                <input type="hidden" name="status_code" value="{{ request('status_code') }}">
                <select name="category" onchange="this.form.submit()" class="bg-[#0e1922] border border-[#1f3344] rounded-lg px-2.5 py-1.5 text-slate-300 focus:outline-none focus:border-emerald-500">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }} ({{ $cat->products_count }})
                        </option>
                    @endforeach
                </select>
            </form>

            <!-- Warehouse Filter -->
            <form action="{{ route('inventory.index') }}" method="GET" class="inline">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="category" value="{{ request('category') }}">
                <input type="hidden" name="status_code" value="{{ request('status_code') }}">
                <select name="warehouse" onchange="this.form.submit()" class="bg-[#0e1922] border border-[#1f3344] rounded-lg px-2.5 py-1.5 text-slate-300 focus:outline-none focus:border-emerald-500">
                    <option value="">Semua Lokasi Gudang</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse') == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }} - {{ $wh->location }}
                        </option>
                    @endforeach
                </select>
            </form>

            <!-- Status Code Filter -->
            <form action="{{ route('inventory.index') }}" method="GET" class="inline">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="category" value="{{ request('category') }}">
                <input type="hidden" name="warehouse" value="{{ request('warehouse') }}">
                <select name="status_code" onchange="this.form.submit()" class="bg-[#0e1922] border border-[#1f3344] rounded-lg px-2.5 py-1.5 text-slate-300 focus:outline-none focus:border-emerald-500">
                    <option value="">Semua Status Channel</option>
                    <option value="ACCO" {{ request('status_code') == 'ACCO' ? 'selected' : '' }}>ACCO</option>
                    <option value="11SE" {{ request('status_code') == '11SE' ? 'selected' : '' }}>11SE</option>
                    <option value="CACO" {{ request('status_code') == 'CACO' ? 'selected' : '' }}>CACO</option>
                </select>
            </form>
        </div>

        <div class="text-xs text-slate-400">
            Menampilkan <span class="font-bold text-white">{{ $products->count() }}</span> dari <span class="font-bold text-emerald-400">{{ $totalItems }}</span> total barang
        </div>
    </div>

    <!-- Inventory Table Container (Matching the Figma Screenshot) -->
    <div class="table-container overflow-hidden">
        <form id="bulk-form" action="{{ route('inventory.bulk-action') }}" method="POST">
            @csrf
            <input type="hidden" name="bulk_action" id="bulk-form-action" value="">

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <!-- Table Header -->
                    <thead>
                        <tr class="border-b border-[#1f3344] bg-[#14232e] text-slate-400 font-semibold text-xs tracking-wider">
                            <th class="py-3.5 px-4 w-10 text-center">
                                <input type="checkbox"
                                       x-model="selectAll"
                                       @change="toggleAll()"
                                       class="w-4 h-4 rounded bg-[#0e1922] border-[#294255] text-emerald-500 focus:ring-emerald-500/30 focus:ring-offset-0 cursor-pointer">
                            </th>
                            <th class="py-3.5 px-4">
                                <a href="{{ route('inventory.index', array_merge(request()->query(), ['sort_by' => 'sku', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1.5 hover:text-white">
                                    <span>SKU</span>
                                    <i class="fa-solid fa-sort text-[10px] text-slate-500"></i>
                                </a>
                            </th>
                            <th class="py-3.5 px-4 w-16 text-center">Image</th>
                            <th class="py-3.5 px-4 min-w-[200px]">
                                <a href="{{ route('inventory.index', array_merge(request()->query(), ['sort_by' => 'title', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1.5 hover:text-white">
                                    <span>Title</span>
                                    <i class="fa-solid fa-sort text-[10px] text-slate-500"></i>
                                </a>
                            </th>
                            <th class="py-3.5 px-4">Category</th>
                            <th class="py-3.5 px-4 text-center">
                                <a href="{{ route('inventory.index', array_merge(request()->query(), ['sort_by' => 'quantity', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center justify-center gap-1.5 hover:text-white">
                                    <span>QTY</span>
                                    <i class="fa-solid fa-sort text-[10px] text-slate-500"></i>
                                </a>
                            </th>
                            <th class="py-3.5 px-4">Warehouse</th>
                            <th class="py-3.5 px-4">
                                <a href="{{ route('inventory.index', array_merge(request()->query(), ['sort_by' => 'price', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1.5 hover:text-white">
                                    <span>Price</span>
                                    <i class="fa-solid fa-sort text-[10px] text-slate-500"></i>
                                </a>
                            </th>
                            <th class="py-3.5 px-4 min-w-[120px]">
                                <a href="{{ route('inventory.index', array_merge(request()->query(), ['sort_by' => 'last_modified', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1.5 hover:text-white">
                                    <span>Last Modified</span>
                                    <i class="fa-solid fa-sort text-[10px] text-slate-500"></i>
                                </a>
                            </th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-center w-20">
                                <span title="Filter & Aksi"><i class="fa-solid fa-filter text-slate-400"></i></span>
                            </th>
                        </tr>
                    </thead>

                    <!-- Table Body -->
                    <tbody class="divide-y divide-[#1f3344]/80 text-slate-200">
                        @forelse($products as $product)
                            <tr class="hover:bg-[#182835]/70 transition-colors group">
                                <!-- Checkbox -->
                                <td class="py-3.5 px-4 text-center">
                                    <input type="checkbox"
                                           name="selected_ids[]"
                                           value="{{ $product->id }}"
                                           x-model="selectedIds"
                                           class="w-4 h-4 rounded bg-[#0e1922] border-[#294255] text-emerald-500 focus:ring-emerald-500/30 focus:ring-offset-0 cursor-pointer">
                                </td>

                                <!-- SKU & Variation -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-bold text-white tracking-wide">{{ $product->sku }}</div>
                                    @if($product->variation_text)
                                        <div class="text-[11px] text-emerald-400 font-medium mt-0.5">
                                            {{ $product->variation_text }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Image -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="w-12 h-12 mx-auto rounded-lg overflow-hidden bg-[#0e1922] border border-[#1f3344] p-1 flex items-center justify-center group-hover:scale-105 transition-transform">
                                        <img src="{{ $product->image_url }}"
                                             alt="{{ $product->title }}"
                                             class="w-full h-full object-contain">
                                    </div>
                                </td>

                                <!-- Title -->
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-cyan-400 hover:text-cyan-300 transition cursor-pointer"
                                         @click="openEdit({{ $product->toJson() }})">
                                        {{ $product->title }}
                                    </div>
                                    @if($product->description)
                                        <div class="text-xs text-slate-400 truncate max-w-xs mt-0.5">
                                            {{ $product->description }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Category -->
                                <td class="py-3.5 px-4 whitespace-nowrap text-slate-300">
                                    {{ $product->category?->name ?? 'Uncategorized' }}
                                </td>

                                <!-- QTY -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                        {{ $product->quantity <= 0 ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : ($product->quantity <= $product->min_stock ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-[#182835] text-slate-200 border border-[#22394c]') }}">
                                        {{ $product->quantity }}
                                    </span>
                                </td>

                                <!-- Warehouse Location -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="text-xs font-medium {{ str_contains(strtolower($product->warehouse_location), 'multiple') ? 'text-emerald-400' : 'text-slate-300' }}">
                                        {{ $product->warehouse_location }}
                                    </span>
                                </td>

                                <!-- Price -->
                                <td class="py-3.5 px-4 whitespace-nowrap font-semibold text-slate-200">
                                    ${{ number_format($product->price, 2) }}
                                </td>

                                <!-- Last Modified -->
                                <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-400 leading-snug">
                                    @if($product->last_modified)
                                        <div>{{ $product->last_modified->format('g.i A') }}</div>
                                        <div class="text-slate-500">{{ $product->last_modified->format('M d, Y') }}</div>
                                    @else
                                        <div>{{ $product->updated_at->format('g.i A') }}</div>
                                        <div class="text-slate-500">{{ $product->updated_at->format('M d, Y') }}</div>
                                    @endif
                                </td>

                                <!-- Status Code -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="text-xs font-bold tracking-wider text-slate-300 bg-[#162532] px-2.5 py-1 rounded-md border border-[#223849]">
                                        {{ $product->status_code }}
                                    </span>
                                </td>

                                <!-- Row Actions -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center space-x-1.5 opacity-80 group-hover:opacity-100 transition">
                                        <!-- Print Barcode Label -->
                                        <a href="{{ route('inventory.print-label', $product) }}"
                                           target="_blank"
                                           title="Cetak Label Barcode & QR"
                                           class="w-7 h-7 rounded-lg bg-[#182835] hover:bg-amber-500/20 text-amber-400 border border-[#233b4e] flex items-center justify-center transition">
                                            <i class="fa-solid fa-barcode text-xs"></i>
                                        </a>

                                        <!-- Quick Stock Intake/Out -->
                                        <button type="button"
                                                @click="openStock({{ $product->toJson() }}, 'in')"
                                                title="Mutasi Stok Cepat"
                                                class="w-7 h-7 rounded-lg bg-[#182835] hover:bg-emerald-500/20 text-emerald-400 border border-[#233b4e] flex items-center justify-center transition">
                                            <i class="fa-solid fa-plus-minus text-xs"></i>
                                        </button>

                                        <!-- Edit -->
                                        <button type="button"
                                                @click="openEdit({{ $product->toJson() }})"
                                                title="Edit Barang"
                                                class="w-7 h-7 rounded-lg bg-[#182835] hover:bg-cyan-500/20 text-cyan-400 border border-[#233b4e] flex items-center justify-center transition">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </button>

                                        <!-- Delete -->
                                        <button type="button"
                                                @click="if(confirm('Hapus {{ $product->sku }} - {{ addslashes($product->title) }}?')) { document.getElementById('delete-form-{{ $product->id }}').submit(); }"
                                                title="Hapus Barang"
                                                class="w-7 h-7 rounded-lg bg-[#182835] hover:bg-rose-500/20 text-rose-400 border border-[#233b4e] flex items-center justify-center transition">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-12 text-center text-slate-500">
                                    <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-[#162532] flex items-center justify-center text-slate-400 text-2xl">
                                        <i class="fa-solid fa-box-open"></i>
                                    </div>
                                    <div class="text-base font-semibold text-slate-300">Tidak ada data inventaris ditemukan</div>
                                    <div class="text-xs mt-1">Coba sesuaikan kata kunci pencarian atau filter yang aktif.</div>
                                    <button type="button" @click="showAddModal = true" class="mt-4 px-4 py-2 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 text-xs font-bold">
                                        + Tambah Barang Pertama
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <!-- Hidden delete forms -->
        @foreach($products as $product)
            <form id="delete-form-{{ $product->id }}" action="{{ route('inventory.destroy', $product) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endforeach

        <!-- Pagination Bar -->
        @if($products->hasPages())
            <div class="px-6 py-4 border-t border-[#1f3344] bg-[#101b24] flex items-center justify-between">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    <!-- ==================== MODAL: ADD NEW ITEM ==================== -->
    <div x-show="showAddModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/75 backdrop-blur-sm flex items-center justify-center p-4"
         style="display: none;">

        <div @click.away="showAddModal = false" class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-2xl w-full p-6 shadow-2xl space-y-5 text-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f3344]">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-white">Tambah Barang Inventaris Baru</h3>
                        <p class="text-xs text-slate-400">Masukkan detail barang untuk disimpan ke gudang</p>
                    </div>
                </div>
                <button @click="showAddModal = false" class="text-slate-400 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('inventory.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- SKU with Auto Generator -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">SKU <span class="text-rose-400">*</span></label>
                        <div class="flex space-x-2">
                            <input type="text"
                                   id="new_sku_input"
                                   name="sku"
                                   required
                                   placeholder="Contoh: MRP500"
                                   class="flex-1 px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                            <button type="button"
                                    onclick="document.getElementById('new_sku_input').value = 'MRP' + Math.floor(100 + Math.random() * 900);"
                                    class="px-2.5 py-1.5 rounded-xl bg-[#192b3a] border border-[#1f3344] text-[11px] text-emerald-400 hover:bg-emerald-500/10 font-bold">
                                Auto
                            </button>
                        </div>
                    </div>

                    <!-- Title -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Title / Nama Barang <span class="text-rose-400">*</span></label>
                        <input type="text"
                               name="title"
                               required
                               placeholder="Contoh: Winter Jacket Leather"
                               class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Category</label>
                        <select name="category_id" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Warehouse Location -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Warehouse Location <span class="text-rose-400">*</span></label>
                        <input type="text"
                               name="warehouse_location"
                               required
                               placeholder="Contoh: Location A: 5 atau multiple 22"
                               class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Jumlah Stok Masuk (QTY) <span class="text-rose-400">*</span></label>
                        <input type="number"
                               name="quantity"
                               min="0"
                               required
                               value="10"
                               class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <!-- Min Stock Alert -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Min. Stock Alert</label>
                        <input type="number"
                               name="min_stock"
                               min="0"
                               value="5"
                               class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <!-- Price -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Price (Harga Jual $) <span class="text-rose-400">*</span></label>
                        <input type="number"
                               step="0.01"
                               name="price"
                               required
                               placeholder="223.20"
                               class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <!-- Cost Price -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Cost Price (Harga Modal $)</label>
                        <input type="number"
                               step="0.01"
                               name="cost_price"
                               placeholder="130.00"
                               class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <!-- Status Code (ACCO, 11SE, CACO) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Status Code <span class="text-rose-400">*</span></label>
                        <input type="text"
                               name="status_code"
                               required
                               value="ACCO"
                               placeholder="ACCO / 11SE / CACO"
                               class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500 uppercase">
                    </div>

                    <!-- Variation Text -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Variation Text</label>
                        <input type="text"
                               name="variation_text"
                               placeholder="Contoh: Variation (4)"
                               class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <!-- Batch Number & Expiry Date (FIFO) -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Batch / Lot Number</label>
                        <input type="text"
                               name="batch_number"
                               placeholder="Contoh: BCH-2024-001"
                               class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Tanggal Kedaluwarsa (Jika ada)</label>
                        <input type="date"
                               name="expiry_date"
                               class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <!-- Image Upload -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Foto Barang (Opsional)</label>
                    <input type="file"
                           name="image"
                           accept="image/*"
                           class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-xs text-slate-400 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-500/20 file:text-emerald-400 hover:file:bg-emerald-500/30">
                    <p class="text-[11px] text-slate-500 mt-1">Jika dikosongkan, gambar otomatis disesuaikan dengan jenis produk.</p>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan / Deskripsi</label>
                    <textarea name="description"
                              rows="2"
                              placeholder="Deskripsi spesifikasi barang, nomor batch, rak..."
                              class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <!-- Checkbox Marketplace -->
                <div class="flex items-center space-x-2">
                    <input type="checkbox"
                           id="is_marketplace_listed_add"
                           name="is_marketplace_listed"
                           value="1"
                           class="w-4 h-4 rounded bg-[#0e1922] border-[#294255] text-emerald-500 focus:ring-0">
                    <label for="is_marketplace_listed_add" class="text-xs text-slate-300">
                        Otomatis sinkronkan & tayangkan di Marketplace (Shopee / Tokopedia)
                    </label>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-[#1f3344]">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl bg-[#162532] text-slate-300 hover:bg-[#1c3042] text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20">
                        Simpan ke Gudang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL: EDIT ITEM ==================== -->
    <div x-show="showEditModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/75 backdrop-blur-sm flex items-center justify-center p-4"
         style="display: none;">

        <div @click.away="showEditModal = false" class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-2xl w-full p-6 shadow-2xl space-y-5 text-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f3344]">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-cyan-500/20 text-cyan-400 flex items-center justify-center">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-white">Edit Data Barang: <span class="text-cyan-400" x-text="editItem.sku"></span></h3>
                        <p class="text-xs text-slate-400" x-text="editItem.title"></p>
                    </div>
                </div>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="'{{ url('inventory') }}/' + editItem.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">SKU</label>
                        <input type="text" name="sku" required x-model="editItem.sku" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Title</label>
                        <input type="text" name="title" required x-model="editItem.title" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Category</label>
                        <select name="category_id" x-model="editItem.category_id" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Warehouse Location</label>
                        <input type="text" name="warehouse_location" required x-model="editItem.warehouse_location" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Stok QTY</label>
                        <input type="number" name="quantity" min="0" required x-model="editItem.quantity" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Price ($)</label>
                        <input type="number" step="0.01" name="price" required x-model="editItem.price" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Status Code</label>
                        <input type="text" name="status_code" required x-model="editItem.status_code" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500 uppercase">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Variation Text</label>
                        <input type="text" name="variation_text" x-model="editItem.variation_text" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Batch / Lot Number</label>
                        <input type="text" name="batch_number" x-model="editItem.batch_number" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Tanggal Kedaluwarsa</label>
                        <input type="date" name="expiry_date" x-model="editItem.expiry_date" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Ganti Foto Barang</label>
                    <input type="file" name="image" accept="image/*" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-xs text-slate-400 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-cyan-500/20 file:text-cyan-400">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" x-model="editItem.description" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-[#1f3344]">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl bg-[#162532] text-slate-300 hover:bg-[#1c3042] text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-600 text-slate-950 font-bold text-xs shadow-lg shadow-cyan-500/20">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL: QUICK STOCK IN/OUT ==================== -->
    <div x-show="showStockModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/75 backdrop-blur-sm flex items-center justify-center p-4"
         style="display: none;">

        <div @click.away="showStockModal = false" class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5 text-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f3344]">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                        <i class="fa-solid fa-arrow-right-arrow-left"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-white">Mutasi Stok Cepat</h3>
                        <p class="text-xs text-slate-400"><span class="text-emerald-400" x-text="stockItem.sku"></span>: <span x-text="stockItem.title"></span></p>
                    </div>
                </div>
                <button @click="showStockModal = false" class="text-slate-400 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form :action="'{{ url('inventory') }}/' + stockItem.id + '/adjust-stock'" method="POST" class="space-y-4">
                @csrf

                <!-- Type Selector -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Jenis Mutasi</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="in" x-model="stockItem.type" class="sr-only">
                            <div :class="stockItem.type === 'in' ? 'bg-emerald-500/20 border-emerald-500 text-emerald-300' : 'bg-[#0e1922] border-[#1f3344] text-slate-400'"
                                 class="p-2.5 rounded-xl border text-center text-xs font-bold transition flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-arrow-down text-emerald-400"></i> Barang Masuk (+In)
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="out" x-model="stockItem.type" class="sr-only">
                            <div :class="stockItem.type === 'out' ? 'bg-rose-500/20 border-rose-500 text-rose-300' : 'bg-[#0e1922] border-[#1f3344] text-slate-400'"
                                 class="p-2.5 rounded-xl border text-center text-xs font-bold transition flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-arrow-up text-rose-400"></i> Barang Keluar (-Out)
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Quantity -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">
                        Jumlah (<span x-text="stockItem.type === 'in' ? 'Ditambah ke stok' : 'Dikeluarkan dari stok'"></span>)
                    </label>
                    <input type="number"
                           name="quantity"
                           min="1"
                           required
                           x-model="stockItem.amount"
                           class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-lg font-bold text-white text-center focus:outline-none focus:border-emerald-500">
                    <div class="text-[11px] text-slate-400 mt-1 flex justify-between">
                        <span>Stok Saat Ini: <strong class="text-white" x-text="stockItem.quantity"></strong></span>
                        <span>Estimasi Stok Baru: <strong class="text-emerald-400" x-text="stockItem.type === 'in' ? (parseInt(stockItem.quantity || 0) + parseInt(stockItem.amount || 0)) : (parseInt(stockItem.quantity || 0) - parseInt(stockItem.amount || 0))"></strong></span>
                    </div>
                </div>

                <!-- Reference & Notes -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Nomor Referensi / Surat Jalan</label>
                    <input type="text"
                           name="reference_no"
                           placeholder="Contoh: PO-2026-08 atau SJ-9921"
                           class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Keterangan</label>
                    <input type="text"
                           name="notes"
                           placeholder="Contoh: Penerimaan supplier baru / Retur..."
                           class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-[#1f3344]">
                    <button type="button" @click="showStockModal = false" class="px-4 py-2 rounded-xl bg-[#162532] text-slate-300 hover:bg-[#1c3042] text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20">
                        Konfirmasi Mutasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== MODAL: LIST ON MARKETPLACE ==================== -->
    <div x-show="showMarketplaceModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/75 backdrop-blur-sm flex items-center justify-center p-4"
         style="display: none;">

        <div @click.away="showMarketplaceModal = false" class="bg-[#13202b] border border-[#1f3344] rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5 text-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-[#1f3344]">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-base text-white">List on Marketplace</h3>
                        <p class="text-xs text-slate-400">Sinkronkan stok gudang ke channel e-commerce</p>
                    </div>
                </div>
                <button @click="showMarketplaceModal = false" class="text-slate-400 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('marketplace.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Pilih Produk dari Gudang</label>
                    <select name="product_id" required class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->sku }} - {{ $p->title }} (Stok: {{ $p->quantity }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Marketplace Target</label>
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
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Channel Price ($)</label>
                        <input type="number" step="0.01" name="channel_price" required value="223.20" class="w-full px-3 py-2 bg-[#0e1922] border border-[#1f3344] rounded-xl text-sm text-white focus:outline-none focus:border-emerald-500">
                    </div>
                </div>

                <div class="p-3 bg-[#0e1922] rounded-xl border border-[#1f3344] text-xs text-slate-400 space-y-1">
                    <div class="flex items-center gap-1.5 text-emerald-400 font-semibold">
                        <i class="fa-solid fa-bolt"></i> Auto-Stock Sync Enabled
                    </div>
                    <p>Setiap terjadi penjualan di Marketplace atau pergerakan stok di gudang fisik, kuantiti stok akan otomatis sinkron.</p>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-[#1f3344]">
                    <button type="button" @click="showMarketplaceModal = false" class="px-4 py-2 rounded-xl bg-[#162532] text-slate-300 hover:bg-[#1c3042] text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-slate-950 font-bold text-xs shadow-lg shadow-orange-500/20">
                        Publish ke Marketplace
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
