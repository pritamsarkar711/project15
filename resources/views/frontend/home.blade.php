@extends('layouts.app')

@section('content')
@php
    $heroPhrase1 = setting('hero_phrase_1', 'Explore Ideas.');
    $heroPhrase2 = setting('hero_phrase_2', 'Inspire Life.');
    $heroSubtitle = setting('hero_subtitle', 'Tech, health, finance, travel and more — one calm place to read.');
    $heroSearchPlaceholder = setting('hero_search_placeholder', 'Search articles, topics, ideas...');
    $heroImgSetting = setting('hero_person_image');
    $heroImgUrl = $heroImgSetting ? asset('storage/'.$heroImgSetting) : asset('images/hero-person-harry.png');
@endphp
<!-- Hero: solid deep green. Image LEFT in a circular frame (works with any
     image, transparent OR solid-bg), text RIGHT. Short subtitle, no overflow. -->
<section class="relative overflow-hidden bg-[#0C3B2E] dark:bg-[#07231B] text-white">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6">
        <div class="grid lg:grid-cols-2 gap-8 lg:gap-4 items-center py-10 lg:py-14">
            <!-- Left: circular framed image (constrains height + works with any image) -->
            <div class="flex items-center justify-center lg:justify-start relative order-1">
                <div class="relative w-[260px] h-[260px] sm:w-[320px] sm:h-[320px] lg:w-[380px] lg:h-[380px] rounded-full overflow-hidden ring-4 ring-white/10 shadow-[0_18px_36px_rgba(0,0,0,0.35)] bg-white/5">
                    <img src="{{ $heroImgUrl }}" alt="Featured illustration" class="absolute inset-0 w-full h-full object-cover" loading="eager" decoding="async" onerror="this.style.display='none';document.getElementById('hero-fallback').style.display='flex'">
                    <div id="hero-fallback" class="hidden absolute inset-0 items-center justify-center text-white/40 text-sm">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.25a3.375 3.375 0 0 1 4.929 0l2.733 2.733m-5.94-3.06 2.366-2.366a3.375 3.375 0 0 1 4.773 0l4.586 4.586M3 4.5h18M3 9h18M3 19.5h18"/></svg>
                    </div>
                </div>
            </div>

            <!-- Right: text -->
            <div class="order-2 lg:pl-6">
                <h1 class="text-[34px] sm:text-[42px] lg:text-[48px] font-extrabold leading-[1.15] tracking-tight min-h-[2.4em] sm:min-h-[2.2em]">
                    <span id="typing-text" class="typing-text"></span><span class="typing-cursor" aria-hidden="true"></span>
                </h1>
                <p class="mt-4 text-[17px] sm:text-[18px] leading-relaxed text-white/85 max-w-[520px] font-medium">{{ $heroSubtitle }}</p>
                <form action="{{ route('search') }}" method="GET" class="mt-6 max-w-[520px]">
                    <div class="flex items-center bg-white p-1.5 pl-5 shadow-[0_10px_30px_rgba(0,0,0,0.25)]">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ $heroSearchPlaceholder }}" class="flex-1 h-11 bg-transparent text-slate-900 border-0 outline-none text-[15px] placeholder:text-slate-400 min-w-0" aria-label="Search articles">
                        <button type="submit" class="h-11 px-6 sm:px-7 shrink-0 bg-[#0C3B2E] hover:bg-[#072A20] text-white text-sm font-semibold transition">Search</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@php $headerAd = \App\Models\Advertisement::active()->position('header')->first(); @endphp
@if($headerAd && trim(strip_tags($headerAd->code ?? '')) !== '')
<section class="max-w-[1200px] mx-auto px-4 sm:px-6 mt-6">
    <div class="card-elev p-4 text-center">{!! $headerAd->code !!}</div>
</section>
@endif

