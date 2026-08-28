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
        $this->ensureRuntimeTables();

        // ------------------------------------------------------------------
        // Self-updating database: apply pending migrations automatically.
        //
        // This site runs on Hostinger shared hosting WITHOUT SSH, and there
        // is no deploy script to run after a git push. Result: new migrations
        // (post reactions, extra categories, ...) would silently never run
        // and the new code crashed with SQL "table not found" errors. Running `migrate --force` here
        // is idempotent — when nothing is pending Laravel does nothing.
        //
        // Throttled with a filesystem flag (at most once every 10 minutes)
        // so the normal page flow pays one cheap glob + one SELECT, and the
        // actual migrate call only happens when new migration files exist.
        // Never runs inside artisan/console commands (avoids recursion) and
        // any failure is swallowed so a DB hiccup can never take the site
        // down.
        // ------------------------------------------------------------------
        $this->applyPendingMigrations();

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

    /**
     * Apply pending database migrations automatically (shared-hosting safe).
     *
     * How it works:
     *   1. Skipped entirely for console/artisan requests (avoids recursion
     *      while `php artisan migrate` itself boots the app).
     *   2. A filesystem flag throttles the check to once every 10 minutes —
     *      normal traffic pays nothing.
     *   3. When the check runs, the migrations table is compared against the
     *      migration files on disk; only when files are missing from the
     *      table is `migrate --force` invoked (idempotent).
     *   4. Every step is wrapped so a database hiccup can never turn into a
     *      site-wide 500.
     */
    private function applyPendingMigrations(): void
    {
        try {
            if ($this->app->runningInConsole()) {
                return;
            }

            $flag = storage_path('framework/.migrations_last_check');
            $last = @filemtime($flag) ?: 0;
            if (time() - $last < 600) {
                return;
            }
            @touch($flag); // stamp BEFORE checking so parallel requests do not dogpile

            if (!\Illuminate\Support\Facades\Schema::hasTable('migrations')) {
                return; // not installed yet (install.php handles setup)
            }

            $ran = \Illuminate\Support\Facades\DB::table('migrations')->pluck('migration')->all();
            $files = collect(glob(database_path('migrations/*.php')))
                ->map(fn ($f) => basename($f, '.php'));

            if ($files->diff($ran)->isNotEmpty()) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }
        } catch (\Throwable $e) {
            // Never let a migration problem take the site down — the code
            // paths that depend on new tables degrade gracefully.
        }
    }

    /**
     * Make sure the tables/directories session + cache drivers need exist.
     * Runs on every request (one cheap hasTable) so a partial install cannot
     * keep the public site in a 500 loop after a git deploy.
     */
    private function ensureRuntimeTables(): void
    {
        try {
            $sessionTable = (string) config('session.table', 'sessions');
            if (config('session.driver') === 'database' && ! \Illuminate\Support\Facades\Schema::hasTable($sessionTable)) {
                \Illuminate\Support\Facades\Schema::create($sessionTable, function ($table) {
                    $table->string('id')->primary();
                    $table->foreignId('user_id')->nullable()->index();
                    $table->string('ip_address', 45)->nullable();
                    $table->text('user_agent')->nullable();
                    $table->longText('payload');
                    $table->integer('last_activity')->index();
                });
            }
        } catch (\Throwable $e) {
            $this->fallbackSessionDriver();
        }

        try {
            $cacheTable = (string) config('cache.stores.database.table', 'cache');
            if (config('cache.default') === 'database' && ! \Illuminate\Support\Facades\Schema::hasTable($cacheTable)) {
                \Illuminate\Support\Facades\Schema::create($cacheTable, function ($table) {
                    $table->string('key')->primary();
                    $table->mediumText('value');
                    $table->bigInteger('expiration')->index();
                });
            }
            if (config('cache.default') === 'database' && ! \Illuminate\Support\Facades\Schema::hasTable('cache_locks')) {
                \Illuminate\Support\Facades\Schema::create('cache_locks', function ($table) {
                    $table->string('key')->primary();
                    $table->string('owner');
                    $table->bigInteger('expiration')->index();
                });
            }
        } catch (\Throwable $e) {
            $this->fallbackCacheDriver();
        }

        $this->ensureSessionFilesDirectory();
    }

    private function fallbackSessionAndCacheDrivers(): void
    {
        $this->fallbackSessionDriver();
        $this->fallbackCacheDriver();
    }

    private function fallbackSessionDriver(): void
    {
        \Illuminate\Support\Facades\Config::set('session.driver', 'file');
        $this->ensureSessionFilesDirectory();
        try {
            $this->app->forgetInstance('session');
            $this->app->forgetInstance('session.store');
        } catch (\Throwable $e) {
        }
    }

    private function fallbackCacheDriver(): void
    {
        \Illuminate\Support\Facades\Config::set('cache.default', 'file');
        try {
            $this->app->forgetInstance('cache');
            $this->app->forgetInstance('cache.store');
        } catch (\Throwable $e) {
        }
    }

    private function ensureSessionFilesDirectory(): void
    {
        $dir = storage_path('framework/sessions');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
    }
}
