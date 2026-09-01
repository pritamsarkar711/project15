<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\Social\SocialAutoPostService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Auto-post one published post to every configured social network.
 *
 * Dispatched with ->afterResponse() from the Post::saved hook so it works on
 * shared hosting WITHOUT a queue worker (the database queue still works when
 * `php artisan queue:work` IS running — afterResponse dispatch short-circuits
 * into the queue and runs immediately after the HTTP response is sent).
 * The admin can also retry failed networks from the Social Auto-Post page.
 */
class PublishPostToSocial implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;          // per-network retries handled by admin / social:retry-pending
    public int $timeout = 120;

    public function __construct(public int $postId)
    {
    }

    public function handle(SocialAutoPostService $service): void
    {
        $post = Post::find($this->postId);
        if (!$post || $post->status !== 'published' || $post->trashed()) {
            return; // deleted / unpublished between dispatch and run
        }
        try {
            $service->publish($post);
        } catch (\Throwable $e) {
            Log::warning('PublishPostToSocial failed', ['post' => $this->postId, 'err' => $e->getMessage()]);
        }
    }
}