<!-- Categories (repo pattern: centered cards, large icon, hover lift) -->
<section id="categories" class="max-w-[1200px] mx-auto px-4 sm:px-6 py-12">
    <h2 class="text-[26px] sm:text-[32px] font-bold text-slate-900 dark:text-white tracking-tight">Browse Categories</h2>
    <p class="text-sm text-slate-600 dark:text-slate-400 mt-1.5">Explore topics curated by our editors.</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
        @foreach($categories as $cat)
            <a href="{{ route('category.show',$cat->slug) }}" class="group card-elev p-6 text-center hover:-translate-y-1 hover:shadow-xl transition-all duration-200 flex flex-col items-center">
                <span class="text-[#0C3B2E] dark:text-emerald-300 group-hover:scale-110 transition-transform duration-200">
                    @include('partials.category-icon', ['category' => $cat, 'class' => 'w-10 h-10'])
                </span>
                <h3 class="text-[17px] font-semibold text-slate-900 dark:text-white mt-3">{{ $cat->name }}</h3>
                <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed line-clamp-2 max-w-[280px]">{{ $cat->description }}</p>
                <span class="mt-3 text-xs font-semibold text-[#0C3B2E] dark:text-emerald-300 inline-flex items-center gap-1">Explore
                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </span>
            </a>
        @endforeach
    </div>
</section>

