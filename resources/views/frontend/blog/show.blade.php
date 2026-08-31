@extends('layouts.app')
@section('content')
@php
    $shareUrl = urlencode(request()->getSchemeAndHttpHost() . '/blog/' . $post->slug);
    $shareText = urlencode($post->title);
    $authorName = $post->user->name ?? $post->author_name ?? 'Huvanti Team';
    $authorBio = $post->user->bio ?? $post->author_bio ?? 'Editor at Huvanti';
    $authorAvatar = $post->user?->author_avatar_path ? storage_image_url($post->user->author_avatar_path) : ($post->author_avatar ?: 'https://i.pravatar.cc/100?img=15');
    $topComments = $post->approvedComments->whereNull('parent_id');
    // Author profile URL: clicking the author's name or photo (byline above
    // the content AND the author box below it) opens their public profile,
    // where visitors can follow / unfollow them.
    $authorProfileUrl = $post->user?->username ? route('author.profile', $post->user->username) : null;
    // Featured image MUST go through storage_image_url(): the DB stores
    // "uploads/posts/x.webp" and printing that raw inside src="..." makes the
    // browser request /blog/uploads/posts/x.webp → 404 → the image never
    // shows. Alt text comes from the image's own file name (falls back to
    // the post title when the name is meaningless like "IMG_2043").
    $featuredImageUrl = storage_image_url($post->featured_image) ?: 'https://picsum.photos/seed/' . $post->slug . '/1200/700';
    $featuredImageAlt = image_alt_text($post->featured_image, $post->title);
    // og:image / twitter:image for social scrapers — absolute URL required.
    // Legacy rows may store a full http URL as featured_image — only prefix
    // the host for root-relative paths.
    $ogImage = str_starts_with($featuredImageUrl, 'http') ? $featuredImageUrl : request()->getSchemeAndHttpHost() . $featuredImageUrl;
@endphp
@php
    // ---- JSON-LD payload (computed here, printed below) ----
    // Strings are json_encode'd ONCE in PHP and echoed with {!! !!} so
    // quotes survive inside the script tag ({{ }} would HTML-escape them).
    $ldStr    = 'Illuminate\Support\Str';
    $ldSite   = request()->getSchemeAndHttpHost();
    $ldUrl    = $ldSite . '/blog/' . $post->slug;
    $ldImage  = $post->featured_image
        ? (str_starts_with((string) storage_image_url($post->featured_image), 'http')
            ? storage_image_url($post->featured_image)
            : $ldSite . storage_image_url($post->featured_image))
        : $ldSite . asset('images/og-huvanti.jpg');
    $ldTitle  = $ldStr::limit(strip_tags($post->title), 110);
    $ldDesc   = $post->excerpt ? $ldStr::limit(strip_tags($post->excerpt), 160) : null;
    $ldArticle = [
        '@type' => 'Article',
        'headline' => $ldTitle,
        'image' => [$ldImage],
        'dateModified' => $post->updated_at->toIso8601String(),
        'author' => array_filter([
            '@type' => 'Person',
            'name' => $authorName,
            'url' => $authorProfileUrl,
        ]),
        'publisher' => ['@type' => 'Organization', 'name' => setting('site_name', 'Huvanti')],
        'mainEntityOfPage' => $ldUrl,
    ];
    if ($ldDesc) { $ldArticle['description'] = $ldDesc; }
    if ($post->published_at) { $ldArticle['datePublished'] = $post->published_at->toIso8601String(); }

    $ldGraph = [
        $ldArticle,
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $ldSite . '/'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => $ldSite . '/blog'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $ldTitle],
            ],
        ],
    ];
    if ($post->faqs->count() > 0) {
        $ldGraph[] = [
            '@type' => 'FAQPage',
            'mainEntity' => $post->faqs->map(function ($f) use ($ldStr) {
                return [
                    '@type' => 'Question',
                    'name' => $ldStr::limit(strip_tags($f->question), 120),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $ldStr::limit(strip_tags($f->answer), 400),
                    ],
                ];
            })->values()->all(),
        ];
    }
    $ldJson = json_encode(
        ['@context' => 'https://schema.org', '@graph' => $ldGraph],
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
    );
@endphp
@push('head')
{{-- Article + BreadcrumbList + FAQPage structured data for rich results --}}
<script type="application/ld+json">{!! $ldJson !!}</script>
{{-- LCP preload: the featured image is the largest contentful paint on
     mobile — hinting the browser shaves a full round trip off first load
     (Ahrefs "Slow page" finding). --}}
