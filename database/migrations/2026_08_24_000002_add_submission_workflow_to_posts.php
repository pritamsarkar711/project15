<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the multi-author submission workflow to the posts table.
 *
 * Columns added:
 *   - review_status: tracks where the post is in the approval pipeline.
 *       draft           (author is still writing)
 *       pending_review  (author clicked "Submit for review")
 *       approved        (admin approved; published_at set; status=published)
 *       returned        (admin returned with a note; author must edit & resubmit)
 *   - submitted_at:    timestamp of last submission
 *   - reviewed_at:     timestamp of last admin review action
 *   - reviewer_id:     FK to users (admin who approved/returned)
 *   - reviewer_note:   short note from admin when returning a post
 *   - is_affiliate:    bool — if true, frontend renders a disclosure notice board
 *
 * Notes:
 *   - To preserve the daily-1-post limit we don't need a new column —
 *     we query posts.submitted_at within the last 24h for the author.
 *   - We do NOT touch the existing `status` column. The author flow uses
 *     review_status as the source of truth; status is synced to "published"
 *     only when review_status = approved.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('review_status', 32)->default('draft')->after('status');
            $table->timestamp('submitted_at')->nullable()->after('review_status');
            $table->timestamp('reviewed_at')->nullable()->after('submitted_at');
            $table->foreignId('reviewer_id')->nullable()
                ->after('reviewed_at')->constrained('users')->nullOnDelete();
            $table->text('reviewer_note')->nullable()->after('reviewer_id');
            $table->boolean('is_affiliate')->default(false)->after('reviewer_note');

            $table->index(['user_id', 'review_status']);
            $table->index('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'review_status']);
            $table->dropIndex('submitted_at');
            $table->dropColumn([
                'review_status', 'submitted_at', 'reviewed_at',
                'reviewer_id', 'reviewer_note', 'is_affiliate',
            ]);
        });
    }
};
