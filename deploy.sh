#!/usr/bin/env bash
# Huvanti — optional Hostinger post-deploy checks (no Composer, npm or Artisan)

set -Eeuo pipefail

readonly DEPLOYMENT="2026-08-24-hostinger-launch-v2"
echo "=== Huvanti ${DEPLOYMENT} post-deploy ==="

# A root Composer manifest is the exact trigger that broke production: the old
# dependency-less deployment stub made Hostinger erase Illuminate mappings.
if [[ -e composer.json || -e composer.lock ]]; then
    echo "FATAL: composer.json/composer.lock exists at the project root." >&2
    echo "Remove stale root manifests and deploy current main again." >&2
    exit 1
fi

if [[ ! -f public/deployment.json ]] || ! grep -q "${DEPLOYMENT}" public/deployment.json; then
    echo "FATAL: deployment marker does not match ${DEPLOYMENT}." >&2
    exit 1
fi

php -v
php -r 'if (PHP_VERSION_ID < 80300) { fwrite(STDERR, "Huvanti requires PHP 8.3+\n"); exit(1); }'

echo "[1/4] Ensuring runtime directories exist..."
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache
touch storage/logs/laravel.log

echo "[2/4] Setting runtime permissions..."
# Hostinger normally executes PHP as the account owner. Keep application source
# read-only to other users and only make runtime paths group-writable.
chmod 755 . 2>/dev/null || true
find storage bootstrap/cache -type d -exec chmod 775 {} + 2>/dev/null || true
find storage bootstrap/cache -type f -exec chmod 664 {} + 2>/dev/null || true

echo "[3/4] Ensuring public/storage points at uploaded media..."
if [[ -e public/storage && ! -L public/storage ]]; then
    echo "WARNING: public/storage exists but is not a symlink; inspect it manually." >&2
elif [[ ! -L public/storage ]]; then
    ln -s ../storage/app/public public/storage
fi

echo "[4/4] Verifying vendored Laravel entry points..."
test -f vendor/autoload.php
test -f vendor/composer/autoload_static.php
test -f vendor/laravel/framework/src/Illuminate/Foundation/Application.php

cat <<MESSAGE
Deployment files verified: ${DEPLOYMENT}
Next:
  1. Confirm https://huvanti.com/deployment.json reports this marker.
  2. For a new site, open https://huvanti.com/install.php.
  3. Delete install.php and doctor.php after setup/recovery.
MESSAGE
