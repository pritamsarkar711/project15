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
        $metaTitle = 'About Huvanti: Our Story, Mission and Editorial Team';
        $metaDescription = 'Learn about Huvanti, our editorial mission, content standards, and the team behind our independent and reader first publishing.';

        return view('frontend.about', compact('page', 'metaTitle', 'metaDescription'));
    }

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
            'Privacy Policy: How Huvanti Protects Your Data and Privacy',
            'Read how Huvanti collects, uses, and safeguards your personal data, including cookie choices, advertising standards, and user privacy rights.'
        );
    }

    public function terms()
    {
        return $this->policyPage(
            'terms-conditions',
            'Terms and Conditions',
            $this->defaultContent('terms-conditions'),
            'Terms and Conditions: Rules and Guidelines for Using Huvanti',
            'Review the terms and conditions governing the use of Huvanti, including acceptable use guidelines, content ownership, and user responsibilities.'
        );
    }

    public function cookie()
    {
        return $this->policyPage(
            'cookie-policy',
            'Cookie Policy',
            $this->defaultContent('cookie-policy'),
            'Cookie Policy: How Huvanti Uses Cookies and How to Manage Them',
            'Understand which cookies Huvanti uses, their specific purposes, and how you can easily manage or disable them in your browser settings.'
        );
    }

    public function editorial()
    {
        return $this->policyPage(
            'editorial-policy',
            'Editorial Policy',
            $this->defaultContent('editorial-policy'),
            'Editorial Policy: Fact Checking, Standards, and Human Authorship',
            'Discover how Huvanti researches, writes, verifies, and updates articles, ensuring high journalistic integrity and advertiser independence.'
        );
    }

    public function disclaimer()
    {
        return $this->policyPage(
            'disclaimer',
            'Disclaimer',
            $this->defaultContent('disclaimer'),
            'Disclaimer: Accuracy, Informational Purpose, and Content Limits',
            'Important information regarding the educational and informational nature of Huvanti articles, external links, and liability limitations.'
        );
    }

    public function affiliateDisclosure()
    {
        return $this->policyPage(
            'affiliate-disclosure',
            'Affiliate Disclosure',
            $this->defaultContent('affiliate-disclosure'),
            'Affiliate Disclosure: Transparency and Reader First Recommendations',
            'How affiliate links work on Huvanti, our pledge of editorial independence, and why reader trust always comes before commercial partnerships.'
        );
    }

    public function commentPolicy()
    {
        return $this->policyPage(
            'comment-policy',
            'Comment Policy',
            $this->defaultContent('comment-policy'),
            'Comment Policy: Community Rules and Discussion Guidelines',
            'Guidelines for participating in discussions on Huvanti, including moderation standards, respectful communication, and comment removal.'
        );
    }

    public static function defaultContent(string $slug): string
    {
        return match ($slug) {
            'about' => <<<'HTML'
<h2>About Huvanti</h2>
<p>Huvanti is an independent digital publication committed to delivering clear, insightful, and practical articles across technology, health and wellness, finance, travel, lifestyle, and education. We believe knowledge should be accessible, engaging, and genuinely useful in daily life.</p>
<h3>Our Mission</h3>
<p>Our mission is to help curious minds explore ideas that matter and inspire positive everyday choices. The modern internet is filled with overwhelming jargon and shallow clickbait. We built Huvanti to offer a thoughtful alternative where readers can find thoroughly researched guides written with clarity and care.</p>
<h3>What We Cover</h3>
<p>Technology: We explore modern tools, emerging digital innovations, and software trends, breaking down complex developments into practical insights for everyday creators and professionals.</p>
<p>Health and Wellness: We share research backed guides on balanced nutrition, restorative sleep, physical fitness, and mental well being to help you build sustainable daily habits.</p>
<p>Finance: We provide straightforward advice on personal budgeting, saving strategies, mindful spending, and wealth building fundamentals designed for beginners and experienced planners alike.</p>
<p>Travel: We highlight authentic destinations, cultural journeys, and mindful travel tips to help you experience the world with curiosity and respect.</p>
<p>Lifestyle: We share ideas on intentional living, calm routines, organization, and creative hobbies that bring balance to your personal space and schedule.</p>
<p>Education: We explore evidence based study techniques, cognitive tools, and lifelong learning methods that help you master new skills with confidence.</p>
<h3>Our Editorial Standards</h3>
<p>Every article published on Huvanti is conceptualized, written, and verified by real human writers and subject enthusiasts. We verify facts against primary documentation, scientific studies, and reputable official sources. We do not publish unverified or automated text. When new developments emerge, we review and refresh our content to ensure ongoing accuracy.</p>
<h3>Independence and Integrity</h3>
<p>Editorial integrity is the cornerstone of our publication. Our reviews, ratings, and recommendations are guided solely by independent research and the genuine interests of our readers. Commercial sponsorships or affiliate relationships never compromise our editorial perspective or honest evaluations.</p>
<h3>Get in Touch</h3>
<p>We value open conversation with our community. If you have questions, feedback, or suggestions for upcoming topics, we invite you to connect with us through our dedicated contact page.</p>
HTML,

            'privacy-policy' => <<<'HTML'
<h2>Privacy Policy</h2>
<p>Last updated: August 2026</p>
<p>Huvanti values your privacy and is committed to protecting your personal information. This Privacy Policy details how we collect, use, store, and safeguard your data when you visit our website, read our articles, leave comments, or contact us.</p>
<h3>Information We Collect</h3>
<p>Information you provide directly: When you contact us through our inquiry forms, leave a comment on an article, or create an author account, you may provide details such as your name, email address, and message content.</p>
<p>Information collected automatically: When you browse Huvanti, our servers and analytics tools automatically record standard technical log details. This may include your internet protocol address, browser type, device information, operating system, referring pages, pages viewed, and access timestamps.</p>
<h3>How We Use Your Information</h3>
<p>We process collected information to maintain and optimize website performance, provide requested customer support, moderate community discussions, analyze content trends, and protect our platform against spam or malicious activities. We do not sell, rent, or trade your personal information to third parties.</p>
<h3>Google AdSense and Advertising Partners</h3>
<p>We may display advertisements served by Google AdSense and other trusted advertising networks. Third party vendors, including Google, use cookies to serve ads based on your prior visits to our website or other websites across the internet.</p>
<p>Google uses the DoubleClick DART cookie, which enables it and its partners to serve personalized advertisements to visitors based on their visit to Huvanti and other sites on the web. You may opt out of personalized advertising by visiting Google Ads Settings. Alternatively, you can opt out of third party vendor cookies for personalized advertising by visiting aboutads.info.</p>
<h3>Cookies and Web Beacons</h3>
<p>Cookies are small text files stored on your device by your web browser. We use necessary cookies to ensure core website functionality, preference cookies to remember your interface selections, and analytics cookies to understand how visitors engage with our content. You can modify your browser settings to decline or delete cookies at any time.</p>
<h3>Data Security and Retention</h3>
<p>We implement appropriate technical and organizational security measures to protect your personal data against unauthorized access, loss, or misuse. We retain your information only as long as necessary to fulfill the purposes outlined in this policy or to comply with legal obligations.</p>
<h3>Your Privacy Rights</h3>
<p>Depending on your geographic location, including under regulations such as the General Data Protection Regulation and California Consumer Privacy Act, you may have the right to access, correct, export, or request the deletion of your personal data. You may also object to certain data processing activities. To exercise these rights, please reach out to us through our contact page.</p>
<h3>Children Privacy Protection</h3>
<p>Huvanti is intended for a general audience and does not knowingly collect personal identifiable information from children under the age of thirteen. If you believe a child has provided us with personal information, please notify us immediately so we can promptly delete the data.</p>
<h3>Changes to This Policy</h3>
<p>We may update this Privacy Policy periodically to reflect changes in our practices or legal requirements. Any revisions will be published on this page with an updated revision date. Continued use of the website constitutes acceptance of the updated policy.</p>
<h3>Contact Us</h3>
<p>If you have any questions or concerns regarding this Privacy Policy or our data handling practices, please contact us through our official contact page.</p>
HTML,

            'terms-conditions' => <<<'HTML'
<h2>Terms and Conditions</h2>
<p>Last updated: August 2026</p>
<p>Welcome to Huvanti. By accessing and using our website, you agree to comply with and be bound by the following Terms and Conditions. Please review them carefully. If you do not agree with any part of these terms, please discontinue use of the website.</p>
<h3>Intellectual Property Rights</h3>
<p>All content published on Huvanti, including articles, text, original graphics, illustrations, design layouts, and software code, is the intellectual property of Huvanti and its content creators, protected by applicable international copyright and trademark laws. You may view and print individual pages for personal, non commercial use only. You may not reproduce, redistribute, republish, or sell any content without prior written permission from Huvanti.</p>
<h3>Acceptable Use Guidelines</h3>
<p>When using Huvanti, you agree to engage in lawful and constructive behavior. You agree not to engage in any activity that compromises website security, interferes with server operations, uses automated scraping tools without authorization, or transmits harmful software code.</p>
<h3>User Generated Content and Comments</h3>
<p>Readers and registered authors who post comments or submit content are solely responsible for the material they provide. You agree not to submit material that is defamatory, abusive, unlawful, harassing, invasive of privacy, or infringing upon intellectual property rights. Huvanti reserves the right to review, moderate, edit, or remove any user generated content at its sole discretion.</p>
<h3>Informational and Educational Purpose</h3>
<p>All articles, guides, reviews, and opinions provided on Huvanti are published solely for general informational and educational purposes. While we strive for accuracy, Huvanti makes no representations or warranties regarding the completeness or suitability of any information. The content does not constitute professional medical, legal, or financial advice. Always consult a qualified professional for decisions involving health, legal, or monetary matters.</p>
<h3>Third Party Links</h3>
<p>Our website may contain links to external third party websites for additional reference and reader convenience. We have no control over the content, policies, or practices of third party sites and assume no liability for their services or materials. Visiting external links is at your own discretion.</p>
<h3>Limitation of Liability</h3>
<p>To the fullest extent permitted by law, Huvanti, its editors, authors, and affiliates shall not be liable for any direct, indirect, incidental, or consequential damages resulting from the use of, or inability to use, this website or its content.</p>
<h3>Changes to Terms</h3>
<p>We reserve the right to amend these Terms and Conditions at any time. Changes become effective immediately upon posting to this page. Your continued use of the website following any modifications signifies your acceptance of the updated terms.</p>
<h3>Contact Information</h3>
<p>For questions or legal inquiries regarding these Terms and Conditions, please submit your message via our contact page.</p>
HTML,

            'editorial-policy' => <<<'HTML'
<h2>Editorial Policy</h2>
<p>Last updated: August 2026</p>
<p>Huvanti is dedicated to publishing accurate, helpful, and high quality articles that empower readers to make informed decisions. This Editorial Policy outlines the standards, principles, and verification processes guiding our editorial team.</p>
<h3>Core Editorial Values</h3>
<p>Our work is founded on three primary values: accuracy, clarity, and reader focus. Every article must answer genuine reader questions in a straightforward, accessible manner without unnecessary complexity or filler.</p>
<h3>Research and Fact Checking</h3>
<p>Our writers conduct rigorous research before drafting any piece. We consult reputable primary sources, peer reviewed scientific journals, industry documentation, and verified experts. Claims, statistics, and figures are cross referenced to ensure reliability. When we reference studies or external data, we provide transparent citations so readers can inspect the sources directly.</p>
<h3>Human Authorship and Ethical AI Use</h3>
<p>Every article published on Huvanti is conceptualized, researched, structured, and crafted by human authors and editors. We believe authentic human perspectives and real world context are vital for quality storytelling. AI tools may be utilized solely for spelling checks, grammar verification, or brainstorming assistance, similar to standard editorial software. We never publish unverified, automated, or machine generated articles.</p>
<h3>Independence from Advertisers</h3>
<p>Our editorial decisions are entirely independent from commercial partnerships, advertisers, and sponsors. Advertisers and affiliate partners have no influence over the topics we select, our ratings, or our editorial conclusions. If a product or service falls short of expectations, our writers state this honestly, regardless of potential commercial relationships.</p>
<h3>Transparent Corrections and Updates</h3>
<p>We believe in full accountability. When an error of fact is discovered, our editors review the issue and update the article promptly. Significant factual updates are noted transparently within the article. If you spot an inaccuracy, we encourage you to notify us through our contact page.</p>
<h3>Author Guidelines and Community Contributions</h3>
<p>Contributing authors must adhere to strict guidelines requiring originality, honesty, clear sourcing, and respectful language. All submitted manuscripts undergo rigorous editorial review and approval before publication.</p>
<h3>Contact the Editorial Team</h3>
<p>We welcome your questions, thoughts, and feedback on our published content. Please reach out to our editorial desk through the contact page.</p>
HTML,

            'cookie-policy' => <<<'HTML'
<h2>Cookie Policy</h2>
<p>Last updated: August 2026</p>
<p>This Cookie Policy explains what cookies are, how Huvanti uses cookies and similar technologies, and the options available to you for managing your preferences.</p>
<h3>What Are Cookies</h3>
<p>Cookies are small data files placed on your computer or mobile device when you visit a website. They are widely used by web developers to make websites function efficiently, retain user preferences, and provide analytical insights into site performance.</p>
<h3>Types of Cookies We Use</h3>
<p>Essential Cookies: These cookies are strictly necessary for the fundamental operation of our website, enabling secure page navigation and access to protected account areas.</p>
<p>Preference Cookies: These cookies allow our website to remember choices you have made, such as your preferred display theme, to provide a smoother browsing experience.</p>
<p>Analytics Cookies: We use analytics cookies to measure how visitors interact with our content, identifying popular articles and navigation patterns so we can continuously improve user experience.</p>
<p>Advertising Cookies: Third party advertising partners, including Google AdSense, may set cookies to serve relevant advertisements based on your browsing interests across various websites.</p>
<h3>Managing and Disabling Cookies</h3>
<p>You have the full right to decide whether to accept or decline cookies. Most web browsers automatically accept cookies by default, but you can usually adjust your browser settings to refuse cookies or notify you when a cookie is being placed. Please note that disabling essential cookies may impact certain interactive features on our website.</p>
<h3>Changes to This Policy</h3>
<p>We may update this Cookie Policy periodically to reflect technological or legal updates. We encourage you to review this page periodically to stay informed about our use of cookies.</p>
<h3>Contact Us</h3>
<p>If you have any questions regarding our Cookie Policy, please get in touch via our contact page.</p>
HTML,

            'disclaimer' => <<<'HTML'
<h2>Disclaimer</h2>
<p>Last updated: August 2026</p>
<p>The information provided on Huvanti is for general educational and informational purposes only. All content is published in good faith, and while we strive for accuracy, we make no representations or warranties of any kind, express or implied, regarding the reliability, completeness, or suitability of any information on the site.</p>
<h3>Not Professional Advice</h3>
<p>The content published on Huvanti does not constitute professional medical, financial, investment, or legal advice. Information on this site should never be used as a substitute for direct consultation with certified professionals. Always consult a qualified specialist before making health, financial, or legal decisions.</p>
<h3>External Links Disclaimer</h3>
<p>Huvanti may contain links to external third party websites. These links are provided solely for reader reference and educational context. We do not endorse, control, or take responsibility for the accuracy or practices of external websites.</p>
<h3>Advertising and Endorsements</h3>
<p>Advertisements displayed on Huvanti are served by third party networks such as Google AdSense. The display of advertisements does not constitute an endorsement or recommendation of the advertised products or services by Huvanti.</p>
<h3>Contact</h3>
<p>If you have any questions about this disclaimer, please reach out to us through our contact page.</p>
HTML,

            'affiliate-disclosure' => <<<'HTML'
<h2>Affiliate Disclosure</h2>
<p>Last updated: August 2026</p>
<p>Huvanti believes in full transparency and honesty with our audience. In accordance with digital publisher guidelines, we provide this Affiliate Disclosure to explain how affiliate links are utilized on our platform.</p>
<h3>How Affiliate Relationships Work</h3>
<p>Some articles on Huvanti may contain affiliate links. If you click on an affiliate link and make a purchase from the retailer, we may earn a small referral commission at no additional cost to you. The price you pay remains exactly the same whether you use our link or visit the retailer directly.</p>
<h3>Editorial Independence</h3>
<p>Our editorial recommendations are never influenced by affiliate partnerships or potential commissions. Our writers and editors evaluate products and services based strictly on research, quality, and reader value. We only recommend items we genuinely believe provide real benefit to our readers.</p>
<h3>Supporting Free Content</h3>
<p>Referral commissions help support the operation of Huvanti, allowing us to maintain high editorial standards and continue providing free, comprehensive content to our readers. We sincerely appreciate your support.</p>
<h3>Questions</h3>
<p>If you have any questions regarding our affiliate relationships or specific links, please feel free to reach out through our contact page.</p>
HTML,

            'comment-policy' => <<<'HTML'
<h2>Comment Policy</h2>
<p>Last updated: August 2026</p>
<p>We welcome thoughtful, constructive discussions on Huvanti. This Comment Policy establishes community guidelines to ensure discussions remain respectful, informative, and safe for all readers.</p>
<h3>Constructive Participation</h3>
<p>We encourage insightful questions, constructive feedback, shared experiences, and respectful debate related to the article topic. Please express your opinions politely and respectfully.</p>
<h3>Prohibited Conduct</h3>
<p>To preserve a welcoming environment, we strictly prohibit the following: personal attacks, harassment, profanity, hate speech, promotional spam, commercial solicitations, dissemination of false information, and submission of unlawful or harmful links.</p>
<h3>Editorial Moderation</h3>
<p>Comments are reviewed by our moderation team before or shortly after publication. We reserve the right to edit, decline, or remove any comment that violates these guidelines. User comments reflect the views of individual authors and do not represent the opinions of Huvanti.</p>
<h3>Contact and Removal</h3>
<p>If you wish to request the removal of a comment you previously posted, please submit your request along with the article URL via our contact page.</p>
HTML,

            default => '',
        };
    }
}
