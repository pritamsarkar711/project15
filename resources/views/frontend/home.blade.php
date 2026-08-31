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
{{-- ===================== HERO — editorial masthead ===================== --}}
<section class="relative dotgrid border-b border-[#E4E4DA] dark:border-[#262C28] overflow-hidden">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6">
        <div class="grid lg:grid-cols-12 gap-10 items-center pt-12 pb-12 lg:pt-16 lg:pb-16">
            <!-- Text: 7 cols -->
            <div class="lg:col-span-7 min-w-0">
                <span class="kicker mb-5">Human reviewed · Independent</span>
                <h1 class="mt-5 text-[42px] leading-[1.04] sm:text-[56px] lg:text-[68px] font-black text-[#141A16] dark:text-[#F0F2EB] min-h-[2.15em]">
                    <span id="typing-text" class="typing-text">{{ $heroPhrase1 }}</span><span class="typing-cursor" aria-hidden="true"></span>
                </h1>
                <p class="mt-6 text-[17px] sm:text-[18px] leading-relaxed text-[#5C665E] dark:text-[#97A199] max-w-[540px] font-medium">{{ $heroSubtitle }}</p>
                <form action="{{ route('search') }}" method="GET" class="mt-8 w-full max-w-[540px] min-w-0" autocomplete="off">
                    <div class="flex items-stretch w-full min-w-0 bg-white dark:bg-[#141815] border-2 border-[#141A16] dark:border-[#3A443D] shadow-[7px_7px_0_0_#F5C445] overflow-hidden">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ $heroSearchPlaceholder }}" autocomplete="off" autocorrect="off" spellcheck="false" class="flex-1 min-w-0 h-[52px] px-5 bg-transparent text-[#141A16] dark:text-[#EDEFEA] border-0 outline-none text-[15px] font-medium placeholder:text-[#8B958C] dark:placeholder:text-[#6B756C]" aria-label="Search articles">
                        <button type="submit" class="h-[52px] px-7 shrink-0 bg-[#141A16] hover:bg-[#0C3B2E] text-white text-[12.5px] font-extrabold uppercase tracking-[0.12em] transition flex items-center gap-2">
                            Search
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </form>
                @if($categories->count())
                <div class="mt-6 flex items-center gap-2 flex-wrap">
                    <span class="text-[11px] font-extrabold tracking-[0.2em] uppercase text-[#8B958C] dark:text-[#6B756C] mr-1">Read:</span>
                    @foreach($categories->take(4) as $cat)
                        <a href="{{ route('category.show',$cat->slug) }}" class="text-[12px] font-bold px-3 py-1.5 border border-[#E4E4DA] dark:border-[#3A443D] bg-white dark:bg-[#141815] text-[#3D463F] dark:text-[#C2C9C0] hover:border-[#141A16] dark:hover:border-[#F5C445] hover:text-[#141A16] dark:hover:text-[#F5C445] transition">{{ $cat->name }}</a>
                    @endforeach
                </div>
                @endif
            </div>
            <!-- Photo: 5 cols, plate treatment -->
            <div class="hidden lg:block lg:col-span-5">
                <div class="relative ml-auto w-[400px] max-w-full">
                    <img src="{{ $heroImgUrl }}" alt="Featured" class="plate plate-yellow relative block w-[400px] h-[440px] object-cover object-top select-none" loading="eager" decoding="async" onerror="this.style.display='none'">
                    <span class="absolute -top-4 -left-4 bg-[#141A16] text-white text-[10px] font-extrabold tracking-[0.22em] uppercase px-3.5 py-2">Fresh weekly</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===================== TICKER — topic strip ===================== --}}
@if($categories->count())
<div class="ticker border-b border-[#E4E4DA] dark:border-[#262C28]" aria-hidden="true">
    <div class="ticker-track py-3">
        @for($tick = 0; $tick < 2; $tick++)
            @foreach($categories as $cat)
                <a href="{{ route('category.show',$cat->slug) }}" tabindex="-1" class="inline-flex items-center gap-3 px-6 text-[12px] font-extrabold tracking-[0.22em] uppercase text-[#F0F2EB] hover:text-[#F5C445] transition whitespace-nowrap">
                    {{ $cat->name }} <span class="w-1.5 h-1.5 bg-[#F5C445] inline-block"></span>
                </a>
            @endforeach
        @endfor
    </div>
