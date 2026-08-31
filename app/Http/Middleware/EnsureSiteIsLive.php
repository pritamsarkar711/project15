<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Site-wide maintenance mode.
 *
 * Enabled from Admin → Settings → General → "Maintenance mode". While on,
 * every public URL answers HTTP 503 (the correct status for search engines:
 * Google retries 503s later instead of de-indexing) with a minimal
 * site-styled page showing a live countdown when the admin set a return
 * time. Everything else keeps working for ADMINS so they can update the
 * site while visitors see the timer.
 *
 * Always reachable (by design):
 *   - /manage/*          → the admin panel + its login
 *   - /login, /logout    → sign-in must work while logged out
 *   - /up                → the framework health endpoint (uptime monitors)
 * Any authenticated ADMIN also browses normally — but an admin using the
 * "switch to user" preview mode sees the maintenance page exactly like a
 * visitor, which makes testing the mode honest.
 *
 * Every DB/cache touch is wrapped so a broken database can never turn the
 * maintenance screen itself into an error.
 */
class EnsureSiteIsLive
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $enabled = Setting::get('maintenance_enabled', '0') === '1';
        } catch (\Throwable $e) {
            $enabled = false; // settings unreadable → never trap the site
        }

        if (! $enabled) {
            return $next($request);
        }

        // ---- who stays allowed through ----
        if ($request->is('manage') || $request->is('manage/*')
            || $request->is('login') || $request->is('logout')
            || $request->is('up')) {
            return $next($request);
        }

        $user = $request->user();
        if ($user && ($user->role ?? null) === 'admin' && session('acting_role') !== 'user') {
            return $next($request);
        }

        // ---- 503 + timer ----
        $endsAt = null;
        try {
            $raw = Setting::get('maintenance_ends_at');
            if ($raw) {
                $endsAt = \Illuminate\Support\Carbon::parse($raw);
                if ($endsAt->isPast()) {
                    $endsAt = null; // expired → plain page, no stale timer
                }
            }
        } catch (\Throwable $e) {
            $endsAt = null;
        }

        $headers = [];
        if ($endsAt) {
            // Retry-After (whole seconds) — tells crawlers when to come back.
            $headers['Retry-After'] = (string) max(1, (int) ceil(now()->diffInSeconds($endsAt)));
        }

        try {
            $content = view('errors.maintenance', ['endsAt' => $endsAt])->render();
        } catch (\Throwable $e) {
            $content = '<!DOCTYPE html><html><head><meta charset="utf-8">'
                .'<meta name="viewport" content="width=device-width, initial-scale=1">'
                .'<title>'.htmlspecialchars(Setting::get('site_name', 'Huvanti')).'</title></head>'
                .'<body style="margin:0;font-family:Inter,system-ui,sans-serif;display:flex;'
                .'align-items:center;justify-content:center;min-height:100vh;text-align:center">'
                .'<p style="color:#173A2A;font-weight:600">We will be back soon.</p></body></html>';
        }

        return response($content, 503, $headers);
    }
}
