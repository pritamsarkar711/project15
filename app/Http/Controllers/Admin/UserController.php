<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Admin user management: list, search, filter, role changes, suspension,
 * email verification, password reset links, post reassignment and deletion.
 *
 * Safety rules enforced on every write action:
 *   - An admin can never change, suspend or delete their OWN account here
 *     (use the profile page and the logout button instead).
 *   - The last remaining active admin can never be demoted, suspended or
 *     deleted, so the panel can never lock itself out.
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $role   = $request->input('role');
        $status = $request->input('status');
        $search = trim((string) $request->input('q'));
        $sort   = in_array($request->input('sort'), ['newest', 'oldest', 'name', 'posts'], true)
            ? $request->input('sort')
            : 'newest';

        $query = User::query()->withCount('posts');

        if ($role && in_array($role, ['admin', 'author', 'reader'], true)) {
            $query->where('role', $role);
        }
        if ($status && in_array($status, ['active', 'suspended'], true) && Schema::hasColumn('users', 'status')) {
            $query->where('status', $status);
        }
        $verified = $request->input('verified');
        if ($verified === 'yes') {
            $query->whereNotNull('email_verified_at');
        } elseif ($verified === 'no') {
            $query->whereNull('email_verified_at');
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        match ($sort) {
            'oldest' => $query->orderBy('created_at'),
            'name'   => $query->orderBy('name'),
            'posts'  => $query->orderByDesc('posts_count'),
            default  => $query->latest(),
        };

        $users = $query->paginate(20)->withQueryString();

        try {
            $suspended = Schema::hasColumn('users', 'status')
                ? User::where('status', 'suspended')->count()
                : 0;
        } catch (\Throwable $e) {
            $suspended = 0;
        }

        $stats = [
            'total'     => User::count(),
            'admins'    => User::where('role', 'admin')->count(),
            'authors'   => User::where('role', 'author')->count(),
            'readers'   => User::where('role', 'reader')->count(),
            'verified'  => User::whereNotNull('email_verified_at')->count(),
            'suspended' => $suspended,
        ];

        return view('admin.users.index', compact('users', 'stats', 'sort'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:admin,author,reader']);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot change your own role. Use another admin account for that.');
        }

        $newRole = $request->input('role');

        if (!$this->canChangeRole($user, $newRole)) {
            return back()->with('error', 'This is the only active admin. Promote another admin first.');
        }

        if ($user->role !== $newRole) {
            $wasRole = $user->role;
            $user->update(['role' => $newRole]);
            return back()->with('success', $user->name.' changed from '.$wasRole.' to '.$newRole.'.');
        }

        return back();
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot suspend your own account.');
        }

        if (($user->status ?? 'active') === 'suspended') {
            $user->update(['status' => 'active']);
            return back()->with('success', $user->name.' can sign in again.');
        }

        if ($user->role === 'admin' && $this->activeAdminCount() <= 1) {
            return back()->with('error', 'This is the only active admin. Promote another admin first.');
        }

        $user->update(['status' => 'suspended']);
        $this->forgetSessionsFor($user);

        return back()->with('success', $user->name.' has been suspended and signed out everywhere.');
    }

    public function toggleVerify(User $user)
    {
        $user->update(['email_verified_at' => $user->email_verified_at ? null : now()]);

        return back()->with('success', $user->email_verified_at
            ? $user->name.' is marked as verified.'
            : $user->name.' is no longer marked as verified.');
    }

    public function sendResetLink(User $user)
    {
        try {
            $sent = Password::broker()->sendResetLink(['email' => $user->email]);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'The reset email could not be sent right now (mail server problem). Please try again in a few minutes.');
        }

        return $sent === Password::RESET_LINK_SENT
            ? back()->with('success', 'A password reset link was sent to '.$user->email.'.')
            : back()->with('error', __($sent));
    }

    /**
     * Give this user's posts to the current admin. Useful before deleting
     * a prolific author whose articles should stay online.
     */
    public function reassignPosts(User $user)
    {
        $count = $user->posts()->count();

        if ($count === 0) {
            return back()->with('error', $user->name.' has no posts to reassign.');
        }

        Post::where('user_id', $user->id)->update(['user_id' => Auth::id()]);

        return back()->with('success', $count.' '.($count === 1 ? 'post' : 'posts').' reassigned to you.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->role === 'admin' && $this->activeAdminCount() <= 1) {
            return back()->with('error', 'This is the only active admin. Promote another admin first.');
        }

        $name  = $user->name;
        $posts = $user->posts()->count();

        $this->deleteAccount($user);

        $message = $name.' was deleted.';
        if ($posts > 0) {
            $message .= ' Their '.$posts.' '.($posts === 1 ? 'post stays' : 'posts stay')
                .' published without an author. Use Reassign on a user row to move posts before deleting.';
        }

        return redirect()->route('admin.users.index')->with('success', $message);
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'bulk_action' => 'required|in:verify,unverify,suspend,unsuspend,role_author,role_reader,delete',
            'ids'         => 'required|array|min:1',
            'ids.*'       => 'integer|exists:users,id',
        ]);

        $action  = $request->input('bulk_action');
        $ids     = collect($request->input('ids'))->map(fn ($id) => (int) $id)->unique();
        $done    = 0;
        $skipped = 0;

        foreach (User::whereIn('id', $ids)->get() as $user) {
            // The acting admin is always skipped: you cannot bulk-edit yourself.
            if ($user->id === Auth::id()) {
                $skipped++;
                continue;
            }

            switch ($action) {
                case 'verify':
                    $user->update(['email_verified_at' => now()]);
                    $done++;
                    break;

                case 'unverify':
                    $user->update(['email_verified_at' => null]);
                    $done++;
                    break;

                case 'suspend':
                    if (($user->status ?? 'active') === 'suspended') {
                        break;
                    }
                    if ($user->role === 'admin' && $this->activeAdminCount() <= 1) {
                        $skipped++;
                        break;
                    }
                    $user->update(['status' => 'suspended']);
                    $this->forgetSessionsFor($user);
                    $done++;
                    break;

                case 'unsuspend':
                    if (($user->status ?? 'active') !== 'suspended') {
                        break;
                    }
                    $user->update(['status' => 'active']);
                    $done++;
                    break;

                case 'role_author':
                case 'role_reader':
                    $newRole = $action === 'role_author' ? 'author' : 'reader';
                    if (!$this->canChangeRole($user, $newRole)) {
                        $skipped++;
                        break;
                    }
                    if ($user->role !== $newRole) {
                        $user->update(['role' => $newRole]);
                        $done++;
                    }
                    break;

                case 'delete':
                    if ($user->role === 'admin' && $this->activeAdminCount() <= 1) {
                        $skipped++;
                        break;
                    }
                    $this->deleteAccount($user);
                    $done++;
                    break;
            }
        }

        $label = [
            'verify'       => 'verified',
            'unverify'     => 'unverified',
            'suspend'      => 'suspended',
            'unsuspend'    => 'unsuspended',
            'role_author'  => 'moved to author',
            'role_reader'  => 'moved to reader',
            'delete'       => 'deleted',
        ][$action];

        $message = $done.' '.($done === 1 ? 'user' : 'users').' '.$label.'.';
        if ($skipped > 0) {
            $message .= ' '.$skipped.' '.($skipped === 1 ? 'user was' : 'users were')
                .' skipped (you, or the last active admin).';
        }

        return back()->with($done > 0 ? 'success' : 'error', $message);
    }

    // ---------------------------------------------------------------------
    //  Helpers
    // ---------------------------------------------------------------------

    private function activeAdminCount(): int
    {
        try {
            if (Schema::hasColumn('users', 'status')) {
                return User::where('role', 'admin')->where('status', 'active')->count();
            }
        } catch (\Throwable $e) {
        }
        return User::where('role', 'admin')->count();
    }

    private function canChangeRole(User $user, string $newRole): bool
    {
        if ($user->id === Auth::id()) {
            return false;
        }
        if ($user->role === 'admin' && $newRole !== 'admin' && $this->activeAdminCount() <= 1) {
            return false;
        }
        return true;
    }

    /**
     * Force sign out everywhere: with the database session driver we drop the
     * user's session rows so suspension takes effect immediately. Other
     * drivers are covered by the BlockSuspendedUsers middleware.
     */
    private function forgetSessionsFor(User $user): void
    {
        try {
            if (config('session.driver') === 'database'
                && Schema::hasTable((string) config('session.table', 'sessions'))) {
                DB::table(config('session.table', 'sessions'))
                    ->where('user_id', $user->id)
                    ->delete();
            }
        } catch (\Throwable $e) {
            // Never block the admin action because sessions could not be cleared.
        }
    }

    private function deleteAccount(User $user): void
    {
        DB::transaction(function () use ($user) {
            // The follows pivot has no foreign keys, so clean it up by hand.
            try {
                DB::table('user_follows')->where('follower_id', $user->id)->delete();
                DB::table('user_follows')->where('followee_id', $user->id)->delete();
            } catch (\Throwable $e) {
                // A missing pivot table must not stop the deletion.
            }

            $this->forgetSessionsFor($user);

            foreach (array_filter([$user->author_avatar_path, $user->avatar]) as $path) {
                if (is_string($path) && $path !== '' && !str_starts_with($path, 'http')) {
                    try {
                        Storage::disk('public')->delete($path);
                    } catch (\Throwable $e) {
                        // Best effort only.
                    }
                }
            }

            // posts.user_id is nullOnDelete, reactions cascade, feedback nulls:
            // the database handles the rest.
            $user->delete();
        });
    }
}
