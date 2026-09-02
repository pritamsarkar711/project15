<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Services\HtmlSanitizer;
use App\Services\SeoAnalyzer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * One-off content seeder for the "AI history channel content pillars topics
 * that get most views" article.
 *
 * The goal is to let a site owner publish this long form SEO article without
 * manually pasting the body, images, FAQ records, and SEO metadata into the
 * editor. The seeder is idempotent: if the slug already exists, the post is
 * updated instead of duplicated.
 *
 * Run from the application root:
 *
 *   php artisan db:seed --class=Database\\Seeders\\PublishAiHistoryChannelContentPillars
 */
class PublishAiHistoryChannelContentPillars extends Seeder
{
    /**
     * Existing account that will own the post. Change this to any existing
     * author or admin email before running the seeder on your site.
     */
    protected const AUTHOR_EMAIL = 'admin@huvanti.com';

    public function run(): void
    {
        $payloadPath = base_path('docs/publishing/ai-history-channel-content-pillars/seo.json');
        $articlePath = base_path('docs/publishing/ai-history-channel-content-pillars/article.html');

        if (!File::exists($payloadPath) || !File::exists($articlePath)) {
            throw new \RuntimeException('The article bundle files are missing. Restore docs/publishing/ai-history-channel-content-pillars first.');
        }

        $meta = json_decode(File::get($payloadPath), true);
        if (!is_array($meta)) {
            throw new \RuntimeException('The seo.json payload could not be decoded.');
        }

        $content = HtmlSanitizer::clean(File::get($articlePath));
        $slug = $meta['slug'] ?? 'ai-history-channel-content-pillars-topics-that-get-most-views';
        $title = $meta['title'] ?? 'AI History Channel Content Pillars Topics That Get Most Views';

        $user = User::where('email', static::AUTHOR_EMAIL)->first();
        if (! $user) {
            $user = User::where('role', 'admin')->orderBy('id')->first();
        }
        if (! $user) {
            $user = User::where('role', 'author')->orderBy('id')->first();
        }
        if (! $user) {
            throw new \RuntimeException('No existing user was found. Create or seed a user before running this content seeder.');
        }

        $category = Category::where('slug', $meta['category'] ?? 'technology')->where('is_active', true)->first();
        if (! $category) {
            $category = Category::where('is_active', true)->orderBy('sort_order')->first();
        }

        $featuredAsset = base_path('database/seeders/assets/posts/ai-history-channel-content-pillars-featured.webp');
        $inlineAsset = base_path('database/seeders/assets/posts/ai-history-channel-content-pillars-inline.webp');
        if (!File::exists($featuredAsset) || !File::exists($inlineAsset)) {
            throw new \RuntimeException('The WebP assets are missing from database/seeders/assets/posts/.');
        }

        $featuredPath = $this->copyAsset($featuredAsset, 'uploads/posts');
        $this->copyAsset($inlineAsset, 'uploads/posts');

        $wordCount = str_word_count(strip_tags($content));
        $post = Post::firstOrNew(['slug' => $slug], []);

        $post->title = $title;
        $post->excerpt = $meta['excerpt'] ?? null;
        $post->content = $content;
        $post->category_id = $category?->id;
        $post->user_id = $user->id;
        $post->featured_image = $featuredPath;
        $post->author_name = $user->name;
        $post->author_bio = $user->bio;
        $post->author_avatar = $user->author_avatar_path;
        $post->reading_time = (int) ($meta['reading_time'] ?? max(1, (int) ceil($wordCount / 200)));
        $post->meta_title = $meta['meta_title'] ?? null;
        $post->meta_description = $meta['meta_description'] ?? null;
        $post->meta_keywords = $meta['meta_keywords'] ?? null;
        $post->focus_keyword = $meta['focus_keyword'] ?? null;
        $post->status = 'published';
        $post->review_status = 'approved';
        $post->submitted_at = $post->submitted_at ?? now();
        $post->reviewed_at = $post->reviewed_at ?? now();
        $post->reviewer_id = $post->reviewer_id ?? $user->id;
        $post->is_featured = (bool) ($meta['is_featured'] ?? true);
        $post->allow_comments = (bool) ($meta['allow_comments'] ?? true);
        $post->is_affiliate = false;
        $post->published_at = $post->published_at ?? now();
        $post->instant_indexed_at = null;

        $post->seo_score = app(SeoAnalyzer::class)->analyze(
            $post->title,
            $post->meta_title,
            $post->meta_description,
            $post->focus_keyword,
            $post->slug,
            $post->content
        )['score'];

        $post->save();

        $post->faqs()->delete();
        foreach (($meta['faqs'] ?? []) as $index => $faq) {
            if (empty($faq['question']) || empty($faq['answer'])) {
                continue;
            }
            $post->faqs()->create([
                'question' => mb_substr((string) $faq['question'], 0, 500),
                'answer' => mb_substr((string) $faq['answer'], 0, 2000),
                'sort_order' => (int) $index,
            ]);
        }

        $this->command?->info('Published post: /blog/' . $post->slug . ' (SEO score ' . $post->seo_score . ').');
    }

    /**
     * Copy a bundled WebP into the two storage locations Huvanti serves.
     *
     * Returns the Laravel relative path stored in posts.featured_image, for
     * example "uploads/posts/ai-history-channel-content-pillars-featured.webp".
     */
    protected function copyAsset(string $source, string $dir): string
    {
        $name = basename($source);
        $relative = trim($dir, '/') . '/' . $name;

        $targets = [
            storage_path('app/public/' . $relative),
            public_path('storage/' . $relative),
        ];

        foreach ($targets as $target) {
            $targetDir = dirname($target);
            if (! is_dir($targetDir)) {
                @mkdir($targetDir, 0775, true);
            }
            if (is_dir($targetDir)) {
                copy($source, $target);
            }
        }

        return $relative;
    }
}
