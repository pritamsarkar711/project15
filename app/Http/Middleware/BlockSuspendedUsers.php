<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Signs a suspended account out on its very next request, so a suspension
 * takes effect immediately even while the user is already signed in and
 * browsing. Runs at the end of the web group (after the session starts).
 * A missing status column (migration not yet run) simply never triggers.
 */
class BlockSuspendedUsers
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->status ?? 'active') === 'suspended') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = 'This account has been suspended. Contact the site owner if you think this is a mistake.';

            if ($request->is('manage') || $request->is('manage/*')) {
                return redirect()->route('admin.login')->with('error', $message);
            }

            return redirect()->route('login')->with('error', $message);
        }

        return $next($request);
    }
}
