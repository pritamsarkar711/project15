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

        // Not-found links render the standalone 404 page with a REAL HTTP 404
        // status. (Previously this 302-redirected to the homepage, which made
        // every dead URL look like a redirect to Google Search Console — dead
        // posts/categories were re-crawled forever as "Page with redirect"
        // instead of dropping out of the index.) Admin URLs still redirect to
        // the admin login because those areas are behind auth anyway.
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
