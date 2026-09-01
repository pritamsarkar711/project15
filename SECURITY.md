# Security Policy & Data-Safety Notes

This repository is public — that is safe **by design**. Everything sensitive
about the running site lives OUTSIDE the repository. This document explains
what is (and is not) in here, and how the running site protects its data.

## What is NOT in this repository

| Data | Where it actually lives |
|------|------------------------|
| `.env` (APP_KEY, DB credentials, SMTP, IndexNow key) | On the production server only. Never committed — verified across the full git history. |
| Database (users, posts, settings) | On the production MySQL server. Only migrations/schema are in git. |
| Uploaded media (images) | Server storage (`storage/app/public`), not in git. |
| Social media tokens (X, Facebook, LinkedIn, Instagram, Telegram, Pinterest) | **Encrypted at rest** in the site database (see below), never in git. |
| AI provider API keys (NVIDIA NIM etc.) | **Encrypted at rest** in the site database, never in git, never sent to browsers. |
| Admin credentials | Password-hashed (bcrypt) in the production DB. |

Files like `public/78e73db1…txt` (IndexNow ownership key) and
`public/google724d7b…html` (Search Console verification) are **public by
design** — search engines require them to be fetchable by anyone; they prove
site ownership and carry no access rights.

## Application-level protections built into the code

- **Secrets in settings are encrypted at rest** with Laravel's `Crypt`
  facade (AES-256-GCM, keyed by the server-only `APP_KEY`). See
  `app/Services/Social/SocialAutoPostService.php` and
  `app/Services/Ai/AiAssistantService.php`.
- **Masked in the UI**: after saving a token, the admin panel only ever shows
  `configured — ends "…xxxx"`. Secrets are never echoed back to any browser.
- **AI proxy**: the browser never talks to the AI provider. All calls go
  through an authenticated, throttled, quota-limited server endpoint
  (`app/Http/Controllers/Frontend/AiAssistantController.php`). The API key,
  base URL and model list never reach client-side JavaScript.
- **Admin panel** lives at `/manage` (not linked publicly), protected by
  role middleware, optional TOTP 2FA, and login rate-limiting.
- **Author posts are sandboxed**: authors can only query/modify their own
  rows (scoped queries), cannot publish directly, and uploads are validated
  + sanitized.
- **Content sanitization**: all submitted HTML passes `HtmlSanitizer` before
  storage; output uses Blade escaping.
- **CSRF** tokens on every form, **XSS-safe** templating, SQL via Eloquent
  (parameterized), path-traversal guard on the `/storage` streaming route.
- **Rate limits** on login, registration, password reset, contact form and
  the AI endpoint.

## Reporting a vulnerability

Please open a private GitHub security advisory ("Report a vulnerability"
button on this repo's Security tab) instead of a public issue.

## Deployment notes

The production deploy pipeline (Hostinger git integration) runs
`composer install` and applies pending migrations automatically
(`app/Providers/AppServiceProvider.php`). The committed `vendor/` tree is a
fallback for hosts where composer is unavailable — it contains only public
open-source packages and no site data.
