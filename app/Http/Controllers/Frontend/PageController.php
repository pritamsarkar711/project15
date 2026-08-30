<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\StaticPages;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show($slug)
    {
        // Built-in pages are canonically served at their named route
        // (/privacy-policy — the URL the footer links). The generic
        // /page/{slug} variant used to render a 200 duplicate with its own
        // self-canonical tag, which created duplicate index entries and 7
        // orphan URLs (Ahrefs: "Orphan page", "3XX redirect"). Redirect
        // duplicates permanently so search engines consolidate signals.
        $canonical = StaticPages::ROUTE_MAP[$slug] ?? null;
        if ($canonical !== null && \Route::has($canonical)) {
            return redirect()->route($canonical, [], 301);
        }

        $page = Page::where('slug', $slug)->where('status', 'published')->firstOrFail();
        return view('frontend.pages.show', compact('page'));
    }

    public function about()
    {
        $page = Page::where('slug', 'about')->first();
        // "About Huvanti.com" (17 chars) was flagged "Title too short".
        $metaTitle = 'About Huvanti — Our Story, Mission & Editorial Team';
        $metaDescription = 'Learn about Huvanti.com, our editorial mission, our standards and the team behind the articles — independent, reader-first publishing.';
        return view('frontend.about', compact('page', 'metaTitle', 'metaDescription'));
    }

    /**
     * Every policy page has a full built-in fallback. If the matching row is
     * missing from the database (a migration has not run yet, or the page was
     * deleted by mistake) the page still renders with complete content
     * instead of a 404. Admin edits always win when the row exists.
     *
     * $seoTitle / $seoDescription fix the Ahrefs "Title too short" flags —
     * bare labels like "Disclaimer" (10 chars) carried straight into <title>.
     * The layout also applies a generic top-up for anything still short.
     */
    private function policyPage(string $slug, string $title, string $content, ?string $seoTitle = null, ?string $seoDescription = null)
    {
        $page = Page::where('slug', $slug)->first();

        if ($page === null) {
            $page = (object) [
                'title' => $title,
                'content' => $content,
                'updated_at' => now(),
            ];
        }

        return view('frontend.pages.show', compact('page', 'seoTitle', 'seoDescription'));
    }

    public function privacy()
    {
        return $this->policyPage(
            'privacy-policy',
            'Privacy Policy',
            $this->defaultContent('privacy-policy'),
            'Privacy Policy — How Huvanti Protects Your Data',
            'Read how Huvanti collects, uses and protects your personal data: cookies, analytics, advertising partners and your privacy rights, in plain language.'
        );
    }

    public function terms()
    {
        return $this->policyPage(
            'terms-conditions',
            'Terms and Conditions',
            $this->defaultContent('terms-conditions'),
            'Terms & Conditions — The Rules for Using Huvanti',
            'The terms and conditions that govern your use of Huvanti.com: acceptable use, content ownership, liability limits and account responsibilities.'
        );
    }

    public function cookie()
    {
        return $this->policyPage(
            'cookie-policy',
            'Cookie Policy',
            $this->defaultContent('cookie-policy'),
            'Cookie Policy — What Huvanti Collects & Why',
            'Which cookies Huvanti.com sets, what each one does, how long it lasts and how to control or disable cookies in your browser at any time.'
        );
    }

    public function editorial()
    {
        return $this->policyPage(
            'editorial-policy',
            'Editorial Policy',
            $this->defaultContent('editorial-policy'),
            'Editorial Policy — Fact-Checking & Content Standards',
            'How Huvanti researches, writes, reviews and corrects every article: sourcing rules, independence from advertisers, and our human-editor promise.'
        );
    }

    public function disclaimer()
    {
        return $this->policyPage(
            'disclaimer',
            'Disclaimer',
            $this->defaultContent('disclaimer'),
            'Disclaimer — Accuracy & Liability of Huvanti Content',
            'What Huvanti content is — and is not: informational use only, external links, affiliate relationships and why our articles are not professional advice.'
        );
    }

    public function affiliateDisclosure()
    {
        return $this->policyPage(
            'affiliate-disclosure',
            'Affiliate Disclosure',
            $this->defaultContent('affiliate-disclosure'),
            'Affiliate Disclosure — How Huvanti Earns Commissions',
            'How affiliate links work on Huvanti: what we earn, what you pay (nothing extra), and why commissions never influence our recommendations.'
        );
    }

    public function commentPolicy()
    {
        return $this->policyPage(
            'comment-policy',
            'Comment Policy',
            $this->defaultContent('comment-policy'),
            'Comment Policy — Community Guidelines on Huvanti',
            'The rules for commenting on Huvanti articles: what is welcome, what gets removed, how moderation works and how to report a problem comment.'
        );
    }

    /**
     * Default content for each policy page, kept in one place and shared with
     * the migration that seeds them, so a fresh install and an upgraded one
     * always show the same complete policy pages.
     */
    public static function defaultContent(string $slug): string
    {
        return match ($slug) {
            'editorial-policy' => <<<'HTML'
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
HTML,
            'disclaimer' => <<<'HTML'
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
            'affiliate-disclosure' => <<<'HTML'
<h2>Affiliate Disclosure</h2>
<p>Last updated: August 2026</p>
<p>Huvanti is a reader supported publication. Some pages on this site contain affiliate links. If you click one of these links and make a purchase, we may earn a commission. You never pay more because of this. The price stays the same whether you use our link or go to the seller directly.</p>
<h3>How affiliate links work here</h3>
<p>Every article that contains affiliate links carries a clear disclosure notice before the content begins. The notice tells you the page includes affiliate links and that we may earn a commission. This notice appears on the article itself, not hidden in a footer or a separate policy page only.</p>
<h3>Our editorial promise</h3>
<p>Commissions never decide what we recommend. Writers and editors choose products based on research, real experience and value for the reader. If a product is not good, we say so even when a commission is available. Sponsored placements, if ever used, are labeled as sponsored.</p>
<h3>Who we work with</h3>
<p>We only join affiliate programs of reputable companies that readers already know and trust, such as major retailers and well established brands. We do not link to unknown or low quality sites for the sake of a commission.</p>
<h3>Prices and availability</h3>
<p>Prices and availability mentioned in articles can change without notice on the seller side. Always check the final price on the seller page before buying.</p>
<h3>Your support</h3>
<p>Using our links costs you nothing extra. It simply helps us keep publishing free, well researched content. Thank you for supporting Huvanti.</p>
<h3>Questions</h3>
<p>If you have questions about this disclosure or any link on the site, reach us through the <a href="/contact">contact page</a>.</p>
HTML,
            'comment-policy' => <<<'HTML'
<h2>Comment Policy</h2>
<p>Last updated: August 2026</p>
<p>Comments on Huvanti are welcome and moderated. This policy explains the rules so every reader knows what to expect.</p>
<h3>What we allow</h3>
<p>Questions about the article. Honest opinions shared politely. Additional tips that help other readers. Corrections if you believe something is wrong.</p>
<h3>What we do not allow</h3>
<p>Insults, harassment or personal attacks. Spam, self promotion or links to unrelated sites. Hate speech of any kind. False claims presented as fact. Anything illegal, unsafe or harmful, including links to adult, gambling, pirated or malicious content.</p>
<h3>Moderation</h3>
<p>Every comment is reviewed before it appears, or shortly after, depending on the article. We may edit or remove comments that break this policy. Repeated violations can lead to blocked posting. Comments express the views of the person who wrote them, not the views of Huvanti.</p>
<h3>Your privacy</h3>
<p>We ask for your name and email to comment. Your email is never published or shared. It is stored securely and used only for moderation. See our Privacy Policy for full details.</p>
<h3>Remove a comment</h3>
<p>You can ask us to remove your own comment at any time. Use the contact page and include the article link and the comment text.</p>
HTML,
            default => '',
        };
    }
}
