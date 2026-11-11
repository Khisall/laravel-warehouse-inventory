---
description: Git add, commit with appchangelog, and push
---

BANTU SAYA MELAKUKAN GIT PUSH LENGKAP DENGAN APPCHANGELOG.

## Perubahan saat ini (staged + unstaged)

### Git diff (staged):
!`git diff --cached`

### Git diff (unstaged):
!`git diff`

### Status:
!`git status`

### Recent commits:
!`git log --oneline -10`

### Nama git user:
!`git config user.name`

### Tanggal WIB:
!`TZ=Asia/Jakarta date +%m%d%H%M`

## Instruksi

1. Analisis semua perubahan dari output di atas.
2. Generate ticket: `{MMDDHHmmss}-{gituser}` (contoh: `0626154512-williamser`)
3. Generate deskripsi perubahan dalam bahasa Indonesia, format per baris:
   `[Module] Where: — What: — Fix:`
4. Insert ke `appchangelog` (dbauth) via:
   ```
   php artisan tinker --execute '...'
   ```
   Field `Project` diisi `ai-laravel-logistic`.
5. Stage semua file: `git add -A`
6. Commit dengan format:
   - Title: `TICKET: https://elco.elsoft.id:8443/git/TICKET`
   - Body: daftar deskripsi perubahan
7. `git pull --rebase && git push`

JANGAN tanya lagi, langsung eksekusi.
