---
name: dashboard-mockup
description: "Buat mockup HTML standalone untuk dashboard ringkasan management (summary dashboard) dengan filter, toggle Grafik/Analisa AI, KPI card, chart yang bisa diklik (popup list data), dan analisa poin-per-poin dari data. Gunakan saat user minta 'mockup dashboard', 'summary dashboard', 'dashboard management/chart', 'halaman analisa/insight' untuk modul manapun (CRM, sales, purchasing, stok, HR, finance, dll) sebelum dikerjakan ke Laravel."
---

# Dashboard Mockup (Grafik + Analisa AI)

Menghasilkan **1 file HTML standalone** di `docs/<modul>-summary-dashboard-mockup.html` (CDN only, tanpa backend).
Referensi implementasi lengkap & sudah teruji: `reference-crm-summary.html` di folder skill ini — **baca dulu, lalu salin strukturnya**, ganti domain/datanya. Jangan tulis dari nol.

## Alur kerja
1. **Pahami domain & data.** Kalau user memberi screenshot/skema/tabel → turunkan entitas, field, status, dimensi (sumber, salesman/PIC, kategori, cabang, dll) dan metrik (jumlah, nilai, durasi, rasio). Kalau tidak jelas, cek `docs/ai/modules/*.md` atau tabel terkait; jangan banyak bertanya — pilih default masuk akal.
2. **Tentukan analisa** (lihat "Katalog analisa") yang relevan dengan data tsb — hanya yang bisa dihitung dari field yang ada. Jangan memaksakan analisa yang butuh data yang tidak ada.
3. **Salin referensi → adaptasi**: ganti konstanta domain (SOURCES/SALES/STAGES dst), generator data sampel, kolom tabel popup, KPI, chart, dan fungsi `buildInsights()`.
4. **Verifikasi** di browser pane (`preview_start` dengan `url: file://...`): tidak ada error console, toggle ke Analisa AI jalan, klik 1 insight & 1 chart membuka popup berisi baris data.
5. **Balas ke user**: ringkas isi mockup (tabel grafik → pertanyaan yang dijawab), sebut field yang perlu dicek ke skema asli, tawarkan lanjut ke implementasi Laravel. Ide analisa tambahan yang belum masuk = daftar singkat.

## Format WAJIB (jangan diubah)
- **Stack**: Poppins, Iconify (`tabler:*`), ApexCharts 3.46 via CDN; variabel CSS & dark mode sama seperti referensi (selaras `docs/home-dashboard-mockup.html` dan `docs/ai/design-system.md`).
- **Topbar sticky**: pretitle + judul, filter periode (30/90/180 hari), filter PIC/dimensi utama, **segmented toggle `Grafik | Analisa AI`**, tombol tema.
- **Badge `Powered by AI Elo`** WAJIB ada & bisa diklik ke landing page Elo — lihat bagian "Badge Powered by AI Elo" di bawah.
- **Mode Grafik**: baris KPI (6 card, ada delta vs periode lalu) → grid 12 kolom berisi 8–10 chart. Tiap KPI & tiap chart/segmen **klik → popup list data**. Beri `hint` "klik ..." di header card.
- **Mode Analisa AI**: banner gradient + ringkasan hitungan (Kritis/Perhatian/Positif/Info); daftar insight dikelompokkan per severity (`danger/warning/success/info`), tiap insight = ikon + judul (dengan angka) + penjelasan + baris rekomendasi tindakan + chip "N data", **klik → popup**. Sidebar kanan: Health Score (radialBar) + "Fokus Minggu Ini" (checklist, juga klik → popup). Tampilkan skeleton ±650ms saat toggle.
- **Popup modal**: judul, subjudul, search, sort per kolom, Export & "Buka di CRM" (cukup toast di mockup), footer jumlah data + total nilai, tutup via X/overlay/Esc.
- **Semua angka dihitung dari dataset sampel** (generator seeded `rng()`, ±100–200 baris dengan pola realistis: sebagian sumber/PIC lebih bagus dari yang lain) lalu difilter oleh `P` (periode) & `SEL` (PIC). Filter harus benar-benar mengubah semua KPI, chart, dan insight. Jangan hardcode angka insight.
- Footer note: mockup statis, data sampel acak.
- Bahasa UI: Indonesia. Format uang `Rp x jt / Rp x M`. Tanggal DD/MM.

