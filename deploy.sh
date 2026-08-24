#!/usr/bin/env bash
# Huvanti — Hostinger post-deploy checks

set -Eeuo pipefail

readonly DEPLOYMENT="2026-08-24-hostinger-launch-v3"
echo "=== Huvanti ${DEPLOYMENT} post-deploy ==="

php -v
php -r 'if (PHP_VERSION_ID < 80300) { fwrite(STDERR, "Huvanti requires PHP 8.3+\n"); exit(1); }'

echo "[1/5] Ensuring runtime directories exist..."
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache
touch storage/logs/laravel.log

echo "[2/5] Setting runtime permissions..."
chmod 755 . 2>/dev/null || true
find storage bootstrap/cache -type d -exec chmod 775 {} + 2>/dev/null || true
find storage bootstrap/cache -type f -exec chmod 664 {} + 2>/dev/null || true

echo "[3/5] Ensuring public/storage points at uploaded media..."
if [[ -e public/storage && ! -L public/storage ]]; then
    echo "WARNING: public/storage exists but is not a symlink; inspect it manually." >&2
elif [[ ! -L public/storage ]]; then
    ln -s ../storage/app/public public/storage 2>/dev/null || true
fi

# Composer install if composer.json exists and composer is available.
# This ensures vendor/ has a correct autoloader even if the committed vendor/
# was partially deployed by Git.
echo "[4/5] Checking Composer dependencies..."
if [[ -f composer.json ]] && command -v composer &>/dev/null; then
    echo "Running composer install --no-dev --optimize-autoloader..."
    composer install --no-dev --optimize-autoloader --no-interaction --no-ansi 2>&1 || {
        echo "WARNING: composer install failed — falling back to committed vendor/" >&2
    }
elif [[ -f composer.json ]]; then
    echo "composer.json found but composer CLI not available — using committed vendor/"
else
    echo "No composer.json — using committed vendor/"
fi

echo "[5/5] Verifying vendored Laravel entry points..."
test -f vendor/autoload.php
test -f vendor/composer/autoload_static.php
test -f vendor/laravel/framework/src/Illuminate/Foundation/Application.php

cat <<MESSAGE
Deployment verified: ${DEPLOYMENT}
Next:
  1. Confirm https://huvanti.com/deployment.json reports this marker.
  2. For a new site, open https://huvanti.com/install.php.
  3. Delete install.php and doctor.php after setup/recovery.
MESSAGE
