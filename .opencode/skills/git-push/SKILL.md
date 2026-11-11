---
name: git-push
description: Git add, commit with appchangelog, and push
license: MIT
compatibility: opencode
metadata:
  workflow: git
---

## What I do

- Generate ticket number, insert changelog to `appchangelog` (dbauth), commit, pull, push

## When to use me

Use whenever the user says "push", "commit", "push aja", or similar.

## Workflow

1. **Baca perubahan:**
   
   - `git diff --cached` (staged)
   - `git diff` (unstaged)
   - `git status`
   - `git config user.name`
   - `TZ=Asia/Jakarta date +%m%d%H%M`
2. **Generate ticket:**
   `{MMDDHHmm}-{gituser}` e.g. `0626154512-williamser`
3. **Buat deskripsi perubahan** format per baris:
   `[Module] Where: — What: — Fix:`
4. **Insert ke appchangelog (dbauth):**
   
   ```php
   DB::connection('dbauth')->table('appchangelog')->insert([
       'Oid' => (string) Illuminate\Support\Str::uuid(),
       'Ticket' => 'TICKET',
       'UserName' => 'GIT_USER',
       'Project' => 'ai-laravel-logistic',
       'Description' => 'deskripsi',
       'CreatedAt' => now()->setTimezone('+07:00'),
   ]);
   ```
5. **Commit:** `TICKET: https://elco.elsoft.id:8443/git/TICKET` + body deskripsi
6. **Push:** `git pull --rebase && git push`

