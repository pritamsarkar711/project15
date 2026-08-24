<?php

namespace App\Providers;

use App\Models\Setting;
use App\Support\RelativeAssetUrlGenerator;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollectionInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Override the URL generator so all asset() URLs are root-relative.
        // This is REQUIRED for the Caddy preview proxy on :81 (and any reverse
        // proxy that strips the port from the Host header). Without it, asset
        // URLs come out as "http://localhost/build/..." (port 80, no server),
        // CSS/JS fail to load, and the page collapses into a vertical stack.
        $this->app->extend('url', function ($url, $app) {
            return new RelativeAssetUrlGenerator(
                $app->bound('routes') ? $app['routes'] : $app[RouteCollectionInterface::class],
                $app->rebinding('request', fn ($a, $request) => $request),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ensure the public/storage symlink exists (equivalent of `artisan storage:link`).
        // Many shared hosts (Hostinger, Namecheap, Bluehost) disable the symlink()
        // function for security — gracefully skip in that case. Users on such hosts
        // can either:
        //   (a) create the symlink manually via hPanel → File Manager, or
        //   (b) set FILESYSTEM_DISK=public (already the default) and rely on
        //       the custom route below to stream files from storage/app/public.
        $publicStorage = public_path('storage');
        if ((!file_exists($publicStorage) && !is_link($publicStorage))
            && function_exists('symlink')
        ) {
            @symlink(storage_path('app/public'), $publicStorage);
        }

        // Override Laravel mail config at runtime from the settings table so
        // the admin can configure SMTP without touching .env. Settings are
        // read here (boot) so every request picks up the latest SMTP config.
        // Failures are swallowed so a misconfigured DB never 500s the site.
        try {
            $this->overrideMailConfigFromSettings();
        } catch (\Throwable $e) {
            // DB might not be migrated yet during install; skip silently.
        }
    }

    /**
     * Override Laravel's mail.php config with values from the settings table.
     *
     * Reads the following keys (all optional, fall back to .env defaults):
     *   - mail_mailer        (smtp | log | sendmail | array)
     *   - mail_host          (e.g. smtp.gmail.com)
     *   - mail_port          (e.g. 587)
     *   - mail_username      (SMTP user)
     *   - mail_password      (SMTP password — stored encrypted-ish; we treat as plain)
     *   - mail_encryption    (tls | ssl | null)
     *   - mail_from_address  (e.g. noreply@huvanti.com)
     *   - mail_from_name     (e.g. "Huvanti")
     *
     * Admin updates these in /manage/settings?tab=email. If a key is empty,
     * we leave the .env value intact (so admins can do partial overrides).
     */
    private function overrideMailConfigFromSettings(): void
    {
        // Read all mail-related settings in one query.
        $keys = [
            'mail_mailer', 'mail_host', 'mail_port', 'mail_username',
            'mail_password', 'mail_encryption', 'mail_from_address', 'mail_from_name',
        ];
        $rows = Setting::whereIn('key', $keys)->pluck('value', 'key');

        if ($rows->isEmpty()) {
            return; // No admin-configured SMTP yet — keep using .env defaults.
        }

        $mailer = $rows->get('mail_mailer');
        if (!empty($mailer)) {
            Config::set('mail.default', $mailer);
        }

        // SMTP transport settings — only override if non-empty so admins can
        // partially override (e.g. set host but keep port from .env).
        if (!empty($rows->get('mail_host'))) {
            Config::set('mail.mailers.smtp.host', $rows->get('mail_host'));
        }
        if (!empty($rows->get('mail_port'))) {
            Config::set('mail.mailers.smtp.port', (int) $rows->get('mail_port'));
        }
        if (!empty($rows->get('mail_username'))) {
            Config::set('mail.mailers.smtp.username', $rows->get('mail_username'));
        }
        // Password can be set to empty string intentionally to clear it,
        // so we use has() over get() with default.
        if ($rows->has('mail_password') && $rows->get('mail_password') !== '') {
            Config::set('mail.mailers.smtp.password', $rows->get('mail_password'));
        }
        // Encryption: empty string means "no encryption" (override .env).
        if ($rows->has('mail_encryption')) {
            $enc = $rows->get('mail_encryption');
            Config::set('mail.mailers.smtp.scheme', $enc === '' ? null : $enc);
        }

        // Global "From" address + name.
        if (!empty($rows->get('mail_from_address'))) {
            Config::set('mail.from.address', $rows->get('mail_from_address'));
        }
        if (!empty($rows->get('mail_from_name'))) {
            Config::set('mail.from.name', $rows->get('mail_from_name'));
        }
    }
}
