<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug', $slug)->where('status', 'published')->firstOrFail();
        return view('frontend.pages.show', compact('page'));
    }

    public function about()
    {
        $page = Page::where('slug', 'about')->first();
        return view('frontend.about', compact('page'));
    }

    /**
     * Every policy page has a full built-in fallback. If the matching row is
     * missing from the database (a migration has not run yet, or the page was
     * deleted by mistake) the page still renders with complete content
     * instead of a 404. Admin edits always win when the row exists.
     */
    private function policyPage(string $slug, string $title, string $content)
    {
        $page = Page::where('slug', $slug)->first();

        if ($page === null) {
            $page = (object) [
                'title' => $title,
                'content' => $content,
                'updated_at' => now(),
            ];
        }

        return view('frontend.pages.show', compact('page'));
    }

    public function privacy()
    {
        return $this->policyPage('privacy-policy', 'Privacy Policy', $this->defaultContent('privacy-policy'));
    }

    public function terms()
    {
        return $this->policyPage('terms-conditions', 'Terms and Conditions', $this->defaultContent('terms-conditions'));
    }

    public function cookie()
    {
        return $this->policyPage('cookie-policy', 'Cookie Policy', $this->defaultContent('cookie-policy'));
    }

    public function editorial()
    {
        return $this->policyPage('editorial-policy', 'Editorial Policy', $this->defaultContent('editorial-policy'));
    }

    public function disclaimer()
    {
        return $this->policyPage('disclaimer', 'Disclaimer', $this->defaultContent('disclaimer'));
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
            default => '',
        };
    }
}
