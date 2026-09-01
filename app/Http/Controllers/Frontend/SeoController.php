<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use App\Support\StaticPages;

class SeoController extends Controller
{
    /**
     * Absolute base URL (scheme + host) for SEO output.
     *
     * Sitemaps, robots.txt Sitemap directives and llms.txt links MUST be
     * absolute URLs per the specs — but this app intentionally runs a
     * root-relative UrlGenerator (see RelativeAssetUrlGenerator), which turns
     * url() into "/path" strings. Those are invalid in sitemaps and dead in
     * crawlers' eyes. We therefore build absolute URLs from the request's
     * own host, which is always correct for whoever is asking.
     */
    protected function absoluteBase(): string
    {
        try {
            $base = rtrim(request()->getSchemeAndHttpHost(), '/');
        } catch (\Throwable $e) {
            $base = '';
        }
        if ($base === '' || str_contains($base, 'localhost')) {
            $configured = rtrim((string) config('app.url', ''), '/');
            if ($configured !== '' && !str_contains($configured, 'localhost')) {
                $base = $configured;
            }
        }
        return $base !== '' ? $base : rtrim(url('/'), '/');
    }

    /**
     * These endpoints are served OUTSIDE the "web" middleware group (see
     * routes/bots.php) so crawlers get no session cookie and a short,
     * public Cache-Control header — Hostinger's edge (hcdn) can then serve
     * them from cache. This is what fixes Ahrefs' "slow server response
     * for AI crawlers": bots repeatedly fetch robots.txt / sitemap.xml and
     * used to pay a full PHP bootstrap + DB round trip every time.
     */
    protected function text(string $content, string $mime, int $seconds = 300)
    {
        return response($content, 200)
            ->header('Content-Type', $mime.'; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age='.$seconds.', s-maxage='.$seconds);
    }

    /** GET /robots.txt — content managed in admin Settings > Integrations. */
    public function robots()
    {
        // Explicit Allow blocks for Bingbot, Googlebot, and major AI crawlers
        $allowedBots = [
            'Bingbot',
            'msnbot',
            'BingPreview',
            'Googlebot',
            'Googlebot-Image',
            'Googlebot-News',
            'GPTBot',
            'OAI-SearchBot',
            'ChatGPT-User',
            'ClaudeBot',
            'anthropic-ai',
            'PerplexityBot',
            'Google-Extended',
            'CCBot',
            'Applebot-Extended',
            'Amazonbot',
            'Meta-ExternalAgent',
            'Bytespider',
        ];

        $blocks = [];
        foreach ($allowedBots as $bot) {
            $blocks[] = "User-agent: {$bot}\nAllow: /";
        }

        $defaultBlock = "User-agent: *\n"
            . "Disallow: /manage\n"
            . "Disallow: /author-dashboard\n"
            . "Disallow: /search\n"
            . "Disallow: /login\n"
            . "Disallow: /register\n"
            . "Disallow: /forgot-password\n"
            . "Disallow: /reset-password";

        $sitemapLine = "Sitemap: " . $this->absoluteBase() . "/sitemap.xml";

        $content = implode("\n\n", $blocks) . "\n\n" . $defaultBlock . "\n\n" . $sitemapLine;

        $custom = trim((string) setting('robots_txt_content', ''));
        $custom = str_replace("\r\n", "\n", $custom);
        if ($custom !== '' && !str_contains($custom, 'User-agent: *')) {
            $content .= "\n\n# Custom rules\n" . $custom;
        }

        return $this->text($content . "\n", 'text/plain');
    }

    /** GET /ads.txt — raw ads.txt content (AdSense authorized sellers). */
    public function ads()
    {
        $content = trim((string) setting('ads_txt_content', ''));
        return $this->text($content."\n", 'text/plain');
    }

    /** GET /llms.txt — markdown site summary for LLM crawlers. */
    public function llms()
    {
        $name = site_name();
        $tagline = (string) setting('site_tagline', '');
        $description = (string) setting('site_description', '');
        $base = $this->absoluteBase();

        $custom = trim((string) setting('llms_txt_content', ''));
        // This setting is for handwritten EXTRA markdown appended to the
        // auto-generated file. A stale copy of a previously auto-generated
        // llms.txt stored here used to duplicate every section (with dead
        // /page/* links). Generator section headers are the fingerprint of
        // such stale dumps — skip them.
        if ($custom !== '' && preg_match('/^##\s+(Sections|Articles|Pages)\b/m', $custom)) {
            $custom = '';
        }
        $md = "# {$name}\n\n";
        if ($tagline) $md .= "> {$tagline}\n\n";
        if ($description) $md .= $description."\n\n";
        if ($custom) $md .= $custom."\n\n";

        // Guarded: a DB hiccup must degrade to a minimal file, not a 500.
        try {
            $md .= "## Sections\n\n";
            $md .= '- [Blog]('.$base.'/blog): All latest articles across every category'."\n";
            if (\App\Models\Setting::get('top_contributors_enabled', '1') === '1') {
                $md .= '- [Top Contributors]('.$base.'/top-contributors): The most active writers on Huvanti'."\n";
            }

            $md .= "\n## Articles\n\n";
            $posts = Post::published()->with('category')->latest('published_at')->limit(100)->get();
            foreach ($posts as $post) {
                $md .= '- ['.strip_tags($post->title).']('.$base.'/blog/'.$post->slug.')';
                if ($post->excerpt) $md .= ': '.trim(strip_tags($post->excerpt));
                $md .= "\n";
            }

            $md .= "\n## Pages\n\n";
            foreach (Page::where('status', 'published')->get() as $page) {
                // Built-in pages: link the canonical named route (/privacy-policy),
                // NOT the /page/{slug} duplicate that now 301-redirects onto it.
                // route() is root-relative in this app, so prefix the host.
                $canonical = StaticPages::canonicalUrl((string) $page->slug);
                if ($canonical !== null && !str_starts_with($canonical, 'http')) {
                    $canonical = $base.$canonical;
                }
                $url = $canonical ?? $base.'/page/'.$page->slug;
                $md .= '- ['.strip_tags($page->title).']('.$url.")\n";
            }
        } catch (\Throwable $e) {
            report($e);
            $md .= '- [Home]('.$base.")\n";
        }

        return $this->text($md, 'text/plain');
    }

