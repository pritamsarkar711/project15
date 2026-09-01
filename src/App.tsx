import React, { useState } from 'react';
import {
  BookOpen,
  ShieldCheck,
  FileText,
  Users,
  Send,
  CheckCircle2,
  ExternalLink,
  Globe,
  Compass,
  Cpu,
  HeartPulse,
  Coins,
  MapPin,
  Sparkles,
  Search,
  Scale,
  MessageSquareQuote,
  Eye,
  Info
} from 'lucide-react';

interface PolicyItem {
  id: string;
  title: string;
  category: string;
  updatedAt: string;
  summary: string;
  content: string[];
}

const POLICIES: PolicyItem[] = [
  {
    id: 'privacy-policy',
    title: 'Privacy Policy',
    category: 'Legal and Compliance',
    updatedAt: 'August 2026',
    summary: 'How Huvanti collects, uses, and safeguards your personal data, including cookie choices, advertising standards, and user privacy rights.',
    content: [
      'Huvanti values your privacy and is committed to protecting your personal information. This Privacy Policy details how we collect, use, store, and safeguard your data when you visit our website, read our articles, leave comments, or contact us.',
      'Information you provide directly: When you contact us through inquiry forms, leave comments, or register author credentials, you provide contact details like name and email address.',
      'Information collected automatically: Standard technical log details including IP address, browser type, device information, operating system, and access timestamps are gathered to keep the site secure.',
      'Google AdSense and Advertising Partners: We may display advertisements served by Google AdSense. Third-party vendors use cookies to serve ads based on prior visits.',
      'Your Privacy Rights: Under GDPR and CCPA regulations, you have full rights to access, correct, export, or request deletion of your personal data.'
    ]
  },
  {
    id: 'terms-conditions',
    title: 'Terms and Conditions',
    category: 'Legal and Compliance',
    updatedAt: 'August 2026',
    summary: 'Rules and guidelines governing your use of Huvanti, including acceptable use, intellectual property, and content ownership.',
    content: [
      'Welcome to Huvanti. By accessing and using our website, you agree to comply with and be bound by these Terms and Conditions.',
      'Intellectual Property Rights: All content published on Huvanti, including articles, text, original graphics, illustrations, and layouts, is protected intellectual property.',
      'Acceptable Use: Users agree to engage in lawful, constructive behavior without compromising website security or scraping unauthorized content.',
      'Limitation of Liability: Huvanti, its editors, and authors publish material solely for informational and educational purposes.'
    ]
  },
  {
    id: 'editorial-policy',
    title: 'Editorial Policy',
    category: 'Standards',
    updatedAt: 'August 2026',
    summary: 'Fact-checking, human authorship verification, sourcing integrity, and complete independence from advertisers.',
    content: [
      'Huvanti is dedicated to publishing accurate, helpful, and high-quality articles that empower readers to make informed decisions.',
      'Human Authorship and Ethical AI Use: Every article published on Huvanti is conceptualized, researched, and crafted by human authors and editors. AI tools are limited strictly to spelling and grammar verification.',
      'Independence from Advertisers: Editorial decisions are entirely independent from commercial partnerships, advertisers, and sponsors.',
      'Transparent Corrections: When an error of fact is identified, our editorial desk reviews the issue and updates the article promptly.'
    ]
  },
  {
    id: 'cookie-policy',
    title: 'Cookie Policy',
    category: 'Privacy and Cookies',
    updatedAt: 'August 2026',
    summary: 'Details on essential, preference, analytics, and advertising cookies used on Huvanti, and instructions on managing browser settings.',
    content: [
      'This Cookie Policy explains what cookies are, how Huvanti uses cookies, and the options available to manage your preferences.',
      'Essential Cookies: Necessary for fundamental operations, enabling secure page navigation.',
      'Preference & Analytics: Used to remember interface themes and measure how visitors engage with content.',
      'Managing Cookies: You have the right to decide whether to accept or decline cookies through your browser controls.'
    ]
  },
  {
    id: 'affiliate-disclosure',
    title: 'Affiliate Disclosure',
    category: 'Transparency',
    updatedAt: 'August 2026',
    summary: 'Transparent explanation of referral commissions, reader-first recommendations, and pricing integrity.',
    content: [
      'Huvanti believes in full transparency with our audience. In accordance with digital publisher guidelines, we provide this Affiliate Disclosure.',
      'Reader-First Recommendations: If you purchase through an affiliate link, we may earn a referral commission at no additional cost to you.',
      'Commissions never decide editorial ratings or product recommendations.'
    ]
  },
  {
    id: 'disclaimer',
    title: 'Disclaimer',
    category: 'Legal and Compliance',
    updatedAt: 'August 2026',
    summary: 'Educational and informational nature of Huvanti articles, external links, and liability limitations.',
    content: [
      'The information provided on Huvanti is for general educational and informational purposes only.',
      'Not Professional Advice: Content published on Huvanti does not constitute certified medical, financial, investment, or legal advice.'
    ]
  },
  {
    id: 'comment-policy',
    title: 'Comment Policy',
    category: 'Community',
    updatedAt: 'August 2026',
    summary: 'Community guidelines for constructive discussions, moderation rules, and respectful communication.',
    content: [
      'We welcome thoughtful, constructive discussions on Huvanti.',
      'Prohibited Conduct: Personal attacks, harassment, hate speech, promotional spam, and false claims are strictly prohibited.'
    ]
  }
];

