<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Round 5 polish:
 *
 *  1. Full Editorial Policy content (AdSense reviewers read this page; the
 *     seeded two-paragraph version was too thin). Updated only while the
 *     stored text is still the original seed, so admin edits are kept.
 *  2. Ensures the About page exists with complete content.
 *  3. New eye-catching hero subtitle (updates the stored value only when it
 *     still equals the previous default).
 *  4. Health & Wellness category icon switched to the clean pulse line.
 *  5. ads_enabled setting: ads stay completely hidden until the admin
 *     switches them on in Settings, Ads tab (after AdSense approval).
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // ------------------------------------------------------------------
        // 1. Editorial Policy: replace the thin seeded version.
        // ------------------------------------------------------------------
        try {
            $editorial = <<<'HTML'
<h2>Editorial Policy</h2>
<p>Last updated: August 2026</p>
<p>This policy explains how huvanti.com creates, reviews and maintains its content.</p>
<h3>How we write</h3>
<p>Every article starts with a question real readers ask. We research each answer, test claims where we can, and write in plain language. Our writers work from direct experience with the products, places and techniques they cover.</p>
<h3>Sourcing and accuracy</h3>
<p>When we cite studies, statistics or news, we link to the original source so you can verify it yourself. Numbers are checked before publication. If we cannot verify a claim, we say so clearly or leave it out.</p>
<h3>Independence</h3>
<p>Advertisers and affiliate partners have no say in what we write or how we rate anything. Commissions never influence our recommendations. If a product is bad, we say it is bad, even when we could earn from it.</p>
<h3>Use of AI tools</h3>
<p>We may use AI tools for grammar and research assistance, the same way a writer uses a spell checker. Every published article is written, reviewed and approved by a human editor who takes responsibility for it. We do not publish unreviewed machine-generated text.</p>
<h3>Corrections</h3>
<p>Mistakes happen. When a reader or editor finds one, we fix the article and note the change. You can report a problem any time through the <a href="/contact">contact page</a>.</p>
<h3>Author standards</h3>
<p>Contributing authors follow our posting rules: original writing, real experience, honest sourcing, no filler. Every submission passes a human review before it goes live.</p>
HTML;

            $existing = DB::table('pages')->where('slug', 'editorial-policy')->first();
            if ($existing === null) {
                DB::table('pages')->insert([
                    'title' => 'Editorial Policy',
                    'slug' => 'editorial-policy',
                    'content' => $editorial,
                    'status' => 'published',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } elseif (strlen(strip_tags((string) $existing->content)) < 900) {
                DB::table('pages')->where('slug', 'editorial-policy')->update([
                    'title' => 'Editorial Policy',
                    'content' => $editorial,
                    'updated_at' => $now,
                ]);
            }
        } catch (\Throwable $e) {
            // pages table missing on very fresh installs; non-fatal.
        }

        // ------------------------------------------------------------------
        // 2. About page: make sure it exists with full content.
        // ------------------------------------------------------------------
        try {
            $about = <<<'HTML'
<h2>About Huvanti</h2>
<p>Huvanti is a multi-niche publishing platform built for curious minds who want more from their reading time. We bring together technology, health and wellness, finance, travel, lifestyle and education, not as scattered topics, but as connected parts of a well lived life.</p>
<h2>What we cover</h2>
<p>Our categories are curated by editors who read widely in their fields. Technology explains the tools and trends shaping daily life. Health and Wellness offers practical, evidence informed guidance on food, sleep, fitness and mental focus. Finance breaks down investing, saving and money habits into steps anyone can follow. Travel covers meaningful destinations and smarter trip planning. Lifestyle explores calm, intentional living, and Education shares learning techniques that actually work.</p>
<h2>How we work</h2>
<p>Articles are drafted, reviewed and fact checked before publication. When we cite studies or data, we link to the source so you can verify it yourself. When something changes, we update the article and note the change. Reader feedback is part of our editorial process, and every comment is read by a human being.</p>
<h2>Our mission</h2>
<p>To explore ideas that matter and inspire life through clear, human centered content. We measure success not by clicks, but by whether a reader finishes an article knowing something useful they did not know before.</p>
<h2>Meet the team</h2>
<p>Huvanti is written and edited by a small independent team led by <strong>Pritam Sarkar</strong>, working with contributors who care deeply about their subjects. We are readers first and publishers second, and we build Huvanti around the experience we want for ourselves: calm pages, honest writing and zero clutter.</p>
<p>Questions or ideas? Write to us through the <a href="/contact">contact page</a>.</p>
HTML;

            $existing = DB::table('pages')->where('slug', 'about')->first();
            if ($existing === null) {
                DB::table('pages')->insert([
                    'title' => 'About Us',
                    'slug' => 'about',
                    'content' => $about,
                    'status' => 'published',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        } catch (\Throwable $e) {
            // Non-fatal.
        }

        // ------------------------------------------------------------------
        // 3. Hero subtitle: switch to the punchier line (only when the stored
        //    value still equals the previous default, never clobber edits).
        // ------------------------------------------------------------------
        try {
            DB::table('settings')
                ->where('key', 'hero_subtitle')
                ->whereIn('value', [
                    'Tech, health, finance, travel and more, all in one calm place to read.',
                    'Tech, health, finance, travel and more, one calm place to read.',
                ])
                ->update(['value' => 'Tech, health, money, travel and more. Clear thinking, zero noise.']);
        } catch (\Throwable $e) {
            // Non-fatal.
        }

        // ------------------------------------------------------------------
        // 4. Health & Wellness icon: clean pulse line that reads clearly at
        //    every size (the old heart-pulse drew cluttered at small sizes).
        // ------------------------------------------------------------------
        try {
            DB::table('categories')
                ->where('slug', 'health-wellness')
                ->where('icon', 'heart-pulse')
                ->update(['icon' => 'activity']);
        } catch (\Throwable $e) {
            // Non-fatal.
        }

        // ------------------------------------------------------------------
        // 5. ads_enabled: ads hidden site-wide until the admin turns them on.
        // ------------------------------------------------------------------
        try {
            DB::table('settings')->updateOrInsert(
                ['key' => 'ads_enabled'],
                ['value' => '0', 'type' => 'boolean', 'group' => 'general', 'created_at' => $now, 'updated_at' => $now]
            );
        } catch (\Throwable $e) {
            // settings table may not exist on very fresh installs
        }
    }

    public function down(): void
    {
        // Content and setting changes are not reverted.
    }
};
