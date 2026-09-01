<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\Category;
use App\Models\Faq;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use App\Http\Controllers\Frontend\PageController;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@huvanti.com'],
            [
                'name' => 'Admin User',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'bio' => 'Founder and Editor in Chief at Huvanti.',
                'role_title' => 'Editor in Chief',
                'is_verified' => true,
            ]
        );

        // Categories
        $categories = [
            ['name' => 'Technology', 'slug' => 'technology', 'description' => 'Latest in tech, AI, gadgets, and digital trends.'],
            ['name' => 'Health and Wellness', 'slug' => 'health-wellness', 'description' => 'Tips for physical and mental health, fitness, and nutrition.'],
            ['name' => 'Finance', 'slug' => 'finance', 'description' => 'Personal finance, investing, crypto, and money management.'],
            ['name' => 'Travel', 'slug' => 'travel', 'description' => 'Travel guides, hidden gems, and tips for explorers.'],
            ['name' => 'Lifestyle', 'slug' => 'lifestyle', 'description' => 'Minimalism, home, relationships, and everyday living.'],
            ['name' => 'Education', 'slug' => 'education', 'description' => 'Learning techniques, skills development, and study guides.'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // Pages
        $policySlugs = [
            'about' => 'About Us',
            'contact' => 'Contact',
            'privacy-policy' => 'Privacy Policy',
            'terms-conditions' => 'Terms and Conditions',
            'editorial-policy' => 'Editorial Policy',
            'cookie-policy' => 'Cookie Policy',
            'disclaimer' => 'Disclaimer',
            'affiliate-disclosure' => 'Affiliate Disclosure',
            'comment-policy' => 'Comment Policy',
        ];

        foreach ($policySlugs as $slug => $title) {
            $content = PageController::defaultContent($slug);
            Page::firstOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'content' => $content,
                    'status' => 'published',
                ]
            );
        }

        // Navigation
        $navItems = [
            ['label' => 'Home', 'url' => '/', 'position' => 'header', 'sort_order' => 1],
            ['label' => 'Categories', 'url' => '/#categories', 'position' => 'header', 'sort_order' => 2],
            ['label' => 'Blog', 'url' => '/blog', 'position' => 'header', 'sort_order' => 3],
            ['label' => 'About', 'url' => '/about', 'position' => 'header', 'sort_order' => 4],
            ['label' => 'Contact', 'url' => '/contact', 'position' => 'header', 'sort_order' => 5],
            ['label' => 'Home', 'url' => '/', 'position' => 'mobile', 'sort_order' => 1],
            ['label' => 'Categories', 'url' => '/#categories', 'position' => 'mobile', 'sort_order' => 2],
            ['label' => 'Blog', 'url' => '/blog', 'position' => 'mobile', 'sort_order' => 3],
            ['label' => 'About', 'url' => '/about', 'position' => 'mobile', 'sort_order' => 4],
            ['label' => 'Contact', 'url' => '/contact', 'position' => 'mobile', 'sort_order' => 5],
        ];

        foreach ($navItems as $n) {
            NavigationItem::firstOrCreate(['label' => $n['label'], 'position' => $n['position']], $n);
        }

        // Ads
        Advertisement::firstOrCreate(
            ['title' => 'Sidebar Ad 300x250'],
            [
                'position' => 'sidebar',
                'code' => '<div style="background:#f3f4f6;border:2px dashed #d1d5db;border-radius:12px;height:250px;display:flex;align-items:center;justify-content:center;color:#6b7280">Advertisement 300x250</div>',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Advertisement::firstOrCreate(
            ['title' => 'Inline Ad'],
            [
                'position' => 'in_article',
                'code' => '<div style="background:#fffbeb;border:2px dashed #f59e0b;border-radius:12px;height:90px;display:flex;align-items:center;justify-content:center;color:#92400e">Advertisement Zone</div>',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
    }
}
