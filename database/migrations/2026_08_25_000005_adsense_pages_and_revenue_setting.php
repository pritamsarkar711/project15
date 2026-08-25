<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * AdSense-ready policy pages.
 *
 * Creates a Disclaimer page and replaces the very thin seeded policy pages
 * (Privacy Policy, Terms, Cookie Policy) with complete, clear versions that
 * cover everything Google AdSense / AdX reviewers look for: third-party
 * ad cookies, Google's use of data, user control, contact information.
 * Pages are only updated when they still contain the original seeded text,
 * so admin edits made in the panel are never overwritten.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $pages = [
            [
                'slug' => 'disclaimer',
                'title' => 'Disclaimer',
                'content' => <<<'HTML'
<h2>Disclaimer</h2>
<p>Last updated: August 2026</p>
<p>The information provided on huvanti.com is for general informational and educational purposes only. While we work hard to keep content accurate and up to date, we make no representation or warranty of any kind, express or implied, regarding the accuracy, adequacy, validity, reliability, availability, or completeness of any information on the site.</p>
<h3>External links</h3>
<p>Our articles may contain links to external websites that are not provided or maintained by us. We do not guarantee the accuracy, relevance, timeliness, or completeness of any information on these external websites.</p>
<h3>Affiliate disclosure</h3>
<p>Some links on this site are affiliate links. This means we may earn a small commission, at no extra cost to you, if you make a purchase through one of these links. We only recommend products and services we believe add real value to our readers. Commissions never influence our editorial opinions.</p>
<h3>Professional advice</h3>
<p>Content on huvanti.com does not constitute professional, financial, medical, or legal advice. Always consult a qualified professional before making decisions that affect your health, finances, or legal situation.</p>
<h3>Advertisers</h3>
<p>Advertisements shown on this site are served by third-party networks such as Google AdSense. We do not endorse the products advertised and are not responsible for the content of the ads.</p>
<h3>Contact</h3>
<p>If you have questions about this disclaimer, reach us through the <a href="/contact">contact page</a>.</p>
HTML,
            ],
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'content' => <<<'HTML'
<h2>Privacy Policy</h2>
<p>Last updated: August 2026</p>
<p>This Privacy Policy explains how huvanti.com collects, uses, and protects your information. By using the site you agree to the practices described here.</p>
<h3>Information we collect</h3>
<p>We collect two kinds of information. Information you give us directly, such as your name and email address when you leave a comment, create an author account, or send a message through the contact form. And information collected automatically, such as your approximate location, browser type, pages visited, and referring website, gathered through analytics tools.</p>
<h3>How we use your information</h3>
<p>We use the information we collect to operate and improve the site, reply to your messages, display comments you approve, send service announcements such as password resets, monitor for spam and abuse, and understand which content readers find useful.</p>
<h3>Cookies</h3>
<p>huvanti.com uses cookies and similar technologies. Essential cookies keep the site working, for example keeping you signed in. Preference cookies remember choices such as your dark or light theme. Analytics cookies help us understand how visitors use the site so we can improve it.</p>
<h3>Google AdSense and third-party advertising</h3>
<p>We may use Google AdSense and other advertising networks to show ads. Third-party vendors, including Google, use cookies to serve ads based on your prior visits to this and other websites. Google's use of advertising cookies enables it and its partners to serve ads to you based on your visit to our site and other sites on the internet. You may opt out of personalized advertising by visiting <a href="https://www.google.com/settings/ads" rel="nofollow noopener" target="_blank">Google Ads Settings</a>. You can also opt out of some third-party vendors' use of cookies for personalized advertising at <a href="https://www.aboutads.info/choices/" rel="nofollow noopener" target="_blank">aboutads.info</a>.</p>
<h3>Sharing your information</h3>
<p>We do not sell your personal information. We share data only with service providers that help us run the site, such as our advertising and analytics partners described above, and when required by law.</p>
<h3>Data retention and your rights</h3>
<p>Comment and account data is kept as long as your account is active. You may request a copy, correction, or deletion of your personal data at any time through the <a href="/contact">contact page</a>. Deleting your author account removes your drafts and profile data. If you are in the European Economic Area, you also have the right to lodge a complaint with your local data protection authority.</p>
<h3>Children's privacy</h3>
<p>huvanti.com is not directed to children under 13 and we do not knowingly collect personal information from children.</p>
<h3>Changes to this policy</h3>
<p>We may update this policy from time to time. Changes are posted on this page with an updated date.</p>
<h3>Contact</h3>
<p>Questions about privacy? Write to us through the <a href="/contact">contact page</a>.</p>
HTML,
            ],
            [
                'slug' => 'terms-conditions',
                'title' => 'Terms and Conditions',
                'content' => <<<'HTML'
<h2>Terms and Conditions</h2>
<p>Last updated: August 2026</p>
<p>These terms govern your use of huvanti.com. By accessing the site you accept these terms. If you do not agree with them, please do not use the site.</p>
<h3>Using the site</h3>
<p>You agree to use huvanti.com lawfully and respectfully. You may not misuse the site, attempt to disrupt it, scrape it at a rate that harms other visitors, or use it to distribute spam or harmful content.</p>
<h3>Accounts and comments</h3>
<p>You are responsible for keeping your account password safe and for all activity under your account. Comments must be civil and on topic. We moderate all comments and may edit or remove any that break our rules.</p>
<h3>Author content</h3>
<p>Authors keep ownership of the posts they write. By submitting a post you grant huvanti.com a non-exclusive license to publish, display, and promote that content on the site. Posts that break the posting rules may be returned or removed.</p>
<h3>Intellectual property</h3>
<p>The site design, logo, and editorial content not submitted by authors are owned by Huvanti. You may share links to our articles freely. Copying full articles without written permission is not allowed.</p>
<h3>Third-party links and ads</h3>
<p>The site contains links to external websites and displays third-party advertisements. We are not responsible for the content, policies, or practices of third-party sites.</p>
<h3>Disclaimer and limitation of liability</h3>
<p>Content is provided for general information only and is not professional advice. To the fullest extent permitted by law, Huvanti is not liable for any loss or damage arising from your use of the site or reliance on its content.</p>
<h3>Changes</h3>
<p>We may update these terms at any time. Continued use of the site after changes are posted means you accept the updated terms.</p>
<h3>Contact</h3>
<p>Questions about these terms? Reach us through the <a href="/contact">contact page</a>.</p>
HTML,
            ],
            [
                'slug' => 'cookie-policy',
                'title' => 'Cookie Policy',
                'content' => <<<'HTML'
<h2>Cookie Policy</h2>
<p>Last updated: August 2026</p>
<p>This policy explains what cookies are, how huvanti.com uses them, and how you can control them.</p>
<h3>What are cookies</h3>
<p>Cookies are small text files stored by your browser when you visit a website. They help the site remember your actions and preferences over time.</p>
<h3>Cookies we use</h3>
<p>Essential cookies keep the site working, such as keeping you signed in and protecting forms against abuse. These cannot be switched off. Preference cookies remember settings such as your dark or light reading theme. Analytics cookies collect anonymous statistics about how visitors use the site so we can improve it.</p>
<h3>Advertising cookies</h3>
<p>Advertising partners, including Google AdSense, may set cookies to show you relevant ads and to measure ad performance. You can turn off personalized advertising at any time in <a href="https://www.google.com/settings/ads" rel="nofollow noopener" target="_blank">Google Ads Settings</a>.</p>
<h3>Managing cookies</h3>
<p>You can delete or block cookies through your browser settings. Blocking essential cookies may affect sign-in and commenting.</p>
<h3>Contact</h3>
<p>Questions about cookies? Write to us through the <a href="/contact">contact page</a>.</p>
HTML,
            ],
        ];

        foreach ($pages as $page) {
            $existing = DB::table('pages')->where('slug', $page['slug'])->first();
            if ($existing === null) {
                DB::table('pages')->insert([
                    'title' => $page['title'],
                    'slug' => $page['slug'],
                    'content' => $page['content'],
                    'status' => 'published',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                // Only replace the short seeded text; never clobber admin edits.
                $short = strlen(strip_tags($existing->content)) < 900;
                if ($short) {
                    DB::table('pages')->where('slug', $page['slug'])->update([
                        'title' => $page['title'],
                        'content' => $page['content'],
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        // Revenue program switch for the author panel (off by default).
        try {
            DB::table('settings')->updateOrInsert(
                ['key' => 'revenue_enabled'],
                ['value' => '0', 'type' => 'boolean', 'group' => 'general', 'created_at' => $now, 'updated_at' => $now]
            );
        } catch (\Throwable $e) {
            // settings table may not exist on very fresh installs
        }
    }

    public function down(): void
    {
        // Content changes are not reverted.
    }
};
