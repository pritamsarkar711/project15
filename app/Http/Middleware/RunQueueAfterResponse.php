<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Runs any queued jobs right AFTER the HTTP response is sent.
 *
 * Why: the site deploys to Hostinger shared hosting where NO `queue:work`
 * daemon runs. Without this, jobs pushed to the database queue (social
 * auto-post, notifications, …) would sit in the jobs table forever.
 *
 * This only triggers on write requests (POST/PUT/PATCH/DELETE) that actually
 * left jobs behind, and caps its runtime — so normal GET traffic never pays
 * the cost, and a post-publish request hands the work to the same PHP-FPM
 * worker after the author already received their redirect.
 */
class RunQueueAfterResponse
{
    public function handle($request, \Closure $next)
    {
        return $next($request);
    }

    public function terminate($request, $response): void
    {
        try {
            // Writes only — reads never enqueue anything on this app.
            if (!in_array(strtoupper($request->getMethod()), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                return;
            }
            // Only when the database queue actually has pending work.
            if (!\Illuminate\Support\Facades\Schema::hasTable('jobs')) {
                return;
            }
            $pending = DB::table('jobs')->count();
            if ($pending < 1) {
                return;
            }
            Artisan::call('queue:work', [
                '--stop-when-empty' => true,
                '--max-time'        => 90,
                '--tries'           => 1,
                '--quiet'           => true,
            ]);
        } catch (\Throwable $e) {
            // Best-effort: the queue command / social:retry-pending covers retries.
        }
    }
}
