# Huvanti — Multi-Niche Blog (Laravel 13, PHP 8.3)

**Domain:** huvanti.com • **Brand:** Huvanti • **Tagline:** Explore Ideas. Inspire Life.

Production-ready Laravel CMS for Hostinger custom PHP hosting. Converted from SG Calculators React template, all calculators removed, UI preserved and rebuilt for blogging.

## Features

### Frontend
- Header with Huvanti logo (left), navigation Home / Categories / Blog / About / Contact (drag-&-drop manageable), hamburger mobile, theme toggle
- Hero: left text + search box, right high-quality photo of young woman with glasses reading on smartphone (transparent background, clean, green gradient #2e7d32→#1b5e20)
- Categories section (6 niches) with green palette (#2e7d32 etc), border-top accent, icon centered, original MUI design
- Latest Posts + Featured, responsive, low radius (8-12px)
- Blog Feed (`/blog`), Category pages, Search
- Single Post: featured image, author bio, publication date, reading time, Table of Contents, content, FAQ accordion, comment section (name+email private), share (X, Facebook, Pinterest, Reddit, LinkedIn, WhatsApp)
- Pages: About, Privacy, Terms, Cookie Policy, Editorial Policy (`/page/{slug}`), Contact with reason dropdown
- Footer: © {year} Huvanti. All Rights Reserved. with automatic current year, responsive, no overflow

### Backend / Admin CMS (`/manage` - secured, no frontend link)
- Dashboard with stats (posts, views, pending comments, unread contacts)
- Posts CRUD: create/edit/delete/publish/draft, featured, allow comments, featured image upload, reading time auto, CKEditor 5 full rich editor, FAQ management per post (accordion), author management
- **Social Auto-Post**: publish a post (or approve an author's) and it is automatically shared to X, Facebook Page, LinkedIn, Instagram, Telegram and Pinterest — no manual sharing, saves time on every post. Per-network credentials, test-connection buttons, delivery log with retries (`social:retry-pending` schedule optional)
- **AI Assistant**: admin plugs in a free NVIDIA NIM key (or any OpenAI-compatible API: Groq, OpenRouter, OpenAI…), authors get one-click SEO meta titles / meta descriptions / focus keywords / excerpts / FAQ suggestions plus a free-form "Ask AI" box inside the editor. Automatic model failover — if one model is busy or retired, the next takes over. API keys are encrypted at rest and never exposed to the browser; per-user daily quota
- **RankMath-style SEO score**: focus keyword field + live 0–100 on-page score while writing (keyword placement, density, title/meta lengths, word count, subheadings, internal/external links, image alt text), score persisted per post and shown as badges in the posts lists
- **Instant Indexing**: IndexNow pings on every publish/update/delete plus manual "Index now" buttons (single post or bulk) for admin and authors
- **Post-publish share screen**: after publishing, the URL appears in a copyable box with social share icons (Facebook, X, LinkedIn, WhatsApp, Telegram, Reddit, Pinterest, Email, copy link) — for admin and authors
- Pages CRUD (including Editorial Policy editable)
- Categories with color, drag-reorder
- Advertisement Management: positions (header, sidebar, inline, between_posts, footer), HTML/JS code, image, active toggle
- Contact Messages inbox (read/unread, reply)
- Comment Moderation: approve/reject/spam, pending queue
- Site Settings: name, tagline, meta description/keywords, etc. (reflects instantly on frontend via Settings model + cache)
- Navigation Management: drag & drop for header/mobile/footer separately (SortableJS)
- Security: change password, TOTP 2FA, bcrypt, CSRF
- Theme switcher: Light / Dark / Night (persists localStorage + backend `users.theme_preference`, works entire admin)
- Auth: admin role, middleware `admin`, login/logout with remember, session database

### Tech Stack
- Laravel 13.26 (PHP 8.3), Vite 8 + Tailwind 4, CKEditor 5, SortableJS, SQLite (dev) / MySQL (Hostinger prod), File storage `public`
- Production optimized: `php artisan optimize`, config/route/view cache, `npm run build`, no horizontal overflow, fully responsive (mobile/tablet/laptop/desktop)

## Local Development

> `composer.json` / `composer.lock` are **not** in the repo root on purpose —
> Hostinger's Git auto-deploy runs `composer install` whenever it sees one,
> which once regenerated the committed autoloader and took the site down.
> For local work, restore them first:
> `cp .composer-backup/composer.json composer.json && cp .composer-backup/composer.lock composer.lock`
> (and delete both from the root before pushing). See `.gitignore` for details.

```bash
# PHP 8.3 + Composer
php composer.phar install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
npm install
npm run build   # or npm run dev for HMR
php artisan serve
# Visit http://localhost:8000; admin sign-in is at /manage/login
```

## Hostinger deployment

See **[HOSTINGER_DEPLOYMENT.md](HOSTINGER_DEPLOYMENT.md)**. This repository is a
vendored shared-hosting artifact: deploy current `main` directly into
`public_html` and **do not run Composer or npm on Hostinger**. A root
`composer.json` from an obsolete deployment causes Hostinger to regenerate the
autoloader without Laravel and produces `Class
"Illuminate\\Foundation\\Application" not found`.

After deployment, verify the release before running the installer:

```bash
curl -fsS https://huvanti.com/deployment.json
# Must report: 2026-08-24-hostinger-launch-v2
```

Then verify the homepage loads. The old web installer (`install.php`), deploy
helper (`deploy.php`) and emergency doctor (`doctor.php`) were removed from the
repository for security once setup was complete — the site deploys automatically
on every `git push`.
Restore them from git history only if ever needed, and delete again afterward.

## Structure

- `app/Models` — Post, Category, Faq, Page, Advertisement, ContactMessage, Comment, Setting, NavigationItem, User
- `app/Http/Controllers/Frontend` & `Admin`
- `resources/views/layouts/app.blade.php` (frontend) & `admin.blade.php` (admin)
- `resources/views/frontend/*` — home, blog, pages
- `resources/views/admin/*` — dashboard, posts, etc.
- `database/migrations` — all tables, `seeders/DatabaseSeeder.php` — demo data
- `routes/web.php` — frontend + admin routes

## Security & privacy (public-repo safe)

See **[SECURITY.md](SECURITY.md)**. Short version: no `.env`, no database, no
credentials and no tokens are (or ever were) committed. Runtime secrets that
admins enter in the panel (SMTP, social media tokens, AI keys) are stored
**AES-256-GCM encrypted at rest** and are never rendered back to any browser.
Verification key files under `public/` (IndexNow, Search Console) are public
by design — they grant no access.

## Administrator account

The production administrator email and password were created during the initial
setup; no production credentials are stored in this README.

## License

MIT for the platform code. Built for huvanti.com. All calculators from the original template removed.
