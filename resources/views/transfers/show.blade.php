@extends('layouts.app')

@section('title', 'Detail Transfer ' . $transfer->transfer_number)

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{ showReceiveModal: false, showDispatchModal: false, showCancelModal: false }">

    <!-- Header Breadcrumb & Actions -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('transfers.index') }}" class="hover:text-emerald-400 transition flex items-center gap-1">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Daftar Transfer</span>
                </a>
                <span>/</span>
                <span class="font-mono text-slate-200">{{ $transfer->transfer_number }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-black text-slate-100 font-mono tracking-tight">{{ $transfer->transfer_number }}</h1>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $transfer->status_badge_class }}">
                    <i class="fa-solid {{ $transfer->status_icon }} text-[11px]"></i>
                    <span>{{ $transfer->status_label }}</span>
                </span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Cetak Surat Jalan -->
            <a href="{{ route('transfers.surat-jalan', $transfer->id) }}"
               target="_blank"
               class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold rounded-xl text-xs flex items-center gap-2 border border-slate-700 transition shadow-sm">
                <i class="fa-solid fa-print text-emerald-400"></i>
                <span>Cetak Surat Jalan</span>
            </a>

            <!-- Dispatch Button (Draft -> In Transit) -->
            @if($transfer->canBeDispatched() && in_array(auth()->user()?->role, ['super_admin', 'warehouse_manager']))
                <button type="button"
                        @click="showDispatchModal = true"
                        class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold rounded-xl text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/15 transition">
                    <i class="fa-solid fa-truck-fast"></i>
                    <span>Kirim Barang (Dispatch)</span>
                </button>
            @endif

            <!-- Receive Button (In Transit -> Received) -->
            @if($transfer->canBeReceived())
                <button type="button"
                        @click="showReceiveModal = true"
                        class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold rounded-xl text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/15 transition">
                    <i class="fa-solid fa-clipboard-check"></i>
                    <span>Verifikasi Terima Barang</span>
                </button>
            @endif

            <!-- Cancel Button -->
            @if($transfer->canBeCancelled() && in_array(auth()->user()?->role, ['super_admin', 'warehouse_manager']))
                <button type="button"
                        @click="showCancelModal = true"
                        class="px-3.5 py-2 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 text-rose-300 font-semibold rounded-xl text-xs flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-ban"></i>
                    <span>Batalkan</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Alert Flash Messages -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Visual Tracking Pipeline Timeline -->
    <div class="bg-[#13202b] border border-slate-800 rounded-2xl p-5">
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Status Siklus Hidup Mutasi</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <!-- Step 1: Draft -->
            <div class="p-3.5 rounded-xl border {{ $transfer->status === 'cancelled' ? 'bg-slate-900/60 border-slate-800 opacity-60' : 'bg-[#0b141b]/80 border-slate-700/80' }}">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs font-bold">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-200">1. Draft Dibuat</p>
                        <p class="text-[11px] text-slate-400">{{ $transfer->created_at->translatedFormat('d M Y, H:i') }}</p>
                    </div>
                </div>
                <div class="mt-2 text-[11px] text-slate-500">
                    Oleh: {{ $transfer->creator?->name ?: 'Staff Logistik' }}
                </div>
            </div>

            <!-- Step 2: In-Transit -->
            <div class="p-3.5 rounded-xl border {{ in_array($transfer->status, ['in_transit', 'received']) ? 'bg-[#0b141b]/80 border-amber-500/40' : 'bg-slate-900/60 border-slate-800 opacity-60' }}">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg {{ in_array($transfer->status, ['in_transit', 'received']) ? 'bg-amber-500/20 text-amber-400' : 'bg-slate-800 text-slate-500' }} flex items-center justify-center text-xs font-bold">
                        <i class="fa-solid fa-truck-fast {{ $transfer->status === 'in_transit' ? 'animate-pulse' : '' }}"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold {{ in_array($transfer->status, ['in_transit', 'received']) ? 'text-amber-300' : 'text-slate-400' }}">2. Dalam Pengiriman</p>
                        <p class="text-[11px] text-slate-400">
                            {{ $transfer->dispatched_at ? $transfer->dispatched_at->translatedFormat('d M Y, H:i') : 'Menunggu keberangkatan' }}
                        </p>
                    </div>
                </div>
                <div class="mt-2 text-[11px] text-slate-500">
                    @if($transfer->dispatcher)
                        Diberangkatkan: {{ $transfer->dispatcher->name }}
                    @else
                        Stok di gudang asal dipotong saat dikirim
                    @endif
                </div>
            </div>

            <!-- Step 3: Received -->
            <div class="p-3.5 rounded-xl border {{ $transfer->status === 'received' ? 'bg-[#0b141b]/80 border-emerald-500/40' : 'bg-slate-900/60 border-slate-800 opacity-60' }}">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg {{ $transfer->status === 'received' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-800 text-slate-500' }} flex items-center justify-center text-xs font-bold">
                        <i class="fa-solid fa-warehouse"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold {{ $transfer->status === 'received' ? 'text-emerald-300' : 'text-slate-400' }}">3. Tiba di Gudang Tujuan</p>
                        <p class="text-[11px] text-slate-400">
                            {{ $transfer->received_date ? $transfer->received_date->translatedFormat('d M Y, H:i') : 'Menunggu tiba & verifikasi' }}
                        </p>
                    </div>
                </div>
                <div class="mt-2 text-[11px] text-slate-500">
                    @if($transfer->receiver)
                        Diterima: {{ $transfer->receiver->name }}
                    @else
                        Stok masuk & bertambah di gudang tujuan
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Warehouse Route & Logistical Info -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Source Warehouse Card -->
        <div class="bg-[#13202b] border border-slate-800 rounded-2xl p-5 space-y-3">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2.5">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-up-from-bracket text-rose-400"></i> Gudang Asal (Pengirim)
                </span>
                <span class="font-mono text-[11px] text-slate-400">{{ $transfer->sourceWarehouse->code }}</span>
            </div>
            <div>
                <h4 class="text-base font-bold text-slate-100">{{ $transfer->sourceWarehouse->name }}</h4>
                <p class="text-xs text-slate-400 mt-1 flex items-start gap-1.5">
                    <i class="fa-solid fa-location-dot text-slate-500 mt-0.5"></i>
                    <span>{{ $transfer->sourceWarehouse->address ?: $transfer->sourceWarehouse->location ?: 'Alamat belum tercatat' }}</span>
                </p>
            </div>
            <div class="pt-2 text-[11px] text-slate-400 border-t border-slate-800/80 flex items-center justify-between">
                <span>Kepala Gudang:</span>
                <span class="text-slate-200 font-semibold">{{ $transfer->sourceWarehouse->manager_name ?: '-' }}</span>
            </div>
        </div>

        <!-- Destination Warehouse Card -->
        <div class="bg-[#13202b] border border-slate-800 rounded-2xl p-5 space-y-3">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2.5">
                <span class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-down-to-bracket text-emerald-400"></i> Gudang Tujuan (Penerima)
                </span>
                <span class="font-mono text-[11px] text-emerald-400">{{ $transfer->destinationWarehouse->code }}</span>
            </div>
            <div>
                <h4 class="text-base font-bold text-slate-100">{{ $transfer->destinationWarehouse->name }}</h4>
                <p class="text-xs text-slate-400 mt-1 flex items-start gap-1.5">
                    <i class="fa-solid fa-location-dot text-emerald-500 mt-0.5"></i>
                    <span>{{ $transfer->destinationWarehouse->address ?: $transfer->destinationWarehouse->location ?: 'Alamat belum tercatat' }}</span>
                </p>
            </div>
            <div class="pt-2 text-[11px] text-slate-400 border-t border-slate-800/80 flex items-center justify-between">
                <span>Kepala Gudang:</span>
                <span class="text-slate-200 font-semibold">{{ $transfer->destinationWarehouse->manager_name ?: '-' }}</span>
            </div>
        </div>

        <!-- Transporter & Courier Details -->
        <div class="bg-[#13202b] border border-slate-800 rounded-2xl p-5 space-y-3">
            <div class="border-b border-slate-800 pb-2.5">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-truck-moving text-amber-400"></i> Logistik & Transporter
                </span>
            </div>

            <div class="space-y-1.5 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Pengemudi:</span>
                    <span class="text-slate-200 font-semibold">{{ $transfer->driver_name ?: '-' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">No. Polisi Kendaraan:</span>
                    <span class="font-mono font-bold text-emerald-400">{{ $transfer->vehicle_plate_number ?: '-' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">Ekspedisi / Armada:</span>
                    <span class="text-slate-200">{{ $transfer->expedition ?: 'Internal' }}</span>
                </div>
                @if($transfer->tracking_number)
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">No. Resi / AWB:</span>
                        <span class="font-mono text-cyan-400">{{ $transfer->tracking_number }}</span>
                    </div>
                @endif
                <div class="flex items-center justify-between pt-1 border-t border-slate-800/60">
                    <span class="text-slate-400">Estimasi Tiba:</span>
                    <span class="text-slate-200">{{ $transfer->expected_arrival_date ? $transfer->expected_arrival_date->translatedFormat('d M Y') : '-' }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Notes if present -->
    @if($transfer->notes)
        <div class="bg-[#13202b] border border-slate-800 rounded-2xl p-4 text-xs text-slate-300">
            <span class="font-bold text-slate-400 uppercase tracking-wider text-[10px] block mb-1">Catatan Dokumen Mutasi:</span>
            <p class="whitespace-pre-line text-slate-300">{{ $transfer->notes }}</p>
        </div>
    @endif

    <!-- Items Table -->
    <div class="bg-[#13202b] border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked text-emerald-400"></i>
                    <span>Daftar Muatan Barang Transfer</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Rincian SKU, kuantitas dikirim dari gudang asal, dan hasil verifikasi penerimaan.</p>
            </div>
            <div class="text-right">
                <span class="text-xs font-mono font-bold text-emerald-400">
                    {{ $transfer->total_quantity }} Unit Total Dikirim
                </span>
                @if($transfer->status === 'received')
                    <div class="text-[11px] font-mono text-emerald-300">
                        {{ $transfer->total_received_quantity }} Unit Telah Diterima
                    </div>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#0b141b]/80 border-b border-slate-800 text-[11px] uppercase tracking-wider text-slate-400 font-semibold">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">SKU & Nama Barang</th>
                        <th class="py-3 px-4">Batch & Expiry</th>
                        <th class="py-3 px-4 text-center">Kuantitas Dikirim</th>
                        <th class="py-3 px-4 text-center">Kuantitas Diterima</th>
                        <th class="py-3 px-4">Keterangan / Kondisi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-xs">
                    @foreach($transfer->items as $idx => $item)
                        <tr class="hover:bg-[#162532]/40 transition">
                            <td class="py-3.5 px-4 text-center text-slate-500 font-mono">{{ $idx + 1 }}</td>

                            <td class="py-3.5 px-4">
                                <span class="font-mono font-bold text-emerald-400">{{ $item->sku }}</span>
                                <div class="text-slate-200 font-medium mt-0.5">{{ $item->product_title }}</div>
                            </td>

                            <td class="py-3.5 px-4 text-slate-400 font-mono text-[11px]">
                                <div>Batch: {{ $item->batch_number ?: '-' }}</div>
                                <div class="text-[10px] text-slate-500">
                                    Exp: {{ $item->expiry_date ? $item->expiry_date->translatedFormat('d M Y') : '-' }}
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-200">
                                {{ $item->quantity }} {{ $item->unit }}
                            </td>

                            <td class="py-3.5 px-4 text-center font-mono font-bold">
                                @if($transfer->status === 'received')
                                    <span class="{{ $item->received_quantity < $item->quantity ? 'text-amber-400' : 'text-emerald-400' }}">
                                        {{ $item->received_quantity }} {{ $item->unit }}
                                    </span>
                                @elseif($transfer->status === 'in_transit')
                                    <span class="text-amber-400/80 italic text-[11px]">Sedang Dikirim</span>
                                @else
                                    <span class="text-slate-500 italic text-[11px]">- Menunggu -</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                                {{ $item->notes ?: '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Dispatch Confirmation Modal -->
    <div x-show="showDispatchModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         x-cloak>
        <div class="bg-[#13202b] border border-slate-700 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center gap-3 text-amber-400">
                <div class="w-10 h-10 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-100">Konfirmasi Keberangkatan</h3>
                    <p class="text-xs text-slate-400">Kirim barang mutasi {{ $transfer->transfer_number }}</p>
                </div>
            </div>

            <p class="text-xs text-slate-300">
                Apakah Anda yakin ingin memberangkatkan pengiriman ini?
                <strong>Stok di gudang asal ({{ $transfer->sourceWarehouse->name }}) akan langsung dipotong</strong> sejumlah <strong>{{ $transfer->total_quantity }} unit</strong> dan status mutasi akan berubah menjadi <em>Dalam Pengiriman (In-Transit)</em>.
            </p>

            <form method="POST" action="{{ route('transfers.dispatch', $transfer->id) }}" class="flex items-center justify-end gap-3 pt-2">
                @csrf
                <button type="button"
                        @click="showDispatchModal = false"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl text-xs transition">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold rounded-xl text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/15 transition">
                    <i class="fa-solid fa-check"></i>
                    <span>Ya, Berangkatkan Sekarang</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Receive Verification Modal -->
    <div x-show="showReceiveModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         x-cloak>
        <div class="bg-[#13202b] border border-slate-700 rounded-2xl max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-3 text-emerald-400">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-100">Verifikasi Penerimaan Barang</h3>
                        <p class="text-xs text-slate-400">Gudang Tujuan: {{ $transfer->destinationWarehouse->name }}</p>
                    </div>
                </div>
                <button type="button" @click="showReceiveModal = false" class="text-slate-400 hover:text-slate-200">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <p class="text-xs text-slate-300">
                Silakan periksa fisik barang yang tiba dan masukkan jumlah yang diterima di bawah ini. Stok akan otomatis ditambahkan ke katalog gudang tujuan.
            </p>

            <form method="POST" action="{{ route('transfers.receive', $transfer->id) }}" class="space-y-4">
                @csrf

                <div class="space-y-3">
                    @foreach($transfer->items as $item)
                        <div class="p-3.5 rounded-xl bg-[#0b141b] border border-slate-800 space-y-2">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-mono font-bold text-emerald-400 text-xs">{{ $item->sku }}</span>
                                    <span class="text-slate-200 text-xs font-semibold ml-2">{{ $item->product_title }}</span>
                                </div>
                                <span class="text-xs text-slate-400">Dikirim: <strong>{{ $item->quantity }} {{ $item->unit }}</strong></span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                                <div>
                                    <label class="block text-[11px] text-slate-400 mb-1">Jumlah Fisik Diterima</label>
                                    <div class="relative">
                                        <input type="number"
                                               name="received_quantities[{{ $item->id }}]"
                                               value="{{ $item->quantity }}"
                                               min="0"
                                               max="{{ $item->quantity }}"
                                               required
                                               class="w-full px-3 py-1.5 bg-[#13202b] border border-slate-700 rounded-lg text-xs font-mono font-bold text-slate-100 focus:outline-none focus:border-emerald-500">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400">{{ $item->unit }}</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] text-slate-400 mb-1">Catatan Kondisi Barang</label>
                                    <input type="text"
                                           name="item_notes[{{ $item->id }}]"
                                           placeholder="Contoh: Kondisi bagus, kemasan segel aman"
                                           class="w-full px-3 py-1.5 bg-[#13202b] border border-slate-700 rounded-lg text-[11px] text-slate-300 placeholder-slate-600 focus:outline-none focus:border-emerald-500">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan Penerimaan Tambahan</label>
                    <textarea name="receiving_notes"
                              rows="2"
                              placeholder="Tulis catatan serah-terima fisik, kondisi armada pengangkut, dll..."
                              class="w-full px-3 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                    <button type="button"
                            @click="showReceiveModal = false"
                            class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl text-xs transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold rounded-xl text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/15 transition">
                        <i class="fa-solid fa-check"></i>
                        <span>Konfirmasi & Simpan Stok Masuk</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div x-show="showCancelModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         x-cloak>
        <div class="bg-[#13202b] border border-rose-500/30 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center gap-3 text-rose-400">
                <div class="w-10 h-10 rounded-xl bg-rose-500/15 border border-rose-500/30 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-100">Batalkan Mutasi</h3>
                    <p class="text-xs text-slate-400">No: {{ $transfer->transfer_number }}</p>
                </div>
            </div>

            <p class="text-xs text-slate-300">
                Apakah Anda yakin ingin membatalkan proses transfer stok ini?
                @if($transfer->status === 'in_transit')
                    <span class="text-amber-400 font-semibold">Karena barang sudah dalam pengiriman, seluruh stok sejumlah {{ $transfer->total_quantity }} unit akan dikembalikan ke gudang asal ({{ $transfer->sourceWarehouse->name }}).</span>
                @else
                    Transfer ini belum dikirim dan akan ditandai sebagai dibatalkan.
                @endif
            </p>

            <form method="POST" action="{{ route('transfers.cancel', $transfer->id) }}" class="flex items-center justify-end gap-3 pt-2">
                @csrf
                <button type="button"
                        @click="showCancelModal = false"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold rounded-xl text-xs transition">
                    Kembali
                </button>
                <button type="submit"
                        class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition">
                    <i class="fa-solid fa-ban"></i>
                    <span>Ya, Batalkan Mutasi</span>
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