</div>
@endif

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

{{-- ===================== LATEST — lead story + grid ===================== --}}
<section class="max-w-[1280px] mx-auto px-4 sm:px-6 pt-14 pb-4">
    <div class="flex items-end justify-between gap-6 flex-wrap">
        <div>
            <span class="kicker"><b>01</b> The Latest</span>
            <h2 class="mt-3 text-[32px] sm:text-[42px] font-black text-[#141A16] dark:text-[#F0F2EB] leading-none">Fresh off the desk</h2>
        </div>
        <a href="{{ route('blog.index') }}" class="group inline-flex items-center gap-2 text-[12px] font-extrabold tracking-[0.18em] uppercase text-[#0C3B2E] dark:text-[#34D399] hover:text-[#072A20] dark:hover:text-[#6EE7B7] transition mb-1">
            All articles
            <svg class="w-4 h-4 shrink-0 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>

    @if($featuredPosts->count() > 0)
        @php $big = $featuredPosts->first(); @endphp
        <div class="grid lg:grid-cols-12 gap-8 mt-8">
            {{-- Lead story: image plate + huge headline --}}
            <a href="{{ route('blog.show',$big->slug) }}" class="lg:col-span-7 group min-w-0 block">
                <div class="relative overflow-hidden plate">
                    <img src="{{ storage_image_url($big->featured_image) ?: 'https://picsum.photos/seed/'.$big->slug.'/900/600' }}" alt="{{ image_alt_text($big->featured_image, $big->title) }}" class="w-full h-[260px] sm:h-[340px] lg:h-[400px] object-cover group-hover:scale-[1.02] transition duration-500" loading="lazy" decoding="async">
                    <span class="absolute top-0 left-0 bg-[#F5C445] text-[#141A16] text-[10px] font-extrabold tracking-[0.2em] uppercase px-3.5 py-2">Featured</span>
                </div>
                <div class="pt-5">
                    <div class="flex items-center gap-3 text-[11px] font-extrabold tracking-[0.16em] uppercase">
                        <span class="text-[#0C3B2E] dark:text-[#34D399]">{{ $big->category->name ?? 'Journal' }}</span>
                        <span class="w-6 h-px bg-[#E4E4DA] dark:bg-[#3A443D]"></span>
                        <span class="text-[#8B958C] dark:text-[#6B756C]">{{ $big->published_at?->format('M d, Y') }} · {{ $big->reading_time }} min</span>
                    </div>
                    <h3 class="mt-3 text-[26px] sm:text-[32px] leading-[1.1] font-black text-[#141A16] dark:text-[#F0F2EB] group-hover:text-[#0C3B2E] dark:group-hover:text-[#34D399] transition-colors">{{ $big->title }}</h3>
                    <p class="mt-3 text-[15px] leading-relaxed text-[#5C665E] dark:text-[#97A199] line-clamp-2 max-w-[620px]">{{ $big->excerpt }}</p>
                    <span class="mt-4 inline-flex items-center gap-2 text-[12px] font-extrabold tracking-[0.16em] uppercase text-[#141A16] dark:text-[#F0F2EB] border-b-2 border-[#F5C445] pb-0.5">
                        Read story
                        <svg class="w-4 h-4 shrink-0 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </div>
            </a>
            {{-- Secondary featured: numbered rows --}}
            <div class="lg:col-span-5 flex flex-col">
                @foreach($featuredPosts->skip(1)->take(2) as $fp)
                    <a href="{{ route('blog.show',$fp->slug) }}" class="group flex gap-5 items-start py-6 border-t border-[#E4E4DA] dark:border-[#262C28] first:pt-0 min-w-0 {{ $loop->last ? '' : 'flex-1' }}">
                        <span class="text-[34px] leading-none font-black text-[#E4E4DA] dark:text-[#262C28] group-hover:text-[#F5C445] transition-colors shrink-0 select-none">0{{ $loop->index + 2 }}</span>
                        <img src="{{ storage_image_url($fp->featured_image) ?: 'https://picsum.photos/seed/'.$fp->slug.'/400/300' }}" class="w-[104px] h-[104px] object-cover plate shrink-0" alt="{{ image_alt_text($fp->featured_image, $fp->title) }}" loading="lazy" decoding="async">
                        <div class="flex flex-col min-w-0">
                            <span class="text-[10px] font-extrabold tracking-[0.2em] uppercase text-[#0C3B2E] dark:text-[#34D399]">{{ $fp->category->name ?? 'Story' }}</span>
                            <h4 class="text-[16px] font-bold text-[#141A16] dark:text-[#F0F2EB] leading-snug mt-1.5 line-clamp-2 group-hover:text-[#0C3B2E] dark:group-hover:text-[#34D399] transition-colors">{{ $fp->title }}</h4>
                            <span class="text-[12px] text-[#8B958C] dark:text-[#6B756C] mt-auto pt-2 font-medium">{{ $fp->published_at?->format('M d') }} · {{ $fp->reading_time }} min read</span>
                        </div>
                    </a>
                @endforeach
                <a href="{{ route('blog.index') }}" class="group flex items-center justify-between px-5 py-4 bg-[#141A16] dark:bg-[#141815] text-white dark:text-[#F0F2EB] hover:bg-[#0C3B2E] transition">
                    <span class="text-[12px] font-extrabold tracking-[0.18em] uppercase">Browse the archive</span>
                    <svg class="w-5 h-5 shrink-0 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-7 gap-y-10 mt-12">
        @foreach($latestPosts as $lp)
            <article class="group card-elev lift flex flex-col">
                <a href="{{ route('blog.show',$lp->slug) }}" class="relative h-[200px] overflow-hidden block border-b border-[#E4E4DA] dark:border-[#262C28]">
                    <img src="{{ storage_image_url($lp->featured_image) ?: 'https://picsum.photos/seed/'.$lp->slug.'/600/400' }}" alt="{{ image_alt_text($lp->featured_image, $lp->title) }}" class="w-full h-full object-cover group-hover:scale-[1.03] transition duration-500" loading="lazy" decoding="async">
                    @if($lp->is_featured)<span class="absolute top-0 right-0 bg-[#F5C445] text-[#141A16] text-[10px] font-extrabold tracking-[0.18em] uppercase px-3 py-1.5">Popular</span>@endif
                </a>
                <div class="p-6 flex flex-col flex-1">
                    <span class="text-[10px] font-extrabold tracking-[0.2em] uppercase text-[#0C3B2E] dark:text-[#34D399]">{{ $lp->category->name ?? 'General' }}</span>
                    <a href="{{ route('blog.show',$lp->slug) }}" class="mt-2.5 text-[18px] font-bold text-[#141A16] dark:text-[#F0F2EB] leading-snug line-clamp-2 group-hover:text-[#0C3B2E] dark:group-hover:text-[#34D399] transition-colors">{{ $lp->title }}</a>
                    <p class="text-[13.5px] text-[#5C665E] dark:text-[#97A199] mt-3 leading-relaxed line-clamp-2">{{ $lp->excerpt }}</p>
                    <div class="mt-auto pt-4 border-t border-[#E4E4DA] dark:border-[#262C28] flex items-center gap-2 text-[12px] font-medium text-[#8B958C] dark:text-[#6B756C]">
                        <span>{{ $lp->published_at?->format('M d, Y') }}</span>
                        <span class="w-1 h-1 bg-[#F5C445]"></span>
                        <span>{{ $lp->reading_time }} min read</span>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</section>

