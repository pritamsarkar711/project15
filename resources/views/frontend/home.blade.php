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
    $heroImgUrl = $heroImgSetting ? asset('storage/'.$heroImgSetting) : asset('images/hero-person.png');
@endphp
{{-- ===================== HERO — deep green with the Huvanti reader ===================== --}}
<section class="relative bg-[#0C3B2E] dark:bg-[#07231B] text-white overflow-hidden">
    {{-- Decorations: soft solid circles + fine dot texture (no gradients) --}}
    <div class="hero-dots absolute inset-0 opacity-60 pointer-events-none" aria-hidden="true"></div>
    <div class="absolute -top-32 -right-24 w-[420px] h-[420px] rounded-full bg-emerald-400/10 pointer-events-none" aria-hidden="true"></div>
    <div class="absolute top-24 -left-28 w-[300px] h-[300px] rounded-full bg-amber-400/10 pointer-events-none" aria-hidden="true"></div>

    <div class="relative max-w-[1280px] mx-auto px-4 sm:px-6">
        <div class="grid lg:grid-cols-2 gap-10 lg:gap-6 items-center pt-12 pb-0 lg:pt-16 lg:pb-0">
            <!-- Left: the Huvanti reader (photo) -->
            <div class="relative order-2 lg:order-1 flex items-end justify-center lg:justify-start">
                <div class="relative flex items-end justify-center w-full max-w-[440px]">
                    {{-- Soft circles behind the person --}}
                    <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[340px] h-[340px] sm:w-[400px] sm:h-[400px] rounded-full bg-emerald-400/15 pointer-events-none" aria-hidden="true"></div>
                    <div class="absolute bottom-10 left-[8%] w-16 h-16 rounded-full bg-amber-400/25 pointer-events-none" aria-hidden="true"></div>
                    <div class="absolute top-16 right-[6%] w-10 h-10 rounded-full bg-emerald-300/20 pointer-events-none" aria-hidden="true"></div>
                    <img src="{{ $heroImgUrl }}" alt="Reader enjoying Huvanti articles" class="relative block w-[320px] sm:w-[400px] lg:w-[440px] h-auto object-contain select-none pointer-events-none translate-y-[6px]" loading="eager" decoding="async" fetchpriority="high" onerror="this.style.display='none'">

                    {{-- Floating review card (top right) --}}
                    <div class="float-slow absolute top-6 sm:top-10 right-0 sm:-right-2 bg-white rounded-2xl shadow-xl shadow-emerald-950/30 p-3.5 pr-5 flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.5L11.5 15l5-5.5"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7.5 3v5.2c0 4.6-3.2 8.2-7.5 9.8-4.3-1.6-7.5-5.2-7.5-9.8V6l7.5-3z"/></svg>
                        </span>
                        <span>
                            <span class="block text-[13.5px] font-bold text-slate-900 leading-tight">Human reviewed</span>
                            <span class="block text-[12px] text-slate-500 leading-tight mt-0.5">Every single article</span>
                        </span>
                    </div>

                    {{-- Floating topics card (bottom left) --}}
                    <div class="float-slower hidden sm:flex absolute bottom-16 -left-2 lg:-left-6 bg-white rounded-2xl shadow-xl shadow-emerald-950/30 p-3.5 pr-5 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.5a3 3 0 0 1 3 3c0 1.5-1 2-2 2.7-.7.5-1 1-1 1.8m0 3h.01M12 21a9 9 0 1 1 0-18 9 9 0 0 1 0 18z"/></svg>
                        </span>
                        <span>
                            <span class="block text-[13.5px] font-bold text-slate-900 leading-tight">Tech · Health · Money</span>
                            <span class="block text-[12px] text-slate-500 leading-tight mt-0.5">One calm place</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Right: headline, search, topics -->
            <div class="order-1 lg:order-2 lg:pl-6 pb-12 sm:pb-14 lg:py-20 min-w-0">
                <span class="inline-flex items-center gap-2.5 rounded-full bg-white/10 border border-white/15 px-4 py-1.5 text-[12.5px] font-semibold text-emerald-100">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                    </span>
                    Fresh reads every week
                </span>
                <h1 class="mt-5 text-[38px] leading-[1.08] sm:text-[52px] lg:text-[58px] font-extrabold tracking-tight min-h-[2.3em] sm:min-h-[2.2em]">
                    <span id="typing-text" class="typing-text">{{ $heroPhrase1 }}</span><span class="typing-cursor" aria-hidden="true"></span>
                </h1>
                <p class="mt-4 text-[16.5px] sm:text-lg leading-relaxed text-emerald-50/80 max-w-[520px] font-medium">{{ $heroSubtitle }}</p>
                <form action="{{ route('search') }}" method="GET" class="mt-7 w-full max-w-[540px] min-w-0" autocomplete="off">
                    <div class="flex items-center w-full min-w-0 bg-white rounded-2xl p-1.5 pl-5 shadow-2xl shadow-emerald-950/40 overflow-hidden">
                        <svg class="w-5 h-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ $heroSearchPlaceholder }}" autocomplete="off" autocorrect="off" spellcheck="false" class="flex-1 min-w-0 h-11 bg-transparent text-slate-900 border-0 outline-none text-[15px] font-medium placeholder:text-slate-400 px-3" aria-label="Search articles">
                        <button type="submit" class="h-11 px-6 sm:px-7 shrink-0 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold shadow-sm shadow-emerald-600/30 transition">Search</button>
                    </div>
                </form>
                @if($categories->count())
                <div class="mt-6 flex items-center gap-2 flex-wrap">
                    <span class="text-[12.5px] font-medium text-emerald-100/70 mr-1">Trending:</span>
                    @foreach($categories->take(4) as $cat)
                        <a href="{{ route('category.show',$cat->slug) }}" class="text-[13px] font-medium px-3.5 py-1.5 rounded-full bg-white/10 border border-white/10 text-emerald-50 hover:bg-white/20 hover:border-white/20 transition">{{ $cat->name }}</a>
                    @endforeach
                </div>
                @endif
                <div class="mt-7 flex items-center gap-x-5 gap-y-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 text-[13px] text-emerald-100/75">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Free to read
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-[13px] text-emerald-100/75">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        No AI filler
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-[13px] text-emerald-100/75">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Weekly new stories
                    </span>
                </div>
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
    <div class="ad-slot-wrap max-w-[1280px] mx-auto px-4 sm:px-6 mt-8">
        <div class="card-elev p-4 text-center">{!! $headerAd->code !!}</div>
    </div>
