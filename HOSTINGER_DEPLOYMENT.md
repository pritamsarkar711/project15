# Huvanti — Hostinger deployment and recovery

Huvanti is packaged for Hostinger shared hosting without requiring Composer or
npm on the server. The repository intentionally includes `vendor/` and
`public/build/`. The root `composer.json` and `composer.lock` are also tracked
on purpose: Hostinger's Git auto-deploy detects them and runs `composer install`
with the correct dependency list, which keeps the generated autoloader valid.
The committed `vendor/` tree stays in place as a fallback for checkouts where
Composer is unavailable or fails, and `bootstrap/autoload_backup/` provides a
pristine set of generated loader files for self-repair.

Current deployment marker:

```text
v69-2026-09-03-revert-arena-pr69-repo-cleanup
```

## "I pushed to GitHub but my site didn't change" — read this first

Pushing to GitHub does **not** update the files on Hostinger by itself. The two
are only connected if the hPanel **Git deployment** integration is configured,
and it must point at the right branch. This is the #1 reason changes "don't
reflect" on the live site.

### Step 1 — Check what the live server is actually running

Open this URL in your browser:

```text
https://huvanti.com/deployment.json
```

Compare the `deployment` value with the latest marker in `public/deployment.json`
on GitHub `main`. **If they differ, the server never received your push** — no
amount of cache clearing will help until the files actually arrive.

The same build marker is shown in the admin panel footer (bottom right of every
admin page).

### Step 2 — Fix the Git deployment in hPanel

1. Log in to **hPanel → Websites → huvanti.com → Git** (Git Deployment).
2. Check which **branch** is selected. It must be **`main`** (an old
   `arena/...` branch was confirmed to be served during a previous incident).
3. Check which **directory** it deploys to. It must be the same folder your
   live site is served from (`public_html` or the subfolder configured for the
   domain).
4. Click **Pull latest** (or "Update from Git") — Hostinger pulls the newest
   commit from GitHub and copies the files over.
5. Hostinger's Git integration usually auto-pulls after each push, but on some
   plans you must press **Pull latest** manually. If there is no Git section at
   all, your plan uses manual deploys — use FTP/File Manager or follow the
   recovery steps below.

### Step 3 — Finish the deploy (clear caches + run migrations)

Nothing to open — `deploy.php` was removed from the repository for security.
Deployment is automatic on `git push`, and the admin dashboard applies pending
database migrations automatically the first time it loads after a deploy.
If a manual cache clear is ever needed, run `deploy.sh` on the server or delete
`storage/framework/views/*.php` via File Manager.

### Step 4 — Hard-refresh the browser

Press `Ctrl + Shift + R` (Windows) or `Cmd + Shift + R` (Mac). Browsers cache
CSS/JS/images aggressively; a normal refresh can keep showing the old design
even when the server is already updated.

### deploy.php history note

During one incident, deploy.php 404'd because the root .htaccess only exempted
install.php and doctor.php from the rewrite into public/. deploy.php, the web
installer and doctor.php have since been removed from the repository for
security. Deployment is automatic on `git push`, and the admin dashboard now
applies pending database migrations automatically the first time it loads
after a deploy.

## Confirmed cause of the production failure

The production installer that reported:

```text
Migration/setup failed: Class "Illuminate\Foundation\Application" not found
.../public_html/install.php(210): require()
```

is not the installer on current `main`. It exactly matches obsolete branch
`arena/01a03298-project15` at commit `ba441cfa24`.

That old revision has a root `composer.json` deployment stub with autoload rules
but **no Laravel dependencies**. Hostinger detects the file and runs Composer.
Composer then regenerates `vendor/composer/autoload_*.php` from the empty
require set, removing the `Illuminate\\` mapping even though the Laravel files are
still on disk. The old installer loads that broken autoloader and requires
`bootstrap/app.php` at line 210, where `Illuminate\Foundation\Application` can no
longer be found.

There are therefore two parts to the incident:

1. Hostinger is deploying or serving the obsolete branch/wrong checkout.
2. Composer has modified that checkout's generated autoloader maps.

Changing database details cannot fix either condition.

## Production recovery — do these steps in order

### 1. Preserve server-owned data

In hPanel **File Manager**, download or copy these before replacing files:

- `public_html/.env`
- `public_html/storage/app/public/` (uploaded media, if any)

Do not publish `.env`, paste it into GitHub, or replace it with `.env.example`.

### 2. Repair the current outage with one file (optional but fastest)

> **Note:** `doctor.php` was removed from the repository for security after the
> incident. This section is kept as a historical record. If a similar outage
> ever happens again, restore it from git history
> (`git checkout <old-commit> -- doctor.php`), upload it to `public_html/`,
> recover, then DELETE it again immediately.

