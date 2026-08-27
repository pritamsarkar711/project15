<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add posts.autosaved_at — marker for the server-side autosave feature.
 *
 * WHY: authors (and admins) can lose a whole post to a browser crash, power
 * cut ("load shedding") or dead network while writing. The editor already
 * keeps a local copy, but that only lives in one browser. With autosaved_at:
 *   - every autosave stamps the time so the client can show "saved X ago";
 *   - postsCreate() can find the newest auto-saved-but-never-saved draft and
 *     offer one-click recovery on the "new post" page;
 *   - a manual save CLEARS the marker (the post is no longer autosave-only),
 *     so recovery banners never point at work the user already saved.
 * Idempotent: safe to re-run, auto-applied by AppServiceProvider.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('posts') && !Schema::hasColumn('posts', 'autosaved_at')) {
            try {
                Schema::table('posts', function (Blueprint $table) {
                    $table->timestamp('autosaved_at')->nullable()->after('updated_at');
                });
            } catch (\Throwable $e) {
                // Never block the site from booting over this column.
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('posts') && Schema::hasColumn('posts', 'autosaved_at')) {
            try {
                Schema::table('posts', function (Blueprint $table) {
                    $table->dropColumn('autosaved_at');
                });
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }
};
