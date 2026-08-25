<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * On shared hosting with OPcache, compiled Blade views can become stale
 * after ANY admin change (settings, posts, categories, navigation, etc.).
 *
 * This middleware detects non-GET requests to /manage/* routes and
 * automatically clears compiled Blade views + OPcache so the next
 * page load always reflects the latest data.
 *
 * This is the nuclear fix for "I changed X but nothing updates on the site."
 */
class ClearViewCacheAfterWrite
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only clear on write requests (POST, PUT, PATCH, DELETE)
        // and only for admin/manage and author-dashboard routes
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])
            && (str_starts_with($request->path(), 'manage')
                || str_starts_with($request->path(), 'author-dashboard'))) {
            $this->clearCompiledViews();
        }

        return $response;
    }

    private function clearCompiledViews(): void
    {
        $dir = storage_path('framework/views');
        if (!is_dir($dir)) return;
        foreach (glob($dir . '/*.php') as $file) {
            @unlink($file);
        }
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
    }
}
