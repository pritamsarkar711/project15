<?php

namespace App\Support;

/**
 * Canonical URL map for the built-in static pages.
 *
 * Problem this solves: every policy page is reachable at TWO urls — the
 * named route (/privacy-policy) that the header/footer link to, and the
 * generic /page/privacy-policy route that renders the same DB row. Both
 * returned 200 with self-canonical tags, so search engines indexed
 * duplicates, the /page/… copies had NO internal links (Ahrefs "orphan
 * pages") and the sitemap advertised the URL variant nobody links to.
 *
 * The named route is the canonical URL (it is the one humans see). The
 * /page/{slug} route now 301-redirects onto it, the sitemap and llms.txt
 * only list the named route, and this single map is the source of truth.
 */
class StaticPages
{
    /** slug (pages table) => named route name that renders it canonically. */
    public const ROUTE_MAP = [
        'about'               => 'about',
        'contact'             => 'contact',
        'privacy-policy'      => 'privacy',
        'terms-conditions'    => 'terms',
        'cookie-policy'       => 'cookie',
        'editorial-policy'    => 'editorial',
        'disclaimer'          => 'disclaimer',
        'affiliate-disclosure'=> 'affiliate',
        'comment-policy'      => 'comments.policy',
    ];

    /**
     * Canonical public URL for a page row, or null when the slug has no
     * dedicated named route (custom admin-created pages keep /page/{slug}).
     */
    public static function canonicalUrl(string $slug): ?string
    {
        $route = self::ROUTE_MAP[$slug] ?? null;
        if ($route === null || !\Route::has($route)) {
            return null;
        }

        return route($route);
    }
}
