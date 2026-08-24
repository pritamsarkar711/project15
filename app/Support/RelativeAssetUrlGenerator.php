<?php

namespace App\Support;

use Illuminate\Routing\UrlGenerator;

/**
 * UrlGenerator that emits ROOT-RELATIVE asset AND route URLs.
 *
 * Why: the preview proxy (Caddy on :81) strips the port from the Host header
 * before forwarding to Laravel on :3000. This makes Laravel's default URL
 * helpers generate URLs like "http://localhost/build/..." (port 80, no server)
 * which the browser cannot fetch, so all Tailwind CSS/JS fails to load and
 * every internal link points to a non-existent origin — the page collapses
 * into a vertical stack of unstyled elements.
 *
 * Emitting root-relative URLs ("/build/...", "/blog/foo", "/manage/posts")
 * makes the browser fetch them from the same origin the user is currently
 * visiting, regardless of whether that's localhost:3000, localhost:81, or
 * the production preview domain.
 *
 * Implementation:
 *   - formatRoot() returns '' (so no scheme+host prefix is added).
 *   - format() is overridden to undo the parent's `trim(..., '/')` that
 *     would otherwise strip the leading slash and turn "/blog/foo" into
 *     "blog/foo" (which the browser would resolve relative to the current
 *     page, breaking links on sub-pages).
 */
class RelativeAssetUrlGenerator extends UrlGenerator
{
    /**
     * Return an empty root so no scheme+host prefix is prepended.
     *
     * Parent returns scheme://host[:port] (e.g. "http://localhost").
     */
    public function formatRoot($scheme, $root = null)
    {
        return '';
    }

    /**
     * Override format() to ensure the result starts with a leading slash.
     *
     * Parent's last line is `return trim($root.$path, '/');` which strips
     * the leading slash we need. We re-add it when the URL is meant to be
     * root-relative (i.e., not already an absolute URL or fragment).
     */
    public function format($root, $path, $route = null)
    {
        $url = parent::format($root, $path, $route);

        // Already absolute, protocol-relative, fragment, or empty → leave alone
        if ($url === '' || $url[0] === '/' || $url[0] === '#' || preg_match('~^[a-z]+://|^//~i', $url)) {
            return $url;
        }

        return '/' . $url;
    }
}
