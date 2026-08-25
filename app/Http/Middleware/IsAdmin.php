<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login')->with('error','Please login');
        }
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
        // Admin switched their own session to "user mode" (Admin ⇄ User switch).
        // While acting as a user, the admin panel is closed — exactly like a
        // regular user would experience it. The "Switch to Admin" button on
        // the site brings them straight back.
        if (session('acting_role') === 'user') {
            return redirect()
                ->to('/')
                ->with('error', 'You are browsing in user mode. Click "Switch to Admin" to open the admin panel again.');
        }
        return $next($request);
    }
}