The doctor was genuinely standalone: it contained a compressed, checksummed copy
of all 11 pristine Composer loader/map files. It can repair the obsolete
checkout even though that checkout has no `bootstrap/autoload_backup/` folder.

The page is locked. Unlock it with the complete `APP_KEY` value from
`public_html/.env`. If `.env` does not exist, create
`public_html/.doctor-key` in File Manager with a long random password, then use
that password. Root dotfiles are denied by `.htaccess`.

Click **Restore the Composer autoloader**. This is a temporary recovery; the
obsolete branch must still be replaced or Hostinger can break it again.

Delete `doctor.php` and `.doctor-key` when recovery is complete.

### 3. Correct Hostinger's Git deployment

In **hPanel → Websites → huvanti.com → Git**:

1. Disconnect/remove the old deployment integration if it is pinned to
   `arena/01a03298-project15` or another old branch.
2. Connect `pritamsarkar711/project15` again.
3. Select branch **`main`** after the launch-fix pull request has been merged.
4. Set the deployment directory to the domain's actual project root:
   `public_html`.
5. Do not configure a command that runs `composer`, `composer install`, npm, or
   `php artisan optimize` before installation.
6. Deploy/pull.

The expected layout is:

```text
public_html/
├── .htaccess
├── app/
├── bootstrap/
├── composer.json
├── composer.lock
├── public/
├── resources/
├── routes/
├── storage/
└── vendor/
```

There must not be an extra `project15-main/` directory between `public_html/`
and these files.

### 4. Verify the root manifests are the real ones

The root `composer.json` and `composer.lock` are tracked and required. Open
`public_html/composer.json` in File Manager and confirm it lists the real
dependencies (`laravel/framework` under `require`). A file that has only
`autoload` sections and no `require` block is the obsolete dependency-less
deployment stub from the original scaffold; if you ever see it, restore the
manifest from current `main`. This check matters most after a ZIP upload that
was extracted over old files, because an overlay can leave stale files behind.

If the Git deployment cannot be trusted, use a clean directory deployment:

1. Preserve `.env` and uploaded media as described above.
2. Remove the old application files (or deploy into a clean `public_html`).
3. Extract a ZIP of current `main` directly into `public_html/`.
4. Restore `.env` and `storage/app/public/`.
5. Confirm `composer.json` lists the real dependencies.

Do not merely overwrite the old tree without deleting stale files.

### 5. Verify the exact release before installing

Open this cache-independent marker:

```text
https://huvanti.com/deployment.json
```

Expected JSON includes:

```json
"deployment": "v69-2026-09-03-revert-arena-pr69-repo-cleanup"
```

Responses also include this header:

```text
X-Huvanti-Deploy: v69-2026-09-03-revert-arena-pr69-repo-cleanup
```

A command-line check is:

```bash
curl -fsS https://huvanti.com/deployment.json
curl -fsSI https://huvanti.com/ | grep -i x-huvanti-deploy
```

Do not continue if the marker is missing. That means the domain is still
serving another document root or revision.

### 6. Configure Hostinger and finish setup

In hPanel set:

- PHP **8.3 or newer**
- extensions: `pdo_mysql`, `openssl`, `mbstring`, `tokenizer`, `gd`,
  `fileinfo`, `curl`