const CATEGORIES = [
  { name: 'Technology', icon: Cpu, count: '85+ Guides', desc: 'AI tools, software developments, digital workflows' },
  { name: 'Health & Wellness', icon: HeartPulse, count: '64+ Guides', desc: 'Evidence-backed nutrition, sleep, mental focus' },
  { name: 'Finance', icon: Coins, count: '72+ Guides', desc: 'Personal budgeting, mindful saving, wealth fundamentals' },
  { name: 'Travel', icon: MapPin, count: '48+ Guides', desc: 'Authentic destinations, cultural journeys, smart planning' },
  { name: 'Lifestyle', icon: Compass, count: '56+ Guides', desc: 'Intentional living, daily routines, workspace design' },
  { name: 'Education', icon: Sparkles, count: '40+ Guides', desc: 'Cognitive learning methods, study systems, skill acquisition' }
];

const INDEXING_STATUS = [
  { item: 'Sitemap XML Index', url: '/sitemap.xml', status: 'Healthy', note: 'Posts, Categories, Pages, Authors partitioned' },
  { item: 'Bingbot & Googlebot Crawl Rules', url: '/robots.txt', status: 'Optimized', note: 'Clean canonical paths, no crawl blocking' },
  { item: 'IndexNow Instant API', url: 'api.indexnow.org', status: 'Active', note: 'Automatic broadcast on article publish & update' },
  { item: 'Editorial & Legal Safeguards', url: '/editorial-policy', status: 'Compliant', note: '100% human authorship policy, zero dashes/asterisks' }
];