{{-- ===================== CTA — write for us band ===================== --}}
<section class="max-w-[1280px] mx-auto px-4 sm:px-6 py-14">
    <div class="bg-[#0C3B2E] text-white">
        <div class="grid lg:grid-cols-2 gap-10 p-8 sm:p-12">
            <div>
                <span class="inline-flex items-center gap-2.5 text-[11px] font-extrabold tracking-[0.22em] uppercase text-[#F5C445]"><span class="w-6 h-[3px] bg-[#F5C445] inline-block"></span> Why Huvanti</span>
                <h2 class="mt-4 text-[30px] sm:text-[40px] leading-[1.05] font-black tracking-tight">Have a story<br>worth telling?</h2>
                <p class="mt-4 text-[15px] leading-relaxed text-white/75 max-w-[460px]">Write for a human editorial team that reviews every piece, pays attention to craft and puts your byline on work you are proud of.</p>
                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <a href="{{ route('register') }}" class="btn btn-accent">
                        Start writing today
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('editorial') }}" class="btn border border-white/30 text-white hover:bg-white hover:text-[#0C3B2E]">
                        Editorial guidelines
                    </a>
                </div>
            </div>
            <div class="grid sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3 gap-px bg-white/15 border border-white/15">
                <div class="bg-[#0C3B2E] p-5">
                    <span class="text-[#F5C445]"><svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></span>
                    <h4 class="text-[14px] font-bold mt-3 text-white">Thoughtfully edited</h4>
                    <p class="text-[13px] text-white/65 mt-1.5 leading-relaxed">Every article is researched and reviewed by our editorial team before it goes live.</p>
                </div>
                <div class="bg-[#0C3B2E] p-5">
                    <span class="text-[#F5C445]"><svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v14"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg></span>
                    <h4 class="text-[14px] font-bold mt-3 text-white">Clean reading</h4>
                    <p class="text-[13px] text-white/65 mt-1.5 leading-relaxed">No pop ups, no clutter, just content that respects your time and attention.</p>
                </div>
                <div class="bg-[#0C3B2E] p-5">
                    <span class="text-[#F5C445]"><svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.57 3.91a2 2 0 0 0 1.66 0l8.57-3.9a1 1 0 0 0 0-1.84z"/><path stroke-linecap="round" stroke-linejoin="round" d="m6.08 9.5-3.5 1.6a1 1 0 0 0 0 1.81l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9a1 1 0 0 0 0-1.83l-3.5-1.59"/><path stroke-linecap="round" stroke-linejoin="round" d="m6.08 14.5-3.5 1.6a1 1 0 0 0 0 1.81l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9a1 1 0 0 0 0-1.83l-3.5-1.59"/></svg></span>
                    <h4 class="text-[14px] font-bold mt-3 text-white">Multi niche, unified</h4>
                    <p class="text-[13px] text-white/65 mt-1.5 leading-relaxed">Technology, health, finance, travel, lifestyle and education, all in one place.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===================== CATEGORIES — editorial index ===================== --}}
