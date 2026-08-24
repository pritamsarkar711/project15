# Huvanti — Hostinger Deployment Guide

This guide walks you through deploying Huvanti on Hostinger shared business hosting.
After deployment you'll have a one-step web installer where you enter your MySQL
database details + admin credentials, and the site is ready to use.

---

## 🚨 EMERGENCY RECOVERY (blank HTTP 500 / "Class Illuminate\Foundation\Application not found")

If your site is **down right now** — homepage shows a blank `HTTP ERROR 500` and/or
`install.php` fails with `Class "Illuminate\Foundation\Application" not found` —
follow these steps **in order**. Stop as soon as the homepage loads.

> **Why this happens:** the Composer autoloader maps inside `vendor/composer/` got
> damaged or `vendor/` was incompletely deployed, so PHP cannot find Laravel's
> classes. The committed code detects and self-heals this automatically — **but
> only if the current version of the files is actually on your server.** If you are
> still seeing the raw error, your server is running an older copy of the code.

### Step 0 — Diagnose in 30 seconds (upload one file)

Upload the single **`doctor.php`** file (it's in the repo root) to `public_html/`
via **hPanel → File Manager**, then open:

```
https://huvanti.com/doctor.php
```

It shows a green/red health checklist pinpointing the exact problem and has
one-click repair buttons. **Delete `doctor.php` when you're done** (it has a
self-delete button).

### Step 1 — Click "Restore the Composer autoloader"

On `doctor.php` (or `install.php?repair=1`), click **Restore the Composer
autoloader**. This copies the good maps from `bootstrap/autoload_backup/` back over
`vendor/composer/`. Then reload the homepage. This alone fixes the issue in most cases.

### Step 2 — If that didn't work, do a CLEAN re-deploy (most reliable)

The Git deploy tool sometimes serves a stale or partial copy. Do a fresh manual deploy:

1. Download the repo as a ZIP: GitHub → `Code` → **Download ZIP** (or clone locally + zip).
2. In **hPanel → File Manager**, go to `public_html/`.
3. **Important:** back up your existing `.env` if it exists (it holds your DB
   credentials) — copy it somewhere safe.
4. Delete the old `vendor/` folder entirely (a half-deployed vendor is the usual culprit).
5. Upload the ZIP to `public_html/`, right-click → **Extract**.
6. If extraction created a subfolder (e.g. `project15-main/`), move its contents
   up into `public_html/` directly so `install.php`, `vendor/`, `public/` are all
   at the `public_html/` level.
7. Restore your `.env` if you backed it up in step 3.
8. Set folder permissions: `storage/` and `bootstrap/cache/` → **755** (or 775).
9. Open `https://huvanti.com/` — it should load. If not, open `doctor.php` again.

### Step 3 — Finish setup (if you never completed the installer)

If your database is empty / `.env` is missing, open:

```
https://huvanti.com/install.php
```

Fill in your MySQL details (from hPanel → MySQL Databases) + admin credentials and
click **Install**. The current `install.php` self-heals the autoloader before doing
anything, so the old "Class not found" crash can no longer happen during setup.

### Quick reference for the most common errors

| Symptom | Fix |
|---------|-----|
| `Class "Illuminate\Foundation\Application" not found` | Autoloader clobbered / vendor incomplete. `doctor.php` → **Restore autoloader**, or clean re-deploy (Step 2). |
| Blank `HTTP ERROR 500` on every page | Same as above — the front controller can't boot Laravel. `doctor.php` tells you which red item is blocking it. |
| `Composer detected issues in your platform … PHP version` | Set PHP **8.3** in hPanel → PHP Configuration. |
| `storage/ not writable` | File Manager → `storage/` → Permissions → **755**, apply to subdirectories. |
| `DB connection failed` | Check DB name/user/password in hPanel → MySQL Databases; user needs ALL PRIVILEGES. |
| Works after fix, then breaks again later | Re-run Git → Deploy WITHOUT a post-deploy script that calls composer. The repo intentionally ships no root `composer.json`. |

---

## TL;DR (quick path)