<link rel="preload" as="image" href="{{ $ogImage }}" fetchpriority="high">
@endpush
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 py-6">
    <nav class="text-[11px] font-extrabold tracking-[0.16em] uppercase text-[#8B958C] dark:text-[#6B756C] flex items-center gap-2 flex-wrap mb-6" aria-label="Breadcrumb">
        <a href="/" class="hover:text-[#141A16] dark:hover:text-white transition">Home</a>
        <span class="text-[#F5C445]">/</span>
        <a href="{{ route('blog.index') }}" class="hover:text-[#141A16] dark:hover:text-white transition">Blog</a>
        @if($post->category)
            <span class="text-[#F5C445]">/</span>
            <a href="{{ route('category.show',$post->category->slug) }}" class="hover:text-[#141A16] dark:hover:text-white transition">{{ $post->category->name }}</a>
        @endif
        <span class="text-[#F5C445]">/</span>
        <span class="text-[#141A16] dark:text-white line-clamp-1">{{ \Illuminate\Support\Str::limit($post->title, 48) }}</span>
    </nav>

    <div class="grid lg:grid-cols-12 gap-10">
        <div class="lg:col-span-8 space-y-8">
            {{-- Article — editorial sheet --}}
            <article class="card-elev">
                <div class="relative h-[240px] sm:h-[380px] overflow-hidden border-b-2 border-[#141A16] dark:border-[#3A443D]">
                    <img src="{{ $featuredImageUrl }}" alt="{{ $featuredImageAlt }}" class="w-full h-full object-cover" decoding="async" fetchpriority="high">
                    <div class="absolute top-0 left-0 flex items-center">
                        @if($post->category)<span class="bg-[#141A16] text-white text-[10px] font-extrabold tracking-[0.2em] uppercase px-3.5 py-2">{{ $post->category->name }}</span>@endif
                        @if($post->is_featured)<span class="bg-[#F5C445] text-[#141A16] text-[10px] font-extrabold tracking-[0.2em] uppercase px-3.5 py-2">Popular</span>@endif
                    </div>
                </div>

                <div class="p-6 sm:p-9">
                    <h1 class="text-[30px] sm:text-[42px] font-black leading-[1.08] tracking-tight text-[#141A16] dark:text-[#F0F2EB]">{{ $post->title }}</h1>
                    @if($post->excerpt)<p class="text-[16px] sm:text-[17px] leading-relaxed text-[#5C665E] dark:text-[#97A199] mt-4 border-l-[3px] border-[#F5C445] pl-4">{{ $post->excerpt }}</p>@endif

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-3 mt-7 py-4 border-y border-[#E4E4DA] dark:border-[#262C28]">
                        @if($authorProfileUrl)<a href="{{ $authorProfileUrl }}" class="flex items-center gap-3 group" aria-label="View author profile">@else<div class="flex items-center gap-3">@endif
                            <img src="{{ $authorAvatar }}" alt="{{ $authorName }}" class="w-10 h-10 object-cover border border-[#E4E4DA] dark:border-[#3A443D] {{ $authorProfileUrl ? 'group-hover:border-[#0C3B2E] dark:group-hover:border-[#34D399] transition' : '' }}" loading="lazy" decoding="async">
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap {{ $authorProfileUrl ? 'group-hover:text-[#0C3B2E] dark:group-hover:text-[#34D399] transition' : '' }}">
                                    <span class="text-[13.5px] font-bold text-[#141A16] dark:text-[#F0F2EB]">{{ $authorName }}</span>
                                    @if($post->user)
                                        @include('partials.country-flag', ['user' => $post->user, 'class' => 'w-4 h-3'])
                                        {!! $post->user->badgeHtml() !!}
                                    @endif
                                </div>
                            </div>
                        @if($authorProfileUrl)</a>@else</div>@endif
                        <div class="flex items-center gap-3 text-[12px] font-medium text-[#8B958C] dark:text-[#6B756C] flex-wrap sm:ml-auto">
                            <span class="inline-flex items-center gap-1.5"><svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v4"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18"/></svg> {{ $post->published_at?->format('M d, Y') }}</span>
                            <span class="text-[#F5C445]">·</span>
                            <span class="inline-flex items-center gap-1.5"><svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg> {{ $post->reading_time }} min read</span>
                            <span class="text-[#F5C445]">·</span>
                            <span class="inline-flex items-center gap-1.5"><svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg> {{ number_format((int) $post->views) }} views</span>
                        </div>
                    </div>

                    {{-- Share: square bordered icons + working copy link --}}
                    <div class="flex flex-wrap items-center gap-2 mt-6">
                        <span class="text-[10px] font-extrabold tracking-[0.2em] uppercase text-[#8B958C] dark:text-[#6B756C] mr-1.5 self-center">Share</span>
                        <a href="https://twitter.com/intent/tweet?text={{ $shareText }}&url={{ $shareUrl }}" target="_blank" rel="noopener" class="w-10 h-10 border border-[#E4E4DA] dark:border-[#3A443D] bg-white dark:bg-[#141815] text-[#3D463F] dark:text-[#C2C9C0] flex items-center justify-center hover:bg-[#141A16] hover:text-white hover:border-[#141A16] dark:hover:bg-[#F0F2EB] dark:hover:text-[#141A16] dark:hover:border-[#F0F2EB] transition" aria-label="Share on X">
                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" class="w-10 h-10 border border-[#E4E4DA] dark:border-[#3A443D] bg-white dark:bg-[#141815] text-[#3D463F] dark:text-[#C2C9C0] flex items-center justify-center hover:bg-[#1877F2] hover:text-white hover:border-[#1877F2] transition" aria-label="Share on Facebook">
                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="https://pinterest.com/pin/create/button/?url={{ $shareUrl }}&description={{ $shareText }}" target="_blank" rel="noopener" class="w-10 h-10 border border-[#E4E4DA] dark:border-[#3A443D] bg-white dark:bg-[#141815] text-[#3D463F] dark:text-[#C2C9C0] flex items-center justify-center hover:bg-[#E60023] hover:text-white hover:border-[#E60023] transition" aria-label="Share on Pinterest">
                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener" class="w-10 h-10 border border-[#E4E4DA] dark:border-[#3A443D] bg-white dark:bg-[#141815] text-[#3D463F] dark:text-[#C2C9C0] flex items-center justify-center hover:bg-[#0A66C2] hover:text-white hover:border-[#0A66C2] transition" aria-label="Share on LinkedIn">
                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.634-1.85 3.364-1.85 3.604 0 4.268 2.37 4.268 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.777 13.019H3.56V9h3.554v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.454C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                        <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener" class="w-10 h-10 border border-[#E4E4DA] dark:border-[#3A443D] bg-white dark:bg-[#141815] text-[#3D463F] dark:text-[#C2C9C0] flex items-center justify-center hover:bg-[#25D366] hover:text-white hover:border-[#25D366] transition" aria-label="Share on WhatsApp">
                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                        <button type="button" id="copy-link-btn" data-url="{{ url()->current() }}" class="w-10 h-10 border border-[#E4E4DA] dark:border-[#3A443D] bg-white dark:bg-[#141815] text-[#3D463F] dark:text-[#C2C9C0] flex items-center justify-center hover:bg-[#0C3B2E] hover:text-white hover:border-[#0C3B2E] dark:hover:bg-[#34D399] dark:hover:text-[#141A16] dark:hover:border-[#34D399] transition" aria-label="Copy link" title="Copy link">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                        </button>
                    </div>

                    @if(count($toc) > 0)
                        {{-- Collapsible Table of Contents — same native
                             <details> pattern as the FAQ accordion below:
                             tap the header (or the chevron icon) to hide or
                             show the list. Open by default. --}}
                        <details class="mt-7 bg-[#F5C445]/10 dark:bg-[#F5C445]/5 border border-[#E4E4DA] dark:border-[#262C28] border-l-4 border-l-[#F5C445] group" open>
                            <summary class="flex items-center justify-between p-4 cursor-pointer list-none select-none [&::-webkit-details-marker]:hidden hover:bg-[#F5C445]/15 dark:hover:bg-[#F5C445]/10 transition">
                                <span class="text-[12px] font-extrabold tracking-[0.16em] uppercase text-[#141A16] dark:text-[#F0F2EB] flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#0C3B2E] dark:text-[#34D399] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 18h16"/></svg> Table of Contents
                                </span>
                                <span class="w-7 h-7 bg-white dark:bg-[#141815] border border-[#E4E4DA] dark:border-[#3A443D] flex items-center justify-center shrink-0 transition-transform duration-200 group-open:rotate-180">
                                    <svg class="w-4 h-4 text-[#3D463F] dark:text-[#C2C9C0] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </summary>
                            <ol class="px-4 pb-4 pt-3 border-t border-[#E4E4DA] dark:border-[#262C28] space-y-1.5 list-decimal list-inside text-[13.5px] font-medium text-[#3D463F] dark:text-[#C2C9C0]">
                                @foreach($toc as $item)<li><a href="#{{ $item['id'] }}" class="hover:text-[#0C3B2E] dark:hover:text-[#34D399] hover:underline underline-offset-4">{{ $item['title'] }}</a></li>@endforeach
                            </ol>
                        </details>
                    @endif

                    @if($post->is_affiliate)
                        <div class="mt-7 bg-[#F5C445] text-[#141A16] border-2 border-[#141A16] p-4 shadow-[5px_5px_0_0_#141A16]">
                            <div class="flex items-start gap-3">
                                <span class="w-9 h-9 bg-[#141A16] text-[#F5C445] flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m15 9-6 6"/><circle cx="9.5" cy="9.5" r=".5" fill="currentColor"/><circle cx="14.5" cy="14.5" r=".5" fill="currentColor"/></svg>
                                </span>
                                <div class="text-[13.5px] leading-relaxed font-medium">
                                    <strong class="font-extrabold">Affiliate disclosure:</strong> Some links on this page are affiliate links. If you buy through them, we may earn a small commission at no extra cost to you. Read our <a href="{{ route('editorial') }}" class="underline font-extrabold underline-offset-4">full disclosure</a>.
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="prose dark:prose-invert max-w-none mt-6">{!! $contentWithAnchors !!}</div>

                    {{-- Tags (meta keywords shown as chips) --}}
                    @php
                        $tags = array_values(array_filter(array_map('trim', explode(',', (string) $post->meta_keywords)), fn ($t) => $t !== ''));
                        $tags = array_slice($tags, 0, 8);
                    @endphp
                    @if(count($tags) > 0)
                        <div class="flex items-center gap-2 flex-wrap mt-7 pt-6 border-t border-[#E4E4DA] dark:border-[#262C28]">
                            <span class="text-[10px] font-extrabold text-[#8B958C] dark:text-[#6B756C] uppercase tracking-[0.2em]">Tags</span>
                            @foreach($tags as $tag)
                                <a href="{{ route('search', ['q' => $tag]) }}" class="text-[12px] font-bold border border-[#E4E4DA] dark:border-[#3A443D] text-[#3D463F] dark:text-[#C2C9C0] px-3 py-1.5 hover:bg-[#F5C445] hover:border-[#F5C445] hover:text-[#141A16] transition"># {{ $tag }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </article>

            {{-- FAQ --}}
            @if($post->faqs->count() > 0)
            <div class="card-elev p-6 sm:p-8">
                <span class="kicker"><b>FAQ</b> Questions</span>
                <h3 class="text-[22px] font-black text-[#141A16] dark:text-[#F0F2EB] mt-3">Frequently asked questions</h3>
                <div class="space-y-2 mt-5" id="faq-accordion">
                    @foreach($post->faqs as $faq)
                        <details class="group border border-[#E4E4DA] dark:border-[#262C28] bg-white dark:bg-[#141815]">
                            <summary class="flex items-center justify-between p-4 cursor-pointer list-none hover:bg-[#F5C445]/10 dark:hover:bg-[#F5C445]/5 transition select-none [&::-webkit-details-marker]:hidden">
                                <span class="text-[14px] font-bold text-[#141A16] dark:text-[#F0F2EB] pr-4">{{ $faq->question }}</span>
                                <span class="w-7 h-7 bg-[#F5C445] text-[#141A16] flex items-center justify-center shrink-0 transition-transform group-open:rotate-180"><svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg></span>
                            </summary>
                            <div class="px-4 pb-4 text-[13.5px] leading-relaxed text-[#5C665E] dark:text-[#97A199] border-t border-[#E4E4DA] dark:border-[#262C28] pt-3">{{ $faq->answer }}</div>
                        </details>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Was this helpful? Like / Dislike reactions, shown right
                 after the content + FAQ. Clicking the active button removes
                 your reaction; the other button switches it. --}}
<div class="card-elev py-9 px-6 text-center">
    <span class="kicker justify-center">Your verdict</span>
    <h3 class="text-[20px] font-black text-[#141A16] dark:text-[#F0F2EB] mt-3">Was this story helpful?</h3>
    <p class="text-[13px] text-[#8B958C] dark:text-[#6B756C] mt-1.5">Your feedback helps other readers.</p>
    <div class="flex items-center justify-center gap-3 mt-6">
        @auth
            <form method="POST" action="{{ route('blog.react', $post->slug) }}" class="inline">
                @csrf
                <input type="hidden" name="reaction" value="like">
                <button type="submit" title="{{ $myReaction === 'like' ? 'Remove your like' : 'Like this post' }}" class="inline-flex items-center gap-2 h-11 px-5 text-sm font-bold transition cursor-pointer border {{ $myReaction === 'like' ? 'bg-emerald-600 border-emerald-600 text-white shadow-sm' : 'bg-white dark:bg-[#141815] border-[#D8D8CC] dark:border-[#3A443D] text-[#3D463F] dark:text-[#C2C9C0] hover:border-[#0C3B2E] hover:text-[#0C3B2E] dark:hover:border-[#34D399] dark:hover:text-[#34D399]' }}">
                    <svg class="w-[18px] h-[18px]" fill="{{ $myReaction === 'like' ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="{{ $myReaction === 'like' ? '0' : '2' }}" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"/></svg>
                    <span class="ml-1 px-2 py-0.5 text-xs font-bold {{ $myReaction === 'like' ? 'bg-white/20 text-white' : 'bg-emerald-50 dark:bg-emerald-400/10 text-emerald-700 dark:text-emerald-300' }}">{{ number_format($likesCount) }}</span>
                </button>
            </form>
            <form method="POST" action="{{ route('blog.react', $post->slug) }}" class="inline">
                @csrf
                <input type="hidden" name="reaction" value="dislike">
                <button type="submit" title="{{ $myReaction === 'dislike' ? 'Remove your dislike' : 'Dislike this post' }}" class="inline-flex items-center gap-2 h-11 px-5 text-sm font-bold transition cursor-pointer border {{ $myReaction === 'dislike' ? 'bg-rose-600 border-rose-600 text-white shadow-sm' : 'bg-white dark:bg-[#141815] border-[#D8D8CC] dark:border-[#3A443D] text-[#3D463F] dark:text-[#C2C9C0] hover:border-[#E11D48] hover:text-[#E11D48] dark:hover:border-[#FB7185] dark:hover:text-[#FB7185]' }}">
                    <svg class="w-[18px] h-[18px]" fill="{{ $myReaction === 'dislike' ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="{{ $myReaction === 'dislike' ? '0' : '2' }}" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M17 14V2"/><path d="M9 18.12 10 14H4.17a2 2 0 0 1-1.92-2.56l2.33-8A2 2 0 0 1 6.5 2H20a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2.76a2 2 0 0 0-1.79 1.11L12 22a3.13 3.13 0 0 1-3-3.88Z"/></svg>
                    <span class="ml-1 px-2 py-0.5 text-xs font-bold {{ $myReaction === 'dislike' ? 'bg-white/20 text-white' : 'bg-rose-50 dark:bg-rose-400/10 text-rose-600 dark:text-rose-300' }}">{{ number_format($dislikesCount) }}</span>
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" title="Sign in to react" class="inline-flex items-center gap-2 h-11 px-5 text-sm font-bold transition border bg-white dark:bg-[#141815] border-[#D8D8CC] dark:border-[#3A443D] text-[#3D463F] dark:text-[#C2C9C0] hover:border-[#0C3B2E] hover:text-[#0C3B2E] dark:hover:border-[#34D399] dark:hover:text-[#34D399] transition">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"/></svg>
                <span class="ml-1 px-2 py-0.5 text-xs font-bold bg-emerald-50 dark:bg-emerald-400/10 text-emerald-700 dark:text-emerald-300">{{ number_format($likesCount) }}</span>
            </a>
            <a href="{{ route('login') }}" title="Sign in to react" class="inline-flex items-center gap-2 h-11 px-5 text-sm font-bold transition border bg-white dark:bg-[#141815] border-[#D8D8CC] dark:border-[#3A443D] text-[#3D463F] dark:text-[#C2C9C0] hover:border-[#E11D48] hover:text-[#E11D48] dark:hover:border-[#FB7185] dark:hover:text-[#FB7185] transition">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M17 14V2"/><path d="M9 18.12 10 14H4.17a2 2 0 0 1-1.92-2.56l2.33-8A2 2 0 0 1 6.5 2H20a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2.76a2 2 0 0 0-1.79 1.11L12 22a3.13 3.13 0 0 1-3-3.88Z"/></svg>
                <span class="ml-1 px-2 py-0.5 text-xs font-bold bg-rose-50 dark:bg-rose-400/10 text-rose-600 dark:text-rose-300">{{ number_format($dislikesCount) }}</span>
            </a>
        @endauth
    </div>
</div>

{{-- Author bio separate container (user profile driven) --}}
            @php
                $author = $post->user;
                $authorUsername = $author?->username;
                $authorVerified = $author?->is_verified;
                $authorSocials = $author ? $author->socialProfiles() : [];
            @endphp
            <div class="card-elev p-6 sm:p-8">
                <span class="kicker"><b>Bio</b> The author</span>
                <h3 class="text-[22px] font-black text-[#141A16] dark:text-[#F0F2EB] mt-3">About the author</h3>
                <div class="flex gap-5 mt-5">
                    @if($authorProfileUrl)<a href="{{ $authorProfileUrl }}" class="shrink-0 group" aria-label="View author profile">@endif
                        <img src="{{ $authorAvatar }}" alt="{{ $authorName }}" class="w-16 h-16 object-cover border-2 border-[#141A16] dark:border-[#3A443D] shadow-[4px_4px_0_0_#F5C445] {{ $authorProfileUrl ? 'group-hover:shadow-[4px_4px_0_0_#0C3B2E] transition-shadow' : '' }}" loading="lazy" decoding="async">
                    @if($authorProfileUrl)</a>@endif
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @if($authorUsername)
                                <a href="{{ route('author.profile', $authorUsername) }}" class="text-[15px] font-extrabold text-[#141A16] dark:text-[#F0F2EB] hover:text-[#0C3B2E] dark:hover:text-[#34D399] transition-colors">{{ $authorName }}</a>
                            @else
                                <span class="text-[15px] font-extrabold text-[#141A16] dark:text-[#F0F2EB]">{{ $authorName }}</span>
                            @endif
                            {{-- Achievement badge: purple for admins, green at 10+ posts, yellow at 100+ --}}
                            @if($post->user)
                                {!! $post->user->badgeHtml() !!}
                            @endif
                            @if($author?->role_title)
                                <span class="text-[12px] text-[#8B958C] dark:text-[#6B756C]">· {{ $author->role_title }}</span>
                            @endif
                        </div>
                        <div class="text-[13.5px] text-[#5C665E] dark:text-[#97A199] mt-1.5 leading-relaxed">{{ $authorBio }}</div>
                        <div class="flex items-center gap-2 mt-3 flex-wrap">
                            @if($authorProfileUrl)
                                <a href="{{ $authorProfileUrl }}" class="inline-flex items-center gap-1.5 h-9 px-4 text-[11px] font-extrabold uppercase tracking-wide bg-[#141A16] hover:bg-[#0C3B2E] text-white transition">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                                    View profile
                                </a>
                            @endif
                            {{-- Follow / unfollow straight from the post --}}
                            @if(auth()->check() && $post->user && auth()->id() !== $post->user->id)
                                <form method="POST" action="{{ route('author.follow', $post->user->username) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 h-9 px-4 text-xs font-semibold border transition cursor-pointer {{ $followingAuthor ? 'bg-[#EFEFE8] dark:bg-[#1E2420] text-[#3D463F] dark:text-[#C2C9C0] border-[#D8D8CC] dark:border-[#3A443D]' : 'bg-white dark:bg-[#141815] text-[#0C3B2E] dark:text-[#34D399] border-[#0C3B2E] dark:border-[#34D399] hover:bg-[#F5C445] hover:border-[#F5C445] hover:text-[#141A16]' }}">
                                        <svg class="w-3.5 h-3.5" fill="{{ $followingAuthor ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/></svg>
                                        {{ $followingAuthor ? 'Following' : 'Follow' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                        @if(count($authorSocials) > 0)
                        <div class="flex items-center gap-2 mt-3">
                            @foreach($authorSocials as $s)
                                <a href="{{ $s['url'] }}" target="_blank" rel="noopener nofollow" class="w-7 h-7 flex items-center justify-center border border-[#D8D8CC] dark:border-[#3A443D] bg-white dark:bg-[#141815] hover:bg-[#0C3B2E] dark:hover:bg-[#34D399] text-[#3D463F] dark:text-[#C2C9C0] hover:text-white dark:hover:text-[#141A16] hover:border-[#0C3B2E] dark:hover:border-[#34D399] transition" aria-label="{{ $s['label'] }}">
                                    @include('partials.social-icon', ['platform' => $s['platform'], 'class' => 'w-3.5 h-3.5'])
                                </a>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Comments separate container (new icon + reply system) --}}
            <div class="card-elev p-6 sm:p-8">
                <span class="kicker"><b>Q&amp;A</b> Readers</span>
                <h3 class="text-[22px] font-black text-[#141A16] dark:text-[#F0F2EB] mt-3 flex items-center gap-2">
                    Comments <span class="text-[13px] font-bold text-[#8B958C] dark:text-[#6B756C]">({{ $topComments->count() }})</span>
                </h3>
                <p class="text-[12.5px] text-[#8B958C] dark:text-[#6B756C] mt-2">Your email stays private. Comments are moderated.</p>
                @if(session('success'))<div class="mt-3 bg-emerald-50 dark:bg-emerald-400/10 border border-emerald-200 dark:border-emerald-400/20 text-emerald-800 dark:text-emerald-300 px-4 py-3 text-sm">{{ session('success') }}</div>@endif

                {{-- Comment form --}}
                <form action="{{ route('blog.comment.store',$post->slug) }}" method="POST" class="mt-5 bg-[#FAFAF7] dark:bg-[#0D100E] border border-[#E4E4DA] dark:border-[#262C28] p-5" id="comment-form">
                    @csrf
                    <input type="hidden" name="parent_id" id="reply-parent-id" value="">
                    <div id="replying-to" class="hidden mb-3 text-xs flex items-center gap-2 bg-emerald-50 dark:bg-emerald-400/10 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-400/20 px-3 py-2">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m15 10 5 5-5 5"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v7a4 4 0 0 0 4 4h12"/></svg>
                        Replying to <b id="replying-to-name" class="font-semibold"></b>
                        <button type="button" onclick="cancelReply()" class="ml-auto" aria-label="Cancel reply">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-3">
                        <div><label class="text-[12px] font-extrabold uppercase tracking-wide text-[#141A16] dark:text-[#EDEFEA]">Name *</label><input type="text" name="name" required value="{{ old('name') }}" class="field mt-1.5 h-10 px-3 text-sm">@error('name')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror</div>
                        <div><label class="text-[12px] font-extrabold uppercase tracking-wide text-[#141A16] dark:text-[#EDEFEA]">Email * <span class="text-[#8B958C] dark:text-[#6B756C] normal-case font-semibold">(private)</span></label><input type="email" name="email" required value="{{ old('email') }}" class="field mt-1.5 h-10 px-3 text-sm">@error('email')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror</div>
                    </div>
                    <div class="mt-3"><label class="text-[12px] font-extrabold uppercase tracking-wide text-[#141A16] dark:text-[#EDEFEA]">Comment *</label><textarea name="content" required rows="4" class="field mt-1.5 px-3 py-2.5 text-sm" placeholder="Share your thoughts...">{{ old('content') }}</textarea>@error('content')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror</div>
                    <button type="submit" class="btn btn-primary btn-sm mt-4">Post comment</button>
                </form>

                {{-- Threaded comments --}}
                <div class="mt-6 space-y-3">
                    @forelse($topComments as $c)
                        <div class="bg-[#FAFAF7] dark:bg-[#0D100E] border border-[#E4E4DA] dark:border-[#262C28] p-5">
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 bg-[#141A16] dark:bg-[#F5C445] text-white dark:text-[#141A16] flex items-center justify-center text-sm font-black">{{ strtoupper(substr($c->name,0,1)) }}</span>
                                <div><div class="text-[13.5px] font-bold text-[#141A16] dark:text-[#F0F2EB]">{{ $c->name }}</div><div class="text-[11.5px] text-[#8B958C] dark:text-[#6B756C]">{{ $c->created_at->format('M d, Y') }}</div></div>
                                <button type="button" onclick="replyTo({{ $c->id }}, '{{ addslashes($c->name) }}')" class="ml-auto text-[11px] font-extrabold uppercase tracking-wide text-[#0C3B2E] dark:text-[#34D399] hover:underline underline-offset-4 inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m15 10 5 5-5 5"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v7a4 4 0 0 0 4 4h12"/></svg> Reply
                                </button>
                            </div>
                            <p class="text-[13.5px] text-[#3D463F] dark:text-[#C2C9C0] mt-2.5 leading-relaxed">{{ $c->content }}</p>
                            @php $replies = $post->approvedComments->where('parent_id', $c->id); @endphp
                            @if($replies->count())
                                <div class="mt-3 ml-10 space-y-2 border-l-2 border-[#F5C445] pl-4">
                                    @foreach($replies as $r)
                                        <div class="bg-white dark:bg-[#141815] p-3.5 border border-[#E4E4DA] dark:border-[#262C28]">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-3.5 h-3.5 text-[#8B958C] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m15 10 5 5-5 5"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v7a4 4 0 0 0 4 4h12"/></svg>
                                                <span class="w-6 h-6 bg-[#0C3B2E] text-white flex items-center justify-center text-[11px] font-black">{{ strtoupper(substr($r->name,0,1)) }}</span>
                                                <div><div class="text-[12.5px] font-bold text-[#141A16] dark:text-[#F0F2EB]">{{ $r->name }}</div><div class="text-[11px] text-[#8B958C] dark:text-[#6B756C]">{{ $r->created_at->format('M d, Y') }}</div></div>
                                            </div>
                                            <p class="text-[13px] text-[#3D463F] dark:text-[#C2C9C0] mt-1.5 leading-relaxed">{{ $r->content }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-8 bg-[#FAFAF7] dark:bg-[#0D100E] border border-dashed border-[#D8D8CC] dark:border-[#3A443D]"><p class="text-[13.5px] font-medium text-[#8B958C] dark:text-[#6B756C]">No comments yet. Be the first to share your thoughts!</p></div>
                    @endforelse
                </div>
            </div>
        </div>

        <aside class="lg:col-span-4 space-y-6">
            @if($related->count())
                <div class="card-elev p-5 lg:sticky lg:top-20">
                    <span class="kicker"><b>+</b> Keep reading</span>
                    <h3 class="text-[16px] font-black text-[#141A16] dark:text-[#F0F2EB] mt-2.5 mb-4">Related stories</h3>
                    <div class="space-y-4">
                        @foreach($related as $r)
                            <a href="{{ route('blog.show',$r->slug) }}" class="flex gap-3.5 group">
                                <img src="{{ storage_image_url($r->featured_image) ?: 'https://picsum.photos/seed/'.$r->slug.'/200/200' }}" class="w-16 h-16 object-cover plate shrink-0" alt="{{ image_alt_text($r->featured_image, $r->title) }}" loading="lazy" decoding="async">
                                <div class="min-w-0"><h4 class="text-[13.5px] font-bold text-[#141A16] dark:text-[#F0F2EB] group-hover:text-[#0C3B2E] dark:group-hover:text-[#34D399] line-clamp-2 leading-snug transition-colors">{{ $r->title }}</h4><span class="text-[11.5px] text-[#8B958C] dark:text-[#6B756C] font-medium">{{ $r->reading_time }} min read</span></div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
            {{-- Popular posts: most viewed across the site --}}
            @if($popular->count())
                <div class="card-elev p-5">
                    <span class="kicker"><b>02</b> Most read</span>
                    <h3 class="text-[16px] font-black text-[#141A16] dark:text-[#F0F2EB] mt-2.5 mb-4">Popular on Huvanti</h3>
                    <div class="space-y-4">
                        @foreach($popular as $i => $p)
                            <a href="{{ route('blog.show',$p->slug) }}" class="flex gap-3.5 group">
                                <span class="text-[22px] leading-none font-black text-[#D8D8CC] dark:text-[#3A443D] group-hover:text-[#F5C445] transition-colors shrink-0 select-none tabular-nums w-7">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <div class="min-w-0"><h4 class="text-[13.5px] font-bold text-[#141A16] dark:text-[#F0F2EB] group-hover:text-[#0C3B2E] dark:group-hover:text-[#34D399] line-clamp-2 leading-snug transition-colors">{{ $p->title }}</h4><span class="text-[11.5px] text-[#8B958C] dark:text-[#6B756C] font-medium">{{ number_format($p->views) }} views</span></div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
            {{-- Sidebar categories (live icons) --}}
            <div class="card-elev p-5">
                <span class="kicker"><b>§</b> Topics</span>
                <h3 class="text-[16px] font-black text-[#141A16] dark:text-[#F0F2EB] mt-2.5 mb-3">Categories</h3>
                <div class="divide-y divide-[#E4E4DA] dark:divide-[#262C28] border-y border-[#E4E4DA] dark:border-[#262C28]">
                    {{-- Live categories only (active + has published posts), with
                         published-post counts. Avoids empty category links and
                         the N+1 query the old ->posts->count() caused.
                         Wrapped in try/catch: a DB hiccup must never 500 the
                         whole article page just because a sidebar list failed. --}}
                    @php
                        $sidebarCategories = collect();
                        try {
                            $sidebarCategories = \App\Models\Category::live()->withCount(['posts as published_posts_count' => fn ($q) => $q->published()])->orderBy('sort_order')->get();
                        } catch (\Throwable $e) { $sidebarCategories = collect(); }
                    @endphp
                    @foreach($sidebarCategories as $cat)
                        <a href="{{ route('category.show',$cat->slug) }}" class="flex items-center justify-between py-2.5 group">
                            <span class="flex items-center gap-2.5 text-[13.5px] font-bold text-[#3D463F] dark:text-[#C2C9C0] group-hover:text-[#0C3B2E] dark:group-hover:text-[#34D399] transition-colors">
                                <span class="chip w-8 h-8">
                                    @include('partials.category-icon', ['category' => $cat, 'class' => 'w-4 h-4'])
                                </span>
                                {{ $cat->name }}
                            </span>
                            <span class="text-[11.5px] font-extrabold text-[#8B958C] dark:text-[#6B756C] tabular-nums">{{ $cat->published_posts_count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            {{-- Sidebar ad: renders only when ads are switched on in admin
                 settings AND the ad actually has code. No empty boxes, ever. --}}
            @php
                // Guarded exactly like the controller-side queries: an ad or DB
                // problem collapses the slot invisibly instead of erroring.
                $ad = null;
                try {
                    $ad = setting('ads_enabled') === '1' ? \App\Models\Advertisement::active()->position('sidebar')->first() : null;
                } catch (\Throwable $e) { $ad = null; }
            @endphp
            @if($ad && trim(strip_tags($ad->code ?? '')) !== '')
                <div class="card-elev p-3 ad-slot-wrap"><div class="ad-slot-label text-[10px] font-extrabold tracking-[0.2em] text-[#8B958C] dark:text-[#6B756C] uppercase text-center mb-2">Advertisement</div>{!! $ad->code !!}</div>
            @endif
        </aside>
    </div>
</div>
@push('scripts')
<script>
(function(){
    const acc = document.getElementById('faq-accordion');
    if(acc){
        acc.querySelectorAll('details').forEach(d=>{
            d.addEventListener('toggle', ()=>{
                if(d.open){
                    acc.querySelectorAll('details').forEach(o=>{
                        if(o!==d && o.open) o.removeAttribute('open');
                    });
                }
            });
        });
    }
})();
// Reply system
function replyTo(id, name){
    document.getElementById('reply-parent-id').value = id;
    const box = document.getElementById('replying-to');
    document.getElementById('replying-to-name').textContent = name;
    box.classList.remove('hidden');
    document.getElementById('comment-form').scrollIntoView({behavior:'smooth', block:'center'});
}
function cancelReply(){
    document.getElementById('reply-parent-id').value = '';
    document.getElementById('replying-to').classList.add('hidden');
}
// Hide blank/unfilled ad slots: when an ad network serves nothing (site not
// approved yet, no fill), the slot collapses invisibly instead of showing an
// ugly empty box. The "Advertisement" label itself never counts as content,
// so a labeled but unfilled slot still collapses.
(function(){
    function collapseEmptyAdSlots(){
        document.querySelectorAll('.ad-slot, .ad-slot-wrap').forEach(function(el){
            if (el.dataset.adChecked) return;
            // text WITHOUT the label: an unfilled slot has nothing else
            var text = (el.innerText || '').replace(/advertisement/gi, '').trim();
            var hasContent = text.length > 2;
            var media = el.querySelectorAll('img, iframe, ins');
            var visibleMedia = false;
            media.forEach(function(m){ if (m.getBoundingClientRect().height > 4) visibleMedia = true; });
            // any real sized child content (e.g. ad text served late)
            if (!visibleMedia) {
                el.querySelectorAll('a, span, div').forEach(function(n){
                    var r = n.getBoundingClientRect();
                    if (r.height > 30 && (n.textContent || '').trim().length > 3) visibleMedia = true;
                });
            }
            if (!hasContent && !visibleMedia) {
                el.style.display = 'none';
                el.dataset.adChecked = '1';
            }
        });
    }
    window.addEventListener('load', function(){
        setTimeout(collapseEmptyAdSlots, 800);
        setTimeout(collapseEmptyAdSlots, 2500);
        setTimeout(collapseEmptyAdSlots, 5000);
    });
})();

// Affiliate / outbound click tracking: records clicks on external links so
// authors can see click counts and click rate on their Revenue page.
(function(){
    var postSlug = @json($post->slug);
    if (!postSlug) return;
    document.querySelectorAll('.prose a[href], .ad-in-article a[href], a.ad-link').forEach(function(a){
        a.addEventListener('click', function(){
            try {
                var href = a.getAttribute('href') || '';
                if (!/^https?:\/\//i.test(href)) return;
                var token = document.querySelector('meta[name=csrf-token]');
                fetch('/blog/' + encodeURIComponent(postSlug) + '/click', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token ? token.getAttribute('content') : ''
                    },
                    body: JSON.stringify({ url: href }),
                    keepalive: true
                }).catch(function(){});
            } catch (e) { /* tracking must never break the click */ }
        });
    });
})();

// Copy link (fixed: clipboard API with fallback + feedback)
(function(){
    const btn = document.getElementById('copy-link-btn');
    if(!btn) return;
    const originalTitle = btn.getAttribute('title') || 'Copy link';
    btn.addEventListener('click', async ()=>{
        const url = btn.getAttribute('data-url');
        let ok = false;
        try{
            if(navigator.clipboard && window.isSecureContext){
                await navigator.clipboard.writeText(url);
                ok = true;
            } else { throw new Error('fallback'); }
        }catch(e){
            const ta = document.createElement('textarea');
            ta.value = url;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try{ ok = document.execCommand('copy'); }catch(_){}
            document.body.removeChild(ta);
        }
        const prevBg = btn.style.background;
        btn.setAttribute('title', ok ? 'Copied!' : 'Press Ctrl+C');
        btn.style.background = ok ? '#16a34a' : '';
        setTimeout(()=>{
            btn.setAttribute('title', originalTitle);
            btn.style.background = '';
        }, 1600);
    });
})();
</script>
@endpush
@endsection
