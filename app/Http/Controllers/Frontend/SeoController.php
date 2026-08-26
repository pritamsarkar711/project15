<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;

class SeoController extends Controller
{
    /** GET /robots.txt — content managed in admin Settings > Integrations. */
    public function robots()
    {
        $content = trim((string) setting('robots_txt_content', ''));
        if ($content === '') {
            $content = "User-agent: *\n"
                ."Disallow: /manage\n"
                ."Disallow: /author-dashboard\n"
                ."Disallow: /login\n"
                ."Disallow: /register\n"
                ."Disallow: /forgot-password\n"
                ."Disallow: /reset-password\n"
                ."Disallow: /deploy.php\n"
                ."Disallow: /install.php\n"
                ."Disallow: /doctor.php\n\n"
                ."Sitemap: ".url('/sitemap.xml');
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

        $custom = trim((string) setting('llms_txt_content', ''));
        $md = "# {$name}\n\n";
        if ($tagline) $md .= "> {$tagline}\n\n";
        if ($description) $md .= $description."\n\n";
        if ($custom) $md .= $custom."\n\n";

        $md .= "## Articles\n\n";
        $posts = Post::published()->with('category')->latest('published_at')->limit(100)->get();
        foreach ($posts as $post) {
            $md .= '- ['.strip_tags($post->title).']('.url('/blog/'.$post->slug).')';
            if ($post->excerpt) $md .= ': '.trim(strip_tags($post->excerpt));
            $md .= "\n";
        }

        $md .= "\n## Pages\n\n";
        foreach (Page::where('status', 'published')->get() as $page) {
            $md .= '- ['.strip_tags($page->title).']('.url('/page/'.$page->slug).")\n";
        }

        return response($md, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /** GET /sitemap.xml — posts, pages, categories + home. */
    public function sitemap()
    {
        $entries = collect([
            ['loc' => url('/'), 'lastmod' => optional(Post::published()->latest('updated_at')->first())->updated_at],
            ['loc' => url('/blog'), 'lastmod' => optional(Post::published()->latest('updated_at')->first())->updated_at],
        ]);

        foreach (Post::published()->latest()->get() as $post) {
            $entries[] = ['loc' => url('/blog/'.$post->slug), 'lastmod' => $post->updated_at];
        }
        // Only live categories (active + has published posts) belong in the
        // sitemap — empty category pages return "no posts" to crawlers.
        foreach (Category::live()->get() as $category) {
            $entries[] = ['loc' => url('/category/'.$category->slug), 'lastmod' => $category->updated_at];
        }
        foreach (Page::where('status', 'published')->get() as $page) {
            $entries[] = ['loc' => url('/page/'.$page->slug), 'lastmod' => $page->updated_at];
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
