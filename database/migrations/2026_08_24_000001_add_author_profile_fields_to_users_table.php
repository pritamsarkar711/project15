<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Adds author social media + username + portfolio + verified badge to
     * the users table.
     *
     * These power the "About the Author" card on blog posts (social icons)
     * and the public author profile page at /{username}.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Unique, lowercase, URL-safe username for the public profile page
            // (e.g. huvanti.com/pritam.sarkar). Cannot be changed once set.
            $table->string('username', 50)->nullable()->unique()->after('name');
            // Author role/title shown next to their name (e.g. "Editor-in-Chief").
            $table->string('role_title', 80)->nullable()->after('bio');
            // Personal portfolio URL (optional).
            $table->string('portfolio_url', 255)->nullable()->after('role_title');
            // JSON of social media links, e.g.
            // {"x":"https://x.com/pritam","linkedin":"https://linkedin.com/in/pritam"}
            $table->json('social_links')->nullable()->after('portfolio_url');
            // Verified badge — admins are auto-verified on save.
            $table->boolean('is_verified')->default(false)->after('social_links');
            // Follower count (denormalized cache; followers table added below).
            $table->unsignedInteger('followers_count')->default(0)->after('is_verified');
            $table->unsignedInteger('following_count')->default(0)->after('followers_count');
        });

        Schema::create('user_follows', function (Blueprint $table) {
            // Follower → Followee relationship. Composite PK prevents dupes.
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('followee_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            $table->primary(['follower_id', 'followee_id']);
            $table->index('followee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_follows');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username', 'role_title', 'portfolio_url', 'social_links',
                'is_verified', 'followers_count', 'following_count',
            ]);
        });
    }
};
