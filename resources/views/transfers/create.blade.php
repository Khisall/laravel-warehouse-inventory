@extends('layouts.app')

@section('title', 'Buat Transfer Antar Gudang')

@section('content')
<div class="max-w-5xl mx-auto space-y-6"
     x-data="transferForm({
         warehouses: {{ Js::from($warehouses) }},
         oldSource: '{{ old('source_warehouse_id', request('source_id', '')) }}',
         oldDest: '{{ old('destination_warehouse_id', request('dest_id', '')) }}'
     })"
     x-init="init()">

    <!-- Header Breadcrumb -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('transfers.index') }}" class="hover:text-emerald-400 transition flex items-center gap-1">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Daftar Transfer</span>
                </a>
                <span>/</span>
                <span class="text-slate-200">Buat Mutasi Baru</span>
            </div>
            <h1 class="text-xl font-bold text-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-truck-ramp-box text-emerald-400"></i>
                <span>Pengajuan Mutasi Stok Antar Gudang</span>
            </h1>
        </div>
    </div>

    <!-- Error Validation Alert -->
    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs space-y-1">
            <div class="font-bold flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>Terdapat kesalahan pada formulir pengajuan:</span>
            </div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('transfers.store') }}" @submit="validateSubmit($event)">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left 2 Cols: Warehouse Route & Item List -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Warehouse Selection Card -->
                <div class="bg-[#13202b] border border-slate-800 rounded-2xl p-5 space-y-4">
                    <h3 class="text-sm font-bold text-slate-200 flex items-center gap-2 border-b border-slate-800 pb-3">
                        <i class="fa-solid fa-route text-emerald-400"></i>
                        <span>Rute Pengiriman Antar Gudang</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Source Warehouse -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Gudang Asal (Pengirim) <span class="text-rose-400">*</span>
                            </label>
                            <select name="source_warehouse_id"
                                    x-model="sourceWarehouseId"
                                    @change="onSourceChange()"
                                    required
                                    class="w-full px-3.5 py-2.5 bg-[#0b141b] border border-slate-700 rounded-xl text-xs text-slate-100 focus:outline-none focus:border-emerald-500">
                                <option value="">-- Pilih Gudang Asal --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">
                                        {{ $wh->name }} ({{ $wh->code }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Stok akan dipotong dari gudang ini saat keberangkatan.</p>
                        </div>

                        <!-- Destination Warehouse -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Gudang Tujuan (Penerima) <span class="text-rose-400">*</span>
                            </label>
                            <select name="destination_warehouse_id"
                                    x-model="destinationWarehouseId"
                                    required
                                    class="w-full px-3.5 py-2.5 bg-[#0b141b] border border-slate-700 rounded-xl text-xs text-slate-100 focus:outline-none focus:border-emerald-500">
                                <option value="">-- Pilih Gudang Tujuan --</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" :disabled="sourceWarehouseId == '{{ $wh->id }}'">
                                        {{ $wh->name }} ({{ $wh->code }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">Stok akan bertambah di gudang ini saat diverifikasi tiba.</p>
                        </div>
                    </div>

                    <!-- Warning if same warehouse -->
                    <div x-show="sourceWarehouseId && destinationWarehouseId && sourceWarehouseId === destinationWarehouseId"
                         class="p-3 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-300 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>Gudang asal dan tujuan tidak boleh sama. Harap pilih gudang berbeda.</span>
                    </div>
                </div>

                <!-- Product Items Card -->
                <div class="bg-[#13202b] border border-slate-800 rounded-2xl p-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <h3 class="text-sm font-bold text-slate-200 flex items-center gap-2">
                            <i class="fa-solid fa-boxes-stacked text-emerald-400"></i>
                            <span>Daftar Barang yang Dimutasi</span>
                        </h3>
                        <span class="text-xs font-mono text-slate-400" x-text="items.length + ' Item Terdaftar'"></span>
                    </div>

                    <!-- Prompt when no source selected -->
                    <div x-show="!sourceWarehouseId" class="py-8 text-center text-slate-400 text-xs">
                        <i class="fa-solid fa-arrow-up text-lg text-emerald-500/60 mb-2 block animate-bounce"></i>
                        Silakan tentukan <strong>Gudang Asal</strong> terlebih dahulu untuk memuat daftar stok barang yang tersedia.
                    </div>

                    <!-- Loading State -->
                    <div x-show="isLoadingProducts" class="py-6 text-center text-slate-400 text-xs flex items-center justify-center gap-2">
                        <i class="fa-solid fa-circle-notch fa-spin text-emerald-400 text-sm"></i>
                        <span>Memuat katalog stok gudang asal...</span>
                    </div>

                    <!-- Repeater Table -->
                    <div x-show="sourceWarehouseId && !isLoadingProducts" class="space-y-3">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="p-4 rounded-xl bg-[#0b141b]/80 border border-slate-800 relative space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                                        <span class="w-5 h-5 rounded-full bg-slate-800 text-slate-300 flex items-center justify-center text-[10px]" x-text="index + 1"></span>
                                        Item Mutasi
                                    </span>
                                    <button type="button"
                                            @click="removeItem(index)"
                                            class="text-rose-400 hover:text-rose-300 text-xs flex items-center gap-1 transition">
                                        <i class="fa-solid fa-trash-can"></i>
                                        <span>Hapus</span>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                    <!-- Product Select -->
                                    <div class="md:col-span-8">
                                        <label class="block text-[11px] text-slate-400 mb-1">Pilih Produk (Stok Gudang Asal)</label>
                                        <select :name="'items[' + index + '][product_id]'"
                                                x-model="item.product_id"
                                                @change="onProductSelect(index)"
                                                required
                                                class="w-full px-3 py-2 bg-[#13202b] border border-slate-700 rounded-lg text-xs text-slate-100 focus:outline-none focus:border-emerald-500">
                                            <option value="">-- Pilih Barang --</option>
                                            <template x-for="p in availableProducts" :key="p.id">
                                                <option :value="p.id"
                                                        x-text="p.title + ' (' + p.sku + ') - Sisa Stok: ' + p.quantity + ' ' + (p.unit || 'pcs')">
                                                </option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- Quantity Input -->
                                    <div class="md:col-span-4">
                                        <label class="block text-[11px] text-slate-400 mb-1">
                                            Jumlah Transfer
                                            <span class="text-[10px] text-emerald-400" x-show="item.max_qty > 0" x-text="'(Maks: ' + item.max_qty + ')'"></span>
                                        </label>
                                        <div class="relative">
                                            <input type="number"
                                                   :name="'items[' + index + '][quantity]'"
                                                   x-model.number="item.quantity"
                                                   min="1"
                                                   :max="item.max_qty || 99999"
                                                   required
                                                   placeholder="0"
                                                   class="w-full pl-3 pr-12 py-2 bg-[#13202b] border border-slate-700 rounded-lg text-xs font-mono font-bold text-slate-100 focus:outline-none focus:border-emerald-500">
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[11px] font-semibold text-slate-400" x-text="item.unit || 'pcs'"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Note / Remark per item -->
                                <div>
                                    <input type="text"
                                           :name="'items[' + index + '][notes]'"
                                           x-model="item.notes"
                                           placeholder="Catatan kondisi barang / nomor batch / nomor segel koli (opsional)"
                                           class="w-full px-3 py-1.5 bg-[#13202b] border border-slate-800 rounded-lg text-[11px] text-slate-300 placeholder-slate-600 focus:outline-none focus:border-emerald-500">
                                </div>
                            </div>
                        </template>

                        <!-- Add Item Row Button -->
                        <div class="pt-2">
                            <button type="button"
                                    @click="addItem()"
                                    :disabled="availableProducts.length === 0"
                                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 disabled:opacity-50 text-slate-200 text-xs font-semibold rounded-xl flex items-center gap-2 transition border border-slate-700">
                                <i class="fa-solid fa-plus text-emerald-400"></i>
                                <span>Tambah Baris Barang</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right 1 Col: Logistical Details & Actions -->
            <div class="space-y-6">

                <!-- Ekspedisi & Armada Card -->
                <div class="bg-[#13202b] border border-slate-800 rounded-2xl p-5 space-y-4">
                    <h3 class="text-sm font-bold text-slate-200 flex items-center gap-2 border-b border-slate-800 pb-3">
                        <i class="fa-solid fa-truck text-emerald-400"></i>
                        <span>Informasi Logistik & Pengiriman</span>
                    </h3>

                    <!-- Tanggal Transfer -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Tanggal Keberangkatan <span class="text-rose-400">*</span>
                        </label>
                        <input type="date"
                               name="transfer_date"
                               value="{{ old('transfer_date', date('Y-m-d')) }}"
                               required
                               class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs text-slate-100 focus:outline-none focus:border-emerald-500">
                    </div>

                    <!-- Estimasi Tiba -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Estimasi Tanggal Tiba
                        </label>
                        <input type="date"
                               name="expected_arrival_date"
                               value="{{ old('expected_arrival_date', date('Y-m-d', strtotime('+1 day'))) }}"
                               class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs text-slate-100 focus:outline-none focus:border-emerald-500">
                    </div>

                    <!-- Driver Name -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Nama Driver / Kurir
                        </label>
                        <input type="text"
                               name="driver_name"
                               value="{{ old('driver_name') }}"
                               placeholder="Contoh: Bambang Supriyadi"
                               class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500">
                    </div>

                    <!-- Vehicle Plate -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Nomor Polisi Kendaraan
                        </label>
                        <input type="text"
                               name="vehicle_plate_number"
                               value="{{ old('vehicle_plate_number') }}"
                               placeholder="Contoh: B 9283 KXA"
                               class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs font-mono uppercase text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500">
                    </div>

                    <!-- Expedition / Transporter -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Armada / Ekspedisi
                        </label>
                        <input type="text"
                               name="expedition"
                               value="{{ old('expedition', 'Armada Internal Gudang') }}"
                               placeholder="Contoh: Armada Internal / JNE Cargo / Indah Logistik"
                               class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500">
                    </div>

                    <!-- Tracking Number -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Nomor Resi / AWB (Jika 3PL)
                        </label>
                        <input type="text"
                               name="tracking_number"
                               value="{{ old('tracking_number') }}"
                               placeholder="Contoh: JTR-992102910"
                               class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs font-mono text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500">
                    </div>

                    <!-- General Notes -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Catatan Mutasi
                        </label>
                        <textarea name="notes"
                                  rows="3"
                                  placeholder="Tulis instruksi khusus penanganan, nomor segel box container, dll..."
                                  class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Action Buttons Card -->
                <div class="bg-[#13202b] border border-slate-800 rounded-2xl p-5 space-y-3">
                    <button type="submit"
                            name="action"
                            value="draft"
                            class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl text-xs flex items-center justify-center gap-2 border border-slate-700 transition">
                        <i class="fa-solid fa-file-pen text-slate-400"></i>
                        <span>Simpan sebagai Draft</span>
                    </button>

                    @if(in_array(auth()->user()?->role, ['super_admin', 'warehouse_manager']))
                        <button type="submit"
                                name="action"
                                value="dispatch"
                                class="w-full py-2.5 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold rounded-xl text-xs flex items-center justify-center gap-2 shadow-lg shadow-emerald-500/10 transition">
                            <i class="fa-solid fa-truck-fast"></i>
                            <span>Simpan & Langsung Kirim (In-Transit)</span>
                        </button>
                    @endif

                    <a href="{{ route('transfers.index') }}"
                       class="w-full py-2 text-center text-xs text-slate-400 hover:text-slate-200 block transition">
                        Batal & Kembali
                    </a>
                </div>

            </div>

        </div>
    </form>

</div>

<script>
function transferForm(config) {
    return {
        warehouses: config.warehouses || [],
        sourceWarehouseId: config.oldSource || '',
        destinationWarehouseId: config.oldDest || '',
        availableProducts: [],
        isLoadingProducts: false,
        items: [],

        init() {
            if (this.sourceWarehouseId) {
                this.loadProducts(this.sourceWarehouseId);
            }
        },

        onSourceChange() {
            this.items = [];
            this.availableProducts = [];
            if (this.sourceWarehouseId) {
                this.loadProducts(this.sourceWarehouseId);
            }
        },

        loadProducts(warehouseId) {
            this.isLoadingProducts = true;
            fetch('{{ url("transfers/api/products-by-warehouse") }}/' + warehouseId)
                .then(res => res.json())
                .then(data => {
                    this.availableProducts = data;
                    this.isLoadingProducts = false;
                    if (this.items.length === 0 && data.length > 0) {
                        this.addItem();
                    }
                })
                .catch(err => {
                    console.error('Failed to load products for warehouse:', err);
                    this.isLoadingProducts = false;
                });
        },

        addItem() {
            this.items.push({
                product_id: '',
                quantity: 1,
                max_qty: 0,
                unit: 'pcs',
                notes: ''
            });
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        onProductSelect(index) {
            const item = this.items[index];
            const p = this.availableProducts.find(prod => prod.id == item.product_id);
            if (p) {
                item.max_qty = p.quantity;
                item.unit = p.unit || 'pcs';
                if (item.quantity > p.quantity) {
                    item.quantity = p.quantity;
                }
            } else {
                item.max_qty = 0;
                item.unit = 'pcs';
            }
        },

        validateSubmit(e) {
            if (this.items.length === 0) {
                e.preventDefault();
                alert('Silakan tambahkan minimal satu barang untuk dimutasi.');
                return false;
            }
            if (this.sourceWarehouseId && this.destinationWarehouseId && this.sourceWarehouseId === this.destinationWarehouseId) {
                e.preventDefault();
                alert('Gudang asal dan tujuan tidak boleh sama.');
                return false;
            }
        }
    };
}
</script>
@endsection
