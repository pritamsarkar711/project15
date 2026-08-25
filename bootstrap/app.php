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
    })->create();