- permissions: `storage/` and `bootstrap/cache/` writable by PHP (normally 755;
  use 775 only if Hostinger's PHP user/group requires it)

Create a MySQL database and user with all privileges. The web installer was
removed from the repository for security, so configure the database in
`public_html/.env` directly (copy the shape from `.env.example`, never commit
that file) and let the admin dashboard apply pending migrations automatically
the first time it loads after a deploy.

If the database contains tables from a partial installation, either use the
same database and allow pending migrations to complete, or create a clean
empty database. Do not drop production data unless it has been backed up and a
clean reinstall is intentional.

### 7. Post-deploy verification

- Visit `/`, `/manage`, and `/up`.
- Confirm uploaded images load through `public/storage`.
- Confirm `https://huvanti.com/deployment.json` reports the current marker.
- If a temporary recovery file such as `doctor.php` or `.doctor-key` was
  uploaded during an outage, delete it now.

## Why the repository is packaged this way

Hostinger's automated Composer step once stripped the framework mappings from
an obsolete checkout, so the repository is protected in depth. It uses all of
the following together:

- tracked root `composer.json` and `composer.lock` with the real dependency
  list, so any Composer run on the host produces a valid autoloader;
- tracked `vendor/` with the complete Laravel dependency tree as a fallback;
- tracked `public/build/` with compiled frontend assets;
- mirrored manifests under `.composer-backup/`;
- `App\Application::getNamespace()` override so Laravel boots even on a
  checkout whose manifest is missing or damaged;
- pristine generated loader files in `bootstrap/autoload_backup/`;
- pre-bootstrap checks and automatic map restoration in `public/index.php`;
- a public deployment marker to detect stale revisions/document roots.

The old web installer (`install.php`), the deploy hook (`deploy.php`) and the
recovery page (`doctor.php`) were removed from the repository for security.
They can be restored temporarily from git history during an outage and must
be deleted again afterwards.

These pieces are not interchangeable. In particular, committing `vendor/` does
not help if Hostinger subsequently runs Composer and rewrites its generated
maps.

## Document root and rewrite behavior

For this installation workflow, keep the domain pointed at `public_html`. The
root `.htaccess` forwards every normal request into `public/`. The removed
`install.php` and `doctor.php` would be exempted from the rewrite only while
they are temporarily restored during an outage.

The corrected rules:

- stop an internal path already beginning with `public/`, preventing repeated
  `public/public/...` rewrites and HTTP 500 loops;
- do not serve existing project-root files directly;
- prevent access to application source, logs, manifests and configuration;
- emit the deployment marker header.

After installation, a custom document root of `public_html/public` is also a
valid hardened layout, but a temporarily restored root recovery file would
then not be reachable. Do not switch layouts during recovery.

## Updating dependencies locally

Use PHP 8.3+ and Composer on a development machine. The manifests are tracked
at the repository root, so edit or update them in place:

```bash
composer update
```

Then keep everything in sync and commit it together:

1. Copy the updated `composer.json` and `composer.lock` into
   `.composer-backup/` so the mirror stays identical.
2. Refresh the pristine loader copies as documented in
   `bootstrap/autoload_backup/README.md`.
3. Regenerate the doctor's embedded bundle if a doctor.php will ever be
   uploaded during an outage:

```bash
python3 tools/build-doctor-bundle.py
```

4. Commit the updated `vendor/`, manifests and backups together.

## Troubleshooting matrix

| Symptom | Meaning | Action |
|---|---|---|
| Installer says PHP 8.1+ and fails at line 210 | Obsolete installer is still served | Correct branch/document root; verify `deployment.json` |
| `Illuminate\Foundation\Application not found` | Composer maps lost `Illuminate\\`, or framework files are absent | Upload doctor and restore maps; if framework entry file is missing, clean-deploy `vendor/` |
| `deployment.json` missing/500 | Wrong or stale deployment | Stop; correct Hostinger Git settings/document root |
| Repaired site breaks after every deploy | Obsolete dependency-less manifest stub or a deploy Composer command still in place | Restore the real tracked `composer.json`, remove the command, redeploy current `main` |
| Too many internal redirects / immediate Apache 500 | Old recursive root rewrite rules | Deploy corrected `.htaccess` |
| Requirements show PHP below 8.3 | Unsupported runtime | Select PHP 8.3+ in hPanel |
| Storage/cache not writable | PHP cannot write runtime files | Correct ownership/permissions in File Manager |
| MySQL connection fails | Credentials, host, user assignment or privileges are wrong | Verify in hPanel MySQL Databases |
| Homepage fails after partial install | Database tables/session/cache may be incomplete | Unlock doctor, inspect DB status, run pending migrations |

## Server requirements

- PHP 8.3+
- MySQL 5.7+ or compatible MariaDB
- Apache/LiteSpeed rewrite support
- PHP extensions listed above
- writable `storage/`, `bootstrap/cache/`, and project root during initial `.env`
  creation

## IMPORTANT: Tailwind classes in Blade views need a CSS rebuild

The site ships a pre-compiled stylesheet in `public/build/assets/app-*.css`.
Hostinger never runs `npm run build`, so **any new Tailwind class added to a
Blade view without rebuilding will silently do nothing on the live site**.

This exact bug shipped in build v16: the hero gradient, the purple verified
badge and the affiliate disclosure background were added to views but not to
the compiled CSS, so light mode showed a white hero and invisible badges.

Rules for anyone editing views:

1. Reusing classes that already exist in the compiled CSS is always safe.
2. After adding ANY new `bg-*`, `text-*`, `rounded-*`, `from-[...]` or similar
   class to a view, the CSS must be rebuilt locally:
   - `cp .npm-backup/package.json .npm-backup/package-lock.json .` (repo root)
   - `npm install`
   - `npm run build`
   - commit the changed files in `public/build/` (the new hashed CSS and
     `manifest.json`). `package.json` is gitignored and stays local.
3. Deploy as usual (hPanel Git pull). No server-side step is needed.

Quick check that a class exists in the shipped CSS:

```bash
rg -F 'class-name' public/build/assets/app-*.css
```

Remember CSS class selectors are escaped (for example `.from-\[\#0C3B2E\]`),
so search for a distinctive fragment like the hex color instead.
