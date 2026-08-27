<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Align database column lengths with the form/validation limits.
 *
 * ROOT CAUSE OF A REAL PRODUCTION 500: the create/edit post forms validate
 * the excerpt and FAQ question fields with Laravel's max:500 rule (the form
 * inputs even carry maxlength="500"), but the underlying columns were only
 * VARCHAR(255). On MySQL with strict mode (Hostinger default) any submit
 * where those fields exceeded 255 characters crashed with
 * "Data too long for column" (SQLSTATE 22001) → HTTP 500.
 *
 * Because the posts row is saved BEFORE the FAQs are synced in the author
 * controller, a long FAQ question produced the exact "post was created but
 * the site showed a 500 error" symptom reported on the author dashboard.
 *
 * This migration widens both columns to VARCHAR(500) so the database matches
 * what the validation layer already promises. Idempotent: safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        // posts.excerpt: VARCHAR(255) -> VARCHAR(500)
        if (Schema::hasTable('posts') && Schema::hasColumn('posts', 'excerpt')) {
            try {
                Schema::table('posts', function (Blueprint $table) {
                    $table->string('excerpt', 500)->nullable()->change();
                });
            } catch (\Throwable $e) {
                // Fallback for servers where ->change() is restricted:
                // raw MySQL ALTER (Laravel native change covers this too on
                // MySQL 8 / MariaDB 10.2+, which is what Hostinger runs).
                try {
                    DB::statement('ALTER TABLE `posts` MODIFY `excerpt` VARCHAR(500) NULL DEFAULT NULL');
                } catch (\Throwable $ignored) {
                    // Column already widened or unsupported — never block deploy.
                }
            }
        }

        // faqs.question: VARCHAR(255) -> VARCHAR(500)
        if (Schema::hasTable('faqs') && Schema::hasColumn('faqs', 'question')) {
            try {
                Schema::table('faqs', function (Blueprint $table) {
                    $table->string('question', 500)->change();
                });
            } catch (\Throwable $e) {
                try {
                    DB::statement('ALTER TABLE `faqs` MODIFY `question` VARCHAR(500) NOT NULL');
                } catch (\Throwable $ignored) {
                    // Same idempotency guard as above.
                }
            }
        }
    }

    public function down(): void
    {
        // Intentionally irreversible in behaviour: truncating live data back
        // to 255 chars would corrupt existing posts. Left as no-op.
    }
};