@endif

{{-- ===================== CATEGORIES ===================== --}}
<section id="categories" class="max-w-[1280px] mx-auto px-4 sm:px-6 pt-16 pb-4">
    <div class="flex items-end justify-between gap-6 flex-wrap">
        <div>
            <span class="kicker">Browse by topic</span>
            <h2 class="mt-4 text-[28px] sm:text-[36px] font-extrabold text-slate-900 dark:text-[#F1F5F4] tracking-tight leading-none">Find what interests you</h2>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mt-8">
        @foreach($categories as $cat)
            <a href="{{ route('category.show',$cat->slug) }}" class="group card-elev lift p-6 flex flex-col">
                <span class="chip w-12 h-12 group-hover:scale-105 transition-transform duration-200">
                    @include('partials.category-icon', ['category' => $cat, 'class' => 'w-6 h-6'])
                </span>
                <h3 class="text-[17px] font-bold text-slate-900 dark:text-[#F1F5F4] mt-4">{{ $cat->name }}</h3>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-[#8FA398] leading-relaxed line-clamp-2 flex-1">{{ $cat->description }}</p>
                <span class="mt-4 inline-flex items-center gap-1.5 text-[13.5px] font-semibold text-emerald-600 dark:text-emerald-400">
                    Explore
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            </a>
        @endforeach
    </div>
</section>

{{-- ===================== LATEST — lead story + grid ===================== --}}
<section class="bg-slate-50/80 dark:bg-[#0D1411] border-y border-slate-100 dark:border-[#151D19] mt-12">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 pt-14 pb-16">
        <div class="flex items-end justify-between gap-6 flex-wrap">
            <div>
                <span class="kicker">The latest</span>
                <h2 class="mt-4 text-[28px] sm:text-[36px] font-extrabold text-slate-900 dark:text-[#F1F5F4] tracking-tight leading-none">Fresh off the desk</h2>
            </div>
            <a href="{{ route('blog.index') }}" class="group inline-flex items-center gap-2 text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition mb-1">
                All articles
                <svg class="w-4 h-4 shrink-0 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        @if($featuredPosts->count() > 0)
            @php $big = $featuredPosts->first(); @endphp
            <div class="grid lg:grid-cols-12 gap-6 mt-8">
                {{-- Lead story: big image card with scrim --}}
                <a href="{{ route('blog.show',$big->slug) }}" class="lg:col-span-7 group relative overflow-hidden rounded-3xl min-h-[320px] sm:min-h-[400px] flex flex-col justify-end shadow-sm hover:shadow-xl transition-shadow duration-300 min-w-0 block">
                    <img src="{{ storage_image_url($big->featured_image) ?: 'https://picsum.photos/seed/'.$big->slug.'/900/600' }}" alt="{{ image_alt_text($big->featured_image, $big->title) }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-[1.03] transition duration-500" loading="lazy" decoding="async">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
                    <div class="relative p-6 sm:p-8">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <span class="text-[11.5px] font-bold uppercase tracking-wide bg-emerald-500 text-white px-3 py-1 rounded-full">{{ $big->category->name ?? 'Featured' }}</span>
                            <span class="text-xs font-medium text-white/80">{{ $big->published_at?->format('M d, Y') }} · {{ $big->reading_time }} min read</span>
                        </div>
                        <h3 class="mt-3 text-[24px] sm:text-[30px] leading-[1.15] font-extrabold text-white tracking-tight">{{ $big->title }}</h3>
                        <p class="mt-2.5 text-sm sm:text-[15px] text-white/75 leading-relaxed line-clamp-2 max-w-[560px]">{{ $big->excerpt }}</p>
                        <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-white">
                            Read story
                            <svg class="w-4 h-4 shrink-0 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </div>
                </a>
                {{-- Secondary featured: horizontal cards --}}
                <div class="lg:col-span-5 flex flex-col gap-5 min-w-0">
                    @foreach($featuredPosts->skip(1)->take(2) as $fp)
                        <a href="{{ route('blog.show',$fp->slug) }}" class="group card-elev lift p-4 flex gap-4 items-center flex-1 min-w-0">
                            <img src="{{ storage_image_url($fp->featured_image) ?: 'https://picsum.photos/seed/'.$fp->slug.'/400/300' }}" class="w-[116px] h-[100px] rounded-xl object-cover shrink-0" alt="{{ image_alt_text($fp->featured_image, $fp->title) }}" loading="lazy" decoding="async">
                            <div class="flex flex-col min-w-0">
                                <span class="text-[11.5px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide">{{ $fp->category->name ?? 'Story' }}</span>
                                <h4 class="text-[15.5px] font-bold text-slate-900 dark:text-[#F1F5F4] leading-snug mt-1.5 line-clamp-2 group-hover:text-emerald-700 dark:group-hover:text-emerald-300 transition-colors">{{ $fp->title }}</h4>
                                <span class="text-xs text-slate-400 dark:text-[#6B7F75] mt-auto pt-2 font-medium">{{ $fp->published_at?->format('M d') }} · {{ $fp->reading_time }} min read</span>
                            </div>
                        </a>
                    @endforeach
                    <a href="{{ route('blog.index') }}" class="group flex items-center justify-between px-5 py-4 rounded-2xl border border-emerald-200 bg-emerald-50/60 text-emerald-800 hover:bg-emerald-50 hover:border-emerald-300 dark:bg-emerald-500/10 dark:border-emerald-500/25 dark:text-emerald-300 dark:hover:bg-emerald-500/15 transition">
                        <span class="text-sm font-semibold">Browse the archive</span>
                        <svg class="w-5 h-5 shrink-0 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8 mt-12">
            @foreach($latestPosts as $lp)
                <article class="group card-elev lift overflow-hidden flex flex-col">
                    <a href="{{ route('blog.show',$lp->slug) }}" class="relative h-[190px] overflow-hidden block">
                        <img src="{{ storage_image_url($lp->featured_image) ?: 'https://picsum.photos/seed/'.$lp->slug.'/600/400' }}" alt="{{ image_alt_text($lp->featured_image, $lp->title) }}" class="w-full h-full object-cover group-hover:scale-[1.04] transition duration-500" loading="lazy" decoding="async">
                        @if($lp->is_featured)<span class="absolute top-3 right-3 inline-flex items-center gap-1 text-[11px] font-bold bg-amber-400 text-amber-950 px-2.5 py-1 rounded-full shadow-sm">★ Popular</span>@endif
                    </a>
                    <div class="p-5 flex flex-col flex-1">
                        <span class="inline-flex self-start text-[11.5px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-1 rounded-full">{{ $lp->category->name ?? 'General' }}</span>
                        <a href="{{ route('blog.show',$lp->slug) }}" class="mt-3 text-[17.5px] font-bold text-slate-900 dark:text-[#F1F5F4] leading-snug line-clamp-2 group-hover:text-emerald-700 dark:group-hover:text-emerald-300 transition-colors">{{ $lp->title }}</a>
                        <p class="text-[13.5px] text-slate-500 dark:text-[#8FA398] mt-2.5 leading-relaxed line-clamp-2 flex-1">{{ $lp->excerpt }}</p>
                        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-[#1F2925] flex items-center gap-2 text-xs font-medium text-slate-400 dark:text-[#6B7F75]">
                            <span>{{ $lp->published_at?->format('M d, Y') }}</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-[#3A4A42]"></span>
                            <span>{{ $lp->reading_time }} min read</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="text-center mt-12">
            <a href="{{ route('blog.index') }}" class="btn btn-primary">
                Browse all posts
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- ===================== CTA — write for us ===================== --}}
<section class="max-w-[1280px] mx-auto px-4 sm:px-6 py-16">
    <div class="relative bg-[#0C3B2E] dark:bg-[#0A2F25] text-white rounded-[2rem] overflow-hidden">
        <div class="hero-dots absolute inset-0 opacity-50 pointer-events-none" aria-hidden="true"></div>
        <div class="absolute -top-24 -right-16 w-80 h-80 rounded-full bg-emerald-400/10 pointer-events-none" aria-hidden="true"></div>
        <div class="absolute -bottom-28 -left-20 w-96 h-96 rounded-full bg-amber-400/10 pointer-events-none" aria-hidden="true"></div>
        <div class="relative grid lg:grid-cols-2 gap-10 p-8 sm:p-12">
            <div>
                <span class="inline-flex items-center gap-2.5 rounded-full bg-white/10 border border-white/15 px-4 py-1.5 text-[12px] font-semibold text-emerald-100">Why Huvanti</span>
                <h2 class="mt-5 text-[30px] sm:text-[40px] leading-[1.08] font-extrabold tracking-tight">Have a story<br>worth telling?</h2>
                <p class="mt-4 text-[15px] leading-relaxed text-white/70 max-w-[460px]">Write for a human editorial team that reviews every piece, pays attention to craft and puts your byline on work you are proud of.</p>
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a href="{{ route('register') }}" class="btn btn-accent">
                        Start writing today
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('editorial') }}" class="btn border border-white/25 text-white hover:bg-white hover:text-[#0C3B2E]">
                        Editorial guidelines
                    </a>
                </div>
            </div>
            <div class="flex flex-col gap-4 justify-center">
                <div class="flex gap-4 items-start bg-white/5 border border-white/10 rounded-2xl p-5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 text-amber-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                    </span>
                    <span>
                        <h4 class="text-[15px] font-bold text-white">Thoughtfully edited</h4>
                        <p class="text-[13.5px] text-white/65 mt-1 leading-relaxed">Every article is researched and reviewed by our editorial team before it goes live.</p>
                    </span>
                </div>
                <div class="flex gap-4 items-start bg-white/5 border border-white/10 rounded-2xl p-5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 text-amber-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v14"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
                    </span>
                    <span>
                        <h4 class="text-[15px] font-bold text-white">Clean reading</h4>
                        <p class="text-[13.5px] text-white/65 mt-1 leading-relaxed">No pop ups, no clutter, just content that respects your time and attention.</p>
                    </span>
                </div>
                <div class="flex gap-4 items-start bg-white/5 border border-white/10 rounded-2xl p-5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 text-amber-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.57 3.91a2 2 0 0 0 1.66 0l8.57-3.9a1 1 0 0 0 0-1.84z"/><path stroke-linecap="round" stroke-linejoin="round" d="m6.08 9.5-3.5 1.6a1 1 0 0 0 0 1.81l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9a1 1 0 0 0 0-1.83l-3.5-1.59"/><path stroke-linecap="round" stroke-linejoin="round" d="m6.08 14.5-3.5 1.6a1 1 0 0 0 0 1.81l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9a1 1 0 0 0 0-1.83l-3.5-1.59"/></svg>
                    </span>
                    <span>
                        <h4 class="text-[15px] font-bold text-white">Multi niche, unified</h4>
                        <p class="text-[13.5px] text-white/65 mt-1 leading-relaxed">Technology, health, finance, travel, lifestyle and education, all in one place.</p>
                    </span>
                </div>
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