## Pola kode (dari referensi)
- `recompute()` → `D` (data terfilter) & `O` (subset aktif/open); `prevSet()` untuk periode sebelumnya (delta hanya jika `2*P<=rentang data`).
- `openModal(title, sub, rows, icon, color)` satu-satunya pintu popup; chart memakai helper `click((idx, series)=>...)` pada `events.dataPointSelection`.
- `base(extra)` = default Apex (font, warna dari CSS var, tema) — re-render semua chart saat tema/filter berubah (`charts.forEach(destroy)`).
- `buildInsights()` memakai `add(sev, icon, title, text, action, list, sub)`; insight otomatis hilang kalau `list` kosong. Urutkan severity: danger → warning → success → info. Health score = 100 dikurangi rasio masalah berbobot, di-clamp 20–98.
- Funnel/progress yang bukan chart Apex dibuat div custom yang tetap clickable.

## Badge "Powered by AI Elo" (WAJIB di setiap mockup)
Setiap mockup **wajib** menampilkan badge **Powered by AI Elo** yang bisa diklik dan membuka
landing page Elo (`/about-elo`, hanya ada di `ai-laravel-core`). Pasang di **header mode Analisa AI**,
di bawah judul/subjudul (sama seperti implementasi sibling: CRM/Tenant/Property).

Tambahkan `Bungee+Tint` ke link Google Fonts, lalu tempel badge di dalam `.ai-head` (setelah `<p id="aiSub">`):

```html
<a class="elo-badge" href="http://localhost:8888/ai-laravel-core/public/about-elo" target="_blank" rel="noopener">
  <span class="elo-badge__label">🟢 Powered by AI</span>
  <span class="elo-badge__brand">Elo</span>
</a>
```
```css
.elo-badge { display:inline-flex; align-items:center; gap:.35em; line-height:1; margin-top:8px; text-decoration:none; }
.elo-badge__label { font-family:'Poppins',sans-serif; font-weight:200; font-size:7pt; color:#a1a1a1; }
.elo-badge__brand { font-family:'Bungee Tint',system-ui; font-size:8pt; line-height:1; background:#ffee00; padding:2px 4px; border-radius:5px; }
```

**URL badge:** di mockup lokal pakai `http://localhost:8888/ai-laravel-core/public/about-elo`;
di produksi = `{APP_URL_AI_LARAVEL_CORE}/about-elo`. **Jangan hardcode domain produksi.**
Saat disetujui user → implementasi Laravel, **ganti badge HTML dengan komponen**
`<x-powered-by-ai size="0.85rem" style="margin-top:8px" />` (sudah ada di semua project, otomatis
resolve URL cross-project ke landing page Elo). Detail sync: `docs/ai/multi-project-sync.md`.

## Katalog analisa (pilih yang cocok dengan data)
**Selalu coba ada:** KPI total & delta vs periode lalu · tren masa-ke-masa (baru vs selesai) · breakdown per dimensi utama (sumber/kategori) dengan volume + rasio · leaderboard per PIC (stacked) · distribusi status · aging/kesegaran · alasan gagal/penolakan · histogram durasi siklus.
**Insight standar:** item terlambat/overdue · item prioritas tinggi tanpa tindakan berikutnya · item tak tersentuh >N hari · pelanggaran SLA respon · item bernilai besar berstatus lemah · bottleneck funnel (drop-off terbesar) · dimensi terbaik vs terboros · PIC terbaik vs PIC dengan backlog terbanyak · konsentrasi nilai di top-3 · alasan gagal terbanyak · tren rasio (naik/turun poin) · forecast tertimbang · item positif yang perlu dikawal.
**Padanan per domain:** 
- Sales/CRM: funnel, win rate, pipeline value, sales cycle, lead response, forecast.
- Purchasing: PO terlambat, supplier on-time rate, price variance, konsentrasi supplier, outstanding PR→PO→GR.
- Inventory: stok minus/dead stock/slow moving, turnover, di bawah min stock, aging stok, ABC analysis.
- Finance/AR-AP: aging piutang/hutang bucket, DSO, top debitur, overdue per customer, cashflow trend.
- HR: absensi/terlambat, turnover, lembur, kontrak akan habis, headcount per dept.
- Produksi/Service: backlog, lead time, reject rate, SLA tiket, reopen rate.

## Setelah disetujui user → implementasi
Ikuti aturan project (`CLAUDE.md`): modul baru di `app/Modules/...`, daftar di `docs/ai/modules/module-registry.md`, menu di `config/menus.php`, permission dari role primary DB, query via `DB::tableWithCompany()`, dan dokumentasikan desain ke `docs/ai/modules/<modul>.md`. Mockup ini bukan kode produksi.
