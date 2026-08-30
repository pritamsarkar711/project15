<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * IndexNow instant-indexing client (shared endpoint -> Bing, Yandex,
 * Seznam, Naver).
 *
 * Why: Ahrefs' audit flagged "Changed pages not submitted to IndexNow".
 * Posts on this site were only discovered by crawlers whenever they felt
 * like re-crawling; Google indexing of new articles dragged for days.
 * Now every publish/update/delete pings the changed URL within seconds.
 *
 * Failure policy: indexing notification is best-effort. A slow/failed ping
 * must NEVER break a post save — everything is timeout-bounded, try/catch
 * wrapped and only logged.
 */
class IndexNowService
{
    public function enabled(): bool
    {
        return (bool) config('services.indexnow.key');
    }

    /** Submit one or many absolute URLs (up to 10,000 per call). */
    public function submit(array $urls): bool
    {
        $key = (string) config('services.indexnow.key');
        if ($key === '' || $urls === []) {
            return false;
        }

        try {
            $response = Http::timeout(3)
                ->connectTimeout(2)
                ->asJson()
                ->post((string) config('services.indexnow.endpoint'), [
                    'host'        => (string) config('services.indexnow.host'),
                    'key'         => $key,
                    'keyLocation' => 'https://'.config('services.indexnow.host').'/'.$key.'.txt',
                    'urlList'     => array_values(array_unique($urls)),
                ]);

            if (!$response->successful()) {
                Log::warning('IndexNow submit failed', ['status' => $response->status(), 'urls' => $urls]);
                return false;
            }
            return true;
        } catch (\Throwable $e) {
            Log::warning('IndexNow submit error: '.$e->getMessage());
            return false;
        }
    }

    /** Absolute URL for a post slug on the configured host. */
    public function postUrl(string $slug): string
    {
        return 'https://'.rtrim((string) config('services.indexnow.host'), '/').'/blog/'.$slug;
    }

    /**
     * Fire-and-forget submit used by model hooks. Skips local console runs
     * that are not the dedicated submit command, so migrations/seeders and
     * tinker experiments never spam the API.
     */
    public function submitQuietly(array $urls): void
    {
        try {
            $this->submit($urls);
        } catch (\Throwable $e) {
            // never propagate
        }
    }
}
