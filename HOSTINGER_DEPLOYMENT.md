# Huvanti — Hostinger Deployment Guide

This guide walks you through deploying Huvanti on Hostinger shared business hosting.
After deployment you'll have a one-step web installer where you enter your MySQL
database details + admin credentials, and the site is ready to use.

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
- **Bundle a self-contained `install.php`** at the project root. It writes `.env`,
  runs migrations via `Artisan::call()` (in-process, no shell), seeds demo data,
  and creates your admin user — all from the browser.
- **Commit the compiled `public/build/` assets** so you don't need `npm run build`
  on the server either.

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
Hostinger trying to run `composer install`. With `vendor/` now committed to git,
composer is no longer needed, so the error will disappear on the next deploy.

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
| `storage/ not writable` | File Manager → right-click `storage/` → Permissions → set to 755 (or 775). Apply recursively. |
| `bootstrap/cache/ not writable` | Same — set permissions to 755/775. |
| `DB connection failed` | Verify DB host (`localhost`), DB name, username, password in hPanel → MySQL Databases. |
| `Migration failed: SQLSTATE[42S01] Base table already exists` | Your DB already has data. Drop all tables in phpMyAdmin (or drop + recreate the database in hPanel) and re-run installer. |
| `Migration failed: SQLSTATE[42000] Access denied` | Your DB user lacks privileges. In hPanel → MySQL → Users → edit user → check ALL PRIVILEGES. |
| Blank white page after install | Check `storage/logs/laravel.log`. Most likely a permissions issue on `storage/`. |
| `500` on homepage but admin works | Probably `php artisan storage:link` is missing. Run via File Manager → Terminal, or via cron: `php artisan storage:link`. |

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
