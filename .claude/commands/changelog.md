Jalankan langkah berikut secara berurutan:

**1. Dapatkan identitas user**

Jalankan perintah bash berikut:

```bash
git config user.name 2>/dev/null || echo "unknown"
```

Ambil hasilnya sebagai `GIT_NAME`. Sanitize menjadi lowercase, spasi diganti `-`, hapus karakter non-alfanumerik kecuali `-`.
Contoh: "William Ser" → `william-ser`, "Budi Santoso" → `budi-santoso`.

**2. Dapatkan tanggal hari ini**

Jalankan:

```bash
date +%Y%m%d
```

Hasilnya adalah `DATE` (format YYYYMMDD, contoh: `20260622`).

**3. Tentukan path file**

```
docs/changeslog/{GIT_NAME}-{DATE}.md
```

**4. Cek apakah file sudah ada**

Gunakan Read tool untuk cek file tersebut. Jika sudah ada, mode = **append**. Jika belum ada, mode = **create**.

**5. Tulis changelog**

Buat summary dari semua yang dikerjakan dalam sesi ini. Format setiap item:

```
- [NamaModule] Where: file/fitur — What: masalah/request — Fix: apa yang diubah/dibuat
```

Untuk sesi **create** (file baru), tulis seluruh file:

```markdown
# Changelog — {GIT_NAME} — {YYYY-MM-DD}

## Sesi {HH:MM}

- [Module]: ... — : ... — Fix: ...
- [Module]: ... — : ... — Fix: ...
```

Untuk sesi **append** (file sudah ada), tambahkan di bawah konten yang ada:

```markdown
## Sesi {HH:MM}

- [Module]: ... —: ... —: ...
```

Dapatkan waktu dengan:

```bash
TZ=Asia/Jakarta date +%H:%M
```

**6. Konfirmasi**

Setelah file ditulis, tampilkan ke user:

- Path file yang ditulis
- Jumlah item yang dicatat
- Preview 3 baris pertama dari yang baru ditulis

**Aturan penulisan summary:**

- Maksimal 1 baris per perubahan — padat, tidak bertele-tele
- `[Module]` = nama modul/area (CRM, Auth, OpenProfile, Datatable, dll)
- `Where` = file atau fitur yang terdampak
- `What` = masalah atau permintaan user (singkat)
- `Fix` = solusi atau hasil akhir
- Kalau session ini hanya tanya-jawab atau dokumentasi tanpa perubahan kode, tulis `[Docs]` atau `[QnA]`
- Jangan tulis hal teknis internal yang tidak relevan untuk dibaca programmer lain

