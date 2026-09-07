<?php

namespace App\Support;

/**
 * SEO title / meta-description finalizers.
 *
 * Ahrefs flagged 13 pages with "Title too short" and 4 pages with "Meta
 * description too short". Root cause: policy/about/contact/category pages
 * fell back to tiny labels ("Disclaimer", "Blog · Huvanti.com", a 37-char
 * category blurb). Blog POSTS are excluded on purpose — the owner wants
 * post titles/meta descriptions rendered exactly as authored in the
 * dashboard.
 *
 * finalizeTitle() pads short NON-post titles with the site name (and the
 * tagline when still short) so every indexable page clears the ~30 char
 * audit threshold. finalizeDescription() tops short descriptions up with
 * the site description sentence and caps the result at a word boundary.
 */
class Seo
{
    /**
     * Absolute, production-correct origin (scheme + host) for every URL we
     * hand to crawlers (canonical, og:url, JSON-LD, sitemap, llms.txt).
     *
     * Two guarantees the raw request host cannot give:
     *   1. HTTPS always. On Hostinger the edge (hCDN/LiteSpeed proxy) can
     *      forward HTTPS visitor traffic to the origin over plain HTTP.
     *      Without TrustProxies Laravel's isSecure() is false, so a naïve
     *      getSchemeAndHttpHost() emitted "http://huvanti.com/…" inside
     *      canonical tags — while the .htaccess 301-redirects every http://
     *      URL to https://. Googlebot then sees a canonical pointing at a
     *      redirect (a crawl-wasting, index-delaying loop). config('app.url')
     *      is the production HTTPS origin, so we trust it whenever the
     *      request is not a local/dev preview.
     *   2. No www. The www host 301-redirects to the apex; a www self-
     *      canonical would point at a redirect the same way.
     */
    public static function canonicalOrigin(): string
    {
        $configured = rtrim((string) config('app.url', ''), '/');
        $configuredHost = strtolower((string) parse_url($configured, PHP_URL_HOST));

        // Local preview / install without a configured APP_URL: use whatever
        // the request says (there is no production origin to pin to).
        if ($configured === '' || str_contains($configuredHost, 'localhost') || $configuredHost === '') {
            try {
                return rtrim(request()->getSchemeAndHttpHost(), '/');
            } catch (\Throwable $e) {
                return 'http://localhost';
            }
        }

        // Production: always the configured https:// apex. This also makes
        // every URL identical regardless of which Hostinger edge/proxy IP
        // handled the crawl, so Google can never split signals across hosts.
        $origin = 'https://'.preg_replace('/^www\./i', '', $configuredHost);
        if (($port = parse_url($configured, PHP_URL_PORT)) !== null) {
            $origin .= ':'.$port;
        }
        return $origin;
    }

    /**
     * The self-referencing canonical URL for the current page.
     *
     * Canonical rules applied here:
     *   - Path comes from the request (the route that actually rendered).
     *   - Query params are stripped EXCEPT pagination (?page=N) and the
     *     blog category filter (?category=slug), which Google documents as
     *     indexable faceted variants. Everything else (?q=, ?utm_*, ?ref=,
     *     sort/filter params, click IDs) collapses to the clean URL — those
     *     variants otherwise pile up in GSC as duplicate/canonical-chosen
     *     pages ("Alternate page with proper canonical tag").
     */
    public static function canonicalUrl(): string
    {
        try {
            $request = request();
        } catch (\Throwable $e) {
            return self::canonicalOrigin().'/';
        }

        $path = $request->getPathInfo();
        if ($path === '') {
            $path = '/';
        }

        $kept = [];
        $page = (int) $request->query('page', 0);
        if ($page > 1) {
            $kept['page'] = $page;
        }
        // The blog list's category facet (/blog?category=lifestyle) is a
        // real, internal-linkable listing (the filter form posts to it),
        // so it keeps a self canonical. Free-text search (?q=) does not —
        // search results are noindexed (see SearchController behaviour /
        // search.blade.php) and must canonicalize onto the clean list.
        $category = trim((string) $request->query('category', ''));
        if ($category !== '' && preg_match('/^[a-z0-9\-_]+$/i', $category) && $path === '/blog') {
            $kept['category'] = $category;
        }

        $query = $kept ? '?'.http_build_query($kept) : '';

        return self::canonicalOrigin().$path.$query;
    }

    /** Minimum length search audits expect from a page title. */
    public const MIN_TITLE = 30;

    /** Minimum length search audits expect from a meta description. */
    public const MIN_DESCRIPTION = 70;

    /** Hard cap for meta descriptions (Google truncates around 155-160). */
    public const MAX_DESCRIPTION = 158;

    public static function finalizeTitle(?string $title, bool $isPost = false): string
    {
        $title = trim((string) $title);
        $site = setting('site_name', 'huvanti.com');

        if ($title === '') {
            $title = $site;
        }

        if ($isPost) {
            // Posts render exactly what the author typed — owner requirement.
            return $title;
        }

        if (mb_strlen($title) < self::MIN_TITLE) {
            $title = trim($title.' | '.$site);
        }
        if (mb_strlen($title) < self::MIN_TITLE) {
            $tagline = trim((string) setting('site_tagline', ''));
            if ($tagline !== '') {
                $title = trim($title.' — '.$tagline);
            }
        }

        return $title;
    }

    public static function finalizeDescription(?string $description, bool $isPost = false): string
    {
        $description = trim(strip_tags((string) $description));

        // Posts show the dashboard-set meta description verbatim (owner
        // requirement from the SEO round: "search engines must show the
        // meta description I set").
        if ($isPost && $description !== '') {
            return $description;
        }

        $site = trim((string) setting('site_description', 'Huvanti is a multi niche blog covering technology, health, finance, travel, lifestyle and education.'));

        if ($description === '') {
            $description = $site;
        }

        if (mb_strlen($description) < self::MIN_DESCRIPTION) {
            // Join as a proper second sentence, not a run-on string.
            if (!preg_match('/[.!?…」』]$/u', $description)) {
                $description .= '.';
            }
            $description = trim($description.' '.$site);
        }

        if (mb_strlen($description) > self::MAX_DESCRIPTION) {
            $cropped = wordwrap($description, self::MAX_DESCRIPTION, "\n", false);
            $description = trim(substr($cropped, 0, (int) strpos($cropped."\n", "\n")));
            // Always end clean, never mid-word or with a dangling comma.
            $description = rtrim($description, " \t\n\r\0\x0B,;:-");
        }

        return $description;
    }
}
