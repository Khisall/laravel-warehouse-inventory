@extends('layouts.app')

@section('title', 'Mobile Barcode & QR Scanner')

@section('content')
<div class="max-w-2xl mx-auto space-y-5"
     x-data="scannerApp()"
     x-init="init()">

    <!-- Header & Mode Tabs -->
    <div class="bg-[#13202b] border border-slate-800 rounded-2xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                <i class="fa-solid fa-camera text-lg"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-100">Scanner Barcode &amp; QR Mobile</h1>
                <p class="text-[11px] text-slate-400">Pindai SKU, Batch Lot, atau No. Transfer dari kamera ponsel / tablet</p>
            </div>
        </div>

        <!-- Sound & Flash Toggles -->
        <div class="flex items-center gap-2">
            <button type="button"
                    @click="soundEnabled = !soundEnabled"
                    class="w-9 h-9 rounded-xl border flex items-center justify-center transition"
                    :class="soundEnabled ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-400' : 'bg-slate-800 border-slate-700 text-slate-500'"
                    :title="soundEnabled ? 'Suara Aktif' : 'Suara Mati'">
                <i class="fa-solid" :class="soundEnabled ? 'fa-volume-high' : 'fa-volume-xmark'"></i>
            </button>
        </div>
    </div>

    <!-- Camera Viewport Card -->
    <div class="bg-[#13202b] border border-slate-800 rounded-2xl overflow-hidden shadow-2xl relative">
        <!-- Viewfinder Overlay -->
        <div class="relative bg-black min-h-[300px] flex items-center justify-center overflow-hidden">
            <div id="mobile-scanner-view" class="w-full"></div>

            <!-- Scanning Laser Line Indicator -->
            <div x-show="isScanning" class="absolute inset-x-8 top-1/2 -translate-y-1/2 pointer-events-none">
                <div class="w-full h-0.5 bg-emerald-400 shadow-[0_0_12px_#10b981] animate-pulse"></div>
            </div>

            <!-- Loading or Camera Offline -->
            <div x-show="!isScanning" class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center bg-[#0b141b]/95">
                <div class="w-16 h-16 rounded-full bg-slate-800/80 flex items-center justify-center text-slate-400 text-2xl mb-3">
                    <i class="fa-solid fa-camera"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-200">Kamera Sedang Siaga</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-xs">Tekan tombol di bawah untuk mengaktifkan pemindaian kamera langsung.</p>
                <button type="button"
                        @click="startCamera()"
                        class="mt-4 px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-lg shadow-emerald-500/20 transition">
                    <i class="fa-solid fa-play"></i>
                    <span>Aktifkan Kamera Scanner</span>
                </button>
            </div>
        </div>

        <!-- Camera Control Bar -->
        <div x-show="isScanning" class="p-3 bg-[#0e1922] border-t border-slate-800 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2 text-slate-300">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>Kamera Aktif &bull; Arahkan ke Barcode / QR</span>
            </div>
            <button type="button"
                    @click="stopCamera()"
                    class="px-3 py-1 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 text-rose-300 text-[11px] font-semibold transition">
                Hentikan Kamera
            </button>
        </div>
    </div>

    <!-- Manual Barcode Search Bar -->
    <div class="bg-[#13202b] border border-slate-800 rounded-2xl p-4">
        <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">
            Atau Pindai dengan Scanner USB / Ketik Manual:
        </label>
        <form @submit.prevent="lookupCode(manualCode)" class="flex items-center gap-2">
            <div class="relative flex-1">
                <i class="fa-solid fa-barcode absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                <input type="text"
                       x-model="manualCode"
                       placeholder="Masukkan SKU (e.g. MRP400), Lot (LOT-...), atau No. TRF-..."
                       class="w-full pl-9 pr-4 py-2 bg-[#0b141b] border border-slate-700 rounded-xl text-xs font-mono text-slate-100 placeholder-slate-600 focus:outline-none focus:border-emerald-500">
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 font-bold rounded-xl text-xs transition">
                Cari
            </button>
        </form>
    </div>

    <!-- Search / Scanning Status Alert -->
    <div x-show="errorMessage" class="p-4 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs flex items-center justify-between" x-cloak>
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-base"></i>
            <span x-text="errorMessage"></span>
        </div>
        <button type="button" @click="errorMessage = ''" class="text-rose-400 hover:text-rose-200">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Result Card 1: Product Result -->
    <div x-show="result && resultType === 'product'" class="bg-[#13202b] border border-emerald-500/30 rounded-2xl p-5 shadow-2xl space-y-4" x-cloak>
        <div class="flex items-start justify-between border-b border-slate-800 pb-3">
            <div class="flex items-start gap-3">
                <img :src="result?.image_url" alt="" class="w-14 h-14 rounded-xl object-cover bg-slate-800 border border-slate-700">
                <div>
                    <span class="font-mono text-xs font-bold text-emerald-400" x-text="result?.sku"></span>
                    <h3 class="text-base font-bold text-slate-100 leading-tight mt-0.5" x-text="result?.title"></h3>
                    <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-400">
                        <span class="flex items-center gap-1">
                            <i class="fa-solid fa-warehouse text-slate-500"></i>
                            <span x-text="result?.warehouse"></span>
                        </span>
                        <span>&bull;</span>
                        <span class="text-cyan-400 font-mono" x-text="'Lokasi: ' + (result?.warehouse_location || '-')"></span>
                    </div>
                </div>
            </div>

            <div class="text-right">
                <span class="text-[10px] text-slate-400 uppercase tracking-wider block">Total Stok</span>
                <span class="text-2xl font-black font-mono text-emerald-400" x-text="result?.quantity"></span>
                <span class="text-[10px] text-slate-500 block" x-text="result?.unit"></span>
            </div>
        </div>

        <!-- Quick Stock Adjust Bar -->
        <div class="p-3 rounded-xl bg-[#0b141b] border border-slate-800 flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-300">Quick Adjust:</span>
            <div class="flex items-center gap-1.5">
                <button type="button" @click="quickAdjust(-5)" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-rose-400 font-mono text-xs font-bold">-5</button>
                <button type="button" @click="quickAdjust(-1)" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-rose-400 font-mono text-xs font-bold">-1</button>
                <button type="button" @click="quickAdjust(1)" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-emerald-400 font-mono text-xs font-bold">+1</button>
                <button type="button" @click="quickAdjust(5)" class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-emerald-400 font-mono text-xs font-bold">+5</button>
            </div>
        </div>

        <!-- Product Batches List (FEFO Breakdown) -->
        <div class="space-y-2">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center justify-between">
                <span>Daftar Batch &amp; Kedaluwarsa (FEFO):</span>
                <span class="text-emerald-400" x-text="(result?.batches?.length || 0) + ' Batch'"></span>
            </h4>

            <template x-if="result?.batches && result.batches.length > 0">
                <div class="space-y-2">
                    <template x-for="b in result.batches" :key="b.batch_number">
                        <div class="p-3 rounded-xl bg-[#0b141b] border border-slate-800/80 flex items-center justify-between text-xs">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-slate-200" x-text="b.batch_number"></span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-800 text-cyan-300" x-text="'Rak: ' + (b.location_code || '-')"></span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5" x-text="'Exp: ' + b.expiry_date + ' (' + b.days_until_expiry + ' hari lagi)'"></div>
                            </div>

                            <div class="text-right">
                                <span class="font-mono font-bold text-emerald-400" x-text="b.current_quantity + ' ' + (result?.unit || 'pcs')"></span>
                                <div class="text-[10px] text-slate-500" x-text="b.status_label"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="!result?.batches || result.batches.length === 0">
                <p class="text-xs text-slate-500 italic p-3 rounded-xl bg-[#0b141b]">Belum ada batch/lot number yang didaftarkan untuk produk ini.</p>
            </template>
        </div>
    </div>

    <!-- Result Card 2: Transfer Result -->
    <div x-show="result && resultType === 'transfer'" class="bg-[#13202b] border border-cyan-500/30 rounded-2xl p-5 shadow-2xl space-y-4" x-cloak>
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <div>
                <span class="font-mono font-bold text-cyan-400 text-sm" x-text="result?.transfer_number"></span>
                <h3 class="text-sm font-bold text-slate-100 mt-0.5">Surat Jalan Mutasi Antar Gudang</h3>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold border" :class="result?.status_badge_class" x-text="result?.status_label"></span>
        </div>

        <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="p-3 rounded-xl bg-[#0b141b] border border-slate-800">
                <span class="text-[10px] text-slate-500 uppercase block">Gudang Asal</span>
                <strong class="text-slate-200" x-text="result?.source_warehouse"></strong>
            </div>
            <div class="p-3 rounded-xl bg-[#0b141b] border border-slate-800">
                <span class="text-[10px] text-emerald-400 uppercase block">Gudang Tujuan</span>
                <strong class="text-emerald-300" x-text="result?.destination_warehouse"></strong>
            </div>
        </div>

        <div class="flex items-center justify-between text-xs p-3 rounded-xl bg-[#0b141b] border border-slate-800">
            <div>
                <span class="text-slate-400">Total Muatan:</span>
                <strong class="text-slate-100 font-mono ml-1" x-text="result?.total_quantity + ' Unit (' + result?.total_items_count + ' SKU)'"></strong>
            </div>
            <div>
                <span class="text-slate-400">Driver:</span>
                <span class="text-slate-200 ml-1" x-text="result?.driver_name + ' (' + result?.vehicle_plate_number + ')'"></span>
            </div>
        </div>

        <!-- Action Links -->
        <div class="flex items-center gap-2 pt-2">
            <a :href="result?.show_url" class="flex-1 py-2 text-center rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold text-xs border border-slate-700 transition">
                Buka Detail Mutasi
            </a>
            <a :href="result?.surat_jalan_url" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold text-xs flex items-center gap-1.5 transition">
                <i class="fa-solid fa-print"></i>
                <span>Surat Jalan</span>
            </a>
        </div>
    </div>

</div>

<script>
function scannerApp() {
    return {
        isScanning: false,
        soundEnabled: true,
        html5QrCode: null,
        manualCode: '',
        result: null,
        resultType: null,
        errorMessage: '',

        init() {
            // Check if audio context available
        },

        playBeep(success = true) {
            if (!this.soundEnabled) return;
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();

                osc.connect(gain);
                gain.connect(audioCtx.destination);

                if (success) {
                    // High cheerful chime
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(880, audioCtx.currentTime); // A5
                    osc.frequency.setValueAtTime(1320, audioCtx.currentTime + 0.08); // E6
                    gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.25);
                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.25);
                } else {
                    // Low buzz
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(220, audioCtx.currentTime);
                    gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.3);
                    osc.start();
                    osc.stop(audioCtx.currentTime + 0.3);
                }
            } catch (e) {
                console.log('Audio not allowed without gesture', e);
            }

            // Haptic vibration
            if (navigator.vibrate) {
                navigator.vibrate(success ? 80 : [100, 50, 100]);
            }
        },

        startCamera() {
            if (typeof Html5Qrcode === 'undefined') {
                alert('Library Html5Qrcode belum siap.');
                return;
            }

            this.html5QrCode = new Html5Qrcode("mobile-scanner-view");
            const config = { fps: 15, qrbox: { width: 250, height: 250 } };

            this.html5QrCode.start(
                { facingMode: "environment" },
                config,
                (decodedText) => {
                    this.onScanSuccess(decodedText);
                },
                (errorMessage) => {
                    // ignore frame drops
                }
            ).then(() => {
                this.isScanning = true;
            }).catch(err => {
                console.error("Camera start error: ", err);
                alert("Gagal mengakses kamera: " + err);
            });
        },

        stopCamera() {
            if (this.html5QrCode && this.isScanning) {
                this.html5QrCode.stop().then(() => {
                    this.isScanning = false;
                });
            }
        },

        onScanSuccess(code) {
            this.playBeep(true);
            this.lookupCode(code);
        },

        lookupCode(code) {
            if (!code || !code.trim()) return;
            this.errorMessage = '';

            fetch('{{ route('scanner.lookup') }}?code=' + encodeURIComponent(code.trim()))
                .then(res => {
                    if (!res.ok) throw new Error('Data tidak ditemukan');
                    return res.json();
                })
                .then(data => {
                    this.playBeep(true);
                    this.resultType = data.type;
                    this.result = data.data;
                })
                .catch(err => {
                    this.playBeep(false);
                    this.errorMessage = "Kode '" + code + "' tidak ditemukan dalam sistem gudang.";
                    this.result = null;
                    this.resultType = null;
                });
        },

        quickAdjust(delta) {
            if (!this.result || this.resultType !== 'product') return;

            fetch('{{ route('scanner.quick-adjust') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    product_id: this.result.id,
                    delta_quantity: delta,
                    reason: 'Penyesuaian stok mobile barcode scanner'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.playBeep(true);
                    this.result.quantity = data.new_quantity;
                }
            });
        }
    };
}
</script>
@endsection
