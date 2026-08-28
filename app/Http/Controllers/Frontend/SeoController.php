<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;

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

    /** GET /robots.txt — content managed in admin Settings > Integrations. */
    public function robots()
    {
        $content = trim((string) setting('robots_txt_content', ''));
        if ($content === '') {
            $content = "User-agent: *\n"
                ."Disallow: /manage\n"
                ."Disallow: /author-dashboard\n"
                ."Disallow: /search\n"
                ."Disallow: /login\n"
                ."Disallow: /register\n"
                ."Disallow: /forgot-password\n"
                ."Disallow: /reset-password\n\n"
                ."Sitemap: ".$this->absoluteBase()."/sitemap.xml";
        }
        return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /** GET /ads.txt — raw ads.txt content (AdSense authorized sellers). */
    public function ads()
    {
        $content = trim((string) setting('ads_txt_content', ''));
        return response($content."\n", 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /** GET /llms.txt — markdown site summary for LLM crawlers. */
    public function llms()
    {
        $name = site_name();
        $tagline = (string) setting('site_tagline', '');
        $description = (string) setting('site_description', '');
        $base = $this->absoluteBase();

        $custom = trim((string) setting('llms_txt_content', ''));
        $md = "# {$name}\n\n";
        if ($tagline) $md .= "> {$tagline}\n\n";
        if ($description) $md .= $description."\n\n";
        if ($custom) $md .= $custom."\n\n";

        $md .= "## Articles\n\n";
        // Guarded: a DB hiccup must degrade to a minimal file, not a 500.
        try {
            $posts = Post::published()->with('category')->latest('published_at')->limit(100)->get();
            foreach ($posts as $post) {
                $md .= '- ['.strip_tags($post->title).']('.$base.'/blog/'.$post->slug.')';
                if ($post->excerpt) $md .= ': '.trim(strip_tags($post->excerpt));
                $md .= "\n";
            }

            $md .= "\n## Pages\n\n";
            foreach (Page::where('status', 'published')->get() as $page) {
                $md .= '- ['.strip_tags($page->title).']('.$base.'/page/'.$page->slug.")\n";
            }
        } catch (\Throwable $e) {
            report($e);
            $md .= '- [Home]('.$base.")\n";
        }

        return response($md, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /** GET /sitemap.xml — posts, pages, categories + home. */
    public function sitemap()
    {
        $base = $this->absoluteBase();

        try {
            $lastmod = optional(Post::published()->latest('updated_at')->first())->updated_at;

            $entries = collect([
                ['loc' => $base.'/', 'lastmod' => $lastmod],
                ['loc' => $base.'/blog', 'lastmod' => $lastmod],
            ]);

            // Static pages linked from the header — indexable, so they belong
            // in the sitemap. The Top Contributors page only exists while the
            // admin feature switch is on (otherwise it 404s).
            $entries[] = ['loc' => $base.'/about', 'lastmod' => null];
            $entries[] = ['loc' => $base.'/contact', 'lastmod' => null];
            if (\App\Models\Setting::get('top_contributors_enabled', '1') === '1') {
                $entries[] = ['loc' => $base.'/top-contributors', 'lastmod' => null];
            }

            foreach (Post::published()->latest()->get() as $post) {
                $entries[] = ['loc' => $base.'/blog/'.$post->slug, 'lastmod' => $post->updated_at];
            }
            // Only live categories (active + has published posts) belong in the
            // sitemap — empty category pages return "no posts" to crawlers.
            foreach (Category::live()->get() as $category) {
                $entries[] = ['loc' => $base.'/category/'.$category->slug, 'lastmod' => $category->updated_at];
            }
            foreach (Page::where('status', 'published')->get() as $page) {
                $entries[] = ['loc' => $base.'/page/'.$page->slug, 'lastmod' => $page->updated_at];
            }
        } catch (\Throwable $e) {
            report($e);
            // Minimal but VALID sitemap so crawlers never get a 500.
            $entries = collect([['loc' => $base.'/', 'lastmod' => null]]);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($entries as $entry) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($entry['loc'], ENT_XML1)."</loc>\n";
            if ($entry['lastmod'] ?? null) {
                $xml .= '    <lastmod>'.$entry['lastmod']->toAtomString()."</lastmod>\n";
            }
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
