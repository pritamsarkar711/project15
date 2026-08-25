<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add more default categories to the site.
 *
 * These complement the original six (Technology, Health & Wellness, Finance,
 * Travel, Lifestyle, Education). Every category is created with
 * firstOrCreate on the slug, so this migration is safe to run repeatedly and
 * never overwrites admin edits (renames, icons, sort order, is_active).
 *
 * Note: a new category stays hidden from the public site until at least one
 * post is published under it (see Category::scopeLive) — exactly the
 * behaviour requested: "category not shown in site until any post is
 * published under that category".
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $categories = [
            ['name' => 'Business',            'slug' => 'business',            'description' => 'Entrepreneurship, startups, marketing and business strategy',        'icon' => 'briefcase',   'sort_order' => 6],
            ['name' => 'Entertainment',       'slug' => 'entertainment',       'description' => 'Movies, music, celebrities and pop culture',                       'icon' => 'film',        'sort_order' => 7],
            ['name' => 'Sports',              'slug' => 'sports',              'description' => 'Match analysis, fitness tips and sports news',                     'icon' => 'dumbbell',    'sort_order' => 8],
            ['name' => 'Food & Recipes',      'slug' => 'food-recipes',        'description' => 'Cooking guides, restaurant reviews and kitchen tips',             'icon' => 'utensils',    'sort_order' => 9],
            ['name' => 'Science',             'slug' => 'science',             'description' => 'Discoveries, research and how the universe works',                 'icon' => 'flask-conical', 'sort_order' => 10],
            ['name' => 'Gaming',              'slug' => 'gaming',              'description' => 'Game reviews, esports and gaming hardware',                       'icon' => 'tv',          'sort_order' => 11],
            ['name' => 'Automotive',          'slug' => 'automotive',          'description' => 'Car reviews, maintenance tips and industry news',                 'icon' => 'car',         'sort_order' => 12],
            ['name' => 'World News',          'slug' => 'world-news',          'description' => 'Breaking headlines and global affairs explained',                 'icon' => 'globe',       'sort_order' => 13],
            ['name' => 'Home & DIY',          'slug' => 'home-diy',            'description' => 'Interior ideas, improvement projects and how-to guides',          'icon' => 'puzzle',      'sort_order' => 14],
            ['name' => 'Personal Growth',     'slug' => 'personal-growth',     'description' => 'Productivity, habits and building a better you',                   'icon' => 'lightbulb',   'sort_order' => 15],
        ];

        foreach ($categories as $c) {
            // Idempotent insert: skip if the slug already exists, and never
            // touch an existing row so admin edits survive re-runs.
            $exists = DB::table('categories')->where('slug', $c['slug'])->exists();
            if (!$exists) {
                DB::table('categories')->insert([
                    'name'        => $c['name'],
                    'slug'        => $c['slug'],
                    'description' => $c['description'],
                    'color'       => '#0C3B2E',
                    'icon'        => $c['icon'],
                    'sort_order'  => $c['sort_order'],
                    'is_active'   => true,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Only remove the categories this migration added, and only if they
        // are still unused (no posts assigned).
        $slugs = ['business','entertainment','sports','food-recipes','science','gaming','automotive','world-news','home-diy','personal-growth'];

        $used = DB::table('posts')->whereIn('category_id', function ($q) use ($slugs) {
            $q->select('id')->from('categories')->whereIn('slug', $slugs);
        })->pluck('category_id')->unique();

        DB::table('categories')
            ->whereIn('slug', $slugs)
            ->whereNotIn('id', $used)
            ->delete();
    }
};
