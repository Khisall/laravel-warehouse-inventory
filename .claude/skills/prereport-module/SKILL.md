---
name: prereport-module
description: Implementasi action "Print" (popup Print Report / PreReport) pada list sebuah modul — daftar template dari apiprereportquery per ModuleReport, difilter per company record. Gunakan saat user minta "tambah print invoice/prereport di menu X", "popup print report", "list printout modul X".
---

# PreReport (popup Print Report) pada modul

Popup yang tampil: daftar template (`Sales Invoice Template 12`, `TUSA-SALES INVOICE IPL`, …) → klik → PDF dibuat lewat API `prereport/generate`.
Semua infrastruktur sudah ada di tiap project; per modul hanya perlu **3 langkah kecil**.

## Cara kerja (jangan dibangun ulang)

| Bagian | Lokasi |
|---|---|
| Route `prereport.list` (GET) & `prereport.generate` (POST), `check.api.token` | `app/Modules/Tools/ReportViewer/routes.php` |
| Controller | `app/Modules/Tools/ReportViewer/PreReportController.php` |
| Popup global `<x-print-modal />` → `window.showPrintModal(oid, objectType)` | `resources/views/components/print-modal.blade.php` |
| Printer icon bawaan datatable (`action => 'prereport'`, pakai `OBJECT_TYPE` datatable) | `resources/views/components/datatable2.blade.php` |

- `list?module=X&oid=…` membaca **dbauth**: `apiprereportquery.ModuleReport = X` JOIN `devcompanyapiprereportquery` (template terdaftar per company). Jika company tak punya template → fallback `IsDefault = 1`.
- `generate` memanggil `https://api-app.elsoft.id/admin/api/v1/prereport/generate` dengan `{Oid, Module, Report(Code), action, PaperSize}`.
- **Module = nilai `apiprereportquery.ModuleReport`**, belum tentu sama dengan ObjectType modul (mis. print dari list `lotcontract` memakai `LotContract`). Cek dulu:
  `SELECT Code, Name, ModuleReport, IsDefault FROM apiprereportquery WHERE GCRecord IS NULL AND ModuleReport LIKE '%<modul>%'` (dbauth, SELECT saja).

## Langkah implementasi

### 1. Action di `<Modul>Action::actionsFor()` (atau `getActions()` controller)
```php
$actions[] = [
    'key' => 'print',
    'label' => 'Print Invoice',
    'icon' => 'tabler:printer',
    'url' => null,
    'type' => 'action',        // ← dispatch event 'moduleAction' (dtHandleAction)
    'variant' => 'default',
    'context' => ['datatable'], // tambah 'openview','form3view' hanya bila handler di sana juga ada
];
```

### 2. View `index.blade.php` modul
```blade
@section('content')
    @parent
    <x-print-modal />   {{-- sekali per halaman --}}
@endsection
```
Dan di listener `moduleAction` yang sudah ada (paling atas):
```js
if (e.detail.key === 'print') {
    window.showPrintModal(e.detail.rowId, 'ModuleReportName'); // = apiprereportquery.ModuleReport
    return;
}
```
Jika modul belum punya listener `moduleAction`, buat: `document.addEventListener('moduleAction', function(e){ ... })` di `@push('js')`.
Halaman yang extends `module.show` sudah punya `<x-print-modal />` — jangan dobel.

### 3. Filter company = company RECORD, bukan session (penting)
User bisa login di company induk (mis. `snl.group`) sementara record milik company anak (`snl.bth`); template terdaftar per company, jadi filter session menghasilkan popup kosong ("No print reports available for this module").
Solusi (sudah ada di tenant): `print-modal` mengirim `&oid=`, dan `PreReportController::list` memetakan module → tabel record:
```php
private const RECORD_COMPANY_TABLES = [
    'lotcontract' => 'mstlotcontract',   // lowercase module => tabel primary DB (punya kolom Company)
];
```
→ `Company` diambil dari record; tanpa mapping/oid tetap pakai company session.
**Modul baru: tambahkan 1 baris ke konstanta ini.** Jika `PreReportController` / `print-modal` project belum punya `RECORD_COMPANY_TABLES` / `&oid=`, porting dari `ai-laravel-tenant` dulu (2 file kecil).

## Registrasi template (DATA — dikerjakan developer manual di SQL editor, bukan migrasi)
Template baru muncul untuk company X hanya bila ada baris di `devcompanyapiprereportquery`:
```sql
INSERT INTO devcompanyapiprereportquery (Oid, Company, ApiPrereportQuery, Code, Name, CreatedAt)
VALUES (UUID(), '<company Oid>', '<apiprereportquery.Oid>', '<Code>', '<Code>', NOW());
```
`IsDefault=0` + company belum terdaftar → tidak tampil. Jangan menulis data ini via kode/migrasi.

## Debug "No print reports available"
1. Cek `module` yang dikirim = `ModuleReport` persis (case-insensitive).
2. Cek Company record (bukan session) punya baris di `devcompanyapiprereportquery`.
3. Cek `p.Name` tidak mengandung `COPY` dan `GCRecord IS NULL` di kedua tabel.
4. Network tab: `GET /api/prereport/list?module=…&oid=…` → lihat `reports`/`message`.

## Checklist
- [ ] ModuleReport dipastikan dari DB
- [ ] Action `print` + listener `moduleAction` + `<x-print-modal />`
- [ ] Mapping `RECORD_COMPANY_TABLES` untuk modul
- [ ] Template terdaftar di `devcompanyapiprereportquery` untuk company record
- [ ] `php -l` + coba klik di browser
