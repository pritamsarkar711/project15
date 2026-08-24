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
- Footer: © 2026-27 All Rights Reserved, Built with ♥ by [Joe Goldberg](https://t.me/JoeGoldberg2025), responsive, no overflow

### Backend / Admin CMS (`/manage` - secured, no frontend link)
- Dashboard with stats (posts, views, pending comments, unread contacts)
- Posts CRUD: create/edit/delete/publish/draft, featured, allow comments, featured image upload, reading time auto, CKEditor 5 full rich editor, FAQ management per post (accordion), author management
- Pages CRUD (including Editorial Policy editable)
- Categories with color, drag-reorder
- Advertisement Management: positions (header, sidebar, inline, between_posts, footer), HTML/JS code, image, active toggle
- Contact Messages inbox (read/unread, reply)
- Comment Moderation: approve/reject/spam, pending queue
- Site Settings: name, tagline, meta description/keywords, etc. (reflects instantly on frontend via Settings model + cache)
- Navigation Management: drag & drop for header/mobile/footer separately (SortableJS)
- Security: change password, toggle 2FA (demo 123456, ready for `pragmarx/google2fa`), bcrypt, CSRF
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
# Visit http://localhost:8000, admin at /manage/login (admin@huvanti.com / Huvanti@2026)
```

## Deployment

See **HOSTINGER_DEPLOYMENT.md** for full Hostinger steps (MySQL, SSL, DocumentRoot, permissions, optimize).

Quick:
```bash
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

## Structure

- `app/Models` — Post, Category, Faq, Page, Advertisement, ContactMessage, Comment, Setting, NavigationItem, User
- `app/Http/Controllers/Frontend` & `Admin`
- `resources/views/layouts/app.blade.php` (frontend) & `admin.blade.php` (admin)
- `resources/views/frontend/*` — home, blog, pages
- `resources/views/admin/*` — dashboard, posts, etc.
- `database/migrations` — all tables, `seeders/DatabaseSeeder.php` — demo data
- `routes/web.php` — frontend + admin routes

## Credentials

- Admin: `admin@huvanti.com` / `Huvanti@2026` (change after first login)

## License

Private — Built for huvanti.com. All calculators from original template removed.
