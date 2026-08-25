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
    })
    ->withExceptions(function (Exceptions $exceptions): void {
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
    })->create();
