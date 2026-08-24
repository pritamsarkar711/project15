#!/bin/bash
# Huvanti — Hostinger shared hosting post-deploy script
# ----------------------------------------------------------------
# This script is safe to run on Hostinger shared hosting where:
#   - proc_open is DISABLED (so composer cannot run)
#   - npm is unavailable (so vite build cannot run)
#   - SSH may be unavailable
#
# vendor/ and public/build/ are committed to git, so composer + npm
# steps are NOT needed. This script only does safe runtime tasks:
#   1. Ensures storage/ + bootstrap/cache/ are writable (775)
#   2. Ensures public/storage symlink exists (for image uploads)
#   3. Clears any stale Laravel caches
#   4. Prints a friendly reminder to run the web installer
#
# The user then opens https://huvanti.com/install.php to set up
# MySQL + admin credentials.
#
# NOTE: composer.json + composer.lock have been moved to
#       .composer-backup/ so Hostinger's auto-detect does not try
#       to run `composer install` during deploy.

set -e
echo "=== Huvanti post-deploy starting ==="
php -v

echo "[1/4] Setting permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chmod 775 . 2>/dev/null || true

echo "[2/4] Ensuring storage directories exist..."
mkdir -p storage/app/public
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
touch storage/logs/laravel.log

echo "[3/4] Creating public/storage symlink..."
# public/storage -> ../storage/app/public
if [ ! -L public/storage ]; then
    ln -sf ../storage/app/public public/storage || true
fi

echo "[4/4] Done."
echo ""
echo "Now open https://huvanti.com/install.php in your browser"
echo "to enter your MySQL DB details + admin credentials."
echo ""
echo "After install, log in at https://huvanti.com/manage"
