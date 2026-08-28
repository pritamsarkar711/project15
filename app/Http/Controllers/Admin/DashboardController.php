<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        // ------------------------------------------------------------------
        // Self-updating database: run pending migrations automatically when
        // an admin opens the dashboard.
        //
        // Why: this site lives on Hostinger shared hosting without SSH, and
        // there is no deploy script to run after a git push. Result: new
        // migrations (extra categories, post reactions, ...) silently never ran and
        // features appeared "broken". Running `migrate --force` here is
        // idempotent: when nothing is pending Laravel does nothing at all.
        // Failures are caught and shown to the admin instead of crashing
        // the dashboard.
        // ------------------------------------------------------------------
        $migrationNotice = null;
        try {
            if (Schema::hasTable('migrations')) {
                $ran = DB::table('migrations')->pluck('migration')->all();
                $files = collect(glob(database_path('migrations/*.php')))
                    ->map(fn ($f) => basename($f, '.php'));
                if ($files->diff($ran)->isNotEmpty()) {
                    Artisan::call('migrate', ['--force' => true]);
                    $migrationNotice = 'Database updated: pending migrations were applied automatically.';
                }
            }
        } catch (\Throwable $e) {
            $migrationNotice = 'A database update could not be applied automatically: '
                . \Illuminate\Support\Str::limit($e->getMessage(), 180);
        }

        $stats = [
            'posts'            => Post::count(),
            'views'            => (int) Post::sum('views'),
            'comments_pending' => Comment::where('status', 'pending')->count(),
            'contact_unread'   => ContactMessage::where('is_read', false)->count(),
        ];

        $recentPosts = Post::with('category')->latest()->take(5)->get();
        $recentComments = Comment::with('post')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentPosts', 'recentComments'))
            ->with($migrationNotice ? ['success' => $migrationNotice] : []);
    }
}