<section id="categories" class="max-w-[1280px] mx-auto px-4 sm:px-6 pb-16">
    <div class="flex items-end justify-between gap-6 flex-wrap">
        <div>
            <span class="kicker"><b>02</b> Sections</span>
            <h2 class="mt-3 text-[32px] sm:text-[42px] font-black text-[#141A16] dark:text-[#F0F2EB] leading-none">Browse by topic</h2>
        </div>
    </div>

    <div class="mt-8 border-b border-[#E4E4DA] dark:border-[#262C28]">
        @foreach($categories as $cat)
            <a href="{{ route('category.show',$cat->slug) }}" class="index-row group grid grid-cols-[44px_56px_1fr_32px] sm:grid-cols-[64px_72px_1fr_auto_40px] items-center gap-3 sm:gap-5 py-5 px-2 sm:px-4">
                <span class="text-[22px] sm:text-[26px] font-black text-[#D8D8CC] dark:text-[#3A443D] group-hover:text-[#F5C445] transition-colors select-none tabular-nums">0{{ $loop->index + 1 }}</span>
                <span class="chip w-12 h-12 sm:w-14 sm:h-14 group-hover:border-[#0C3B2E] dark:group-hover:border-[#34D399] transition-colors">
                    @include('partials.category-icon', ['category' => $cat, 'class' => 'w-6 h-6'])
                </span>
                <span class="min-w-0">
                    <span class="block text-[18px] sm:text-[20px] font-bold text-[#141A16] dark:text-[#F0F2EB] group-hover:translate-x-1 transition-transform">{{ $cat->name }}</span>
                    <span class="hidden sm:block text-[13px] text-[#8B958C] dark:text-[#6B756C] mt-0.5 line-clamp-1 max-w-[560px]">{{ $cat->description }}</span>
                </span>
                <span class="hidden sm:inline-flex text-[12px] font-bold text-[#8B958C] dark:text-[#6B756C] tabular-nums">{{ $cat->posts_count }} {{ Str::plural('article', $cat->posts_count) }}</span>
                <span class="text-[#141A16] dark:text-[#F0F2EB] justify-self-end transition-transform group-hover:translate-x-1.5">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            </a>
        @endforeach
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
