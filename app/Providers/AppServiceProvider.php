<?php

namespace App\Providers;

use App\Support\RelativeAssetUrlGenerator;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollectionInterface;
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
    }
}
