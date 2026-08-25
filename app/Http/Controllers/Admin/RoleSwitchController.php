<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Admin ⇄ User role switch.
 *
 * Lets the (real) admin temporarily browse the site and the author/user panel
 * as a regular user, then switch back — without a second account. Typical use:
 * "I changed the font / hero image / category — let me check how it looks from
 * a normal user's point of view", then switch straight back to admin.
 *
 * Security:
 *   - ONLY the real admin account (users.role === 'admin') can switch either
 *     way. A normal user can never make themselves admin through these routes.
 *   - The state lives in the admin's own session only — nothing is written to
 *     the database, so the admin role itself never changes.
 *   - While in user mode the /manage admin panel is blocked by IsAdmin, and
 *     the frontend shows a persistent "Switch back to Admin" button.
 */
class RoleSwitchController extends Controller
{
    /**
     * Admin → User mode. Registered under /manage (admin middleware), so it
     * is only reachable while in admin mode.
     */
    public function switchToUser(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Only the admin can use the role switch.');
        }

        $request->session()->put('acting_role', 'user');

        return redirect()
            ->to('/')
            ->with('success', 'You are now browsing as a regular user. Use "Switch to Admin" any time to return.');
    }

    /**
     * User mode → Admin. Lives OUTSIDE /manage (route: /switch-back-to-admin)
     * so it stays reachable while the admin is browsing the site in user mode.
     * It still verifies the REAL role, so only the admin can ever use it.
     */
    public function switchBackToAdmin(Request $request)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Only the admin can use the role switch.');
        }

        $request->session()->forget('acting_role');

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Welcome back to admin mode.');
    }
}