<!-- Latest Posts (generous whitespace, repo grid rhythm) -->
<section class="max-w-[1200px] mx-auto px-4 sm:px-6 pb-14">
    <div class="flex items-center gap-2 mb-7">
        <svg class="w-6 h-6 text-[#0C3B2E] dark:text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m22 7-8.5 5.5a4 4 0 0 1-3 0L2 7"/><rect width="20" height="14" x="2" y="5" rx="2"/></svg>
        <h2 class="text-[26px] sm:text-[32px] font-bold text-slate-900 dark:text-white tracking-tight">Latest Posts</h2>
    </div>

    @if($featuredPosts->count() > 0)
        <div class="grid lg:grid-cols-12 gap-6 mb-8">
            @php $big = $featuredPosts->first(); @endphp
            <a href="{{ route('blog.show',$big->slug) }}" class="lg:col-span-7 group relative overflow-hidden bg-[#0C3B2E] min-h-[360px] flex flex-col justify-end p-8">
                <img src="{{ $big->featured_image ?: 'https://picsum.photos/seed/'.$big->slug.'/900/600' }}" alt="{{ $big->title }}" class="absolute inset-0 w-full h-full object-cover opacity-70 group-hover:opacity-60 transition" loading="lazy" decoding="async">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                <div class="relative">
                    <div class="flex items-center gap-2 mb-3 flex-wrap">
                        <span class="text-xs font-semibold bg-white text-slate-900 px-2.5 py-1">{{ $big->category->name ?? 'Featured' }}</span>
                        <span class="text-xs text-white/85">{{ $big->published_at?->format('M d, Y') }} <span class="w-1 h-1 bg-white/50 inline-block mx-1 align-middle"></span> {{ $big->reading_time }} min read</span>
                    </div>
                    <h3 class="text-[24px] font-bold leading-tight text-white">{{ $big->title }}</h3>
                    <p class="text-sm text-white/80 mt-2.5 line-clamp-2 max-w-[560px]">{{ $big->excerpt }}</p>
                </div>
            </a>
            <div class="lg:col-span-5 grid gap-6">
                @foreach($featuredPosts->skip(1)->take(2) as $fp)
                    <a href="{{ route('blog.show',$fp->slug) }}" class="group card-elev p-5 flex gap-4 items-center hover:-translate-y-1 hover:shadow-xl transition-all duration-200">
                        <img src="{{ $fp->featured_image ?: 'https://picsum.photos/seed/'.$fp->slug.'/400/300' }}" class="w-[120px] h-[100px] object-cover shrink-0" alt="{{ $fp->title }}" loading="lazy" decoding="async">
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-semibold text-[#0C3B2E] dark:text-emerald-300 uppercase tracking-wide">{{ $fp->category->name ?? 'Story' }}</span>
                            <h4 class="text-[15px] font-semibold text-slate-900 dark:text-white leading-snug mt-1.5 line-clamp-2 group-hover:text-[#0C3B2E] dark:group-hover:text-emerald-300">{{ $fp->title }}</h4>
                            <span class="text-xs text-slate-500 dark:text-slate-400 mt-auto pt-2">{{ $fp->published_at?->format('M d') }} <span class="w-1 h-1 bg-slate-300 dark:bg-slate-600 inline-block mx-1 align-middle"></span> {{ $fp->reading_time }} min read</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
        @foreach($latestPosts as $lp)
            <article class="group card-elev overflow-hidden hover:-translate-y-1 hover:shadow-xl transition-all duration-200 flex flex-col">
                <a href="{{ route('blog.show',$lp->slug) }}" class="relative h-[190px] overflow-hidden block">
                    <img src="{{ $lp->featured_image ?: 'https://picsum.photos/seed/'.$lp->slug.'/600/400' }}" alt="{{ $lp->title }}" class="w-full h-full object-cover group-hover:scale-[1.03] transition duration-300" loading="lazy" decoding="async">
                    @if($lp->is_featured)<span class="absolute top-2.5 right-2.5 text-xs font-bold bg-[#F5C445] text-slate-900 px-2.5 py-1">Popular</span>@endif
                </a>
                <div class="p-5 flex flex-col flex-1">
                    <span class="text-xs font-semibold text-[#0C3B2E] dark:text-emerald-300 uppercase tracking-wide">{{ $lp->category->name ?? 'General' }}</span>
                    <a href="{{ route('blog.show',$lp->slug) }}" class="mt-2.5 text-[17px] font-semibold text-slate-900 dark:text-white leading-snug line-clamp-2 group-hover:text-[#0C3B2E] dark:group-hover:text-emerald-300">{{ $lp->title }}</a>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-3 leading-relaxed line-clamp-2">{{ $lp->excerpt }}</p>
                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-[#2f2f2f] flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                        <span>{{ $lp->published_at?->format('M d, Y') }}</span>
                        <span class="w-1 h-1 bg-slate-300 dark:bg-slate-600"></span>
                        <span>{{ $lp->reading_time }} min read</span>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <div class="text-center mt-10">
        <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 h-11 px-7 bg-[#0C3B2E] hover:bg-[#072A20] text-white text-sm font-semibold transition shadow">
            Browse all posts
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>
</section>

<!-- Why Huvanti (repo pattern: tinted band, 3-up grid) -->
<section class="max-w-[1200px] mx-auto px-4 sm:px-6 pb-16">
    <div class="bg-emerald-50 dark:bg-[#1e2b24] p-6 sm:p-8">
        <h3 class="text-[22px] font-bold text-slate-900 dark:text-white tracking-tight">Why Huvanti?</h3>
        <div class="grid sm:grid-cols-3 gap-6 mt-5">
            <div class="card-elev p-5 hover:shadow-lg transition">
                <span class="text-[#0C3B2E] dark:text-emerald-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                </span>
                <h4 class="text-sm font-semibold text-slate-900 dark:text-white mt-3">Thoughtfully edited</h4>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed">Every article is researched and reviewed by our editorial team before it goes live.</p>
            </div>
            <div class="card-elev p-5 hover:shadow-lg transition">
                <span class="text-[#0C3B2E] dark:text-emerald-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v14"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/></svg>
                </span>
                <h4 class="text-sm font-semibold text-slate-900 dark:text-white mt-3">Clean reading</h4>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed">No pop ups, no clutter, just content that respects your time and attention.</p>
            </div>
            <div class="card-elev p-5 hover:shadow-lg transition">
                <span class="text-[#0C3B2E] dark:text-emerald-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.57 3.91a2 2 0 0 0 1.66 0l8.57-3.9a1 1 0 0 0 0-1.84z"/><path stroke-linecap="round" stroke-linejoin="round" d="m6.08 9.5-3.5 1.6a1 1 0 0 0 0 1.81l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9a1 1 0 0 0 0-1.83l-3.5-1.59"/><path stroke-linecap="round" stroke-linejoin="round" d="m6.08 14.5-3.5 1.6a1 1 0 0 0 0 1.81l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9a1 1 0 0 0 0-1.83l-3.5-1.59"/></svg>
                </span>
                <h4 class="text-sm font-semibold text-slate-900 dark:text-white mt-3">Multi niche, unified</h4>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed">Technology, health, finance, travel, lifestyle and education, all in one place.</p>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
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
