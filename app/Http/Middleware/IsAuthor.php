<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows access for any logged-in user (admin OR author role).
 * The admin role is also a "user" — they can do everything an author can.
 * Use this on the /author-dashboard routes. The /manage routes use IsAdmin.
 */
class IsAuthor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please sign in to continue.');
        }
        return $next($request);
    }
}
