<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use App\Models\Advertisement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@huvanti.com'],
            [
                'name' => 'Huvanti Admin',
                'password' => Hash::make('Huvanti@2026'),
                'role' => 'admin',
                'bio' => 'Founder & Editor-in-Chief at Huvanti. Passionate about sharing knowledge across technology, lifestyle, health, travel and finance.',
                'email_verified_at' => now(),
            ]
        );

        // Categories - multi-niche - deep green palette consistent with primary #14532d
        $categories = [
            ['name'=>'Technology','slug'=>'technology','description'=>'Latest in tech AI gadgets and software','color'=>'#14532d','icon'=>'cpu'],
            ['name'=>'Health & Wellness','slug'=>'health-wellness','description'=>'Fitness nutrition and mental health','color'=>'#0f3d22','icon'=>'heart'],
            ['name'=>'Finance','slug'=>'finance','description'=>'Money investing and personal finance','color'=>'#1b4332','icon'=>'wallet'],
            ['name'=>'Travel','slug'=>'travel','description'=>'Destinations guides and travel tips','color'=>'#0f5132','icon'=>'plane'],
            ['name'=>'Lifestyle','slug'=>'lifestyle','description'=>'Culture food and everyday inspiration','color'=>'#164e3b','icon'=>'sparkles'],
            ['name'=>'Education','slug'=>'education','description'=>'Learning career and skill development','color'=>'#1a3a2a','icon'=>'book'],
        ];
        foreach($categories as $i=>$c){
            Category::firstOrCreate(['slug'=>$c['slug']], array_merge($c, ['sort_order'=>$i]));
        }

        // Settings
        $settings = [
            ['key'=>'site_name','value'=>'Huvanti','group'=>'general'],
            ['key'=>'site_tagline','value'=>'Explore Ideas. Inspire Life.','group'=>'general'],
            ['key'=>'site_description','value'=>'Huvanti is a multi-niche blog covering technology, health, finance, travel, lifestyle and education with expert insights and curated stories.','group'=>'general'],
            ['key'=>'site_keywords','value'=>'huvanti, blog, multi niche, technology, health, finance, travel','group'=>'seo'],
            ['key'=>'site_logo','value'=>'','group'=>'general'],
            ['key'=>'contact_email','value'=>'hello@huvanti.com','group'=>'general'],
            ['key'=>'footer_copyright','value'=>'2026-27, All Rights Reserved','group'=>'footer'],
            ['key'=>'ads_sidebar_code','value'=>'','group'=>'ads'],
        ];
        foreach($settings as $s){
            Setting::updateOrCreate(['key'=>$s['key']], $s);
        }

        // Pages
        $pages = [
            ['title'=>'Privacy Policy','slug'=>'privacy-policy','content'=>'<h2>Privacy Policy</h2><p>At Huvanti (huvanti.com), we respect your privacy. This policy explains what data we collect, how we use it, and your rights.</p><h3>Information We Collect</h3><p>We collect information you provide directly, such as contact forms and comments, plus anonymized analytics.</p><h3>Cookies</h3><p>We use cookies to improve experience. See our Cookie Policy for details.</p>'],
            ['title'=>'Terms and Conditions','slug'=>'terms-conditions','content'=>'<h2>Terms</h2><p>By accessing huvanti.com you agree to these terms. Content is for informational purposes only.</p><h3>Intellectual Property</h3><p>All articles, images and logos are owned by Huvanti unless otherwise stated.</p>'],
            ['title'=>'Cookie Policy','slug'=>'cookie-policy','content'=>'<h2>Cookie Policy</h2><p>Huvanti uses cookies to personalize content, analyze traffic and improve our services. You can control cookies via browser settings.</p>'],
            ['title'=>'About Us','slug'=>'about','content'=>'<h2>About Huvanti</h2><p>Huvanti is a multi-niche blogging platform built to explore ideas that matter — from technology and finance to health, travel, lifestyle and education. Our mission is to deliver high-quality, research-driven content that inspires and empowers readers worldwide.</p><p>Founded with a love for storytelling, Huvanti blends clean design with meaningful content.</p>'],
            ['title'=>'Contact','slug'=>'contact','content'=>'<h2>Contact Huvanti</h2><p>We love hearing from our readers. Use the form to get in touch.</p>'],
            ['title'=>'Editorial Policy','slug'=>'editorial-policy','content'=>'<h2>Editorial Policy</h2><p>Huvanti is committed to accuracy, independence and transparency. Our editorial process includes fact-checking, expert review and clear sourcing.</p><p><em>This policy can be updated by the admin at any time.</em></p>'],
        ];
        foreach($pages as $p){
            Page::firstOrCreate(['slug'=>$p['slug']], array_merge($p, ['status'=>'published']));
        }

        // Navigation
        $navItems = [
            ['label'=>'Home','url'=>'/','position'=>'header','sort_order'=>1],
            ['label'=>'Categories','url'=>'/#categories','position'=>'header','sort_order'=>2],
            ['label'=>'Blog','url'=>'/blog','position'=>'header','sort_order'=>3],
            ['label'=>'About','url'=>'/about','position'=>'header','sort_order'=>4],
            ['label'=>'Contact','url'=>'/contact','position'=>'header','sort_order'=>5],
            // mobile same but allow separate control
            ['label'=>'Home','url'=>'/','position'=>'mobile','sort_order'=>1],
            ['label'=>'Categories','url'=>'/#categories','position'=>'mobile','sort_order'=>2],
            ['label'=>'Blog','url'=>'/blog','position'=>'mobile','sort_order'=>3],
            ['label'=>'About','url'=>'/about','position'=>'mobile','sort_order'=>4],
            ['label'=>'Contact','url'=>'/contact','position'=>'mobile','sort_order'=>5],
        ];
        foreach($navItems as $n){ NavigationItem::firstOrCreate(['label'=>$n['label'],'position'=>$n['position']], $n); }

        // Ads
        Advertisement::firstOrCreate(['title'=>'Sidebar Ad 300x250'], ['position'=>'sidebar','code'=>'<div style="background:#f3f4f6;border:2px dashed #d1d5db;border-radius:12px;height:250px;display:flex;align-items:center;justify-content:center;color:#6b7280">Advertisement 300x250</div>','is_active'=>true,'sort_order'=>1]);
        Advertisement::firstOrCreate(['title'=>'Inline Ad'], ['position'=>'inline','code'=>'<div style="background:#fffbeb;border:2px dashed #f59e0b;border-radius:12px;height:90px;display:flex;align-items:center;justify-content:center;color:#92400e">Advertisement Zone</div>','is_active'=>true,'sort_order'=>1]);

        // Posts - sample 8 posts across categories
        $cats = Category::all()->keyBy('slug');
        $samplePosts = [
            [
                'title'=>'The Future of AI: 7 Trends That Will Shape 2027',
                'slug'=>'future-of-ai-trends-2027',
                'excerpt'=>'From generative agents to edge AI, discover the breakthrough trends redefining how we live and work.',
                'category'=>'technology',
                'views'=>3421,
                'is_featured'=>true,
                'content'=>'<h2>Introduction</h2><p>Artificial intelligence is no longer a futuristic promise — it is the operating system of modern life. In 2026-27, we are witnessing a convergence of large models, efficient chips and real-world deployment.</p><h2>1. Generative Agents</h2><p>AI agents that can plan, use tools and collaborate are moving from demos to daily workflows.</p><h3>Why it matters</h3><p>Businesses report 40% productivity gains where agents are correctly integrated.</p><h2>2. Edge AI</h2><p>On-device models reduce latency and privacy concerns. Your phone now runs models that previously needed a data center.</p><h2>3. AI in Healthcare</h2><p>Diagnostic models are achieving specialist-level accuracy while remaining interpretable.</p><p>Stay tuned as we explore deeper use cases in the coming months.</p>',
                'faqs'=>[
                    ['q'=>'Is AI going to replace jobs?','a'=>'AI will transform jobs rather than eliminate them. Roles that involve routine pattern recognition are most affected, while creative and interpersonal roles grow.'],
                    ['q'=>'What is Edge AI?','a'=>'Edge AI runs models directly on devices (phone, laptop, sensor) instead of the cloud, offering faster response and better privacy.'],
                ]
            ],
            [
                'title'=>'Minimalist Morning Routines for More Focus and Energy',
                'slug'=>'minimalist-morning-routines-focus-energy',
                'excerpt'=>'A simple 30-minute routine that improves focus, mood and metabolic health — without overwhelm.',
                'category'=>'health-wellness',
                'content'=>'<h2>Why Mornings Matter</h2><p>How you start the day sets your hormonal rhythm. A calm, intentional morning reduces cortisol spikes and improves decision making.</p><h2>The 4-Step Routine</h2><p><strong>1. Light:</strong> Get natural light within 10 minutes of waking.<br><strong>2. Movement:</strong> 5 minutes of mobility or walk.<br><strong>3. Mind:</strong> 2 minutes breathing or journaling.<br><strong>4. Fuel:</strong> Protein + fiber before caffeine.</p><h3>Tracking Progress</h3><p>Use a simple habit tracker. Consistency beats intensity.</p>',
                'faqs'=>[
                    ['q'=>'How long until I see results?','a'=>'Most people notice better energy within 7-10 days. Sleep quality often improves first.'],
                ]
            ],
            [
                'title'=>'Smart Investing for Beginners: Building Wealth in Your 20s and 30s',
                'slug'=>'smart-investing-beginners-building-wealth',
                'excerpt'=>'No jargon, just practical steps to start investing with small amounts and avoid common mistakes.',
                'category'=>'finance',
                'content'=>'<h2>Start with Why</h2><p>Investing is not about picking winners; it is about time, diversification and behavior.</p><h2>The Core Principles</h2><p>Diversify across index funds, keep fees low, automate contributions and stay invested.</p><h3>An Example</h3><p>$200/month at 7% annual return becomes ~$240k in 30 years.</p><h2>Common Mistakes</h2><p>Chasing hype, panic selling and ignoring emergency funds derail most beginners.</p>',
                'faqs'=>[
                    ['q'=>'How much should I invest to start?','a'=>'Start with what you can afford consistently — even $10-50/month builds the habit and benefits from compounding.'],
                    ['q'=>'Is crypto a good investment?','a'=>'Crypto is high risk and volatile. Treat it as a small satellite, not the core of your portfolio.'],
                ]
            ],
            [
                'title'=>'Hidden Gems of Southeast Asia: 5 Islands Without the Crowds',
                'slug'=>'hidden-gems-southeast-asia-islands',
                'excerpt'=>'Crystal water, local food and quiet beaches — our curated list for slow travel lovers.',
                'category'=>'travel',
                'content'=>'<h2>Why Go Off the Beaten Path?</h2><p>Overtourism strains locals. These islands offer culture, nature and space to breathe.</p><h2>1. Koh Rong Sanloem, Cambodia</h2><p>Bioluminescent waters and jungle trails.</p><h2>2. Siargao, Philippines (North)</h2><p>Surf, lagoons and community-run stays.</p><h2>3. Pulau Weh, Indonesia</h2><p>World-class diving without Bali prices.</p>',
                'faqs'=>[
                    ['q'=>'Best time to visit?','a'=>'November to April offers dry season across most of these islands.'],
                ]
            ],
            [
                'title'=>'The Art of Slow Living: Designing a Home That Calms Your Mind',
                'slug'=>'art-of-slow-living-designing-home-calms-mind',
                'excerpt'=>'How texture, light and negative space can turn your home into a restorative sanctuary.',
                'category'=>'lifestyle',
                'content'=>'<h2>Slow Living at Home</h2><p>Our homes shape our nervous system. Choice of materials, light and clutter directly affect stress.</p><h3>Three Principles</h3><p>Natural materials, soft light layers and visible empty space.</p><h2>Start Small</h2><p>Clear one surface, add a linen texture and a warm lamp. Notice how your breath changes.</p>',
                'faqs'=>[]
            ],
            [
                'title'=>'Learn Anything Faster: The Spaced Repetition Playbook',
                'slug'=>'learn-anything-faster-spaced-repetition',
                'excerpt'=>'Neuroscience-backed techniques to remember more in less time — for students and professionals.',
                'category'=>'education',
                'content'=>'<h2>The Forgetting Curve</h2><p>We forget 70% within 24 hours unless we reactivate memory. Spaced repetition fixes this.</p><h2>How to Apply</h2><p>Test yourself before re-reading, space intervals (1d, 3d, 7d, 14d), and use active recall cards.</p><h3>Tools</h3><p>Anki, RemNote or even pen-and-paper boxes work — the system matters more than the app.</p>',
                'faqs'=>[
                    ['q'=>'Does cramming ever work?','a'=>'Cramming helps short-term recall for an exam but fails for long-term retention. Spacing wins for durable learning.'],
                ]
            ],
            [
                'title'=>'Balanced Eating on a Budget: 7-Day High-Protein Meal Plan',
                'slug'=>'balanced-eating-budget-high-protein-meal-plan',
                'excerpt'=>'Nutritious, affordable and time-saving meals designed for busy professionals.',
                'category'=>'health-wellness',
                'views'=>2100,
                'is_featured'=>true,
                'content'=>'<h2>Principles</h2><p>Budget eating does not mean boring. Focus on eggs, legumes, frozen veg and whole grains.</p><h2>Sample Day</h2><p>Breakfast: Greek yogurt + oats. Lunch: Chickpea salad. Dinner: Stir-fry tofu + brown rice.</p><h3>Shopping Tip</h3><p>Buy in bulk, cook 2x per week, and freeze portions.</p>',
                'faqs'=>[]
            ],
            [
                'title'=>'Remote Work Productivity: Deep Work in a Distracted World',
                'slug'=>'remote-work-productivity-deep-work',
                'excerpt'=>'Master attention, async communication and rituals to thrive while working from anywhere.',
                'category'=>'technology',
                'content'=>'<h2>The Attention Economy</h2><p>Distraction is the default. Deep work requires design, not willpower.</p><h2>System</h2><p>Time-block 90-minute focus sessions, batch messages twice daily, and end with a shutdown ritual.</p>',
                'faqs'=>[]
            ],
        ];

        foreach($samplePosts as $sp){
            $cat = $cats[$sp['category']] ?? $cats->first();
            $post = Post::firstOrCreate(['slug'=>$sp['slug']], [
                'title'=>$sp['title'],
                'excerpt'=>$sp['excerpt'],
                'content'=>$sp['content'],
                'category_id'=>$cat->id,
                'user_id'=>$admin->id,
                'author_name'=>'Joe Goldberg',
                'author_bio'=>'Senior Editor at Huvanti covering technology, health and modern culture. Passionate about clear, human-centered storytelling.',
                'reading_time'=> ceil(str_word_count(strip_tags($sp['content']))/200),
                'status'=>'published',
                'published_at'=> now()->subDays(rand(1,20)),
                'meta_title'=>$sp['title'].' | Huvanti',
                'meta_description'=>$sp['excerpt'],
                'views'=>$sp['views'] ?? rand(500,2500),
                'is_featured'=>$sp['is_featured'] ?? false,
                'featured_image'=> 'https://picsum.photos/seed/'.Str::slug($sp['slug']).'/800/450',
            ]);
            foreach($sp['faqs'] as $idx=>$f){
                Faq::firstOrCreate(['post_id'=>$post->id,'question'=>$f['q']], ['answer'=>$f['a'],'sort_order'=>$idx]);
            }
            // Seed comments - 2 approved per post
            if(Comment::where('post_id',$post->id)->count()==0){
                Comment::create(['post_id'=>$post->id,'name'=>'Aarav Mehta','email'=>'aarav@example.com','content'=>'Really insightful article! The FAQ section cleared my doubts.','status'=>'approved']);
                Comment::create(['post_id'=>$post->id,'name'=>'Sofia Lee','email'=>'sofia@example.com','content'=>'Thanks for sharing this. Would love a deeper piece on point 2.','status'=>'pending']);
            }
        }
    }
}
