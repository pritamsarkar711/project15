<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\IndexNowService;
use Illuminate\Console\Command;

/**
 * One-shot backfill: submit every currently-published post URL (plus the
 * home page) to IndexNow. Run once after deploying a batch of new/updated
 * articles, or whenever you want search engines to re-check the whole site:
 *
 *   php artisan indexnow:submit-all
 */
class IndexNowSubmit extends Command
{
    protected $signature = 'indexnow:submit-all {--limit=100 : Maximum number of post URLs to submit}';

    protected $description = 'Submit all published post URLs to IndexNow (Bing/Yandex/Seznam/Naver)';

    public function handle(IndexNowService $indexNow): int
    {
        if (!$indexNow->enabled()) {
            $this->error('IndexNow key is not configured (services.indexnow.key).');
            return self::FAILURE;
        }

        $host = rtrim((string) config('services.indexnow.host'), '/');
        $urls = ['https://'.$host.'/', 'https://'.$host.'/blog'];

        $slugs = Post::published()->latest('updated_at')
            ->limit((int) $this->option('limit'))
            ->pluck('slug');

        foreach ($slugs as $slug) {
            $urls[] = $indexNow->postUrl($slug);
        }

        $this->info('Submitting '.count($urls).' URL(s) to IndexNow...');
        $ok = $indexNow->submit($urls);

        if ($ok) {
            $this->info('Done. Engines typically fetch submitted URLs within minutes.');
            return self::SUCCESS;
        }

        $this->warn('Submission failed (network or API error) — check the log for details.');
        return self::FAILURE;
    }
}