1. Push the repo to GitHub (already done at <https://github.com/pritamsarkar711/project15>).
2. In Hostinger hPanel → Websites → your site → **Git** → connect the repo.
3. Set **Branch** = `main`, **Document Root** = `public_html`, click **Deploy**.
4. Once files are deployed, open **https://huvanti.com/install.php** in your browser.
5. Fill in MySQL details + admin email + admin password → click **Install Huvanti**.
6. Installer runs migrations, seeds demo posts, creates your admin user, deletes itself.
7. Visit **https://huvanti.com/manage** → log in with your admin email + password.

That's it. No SSH, no composer, no artisan needed.

---

## Why this approach

Hostinger shared hosting disables `proc_open` for security reasons, which means
**Composer cannot run on the server** (it relies on `proc_open` to invoke subprocesses).

To work around this we:

- **Commit the entire `vendor/` directory** to git. The repo is ~92 MB heavier but
  the tradeoff is: zero composer step on the server.
- **Ship WITHOUT a root `composer.json`.** Hostinger's Git auto-deploy detects
  that file and runs `composer install` — which regenerates `vendor/composer/`
  from whatever `composer.json` declares. A dependency-less composer.json once
  stripped every framework mapping from the autoloader and took the whole site
  down with a blank HTTP 500. No composer.json → Composer never runs → the
  committed autoloader stays exactly as it is in git. Laravel's one runtime use
  of composer.json (`Application::getNamespace`) is replaced by the override in
  `app/Application.php`. The real composer.json/composer.lock live in
  `.composer-backup/` for local development only.
- **Bundle a self-contained `install.php`** at the project root. It writes `.env`,
  runs migrations via `Artisan::call()` (in-process, no shell), seeds demo data,
  and creates your admin user — all from the browser. It also hosts an emergency
  **repair console** at `install.php?repair=1` (see troubleshooting below).
- **Commit the compiled `public/build/` assets** so you don't need `npm run build`
  on the server either.
- **Keep pristine autoloader backups** in `bootstrap/autoload_backup/`. If
  anything ever damages `vendor/composer/`, `public/index.php` restores them
  automatically on the next request — and shows a readable diagnostic page
  instead of a blank HTTP 500 if it can't.

---

## Pre-deployment checklist (if you forked the repo)

If you forked the project, before deploying make sure the following are committed:

- [ ] `vendor/` — Laravel's dependencies (committed, not gitignored)
- [ ] `public/build/` — Vite-compiled CSS/JS (committed, not gitignored)
- [ ] `install.php` — the web installer (committed at project root)
- [ ] `.htaccess` — the root rewrite rules (committed at project root)
- [ ] `storage/framework/{cache,sessions,views}/` directories with `.gitkeep` files
- [ ] `bootstrap/cache/` directory

Run this locally to regenerate if needed:

```bash
composer install --no-dev --prefer-dist
npm install && npm run build
```

---

## Step-by-step Hostinger deployment

### Step 1 — hPanel preparation

1. Log into **hPanel** (Hostinger control panel).
2. **Websites** → select your site (e.g. `huvanti.com`).
3. **Advanced** → **PHP Configuration** → set PHP version to **8.3** (or 8.2+).
4. In the same screen, ensure these extensions are enabled:
   `pdo_mysql`, `openssl`, `mbstring`, `tokenizer`, `gd`, `fileinfo`, `curl`, `zip`.
5. **Databases** → **MySQL Databases** → create a new database:
   - Database name: e.g. `u123456789_huvanti`
   - Username: e.g. `u123456789_admin`
   - Password: (generate a strong one and save it)
   - Assign the user to the database with **ALL PRIVILEGES**

### Step 2 — Connect Git & deploy

1. **hPanel** → **Websites** → your site → **Git** (or "Deploy from Git").
2. Authorize your GitHub account if not already done.
3. Select the repository: **pritamsarkar711/project15** (or your fork).
4. Branch: **main**
5. Document root: **public_html** (Hostinger's default for shared hosting).
   - If your host plan lets you choose a custom document root, set it to
     `public_html/public` instead, and skip the root `.htaccess` rewrite rules.
6. Click **Deploy / Pull** — Hostinger clones the repo into `public_html/`.

If you saw the earlier error `The Process class relies on proc_open` — that was
Hostinger trying to run `composer install` after detecting a root
`composer.json`. The repo no longer ships a root `composer.json` (and commits
`vendor/` instead), so Composer never runs during deploy and the error is gone.

### Step 3 — Run the web installer

1. Open: **https://huvanti.com/install.php**
2. The installer shows a requirements check at the top. All items should be green ✅.
   - If `vendor/autoload.php` is missing: Git didn't deploy properly — re-pull.
   - If a directory is not writable: use File Manager → right-click → Permissions → 755.
3. Fill in the form:
   - **MySQL**: host (`localhost`), port (`3306`), DB name, username, password
     (from step 1).
   - **Site URL**: `https://huvanti.com` (full URL with `https://`).
   - **Site Name**: e.g. `Huvanti` or `My Blog`.
   - **Admin Name**: e.g. `Pritam Sarkar`.
   - **Admin Email**: e.g. `admin@huvanti.com` (your real email).
   - **Admin Password**: choose a strong one (min 8 chars).
4. Click **Install Huvanti →**.
5. Wait 10–30 seconds. Migrations + seeders run, admin user is created.
6. The installer shows a success page with links to your site + admin panel, and
   **deletes itself** for security.

### Step 4 — Verify

1. Visit **https://huvanti.com/** — homepage should render with the hero, categories,
   latest posts.
2. Visit **https://huvanti.com/manage** — log in with your admin email + password.
3. You should see the admin dashboard with stats (Posts, Views, Pending Comments,
   Unread Messages) and the sidebar (Posts, Categories, Pages, Advertisements,
   Comments, Messages, Profile, Settings, Security).

### Step 5 — Post-install cleanup (optional but recommended)

1. In hPanel → File Manager → check that `install.php` is gone. If it's still there
   (server didn't allow self-delete), **delete it manually**.
2. Verify `storage/app/installed.lock` exists — this prevents re-running the installer.
3. Set up HTTPS if not already (hPanel → SSL → install free Let's Encrypt).
4. (Optional) Set up cron for queue workers + scheduled tasks:
   ```
   * * * * * cd /home/u123456789/domains/huvanti.com/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
   ```

---

## If the installer shows errors

| Error | Fix |
|-------|-----|
| `vendor/autoload.php missing` | Re-deploy from git. The repo includes `vendor/` (~92 MB). |
| `Migration/setup failed: Class "Illuminate\Foundation\Application" not found` | The Composer autoloader maps in `vendor/composer/` were damaged (Hostinger regenerating them) or `vendor/` was incompletely deployed. **Re-deploy from Git** so the current `install.php` (which detects and self-heals this automatically) is on the server, then retry the installer. If it still fails, open `install.php?repair=1` → **Restore autoloader**; if the checklist shows `Laravel framework files → missing`, delete `vendor/` in File Manager and Git → Deploy again. |
| `storage/ not writable` | File Manager → right-click `storage/` → Permissions → set to 755 (or 775). Apply recursively. |
| `bootstrap/cache/ not writable` | Same — set permissions to 755/775. |
| `DB connection failed` | Verify DB host (`localhost`), DB name, username, password in hPanel → MySQL Databases. |
| `Migration failed: SQLSTATE[42S01] Base table already exists` | Your DB already has data. Drop all tables in phpMyAdmin (or drop + recreate the database in hPanel) and re-run installer. |
| `Migration failed: SQLSTATE[42000] Access denied` | Your DB user lacks privileges. In hPanel → MySQL → Users → edit user → check ALL PRIVILEGES. |
| Blank white page after install | Check `storage/logs/laravel.log`. Most likely a permissions issue on `storage/`. |
| `500` on homepage but admin works | Probably `php artisan storage:link` is missing. Run via File Manager → Terminal, or via cron: `php artisan storage:link`. |

---

## If the live site shows a blank HTTP 500

A blank "HTTP ERROR 500" (no Laravel error page) means PHP died **before**
Laravel could boot — almost always a damaged Composer autoloader in
`vendor/composer/`, historically caused by the host running `composer install`
against a root `composer.json`. The repo now prevents and self-heals this:

1. **First, just reload the homepage.** `public/index.php` checks the
   autoloader on every request and, if the `Illuminate\` mappings are missing
   from `vendor/composer/autoload_psr4.php` **or** `autoload_static.php`
   (the authoritative map that actually feeds the runtime loader), restores
   them automatically from `bootstrap/autoload_backup/`. One reload is
   often the entire fix. `install.php` runs the same verify-and-heal before
   loading the autoloader, so the installer can no longer die with
   `Class "Illuminate\Foundation\Application" not found` either.
2. **If it still fails, open the repair console:** `https://huvanti.com/install.php?repair=1`.
   It runs on plain PHP (works even when Laravel can't boot) and offers:
   - **Restore the Composer autoloader** — copies the pristine maps back over
     the damaged ones.
   - **Run pending migrations** — applies new DB schema after a code update.
   The page also shows a red/green health checklist pinpointing the problem.
3. **If the checklist says `vendor/laravel/framework/` is missing**, the
   package files themselves were deleted. In hPanel → File Manager delete the
   whole `vendor/` folder, then run **Git → Deploy** to restore the committed
   copy.
4. **If the checklist flags the PHP version**, set it to **8.3** in
   hPanel → Websites → your site → Advanced → PHP Configuration.

The site also renders a readable diagnostic page (instead of a blank 500)
whenever Laravel fails to start, so the underlying error is always visible.

---

## If Hostinger's "Git Deploy" tool keeps failing

Hostinger's Git deploy tool runs `composer install` by default. If you cannot disable
that step in the deploy settings, use **manual FTP upload** instead:

1. Download the project as a ZIP from GitHub (or `git clone` locally then zip).
2. hPanel → File Manager → upload the ZIP to `public_html/`.
3. Right-click → Extract.
4. Move all files from the extracted subfolder to `public_html/` directly.
5. Open `https://huvanti.com/install.php`.

---

## Server requirements (minimum)

- PHP **8.2+** (8.3 recommended)
- MySQL **5.7+** (or MariaDB 10.3+)
- Extensions: `pdo_mysql`, `openssl`, `mbstring`, `tokenizer`, `gd`, `fileinfo`, `json`, `curl`
- Writable: `storage/`, `bootstrap/cache/`, project root (for `.env` writing)
- Apache with `mod_rewrite` (default on Hostinger) — or nginx with try_files

---

## Default seeded content

After install, your DB contains:

- 1 admin user (the one you created via the installer)
- ~10 sample posts across 6 categories (Technology, Health, Finance, Travel, Lifestyle, Education)
- 6 categories with icons
- 4 navigation items
- 6 FAQ entries
- 5 demo pages (About, Contact, Privacy Policy, Terms, FAQ)
- 0 advertisements (placeholders deleted in round 3)

Log in to `/manage` to edit/delete the demo content and start publishing your own.