export default function App() {
  const [selectedPolicy, setSelectedPolicy] = useState<PolicyItem>(POLICIES[0]);
  const [activeTab, setActiveTab] = useState<'overview' | 'policies' | 'indexing' | 'categories'>('overview');
  const [searchQuery, setSearchQuery] = useState('');

  const filteredPolicies = POLICIES.filter(
    (p) =>
      p.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
      p.summary.toLowerCase().includes(searchQuery.toLowerCase())
  );

  return (
    <div className="min-h-screen bg-slate-950 text-slate-100 font-sans selection:bg-emerald-500/30 selection:text-emerald-200">
      {/* Top Header */}
      <header className="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-md sticky top-0 z-30">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-emerald-600/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400 font-black text-xl shadow-inner shadow-emerald-500/20">
              H
            </div>
            <div>
              <div className="flex items-center gap-2">
                <span className="font-bold text-lg tracking-tight text-white">Huvanti</span>
                <span className="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                  Digital Publication
                </span>
              </div>
              <p className="text-xs text-slate-400">Explore Ideas and Inspire Life</p>
            </div>
          </div>

          <nav className="flex items-center gap-1.5 sm:gap-2">
            {[
              { id: 'overview', label: 'Overview', icon: Globe },
              { id: 'policies', label: 'Policy Hub', icon: ShieldCheck },
              { id: 'categories', label: 'Topics', icon: BookOpen },
              { id: 'indexing', label: 'SEO & Indexing', icon: Search }
            ].map((tab) => {
              const Icon = tab.icon;
              const isActive = activeTab === tab.id;
              return (
                <button
                  key={tab.id}
                  onClick={() => setActiveTab(tab.id as any)}
                  className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs sm:text-sm font-medium transition-colors ${
                    isActive
                      ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'
                      : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60'
                  }`}
                >
                  <Icon className="w-4 h-4" />
                  <span>{tab.label}</span>
                </button>
              );
            })}
          </nav>
        </div>
      </header>

      {/* Main Content Area */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {activeTab === 'overview' && (
          <div className="space-y-8">
            {/* Hero Card */}
            <div className="rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 p-6 sm:p-8 relative overflow-hidden shadow-xl">
              <div className="max-w-3xl relative z-10 space-y-4">
                <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-950/60 border border-emerald-700/40 text-emerald-400 text-xs font-semibold">
                  <Sparkles className="w-3.5 h-3.5" />
                  Independent & Human-Authored Publishing
                </div>
                <h1 className="text-2xl sm:text-4xl font-extrabold text-white tracking-tight">
                  High-Integrity Digital Knowledge Platform
                </h1>
                <p className="text-slate-300 text-sm sm:text-base leading-relaxed">
                  Huvanti delivers thoroughly researched, clear, and practical articles across technology, wellness,
                  finance, travel, lifestyle, and education. Every publication is verified for accuracy, transparency, and
                  reader trust.
                </p>
                <div className="pt-2 flex flex-wrap items-center gap-3">
                  <button
                    onClick={() => setActiveTab('policies')}
                    className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm transition shadow-lg shadow-emerald-900/30"
                  >
                    <ShieldCheck className="w-4 h-4" />
                    Review Published Policies
                  </button>
                  <button
                    onClick={() => setActiveTab('indexing')}
                    className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 font-semibold text-sm transition"
                  >
                    <Search className="w-4 h-4" />
                    SEO & Sitemap Health
                  </button>
                </div>
              </div>
            </div>

            {/* Quick Metrics */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <div className="p-5 rounded-xl border border-slate-800 bg-slate-900/50 space-y-1">
                <div className="text-xs text-slate-400 font-medium">Content Categories</div>
                <div className="text-2xl font-bold text-white">6 Curated Topics</div>
                <div className="text-xs text-emerald-400">Technology to Lifelong Education</div>
              </div>
              <div className="p-5 rounded-xl border border-slate-800 bg-slate-900/50 space-y-1">
                <div className="text-xs text-slate-400 font-medium">Editorial Policy</div>
                <div className="text-2xl font-bold text-white">100% Human Review</div>
                <div className="text-xs text-emerald-400">Zero Unreviewed Machine Content</div>
              </div>
              <div className="p-5 rounded-xl border border-slate-800 bg-slate-900/50 space-y-1">
                <div className="text-xs text-slate-400 font-medium">Search Engine Health</div>
                <div className="text-2xl font-bold text-white">Bing & Google Ready</div>
                <div className="text-xs text-emerald-400">Modular XML Sitemap & IndexNow</div>
              </div>
              <div className="p-5 rounded-xl border border-slate-800 bg-slate-900/50 space-y-1">
                <div className="text-xs text-slate-400 font-medium">Compliance Standards</div>
                <div className="text-2xl font-bold text-white">AdSense & CCPA/GDPR</div>
                <div className="text-xs text-emerald-400">Comprehensive Transparent Policies</div>
              </div>
            </div>

            {/* Topics Preview */}
            <div className="space-y-4">
              <div className="flex items-center justify-between">
                <h2 className="text-lg font-bold text-white">Core Content Coverage</h2>
                <button
                  onClick={() => setActiveTab('categories')}
                  className="text-xs font-semibold text-emerald-400 hover:text-emerald-300 transition"
                >
                  View All Topics →
                </button>
              </div>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                {CATEGORIES.slice(0, 3).map((cat) => {
                  const Icon = cat.icon;
                  return (
                    <div
                      key={cat.name}
                      className="p-5 rounded-xl border border-slate-800 bg-slate-900/40 hover:border-slate-700 transition space-y-2"
                    >
                      <div className="w-9 h-9 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <Icon className="w-5 h-5" />
                      </div>
                      <div className="font-bold text-white text-base">{cat.name}</div>
                      <p className="text-xs text-slate-400 leading-relaxed">{cat.desc}</p>
                    </div>
                  );
                })}
              </div>
            </div>
          </div>
        )}

        {activeTab === 'policies' && (
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {/* Sidebar List */}
            <div className="lg:col-span-4 space-y-4">
              <div className="relative">
                <Search className="w-4 h-4 absolute left-3.5 top-3 text-slate-400" />
                <input
                  type="text"
                  placeholder="Search policies..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full pl-10 pr-4 py-2 text-xs sm:text-sm rounded-xl bg-slate-900 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500"
                />
              </div>

              <div className="space-y-2">
                {filteredPolicies.map((policy) => {
                  const isSelected = selectedPolicy.id === policy.id;
                  return (
                    <button
                      key={policy.id}
                      onClick={() => setSelectedPolicy(policy)}
                      className={`w-full text-left p-4 rounded-xl border transition-all ${
                        isSelected
                          ? 'bg-emerald-950/40 border-emerald-500/50 shadow-md shadow-emerald-950/30'
                          : 'bg-slate-900/50 border-slate-800/80 hover:bg-slate-900 hover:border-slate-700'
                      }`}
                    >
                      <div className="flex items-center justify-between">
                        <span className="text-xs font-semibold text-emerald-400 uppercase tracking-wider">
                          {policy.category}
                        </span>
                        <span className="text-[11px] text-slate-500">{policy.updatedAt}</span>
                      </div>
                      <h3 className="font-bold text-white text-sm mt-1">{policy.title}</h3>
                      <p className="text-xs text-slate-400 mt-1 line-clamp-2">{policy.summary}</p>
                    </button>
                  );
                })}
              </div>
            </div>

            {/* Selected Policy Viewer */}
            <div className="lg:col-span-8">
              <div className="p-6 sm:p-8 rounded-2xl border border-slate-800 bg-slate-900/60 space-y-6">
                <div className="border-b border-slate-800 pb-5 space-y-2">
                  <div className="flex items-center gap-2">
                    <span className="px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-medium">
                      {selectedPolicy.category}
                    </span>
                    <span className="text-xs text-slate-400">Last updated: {selectedPolicy.updatedAt}</span>
                  </div>
                  <h2 className="text-2xl font-bold text-white tracking-tight">{selectedPolicy.title}</h2>
                  <p className="text-sm text-slate-300">{selectedPolicy.summary}</p>
                </div>

                <div className="space-y-4 text-sm text-slate-300 leading-relaxed">
                  {selectedPolicy.content.map((paragraph, idx) => (
                    <div key={idx} className="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80">
                      <p>{paragraph}</p>
                    </div>
                  ))}
                </div>

                <div className="pt-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
                  <div className="flex items-center gap-1.5 text-emerald-400 font-medium">
                    <CheckCircle2 className="w-4 h-4" /> Verified Compliance & Plain Language
                  </div>
                  <span>Huvanti Publishing Standards</span>
                </div>
              </div>
            </div>
          </div>
        )}

        {activeTab === 'categories' && (
          <div className="space-y-6">
            <div className="space-y-1">
              <h2 className="text-2xl font-bold text-white">Publication Categories</h2>
              <p className="text-sm text-slate-400">
                Explore the six primary knowledge verticals maintained by Huvanti editors.
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
              {CATEGORIES.map((cat) => {
                const Icon = cat.icon;
                return (
                  <div
                    key={cat.name}
                    className="p-6 rounded-2xl border border-slate-800 bg-slate-900/50 hover:border-emerald-500/40 transition-all space-y-4 shadow-sm"
                  >
                    <div className="flex items-center justify-between">
                      <div className="w-11 h-11 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                        <Icon className="w-6 h-6" />
                      </div>
                      <span className="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-800 border border-slate-700 text-slate-300">
                        {cat.count}
                      </span>
                    </div>
                    <div>
                      <h3 className="text-lg font-bold text-white">{cat.name}</h3>
                      <p className="text-xs text-slate-400 mt-1 leading-relaxed">{cat.desc}</p>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        )}

        {activeTab === 'indexing' && (
          <div className="space-y-6">
            <div className="space-y-1">
              <h2 className="text-2xl font-bold text-white">Search Engine & Sitemap Status</h2>
              <p className="text-sm text-slate-400">
                Real-time configuration monitoring for Bing Webmaster Tools, Googlebot, and IndexNow protocols.
              </p>
            </div>

            <div className="grid grid-cols-1 gap-4">
              {INDEXING_STATUS.map((row, idx) => (
                <div
                  key={idx}
                  className="p-5 rounded-xl border border-slate-800 bg-slate-900/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                >
                  <div className="space-y-1">
                    <div className="flex items-center gap-2">
                      <h3 className="font-bold text-white text-base">{row.item}</h3>
                      <code className="text-[11px] px-2 py-0.5 rounded bg-slate-800 text-emerald-300 font-mono">
                        {row.url}
                      </code>
                    </div>
                    <p className="text-xs text-slate-400">{row.note}</p>
                  </div>
                  <div className="flex items-center gap-2 shrink-0">
                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold">
                      <CheckCircle2 className="w-3.5 h-3.5" />
                      {row.status}
                    </span>
                  </div>
                </div>
              ))}
            </div>

            <div className="p-6 rounded-2xl border border-slate-800 bg-slate-900/30 space-y-3">
              <div className="flex items-center gap-2 text-emerald-400 text-sm font-semibold">
                <Info className="w-4 h-4" /> IndexNow Protocol Setup
              </div>
              <p className="text-xs text-slate-300 leading-relaxed">
                When new articles are published or existing guides updated, Huvanti automatically broadcasts the URL
                payload to Bing and Yandex via IndexNow endpoint integration. This ensures immediate crawl indexing
                without waiting for passive discovery cycles.
              </p>
            </div>
          </div>
        )}
      </main>

      {/* Footer */}
      <footer className="border-t border-slate-800/80 bg-slate-950 mt-16 py-8">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
          <div className="flex items-center gap-2">
            <span className="font-bold text-slate-300">Huvanti.com</span>
            <span>• Independent Digital Publication</span>
          </div>
          <div className="flex items-center gap-4">
            <span>Privacy</span>
            <span>Terms</span>
            <span>Editorial</span>
            <span>Cookie Policy</span>
          </div>
        </div>
      </footer>
    </div>
  );
}
