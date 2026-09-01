<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\SocialPublish;
use App\Services\Social\SocialAutoPostService;
use Illuminate\Console\Command;

/**
 * Retry social auto-posts that are still pending / failed.
 *
 * Wire it into cron once a minute for fully hands-off behaviour:
 *   * * * * * php /path/to/artisan schedule:run
 * (schedule entry lives in routes/console.php)
 */
class SocialRetryPending extends Command
{
    protected $signature = 'social:retry-pending {--minutes=15 : Only rows older than this many minutes} {--limit=20}';

    protected $description = 'Retry failed/pending social auto-posts for published posts';

    public function handle(SocialAutoPostService $service): int
    {
        if (!$service->enabled()) {
            $this->info('Social auto-post is disabled — nothing to do.');
            return self::SUCCESS;
        }

        $rows = SocialPublish::query()
            ->whereIn('status', ['pending', 'failed'])
            ->where('attempts', '<', 5)
            ->where('updated_at', '<=', now()->subMinutes((int) $this->option('minutes')))
            ->orderBy('updated_at')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($rows->isEmpty()) {
            $this->info('No pending social publishes to retry.');
            return self::SUCCESS;
        }

        foreach ($rows as $row) {
            $post = Post::find($row->post_id);
            if (!$post || $post->status !== 'published' || $post->trashed()) {
                $row->delete();
                continue;
            }
            $this->line("Retrying {$row->network} for post #{$post->id} …");
            $service->publish($post, [$row->network]);
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}
