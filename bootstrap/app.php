<?php

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

// NOTE: App\Application (not Illuminate's) — its getNamespace() override
// removes the runtime dependency on a root composer.json, which we omit on
// purpose so Hostinger's Git auto-deploy never triggers `composer install`.
// See app/Application.php for the full story.
return \App\Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        // Crawler files (robots/sitemap/llms/ads) are registered WITHOUT the
        // web middleware group: crawlers get no session cookie and the edge
        // CDN can cache the responses (see routes/bots.php).
        then: function () {
            Illuminate\Support\Facades\Route::middleware([])->group(
                __DIR__.'/../routes/bots.php'
            );
        },
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'author' => \App\Http\Middleware\IsAuthor::class,
        ]);
        // Auto-clear compiled Blade views after every admin write (POST/PUT/PATCH/DELETE)
        // so changes are instantly visible on shared hosting with OPcache.
        $middleware->append(\App\Http\Middleware\ClearViewCacheAfterWrite::class);
        // Site-wide maintenance mode (Admin → Settings → General). Global so
        // even sitemap.xml answers 503 — the correct signal for crawlers.
        $middleware->append(\App\Http\Middleware\EnsureSiteIsLive::class);
        // Shared hosting has no queue daemon: after any write request that
        // queued jobs (social auto-post, mail notifications), drain the queue
        // right after the response is sent.
        $middleware->append(\App\Http\Middleware\RunQueueAfterResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Throwable $e) {
            try {
                @file_put_contents(storage_path('logs/last-web-error.json'), json_encode([
                    'time' => date('c'),
                    'class' => $e::class,
                    'message' => $e->getMessage(),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine(),
                ], JSON_UNESCAPED_SLASHES));
            } catch (\Throwable $ignored) {
            }
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Friendly handling of the 419 "Page Expired" error. On shared
        // hosting the most common cause is a POST body larger than the
        // php.ini post_max_size (e.g. a hero image upload): PHP silently
        // drops the whole request, the CSRF token never arrives, and the
        // admin sees a bare 419 page that explains nothing. Redirect back
        // with a plain-language message instead.
        $exceptions->renderable(function (\Illuminate\Session\TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your session expired. Refresh the page and try again.',
                ], 419);
            }
            return redirect()->back()->with(
                'error',
                'Your session expired before the form was submitted. This usually happens when an uploaded '
                .'file is larger than the server accepts (images up to 4 MB are supported). '
                .'Please refresh the page and try again with a smaller file.'
            );
        });

        // Not-found URLs return a REAL 404 page (owner asked for "never a
        // bare error page" — this page is themed and helpful). The previous
        // behaviour redirected every unknown URL to the homepage with 302:
        // Google treats that as a soft-404 factory (an infinite URL space
        // that all answer 200 with homepage content), which wastes crawl
        // budget and directly hurts indexing of the real articles — the
        // likely cause of posts not being indexed. JSON clients still get a
        // proper 404 body, and admin URLs still go to the admin login.
        // This also covers abort(404) from controllers (e.g. a deleted post).
        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'The page you were looking for could not be found.'], 404);
            }
            if ($request->is('manage/*')) {
                return redirect()->route('admin.login')
                    ->with('error', 'The admin page you were looking for could not be found.');
            }
            return response()->view('errors.404', [], 404);
        });

        // 403 (no permission, e.g. an author opening /manage): back to the
        // homepage with a short note instead of a bare error page.
        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'You do not have permission to view that page.'], 403);
            }
            return redirect()->to('/')
                ->with('error', 'You do not have permission to view that page.');
        });

        // 405 (wrong HTTP method on a known URL): treat like a not-found and
        // send the visitor to the homepage.
        $exceptions->renderable(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Method not allowed.'], 405);
            }
            return redirect()->to('/')
                ->with('error', 'That request could not be processed.');
        });
    })->create();
