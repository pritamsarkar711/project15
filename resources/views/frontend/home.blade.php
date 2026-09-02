@extends('layouts.app')

@push('head')
{{-- WebSite + SearchAction schema: sitelinks searchbox eligibility --}}
@php
    $ldHome = json_encode(
        [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => setting('site_name', 'Huvanti'),
            'url' => request()->getSchemeAndHttpHost() . '/',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => request()->getSchemeAndHttpHost() . '/search?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ],
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
@endphp
<script type="application/ld+json">{!! $ldHome !!}</script>
@endpush

@section('content')
@php
    $heroPhrase1 = setting('hero_phrase_1', 'Explore Ideas.');
    $heroPhrase2 = setting('hero_phrase_2', 'Inspire Life.');
    $heroSubtitle = setting('hero_subtitle', 'Tech, health, money, travel and more. Clear thinking, zero noise.');
    $heroSearchPlaceholder = setting('hero_search_placeholder', 'Search articles, topics, ideas...');
    $heroImgSetting = setting('hero_person_image');
    $heroImgUrl = $heroImgSetting ? asset('storage/'.$heroImgSetting) : asset('images/hero-person-harry.png');
@endphp
<section class="hero-band relative overflow-hidden bg-[var(--brand-deep)] dark:bg-[#0F261C] text-white">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 relative">
        <div class="grid lg:grid-cols-2 gap-0 lg:gap-8 items-end">
            <!-- Left: hero image — hidden on mobile, grounded on desktop -->
            <div class="hidden lg:flex items-end justify-center lg:justify-start relative order-1 self-end">
                <div class="relative flex items-end">
                    {{-- Yellow organic shape behind the person --}}
                    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-[44%] w-[340px] h-[340px] lg:w-[400px] lg:h-[400px] bg-[#FDE68A] rounded-[2.5rem] rotate-3 pointer-events-none" aria-hidden="true"></div>
                    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-[44%] w-[340px] h-[340px] lg:w-[400px] lg:h-[400px] bg-[#FACC15]/70 rounded-[2.5rem] -rotate-2 pointer-events-none" aria-hidden="true" style="border-radius:42% 58% 55% 45% / 44% 38% 62% 56%"></div>
                    <img src="{{ $heroImgUrl }}" alt="Featured" class="relative block w-[420px] lg:w-[460px] h-[420px] lg:h-[480px] object-cover object-top translate-y-3 pointer-events-none select-none" loading="eager" decoding="async" onerror="this.style.display='none'">
                </div>
            </div>

            <!-- Right: text -->
            <div class="order-2 lg:pl-6 py-12 sm:py-14 lg:py-16 min-w-0">
                <span class="inline-flex items-center gap-2 text-[11px] font-semibold tracking-wide uppercase bg-white/10 border border-white/15 text-[var(--brand-tint)] px-3 py-1.5 mb-4 rounded-full">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/></svg>
                    Fresh reads every week
                </span>
                <h1 class="text-[34px] sm:text-[42px] lg:text-[48px] font-extrabold leading-[1.12] tracking-[-0.03em] min-h-[2.4em] sm:min-h-[2.2em]">
                    <span id="typing-text" class="typing-text">{{ $heroPhrase1 }}</span><span class="typing-cursor" aria-hidden="true"></span>
                </h1>
                <p class="mt-4 text-[16px] sm:text-[17px] leading-relaxed text-white/80 max-w-[520px]">{{ $heroSubtitle }}</p>
                <form action="{{ route('search') }}" method="GET" class="mt-6 w-full max-w-[520px] min-w-0" autocomplete="off">
                    <div class="flex items-center w-full min-w-0 bg-white rounded-xl p-1.5 pl-4 shadow-[0_10px_30px_rgba(0,0,0,0.25)] overflow-hidden">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ $heroSearchPlaceholder }}" autocomplete="off" autocorrect="off" spellcheck="false" class="flex-1 min-w-0 h-10 bg-transparent text-slate-900 border-0 outline-none text-[15px] placeholder:text-slate-400" aria-label="Search articles">
                        <button type="submit" class="h-10 px-6 sm:px-7 shrink-0 rounded-lg bg-[var(--brand)] hover:bg-[var(--brand-strong)] text-white text-sm font-semibold transition">Search</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@php
    // Ads render only when the admin has switched them on (Settings, Ads tab).
    // Until then no ad slot, box or label appears anywhere on the site.
    // Strict '1' comparison: a stored '0' must stay falsy.
    $adsEnabled = setting('ads_enabled') === '1';
    try {
        $headerAd = $adsEnabled ? \App\Models\Advertisement::active()->position('header')->first() : null;
    } catch (\Throwable $e) {
        $headerAd = null;
    }
@endphp
@if($headerAd && trim(strip_tags($headerAd->code ?? '')) !== '')
    {{-- Blank/unfilled ad slots collapse invisibly (same JS pattern as blog posts)
         so the homepage never shows an empty labeled box. --}}
    <div class="ad-slot-wrap max-w-[1200px] mx-auto px-4 sm:px-6 mt-6">
        <div class="card-elev p-4 text-center">{!! $headerAd->code !!}</div>
    </div>
@endif

<!-- Categories: left-aligned product cards with counts -->
<section id="categories" class="max-w-[1200px] mx-auto px-4 sm:px-6 pt-12 pb-4">
    <div class="flex items-end justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3">
            <span class="icon-tile w-10 h-10" aria-hidden="true">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
            </span>
            <div>
                <h2 class="text-[24px] sm:text-[28px] font-bold text-slate-900 dark:text-white tracking-[-0.025em] leading-tight">Browse Categories</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Topics curated by our editors.</p>
            </div>
        </div>
        <a href="/blog" class="hidden sm:inline-flex items-center gap-1.5 text-sm font-semibold text-[var(--brand)] hover:text-[var(--brand-strong)] transition">All posts
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/></svg>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-7">
        @foreach($categories as $cat)
            <a href="{{ route('category.show',$cat->slug) }}" class="group card-elev card-hover p-5 flex items-start gap-4">
                <span class="icon-tile w-11 h-11 group-hover:scale-105 transition-transform duration-200">
                    @include('partials.category-icon', ['category' => $cat, 'class' => 'w-5 h-5'])
                </span>
                <span class="flex-1 min-w-0">
                    <span class="flex items-center justify-between gap-2">
                        <span class="text-[15px] font-semibold text-slate-900 dark:text-white truncate">{{ $cat->name }}</span>
                        <svg class="w-4 h-4 text-slate-300 dark:text-slate-600 group-hover:text-[var(--brand)] group-hover:translate-x-0.5 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </span>
                    <span class="block mt-1 text-[13px] text-slate-500 dark:text-slate-400 leading-relaxed line-clamp-2">{{ $cat->description }}</span>
                </span>
            </a>
        @endforeach
    </div>
</section>

<!-- Featured + Latest: feed-grade editorial block -->
<section class="max-w-[1200px] mx-auto px-4 sm:px-6 py-10">
    <div class="flex items-end justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3">
            <span class="icon-tile w-10 h-10" aria-hidden="true">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path stroke-linecap="round" stroke-linejoin="round" d="M18 14h-8"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 18h-5"/></svg>
            </span>
            <div>
                <h2 class="text-[24px] sm:text-[28px] font-bold text-slate-900 dark:text-white tracking-[-0.025em] leading-tight">Latest Posts</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">Fresh stories from our writers.</p>
            </div>
        </div>
    </div>

    @if($featuredPosts->count() > 0)
        <div class="grid lg:grid-cols-12 gap-4 mt-7 mb-4">
            @php $big = $featuredPosts->first(); @endphp
            <a href="{{ route('blog.show',$big->slug) }}" class="lg:col-span-7 group relative overflow-hidden bg-[var(--brand-deep)] min-h-[360px] flex flex-col justify-end p-7 rounded-2xl">
                <img src="{{ storage_image_url($big->featured_image) ?: 'https://picsum.photos/seed/'.$big->slug.'/900/600' }}" alt="{{ image_alt_text($big->featured_image, $big->title) }}" class="img-fade absolute inset-0 w-full h-full object-cover opacity-70 group-hover:opacity-60 group-hover:scale-[1.02] transition duration-300" loading="lazy" decoding="async" onload="this.classList.add('img-loaded')" onerror="this.onerror=null;this.style.display='none'">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <div class="relative">
                    <div class="flex items-center gap-2 mb-3 flex-wrap">
                        <span class="badge badge-ink">{{ $big->category->name ?? 'Featured' }}</span>
                        <span class="text-xs text-white/85"><span class="tabular-nums">{{ $big->published_at?->format('M d, Y') }}</span> <span class="w-1 h-1 bg-white/50 inline-block mx-1 align-middle rounded-full"></span> <span class="tabular-nums">{{ $big->reading_time }} min read</span></span>
                    </div>
                    <h3 class="text-[23px] font-bold leading-snug text-white tracking-[-0.015em]">{{ $big->title }}</h3>
                    <p class="text-sm text-white/75 mt-2.5 line-clamp-2 max-w-[540px]">{{ $big->excerpt }}</p>
                </div>
            </a>
            <div class="lg:col-span-5 card divide-y divide-[#eef0f4] dark:divide-[#22262e] overflow-hidden">
                <div class="px-5 py-3.5 border-b border-[#eef0f4] dark:border-[#22262e] flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--brand)]"></span>
                    <span class="text-[13px] font-bold text-slate-900 dark:text-white tracking-tight">Editor's picks</span>
                </div>
                @foreach($featuredPosts->skip(1)->take(2) as $fp)
                    <a href="{{ route('blog.show',$fp->slug) }}" class="group flex gap-4 items-center p-4 hover:bg-[#f8f9fb] dark:hover:bg-[#1c1f26] transition">
                        <img src="{{ storage_image_url($fp->featured_image) ?: 'https://picsum.photos/seed/'.$fp->slug.'/400/300' }}" class="img-fade w-[96px] h-[80px] object-cover shrink-0 rounded-lg border border-[#eef0f4] dark:border-[#2c313c]" alt="{{ image_alt_text($fp->featured_image, $fp->title) }}" loading="lazy" decoding="async" onload="this.classList.add('img-loaded')" onerror="this.onerror=null;this.style.display='none'">
                        <span class="flex flex-col min-w-0">
                            <span class="text-[11px] font-bold text-[var(--brand)] dark:text-[var(--brand-light)] uppercase tracking-[0.06em]">{{ $fp->category->name ?? 'Story' }}</span>
                            <span class="text-[14.5px] font-semibold text-slate-900 dark:text-white leading-snug mt-1 line-clamp-2 group-hover:text-[var(--brand)] dark:group-hover:text-[var(--brand-light)] transition-colors">{{ $fp->title }}</span>
                            <span class="text-xs text-slate-400 dark:text-slate-500 mt-auto pt-2"><span class="tabular-nums">{{ $fp->published_at?->format('M d') }}</span> <span class="w-1 h-1 bg-slate-300 dark:bg-slate-600 inline-block mx-1 align-middle rounded-full"></span> <span class="tabular-nums">{{ $fp->reading_time }} min read</span></span>
                        </span>
                    </a>
                @endforeach
                <a href="/blog" class="flex items-center justify-center gap-1.5 text-[13px] font-semibold text-[var(--brand)] dark:text-[var(--brand-light)] py-3 hover:bg-[#f8f9fb] dark:hover:bg-[#1c1f26] transition border-t border-[#eef0f4] dark:border-[#22262e]">View all posts
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                </a>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-5 mt-6">
        @foreach($latestPosts as $lp)
            <article class="group card-elev card-hover overflow-hidden flex flex-col">
                <a href="{{ route('blog.show',$lp->slug) }}" class="relative h-[185px] overflow-hidden block">
                    <img src="{{ storage_image_url($lp->featured_image) ?: 'https://picsum.photos/seed/'.$lp->slug.'/600/400' }}" alt="{{ image_alt_text($lp->featured_image, $lp->title) }}" class="img-fade w-full h-full object-cover group-hover:scale-[1.03] transition duration-300" loading="lazy" decoding="async" onload="this.classList.add('img-loaded')" onerror="this.onerror=null;this.style.display='none'">
                    @if($lp->is_featured)<span class="absolute top-3 right-3 badge" style="background:#F5C445;color:#16181d;">Popular</span>@endif
                </a>
                <div class="p-5 flex flex-col flex-1">
                    <div class="flex items-center gap-2">
                        <span class="chip">{{ $lp->category->name ?? 'General' }}</span>
                    </div>
                    <a href="{{ route('blog.show',$lp->slug) }}" class="mt-2.5 text-[16.5px] font-bold text-slate-900 dark:text-white leading-snug tracking-[-0.01em] line-clamp-2 group-hover:text-[var(--brand)] dark:group-hover:text-[var(--brand-light)] transition-colors">{{ $lp->title }}</a>
                    <p class="text-[13.5px] text-slate-500 dark:text-slate-400 mt-2.5 leading-relaxed line-clamp-2">{{ $lp->excerpt }}</p>
                    <div class="mt-auto pt-4 flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>
                        <span class="tabular-nums">{{ $lp->published_at?->format('M d, Y') }}</span>
                        <span class="w-1 h-1 bg-slate-300 dark:bg-slate-600 rounded-full"></span>
                        <span>{{ $lp->reading_time }} min read</span>
                        <svg class="w-4 h-4 ml-auto text-slate-300 dark:text-slate-600 group-hover:text-[var(--brand)] group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <div class="text-center mt-9">
        <a href="{{ route('blog.index') }}" class="btn btn-primary btn-lg">
            Browse all posts
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/></svg>
        </a>
    </div>
</section>

<!-- Why Huvanti: hairline feature row on white -->
<section class="max-w-[1200px] mx-auto px-4 sm:px-6 pb-6">
    <div class="card-elev p-6 sm:p-8">
        <div class="grid sm:grid-cols-3 gap-6 sm:gap-8">
            <div class="flex flex-col sm:border-l border-[#eef0f4] dark:border-[#22262e] sm:pl-6 sm:first:border-l-0 sm:first:pl-0">
                <span class="icon-tile w-10 h-10">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                </span>
                <h4 class="text-[15px] font-bold text-slate-900 dark:text-white mt-3.5 tracking-tight">Thoughtfully edited</h4>
                <p class="text-[13.5px] text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">Every article is researched and reviewed by our editorial team before it goes live.</p>
            </div>
            <div class="flex flex-col sm:border-l border-[#eef0f4] dark:border-[#22262e] sm:pl-6 sm:first:border-l-0 sm:first:pl-0">
                <span class="icon-tile w-10 h-10">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v14"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
                </span>
                <h4 class="text-[15px] font-bold text-slate-900 dark:text-white mt-3.5 tracking-tight">Clean reading</h4>
                <p class="text-[13.5px] text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">No pop ups, no clutter, just content that respects your time and attention.</p>
            </div>
            <div class="flex flex-col sm:border-l border-[#eef0f4] dark:border-[#22262e] sm:pl-6 sm:first:border-l-0 sm:first:pl-0">
                <span class="icon-tile w-10 h-10">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.57 3.91a2 2 0 0 0 1.66 0l8.57-3.9a1 1 0 0 0 0-1.84z"/><path stroke-linecap="round" stroke-linejoin="round" d="m6.08 9.5-3.5 1.6a1 1 0 0 0 0 1.81l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9a1 1 0 0 0 0-1.83l-3.5-1.59"/><path stroke-linecap="round" stroke-linejoin="round" d="m6.08 14.5-3.5 1.6a1 1 0 0 0 0 1.81l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9a1 1 0 0 0 0-1.83l-3.5-1.59"/></svg>
                </span>
                <h4 class="text-[15px] font-bold text-slate-900 dark:text-white mt-3.5 tracking-tight">Multi niche, unified</h4>
                <p class="text-[13.5px] text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">Technology, health, finance, travel, lifestyle and education, all in one place.</p>
            </div>
        </div>
    </div>
</section>

<!-- Write-for-us band: forest CTA with clear action -->
<section class="max-w-[1200px] mx-auto px-4 sm:px-6 pb-14">
    <div class="relative overflow-hidden bg-[var(--brand-deep)] dark:bg-[#112E20] rounded-2xl px-6 sm:px-10 py-9 sm:py-11 text-white">
        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-white/5 pointer-events-none" aria-hidden="true"></div>
        <div class="absolute -right-6 top-10 w-28 h-28 rounded-full bg-[#F5C445]/15 pointer-events-none" aria-hidden="true"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-center gap-6 lg:gap-10">
            <div class="flex-1 min-w-0">
                <span class="badge" style="background:rgba(245,196,69,.18);color:#F5C445;">Write for us</span>
                <h3 class="text-[22px] sm:text-[26px] font-bold tracking-[-0.025em] mt-3">Have a story worth telling?</h3>
                <p class="text-sm text-white/75 mt-2 max-w-[520px] leading-relaxed">Join Huvanti as a writer. Publish across six niches, reach curious readers and build your byline — our editors review every draft within days.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0 flex-wrap">
                <a href="{{ route('register') }}" class="btn bg-white text-[var(--brand-deep)] hover:bg-[#F5C445] hover:text-[#16181d]">Become a writer</a>
                <a href="{{ route('contact') }}" class="btn btn-ghost text-white hover:bg-white/10">Contact team</a>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
// Hide blank/unfilled ad slots so the homepage never shows empty ad boxes.
(function(){
    function collapseEmptyAdSlots(){
        document.querySelectorAll('.ad-slot, .ad-slot-wrap').forEach(function(el){
            if (el.dataset.adChecked) return;
            var hasContent = el.innerText && el.innerText.trim().length > 2;
            var visibleMedia = false;
            el.querySelectorAll('img, iframe, ins').forEach(function(m){ if (m.getBoundingClientRect().height > 4) visibleMedia = true; });
            if (!hasContent && !visibleMedia) { el.style.display = 'none'; el.dataset.adChecked = '1'; }
        });
    }
    window.addEventListener('load', function(){
        setTimeout(collapseEmptyAdSlots, 800);
        setTimeout(collapseEmptyAdSlots, 2500);
        setTimeout(collapseEmptyAdSlots, 5000);
    });
})();

// Typing animation: cycles the two tagline phrases (admin-editable)
(function(){
    const el = document.getElementById('typing-text');
    if(!el) return;
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const phrases = [@json($heroPhrase1) , @json($heroPhrase2) ];
    if(reduce){ el.textContent = phrases.join(' '); return; }
    let p = 0, i = 0, deleting = false;
    function tick(){
        const word = phrases[p];
        if(!deleting){
            i++;
            el.textContent = word.slice(0, i);
            if(i === word.length){ deleting = true; setTimeout(tick, 1900); return; }
            setTimeout(tick, 85);
        } else {
            i--;
            el.textContent = word.slice(0, i);
            if(i === 0){ deleting = false; p = (p + 1) % phrases.length; setTimeout(tick, 400); return; }
            setTimeout(tick, 40);
        }
    }
    tick();
})();
</script>
@endpush
@endsection
