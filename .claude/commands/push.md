# Push Workflow — Changelog + AppChangelog DB

Jalankan langkah berikut secara berurutan. Jangan skip langkah apapun.

---

## 1. Cek ada perubahan atau tidak

```
git status --short
```

Jika output kosong (tidak ada perubahan), beri tahu user "Tidak ada perubahan untuk di-commit" dan berhenti. Jangan lanjutkan.

---

## 2. Dapatkan identitas user + ticket

```
git config user.name 2>/dev/null || echo "unknown"
TZ=Asia/Jakarta date +%m%d%H%M
```

Sanitize `user.name`: lowercase, spasi → `-`, hapus karakter non-alfanumerik kecuali `-`.

Ticket format: `{MMDDHHmm}-{gituser}`
Contoh: `0626154512-williamser`

---

## 3. Buat deskripsi perubahan

Buat daftar semua perubahan dalam sesi ini. Format per item:

```
- [Module] Where: file/fitur — What: masalah atau request — Fix: solusi atau hasil
```

---

## 4. Insert ke appchangelog (dbauth)

Gunakan `php artisan tinker` untuk insert ke table `appchangelog` di koneksi **dbauth**:

```php
DB::connection('dbauth')->table('appchangelog')->insert([
    'Oid' => (string) Illuminate\Support\Str::uuid(),
    'Ticket' => 'TICKET_ID',
    'UserName' => 'GIT_USER',
    'Project' => 'ai-laravel-logistic',
    'Description' => 'deskripsi perubahan',
    'CreatedAt' => now()->setTimezone('+07:00'),
]);
```

> `Oid` wajib UUID. `CreatedAt` pake WIB (+7).

---

## 5. Git add + commit

```
git add -A
```

Commit message:

```
TICKET_ID: https://elco.elsoft.id:8443/git/TICKET_ID

Deskripsi perubahan (multi-line)
```

Contoh:

```
0626154512-williamser: https://elco.elsoft.id:8443/git/0626154512-williamser

[CashBank] add Show Related action with openTable component
[Search] add open new tab icon for menu results
[Docs] add migration 026_create_appchangelog_table
```

---

## 6. Git pull + push

```
git pull --rebase && git push
```

Jika push gagal karena branch belum ada di remote:

```
git push --set-upstream origin $(git branch --show-current)
```

---

## 7. Konfirmasi ke user

Tampilkan:

- ✓ Ticket: `TICKET_ID`
- ✓ AppChangelog inserted
- ✓ Commit: `TICKET_ID: https://elco.elsoft.id:8443/git/TICKET_ID`
- ✓ Push ke branch: `{nama branch}`

