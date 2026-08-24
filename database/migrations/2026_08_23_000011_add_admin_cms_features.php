<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Comments: threaded replies support (parent_id already exists -> add reply_depth)
        Schema::table('comments', function (Blueprint $table) {
            if (!Schema::hasColumn('comments', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->constrained('comments')->nullOnDelete();
            }
            if (!Schema::hasColumn('comments', 'reply_depth')) {
                $table->tinyInteger('reply_depth')->default(0);
            }
        });

        // Posts: soft delete + scheduling
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable()->index();
            }
            if (!Schema::hasColumn('posts', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->index();
            }
        });

        // Users: real TOTP secret + author avatar path
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'google2fa_secret')) {
                $table->text('google2fa_secret')->nullable();
            }
            if (!Schema::hasColumn('users', 'author_avatar_path')) {
                $table->string('author_avatar_path')->nullable();
            }
        });

        // Categories: icon column already exists from initial migration
        if (Schema::hasColumn('categories', 'icon')) {
            // Normalize legacy seeder icons to the new allowed Lucide set
            $legacyMap = ['heart' => 'heart-pulse', 'wallet' => 'banknote', 'sparkles' => 'sun', 'book' => 'graduation-cap'];
            foreach ($legacyMap as $old => $new) {
                \Illuminate\Support\Facades\DB::table('categories')->where('icon', $old)->update(['icon' => $new]);
            }
            \Illuminate\Support\Facades\DB::table('categories')->whereNull('icon')->update(['icon' => 'newspaper']);
        }

        // Advertisements: normalize legacy position values to new set
        \Illuminate\Support\Facades\DB::table('advertisements')
            ->where('position', 'inline')
            ->update(['position' => 'in_article']);
        \Illuminate\Support\Facades\DB::table('advertisements')
            ->where('position', 'between_posts')
            ->update(['position' => 'in_article']);
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            if (Schema::hasColumn('comments', 'reply_depth')) {
                $table->dropColumn('reply_depth');
            }
        });
        Schema::table('posts', function (Blueprint $table) {
            foreach (['deleted_at', 'scheduled_at'] as $col) {
                if (Schema::hasColumn('posts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::table('users', function (Blueprint $table) {
            foreach (['google2fa_secret', 'author_avatar_path'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        \Illuminate\Support\Facades\DB::table('advertisements')
            ->where('position', 'in_article')
            ->update(['position' => 'inline']);
    }
};
