<?php

use App\Console\Commands\SocialRetryPending;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Console scheduling
|--------------------------------------------------------------------------
| Social auto-post resilience: any publish that failed (network hiccup,
| rate limit, expired token swapped later) is retried every 10 minutes,
| max 5 attempts per row. Requires host cron "php artisan schedule:run"
| every minute — on Hostinger:
|   * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
| The admin Social Auto-Post page also has manual retry buttons, so this
| schedule is optional, not required.
*/
Schedule::command(SocialRetryPending::class, ['--minutes' => 10, '--limit' => 15])->everyTenMinutes();
