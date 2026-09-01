<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SEO + social suite:
 *   posts
 *     - focus_keyword      RankMath-style target keyword used by the live
 *                          SEO score panel and the server-side analyzer.
 *     - seo_score          0–100 score persisted on save so lists can show
 *                          a badge without recomputing anything.
 *     - instant_indexed_at Last time the post URL was manually submitted
 *                          to IndexNow (auto pings also refresh it).
 *   social_publishes       One row per (post × network) auto-post attempt,
 *                          giving the admin a delivery log with retry.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'focus_keyword')) {
                $table->string('focus_keyword')->nullable()->after('meta_keywords');
            }
            if (!Schema::hasColumn('posts', 'seo_score')) {
                $table->unsignedTinyInteger('seo_score')->nullable()->after('focus_keyword');
            }
            if (!Schema::hasColumn('posts', 'instant_indexed_at')) {
                $table->timestamp('instant_indexed_at')->nullable()->after('seo_score');
            }
        });

        if (!Schema::hasTable('social_publishes')) {
            Schema::create('social_publishes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('post_id')->constrained()->cascadeOnDelete();
                $table->string('network', 32);          // x, facebook, linkedin, instagram, telegram, pinterest
                $table->string('status', 16)->default('pending'); // pending|success|failed
                $table->string('external_url', 500)->nullable();  // link to the created post, when the API returns one
                $table->string('error', 1000)->nullable();        // human-readable failure reason
                $table->unsignedInteger('attempts')->default(0);
                $table->timestamp('published_at')->nullable();    // when the remote post succeeded
                $table->timestamps();

                $table->index(['post_id', 'network']);
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('social_publishes');
        Schema::table('posts', function (Blueprint $table) {
            foreach (['focus_keyword', 'seo_score', 'instant_indexed_at'] as $col) {
                if (Schema::hasColumn('posts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
