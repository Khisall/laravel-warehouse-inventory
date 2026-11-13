@extends('layouts.app')

@section('title', 'Buat Purchase Order Baru - INVENTORY')

@section('content')
<div class="space-y-6" x-data="createPoApp()">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('purchase-orders.index') }}" class="p-2 rounded-xl bg-[#13202b] hover:bg-[#182633] text-slate-400 hover:text-white border border-[#1f3344] transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    Buat Purchase Order (PO) Baru
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">Inbound</span>
                </h1>
                <p class="text-xs text-slate-400">Pilih supplier, gudang tujuan, dan daftar barang yang akan dipesan.</p>
            </div>
        </div>
    </div>

    <!-- PO Creation Form -->
    <form action="{{ route('purchase-orders.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Section 1: Header Metadata -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 shadow-lg space-y-4">
            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2 border-b border-[#1f3344] pb-3">
                <i class="fa-solid fa-building-circle-check text-emerald-400"></i>
                <span>Informasi Pemasok & Lokasi Penerimaan</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Supplier -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Pilih Pemasok / Supplier <span class="text-rose-400">*</span>
                    </label>
                    <select name="supplier_id"
                            required
                            class="w-full px-3.5 py-2.5 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                        <option value="">-- Pilih Supplier --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->code }} - {{ $supplier->name }} ({{ $supplier->payment_terms }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Warehouse Destination -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Gudang Tujuan Masuk <span class="text-rose-400">*</span>
                    </label>
                    <select name="warehouse_id"
                            required
                            class="w-full px-3.5 py-2.5 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Payment Status -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Status Pembayaran
                    </label>
                    <select name="payment_status"
                            class="w-full px-3.5 py-2.5 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500">
                        <option value="unpaid" selected>Belum Dibayar (Unpaid)</option>
                        <option value="partial">Dibayar Sebagian</option>
                        <option value="paid">Lunas (Paid)</option>
                    </select>
                </div>

                <!-- Order Date -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Tanggal Pemesanan <span class="text-rose-400">*</span>
                    </label>
                    <input type="date"
                           name="order_date"
                           value="{{ date('Y-m-d') }}"
                           required
                           class="w-full px-3.5 py-2.5 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500 font-mono">
                </div>

                <!-- Expected Delivery Date -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Estimasi Tanggal Tiba (Delivery)
                    </label>
                    <input type="date"
                           name="expected_delivery_date"
                           value="{{ date('Y-m-d', strtotime('+3 days')) }}"
                           class="w-full px-3.5 py-2.5 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white focus:outline-none focus:border-emerald-500 font-mono">
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Catatan PO / Instruksi Pengiriman
                    </label>
                    <input type="text"
                           name="notes"
                           placeholder="Contoh: Kirim via armada truk wingbox..."
                           class="w-full px-3.5 py-2.5 bg-[#0e1821] border border-[#1f3344] rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500">
                </div>
            </div>
        </div>

        <!-- Section 2: Items Table -->
        <div class="bg-[#13202b] border border-[#1f3344] rounded-2xl p-5 shadow-lg space-y-4">
            <div class="flex items-center justify-between border-b border-[#1f3344] pb-3">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-emerald-400"></i>
                    <span>Daftar Barang yang Dipesan</span>
                </h3>
                <button type="button"
                        @click="addItem()"
                        class="px-3 py-1.5 rounded-lg bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-400 text-xs font-bold flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tambah Baris Barang</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-[#1f3344] text-slate-400 uppercase tracking-wider font-semibold">
                            <th class="py-2.5 px-3">Produk / Barang</th>
                            <th class="py-2.5 px-3 w-32 text-center">Jumlah Pesan</th>
                            <th class="py-2.5 px-3 w-40 text-right">Harga Satuan (Rp)</th>
                            <th class="py-2.5 px-3 w-44 text-right">Subtotal (Rp)</th>
                            <th class="py-2.5 px-3">Catatan</th>
                            <th class="py-2.5 px-3 w-12 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#1f3344]/50">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="hover:bg-[#162532]/50">
                                <!-- Product Select -->
                                <td class="py-3 px-3">
                                    <select :name="'items[' + index + '][product_id]'"
                                            x-model="item.product_id"
                                            @change="onProductChange(index)"
                                            required
                                            class="w-full px-3 py-2 bg-[#0e1821] border border-[#1f3344] rounded-lg text-xs text-white focus:outline-none focus:border-emerald-500">
                                        <option value="">-- Pilih Produk --</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" data-cost="{{ $p->cost_price }}" data-unit="{{ $p->unit }}">
                                                {{ $p->sku }} - {{ $p->title }} (Stok: {{ $p->quantity }} {{ $p->unit }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <!-- Quantity -->
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <input type="number"
                                               :name="'items[' + index + '][quantity_ordered]'"
                                               x-model.number="item.quantity_ordered"
                                               min="1"
                                               required
                                               class="w-20 text-center font-mono font-bold py-1.5 bg-[#0a1218] border border-[#1f3344] rounded-lg text-xs text-emerald-300 focus:outline-none focus:border-emerald-500">
                                        <span class="text-[10px] text-slate-500" x-text="item.unit || 'pcs'"></span>
                                    </div>
                                </td>

                                <!-- Unit Cost -->
                                <td class="py-3 px-3 text-right">
                                    <input type="number"
                                           :name="'items[' + index + '][unit_cost]'"
                                           x-model.number="item.unit_cost"
                                           min="0"
                                           step="100"
                                           required
                                           class="w-32 text-right font-mono py-1.5 px-2 bg-[#0a1218] border border-[#1f3344] rounded-lg text-xs text-white focus:outline-none focus:border-emerald-500">
                                </td>

                                <!-- Line Subtotal -->
                                <td class="py-3 px-3 text-right font-mono font-bold text-white">
                                    Rp <span x-text="formatRupiah(item.quantity_ordered * item.unit_cost)"></span>
                                </td>

                                <!-- Notes -->
                                <td class="py-3 px-3">
                                    <input type="text"
                                           :name="'items[' + index + '][notes]'"
                                           x-model="item.notes"
                                           placeholder="Keterangan..."
                                           class="w-full px-2.5 py-1.5 bg-[#0e1821] border border-[#1f3344] rounded-lg text-xs text-slate-300 placeholder-slate-600 focus:outline-none">
                                </td>

                                <!-- Delete Row -->
                                <td class="py-3 px-3 text-center">
                                    <button type="button"
                                            @click="removeItem(index)"
                                            :disabled="items.length <= 1"
                                            class="text-rose-400 hover:text-rose-300 disabled:opacity-30 p-1">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Total Calculations Box -->
            <div class="flex flex-col md:flex-row justify-end pt-4 border-t border-[#1f3344]">
                <div class="w-full md:w-80 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-400">
                        <span>Subtotal Barang:</span>
                        <span class="font-mono font-bold text-white">Rp <span x-text="formatRupiah(calculateSubtotal())"></span></span>
                    </div>

                    <div class="flex items-center justify-between text-slate-400">
                        <label class="flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" x-model="includeTax" class="rounded bg-[#0e1821] border-[#1f3344] text-emerald-500 focus:ring-0">
                            <span>PPN (11%):</span>
                        </label>
                        <input type="hidden" name="tax_percent" :value="includeTax ? 11 : 0">
                        <span class="font-mono text-slate-300">Rp <span x-text="formatRupiah(calculateTax())"></span></span>
                    </div>

                    <div class="flex items-center justify-between text-slate-400">
                        <span>Ongkos Kirim:</span>
                        <div class="flex items-center gap-1">
                            <span class="text-slate-500">Rp</span>
                            <input type="number"
                                   name="shipping_cost"
                                   x-model.number="shippingCost"
                                   min="0"
                                   step="1000"
                                   class="w-28 text-right font-mono py-1 px-2 bg-[#0e1821] border border-[#1f3344] rounded text-xs text-white focus:outline-none">
                        </div>
                    </div>

                    <div class="flex justify-between text-sm font-bold text-emerald-400 pt-2 border-t border-[#1f3344]">
                        <span>Grand Total PO:</span>
                        <span class="font-mono text-base">Rp <span x-text="formatRupiah(calculateGrandTotal())"></span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button Footer -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('purchase-orders.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">
                Batal
            </a>
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/25 transition hover:scale-[1.02]">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Terbitkan & Simpan Purchase Order</span>
            </button>
        </div>
    </form>

</div>

@push('scripts')
<script>
    function createPoApp() {
        return {
            items: [
                { product_id: '', quantity_ordered: 10, unit_cost: 0, unit: 'pcs', notes: '' }
            ],
            includeTax: true,
            shippingCost: 0,

            addItem() {
                this.items.push({
                    product_id: '',
                    quantity_ordered: 5,
                    unit_cost: 0,
                    unit: 'pcs',
                    notes: ''
                });
            },

            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },

            onProductChange(index) {
                const selectEl = document.querySelectorAll("select[name^='items['][name$='][product_id]']")[index];
                if (selectEl) {
                    const selectedOpt = selectEl.options[selectEl.selectedIndex];
                    const cost = parseFloat(selectedOpt.getAttribute('data-cost')) || 0;
                    const unit = selectedOpt.getAttribute('data-unit') || 'pcs';
                    this.items[index].unit_cost = cost;
                    this.items[index].unit = unit;
                }
            },

            calculateSubtotal() {
                return this.items.reduce((sum, it) => sum + ((it.quantity_ordered || 0) * (it.unit_cost || 0)), 0);
            },

            calculateTax() {
                return this.includeTax ? (this.calculateSubtotal() * 0.11) : 0;
            },

            calculateGrandTotal() {
                return this.calculateSubtotal() + this.calculateTax() + (parseFloat(this.shippingCost) || 0);
            },

            formatRupiah(num) {
                return Math.round(num).toLocaleString('id-ID');
            }
        };
    }
</script>
@endpush
@endsection