    /**
     * Indexable, canonical URLs that exist BEHIND a listing route, so every
     * audit tool sees the exact same set the site actually serves.
     */
    private function paginatedUrls(string $pattern, int $total, int $perPage, string $base, $lastmod, array &$entries): void
    {
        $pages = (int) ceil($total / max(1, $perPage));
        for ($p = 2; $p <= $pages; $p++) {
            $entries[] = ['loc' => $base.$pattern.'?page='.$p, 'lastmod' => $lastmod];
        }
    }

    /** GET /sitemap.xml — posts, pages, categories, authors + pagination. */
    public function sitemap()
    {
        $base = $this->absoluteBase();

        // IMPORTANT: plain PHP array, NOT collect(). paginatedUrls() declares
        // `array &$entries` — passing a Collection raised a TypeError on EVERY
        // request, the catch turned it into a homepage-only fallback, and the
        // live sitemap silently shrank to a single URL.
        $entries = [];
        $lastmod = null;

        try {
            $lastmod = optional(Post::published()->latest('updated_at')->first())->updated_at;
        } catch (\Throwable $e) {
            report($e);
        }

        $entries[] = ['loc' => $base.'/', 'lastmod' => $lastmod];
        $entries[] = ['loc' => $base.'/blog', 'lastmod' => $lastmod];

        // Each section below is guarded on its own: a failure in one (a data
        // quirk, a transient DB hiccup on shared hosting) must skip that
        // section only — never collapse the whole sitemap to one URL again.
        try {
            // Blog listing pagination (12 posts per page — same as the
            // controller) so "?page=2" style URLs are discoverable.
            $postTotal = Post::published()->count();
            $this->paginatedUrls('/blog', $postTotal, 12, $base, $lastmod, $entries);

            foreach (Post::published()->latest()->get() as $post) {
                $entries[] = ['loc' => $base.'/blog/'.$post->slug, 'lastmod' => $post->updated_at];
            }
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            // Static pages linked from the header — indexable, so they belong
            // in the sitemap. The Top Contributors page only exists while the
            // admin feature switch is on (otherwise it 404s).
            $entries[] = ['loc' => $base.'/about', 'lastmod' => null];
            $entries[] = ['loc' => $base.'/contact', 'lastmod' => null];
            if (\App\Models\Setting::get('top_contributors_enabled', '1') === '1') {
                $entries[] = ['loc' => $base.'/top-contributors', 'lastmod' => null];
            }
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            // Only live categories (active + has published posts) belong in
            // the sitemap — empty category pages return "no posts" to crawlers.
            foreach (Category::live()->get() as $category) {
                $entries[] = ['loc' => $base.'/category/'.$category->slug, 'lastmod' => $category->updated_at];
                $catTotal = Post::published()->where('category_id', $category->id)->count();
                $this->paginatedUrls('/category/'.$category->slug, $catTotal, 12, $base, $category->updated_at, $entries);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            // Author profile pages for everyone with at least one published
            // post — previously invisible to crawlers' sitemaps entirely.
            $authors = User::whereHas('posts', function ($q) {
                $q->published();
            })->get();
            foreach ($authors as $author) {
                $latest = Post::published()->where('user_id', $author->id)->max('updated_at');
                $entries[] = [
                    'loc' => $base.'/author/'.$author->username,
                    'lastmod' => $latest ? \Illuminate\Support\Carbon::parse($latest) : null,
                ];
            }
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            // Built-in policy pages at their CANONICAL named-route URLs
            // (/privacy-policy — the one the footer links). The /page/{slug}
            // variants 301-redirect here, so listing them would advertise a
            // redirect. Custom admin-created pages keep /page/{slug}.
            $pages = Page::where('status', 'published')->get();
        } catch (\Throwable $e) {
            report($e);
            // Degrade gracefully: if the status filter itself fails (e.g. an
            // older pages table schema), list all pages rather than none.
            try {
                $pages = Page::all();
            } catch (\Throwable $e2) {
                report($e2);
                $pages = collect();
            }
        }
        foreach ($pages as $page) {
            $url = StaticPages::canonicalUrl((string) $page->slug)
                ?? $base.'/page/'.$page->slug;
            if (!str_starts_with($url, $base)) {
                $url = $base.$url; // route() returns root-relative here
            }
            $entries[] = ['loc' => $url, 'lastmod' => $page->updated_at];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($entries as $entry) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars((string) $entry['loc'], ENT_XML1)."</loc>\n";
            if ($entry['lastmod'] ?? null) {
                $xml .= '    <lastmod>'.$entry['lastmod']->toAtomString()."</lastmod>\n";
            }
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return $this->text($xml, 'application/xml', 180);
    }
}
