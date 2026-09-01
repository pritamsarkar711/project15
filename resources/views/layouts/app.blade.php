<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Anti-flash: apply saved theme BEFORE any CSS paints --}}
    <script>
        (function(){
            try{
                var t = localStorage.getItem('huvanti-theme');
                if(t === 'dark'){ document.documentElement.classList.add('dark'); }
            }catch(e){}
        })();
    </script>
    @php
        // Central SEO finalizers: posts render their dashboard-authored
        // title/description EXACTLY (owner requirement); every other page is
        // padded when too short so Ahrefs' "Title too short" (13 pages) and
        // "Meta description too short" (4 pages) stay fixed at the source.
        $seoIsPost = isset($post);
        $seoResolvedTitle = $metaTitle ?? ($post->meta_title ?? ($post->title ?? ($page->meta_title ?? ($page->title ?? (setting('site_name','huvanti.com') . ' · ' . setting('site_tagline','Explore Ideas. Inspire Life.'))))));
        $seoResolvedDescription = $metaDescription ?? ($post->meta_description ?? ($page->meta_description ?? setting('site_description','Huvanti is a multi niche blog covering technology, health, finance, travel, lifestyle and education.')));
        $seoFinalTitle = \App\Support\Seo::finalizeTitle($seoResolvedTitle, $seoIsPost);
        $seoFinalDescription = \App\Support\Seo::finalizeDescription($seoResolvedDescription, $seoIsPost);
    @endphp
    <title>{{ $seoFinalTitle }}</title>
    <meta name="description" content="{{ $seoFinalDescription }}">
    <meta name="robots" content="{{ $robots ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' }}">
    @php
        // Canonical + og:url: build an ABSOLUTE url from the request's own
        // scheme/host (config('app.url') may still be a localhost default on
        // some deploys, and the root-relative URL generator would emit
        // "/path" which is invalid for canonical/og:url). All query params
        // are stripped EXCEPT ?page=N so paginated lists canonicalize
        // correctly; search/filter query strings (?q=, ?category=) collapse
        // to the clean URL, which kills duplicate-content index bloat.
        $seoHost = request()->getSchemeAndHttpHost();
        if (str_contains($seoHost, 'localhost')) {
            $cfg = rtrim((string) config('app.url', ''), '/');
            if ($cfg !== '' && !str_contains($cfg, 'localhost')) { $seoHost = $cfg; }
        }
        $seoPath = request()->getPathInfo();
        $seoPage = (int) request()->query('page', 1);
        $seoCanonical = rtrim($seoHost, '/') . $seoPath . ($seoPage > 1 ? '?page=' . $seoPage : '');
        // Open Graph fallbacks mirror the title/description chain so
        // every page shares something sensible about itself.
        $seoOgTitle = $seoFinalTitle;
        $seoOgDescription = $seoFinalDescription;
    @endphp
    <link rel="canonical" href="{{ $seoCanonical }}">
    <meta property="og:title" content="{{ $seoOgTitle }}">
    <meta property="og:description" content="{{ $seoOgDescription }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:type" content="{{ isset($post) ? 'article' : 'website' }}">
    <meta property="og:site_name" content="{{ setting('site_name','huvanti.com') }}">
    @php
        // og:image / twitter:image: prefer an explicitly passed $ogImage,
        // then the current post's featured image, then the site default.
        // Bug this fixes: posts WITH a featured image still advertised the
        // generic og-huvanti.jpg to social scrapers and Google Discover,
        // because nothing ever bridged $post->featured_image into the meta
        // tags. The URL is made absolute (required by scrapers) and goes
        // through storage_image_url() so legacy/absolute paths also work.
        $seoOgImage = $ogImage
            ?? ((isset($post) && !empty($post->featured_image))
                ? (str_starts_with((string) storage_image_url($post->featured_image), 'http')
                    ? storage_image_url($post->featured_image)
                    : request()->getSchemeAndHttpHost() . storage_image_url($post->featured_image))
                : request()->getSchemeAndHttpHost() . asset('images/og-huvanti.jpg'));
    @endphp
    <meta property="og:image" content="{{ $seoOgImage }}">
    <meta property="og:image:alt" content="{{ $seoOgTitle }}">
    @if(isset($post) && $post->published_at)<meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">@endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoOgTitle }}">
    <meta name="twitter:description" content="{{ $seoOgDescription }}">
    <meta name="twitter:image" content="{{ $seoOgImage }}">
    {{-- Google Discover / image search: explicitly allow large thumbnails.
         Only emitted when the page doesn't define its own robots directive,
         so the two meta tags can never contradict each other. --}}
    
    @if(setting('search_console_token'))<meta name="google-site-verification" content="{{ setting('search_console_token') }}">@endif
    @if(setting('bing_verification_token'))<meta name="msvalidate.01" content="{{ setting('bing_verification_token') }}">@endif
    @if(setting('ahrefs_verification_token'))<meta name="ahrefs-site-verification" content="{{ setting('ahrefs_verification_token') }}">@endif
    @php
        // Derive favicon MIME from extension (don't hardcode image/png since
        // ImageService now converts uploads to WebP, and admins may upload .ico/.svg).
        // A version hash is appended so browsers immediately pick up a newly
        // uploaded favicon instead of serving their hard-cached copy.
        $favSetting = setting('site_favicon');
        $favUrl = ($favSetting ? asset('storage/'.$favSetting) : asset('images/favicon.png'))
            . '?v=' . substr(md5((string) $favSetting), 0, 8);
        $favMime = match (strtolower(pathinfo(parse_url($favSetting ?: 'favicon.png', PHP_URL_PATH) ?: '', PATHINFO_EXTENSION))) {
            'webp' => 'image/webp',
            'ico'  => 'image/x-icon',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
            default => 'image/png',
        };
    @endphp
    <link rel="icon" type="{{ $favMime }}" href="{{ $favUrl }}">
    <link rel="apple-touch-icon" href="{{ $favUrl }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://picsum.photos" crossorigin>
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    <link href="{{ \App\Support\SiteFont::googleUrl() }}" rel="stylesheet">
    {!! \App\Support\ViteAssets::tags(['resources/css/app.css', 'resources/js/app.js']) !!}
    {{-- Root-level font rule: unlayered CSS always beats Tailwind's layered
         preflight, so the admin-chosen font applies everywhere with no chance
         of any stylesheet overriding it. --}}
    <style>html{font-family:{!! \App\Support\SiteFont::cssStack() !!}</style>

    {{-- Google Tag Manager (Admin → Settings → Analytics & Verification) --}}
    @if(setting('gtm_container_id'))
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ setting('gtm_container_id') }}');</script>
    @endif

    {{-- Google Analytics 4 (Search Console GA verification requires the
         gtag.js snippet inside <head>, so it lives here — not at body end) --}}
    @if(setting('ga_measurement_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('ga_measurement_id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ setting('ga_measurement_id') }}');
    </script>
    @endif

    @stack('head')
</head>
<body class="bg-[#f7f8fa] dark:bg-[#0f1115] text-slate-800 dark:text-slate-100 antialiased overflow-x-hidden" style="font-family:{{ \App\Support\SiteFont::cssStack() }}">
    @if(setting('gtm_container_id'))
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ setting('gtm_container_id') }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif
    {{-- Admin ⇄ User switch: silent. While the admin browses in user mode the
         site header shows a small "Switch to Admin" button — no banner, no
         extra text, the public design stays exactly as visitors see it. --}}
    <div class="min-h-screen flex flex-col w-full max-w-[100vw] overflow-x-hidden">
        @include('partials.header')

        <main class="flex-1 w-full">
            @if(session('success'))
                <div class="max-w-[1200px] mx-auto px-4 sm:px-6 mt-4">
                    <div class="text-[#173A2A] dark:text-[#6FB393] pl-4 pr-3 py-3 flex items-center justify-between text-sm border border-[#C7E0D4] dark:border-[#57A37E]/20 bg-[#F0F7F3] dark:!bg-[#12211b] rounded-xl">
                        <span class="font-medium flex items-center gap-2"><svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.5 11 14.5 15 10.5"/><circle cx="12" cy="12" r="9"/></svg>{{ session('success') }}</span>
                        <button onclick="this.parentElement.remove()" class="ml-4 w-7 h-7 flex items-center justify-center rounded-md hover:bg-[#E3F0E9] dark:hover:bg-[#57A37E]/10" aria-label="Dismiss">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div class="max-w-[1200px] mx-auto px-4 sm:px-6 mt-4">
                    <div class="text-red-700 dark:text-red-300 pl-4 pr-3 py-3 flex items-center justify-between text-sm border border-red-200 dark:border-red-400/20 bg-red-50 dark:!bg-[#241417] rounded-xl">
                        <span class="font-medium flex items-center gap-2"><svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>{{ session('error') }}</span>
                        <button onclick="this.parentElement.remove()" class="ml-4 w-7 h-7 flex items-center justify-center rounded-md hover:bg-red-100 dark:hover:bg-red-400/10" aria-label="Dismiss">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            @endif
            @yield('content')
        </main>

        @include('partials.footer')
    </div>

    @include('partials.search-overlay')

    <!-- Scroll top (repo pattern: FAB appears after 100px) -->
    <button id="scroll-top" onclick="window.scrollTo({top:0,behavior:'smooth'})" class="fixed bottom-5 right-5 w-11 h-11 rounded-full bg-[#16181d] dark:bg-[#e6e8ee] text-white dark:text-[#101319] shadow-lg shadow-black/20 hidden items-center justify-center hover:bg-[#2E7856] dark:hover:bg-[#2E7856] dark:hover:text-white transition" aria-label="Scroll to top">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
    </button>

    @stack('scripts')
    <script>
        // Theme toggle
        function toggleTheme(){
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('huvanti-theme', isDark ? 'dark' : 'light');
        }
        // Scroll top visibility (threshold 100px, repo pattern)
        const scrollBtn = document.getElementById('scroll-top');
        window.addEventListener('scroll', ()=>{
            if(window.scrollY > 100){ scrollBtn.classList.remove('hidden'); scrollBtn.classList.add('flex'); }
            else { scrollBtn.classList.add('hidden'); scrollBtn.classList.remove('flex'); }
        });
        // Fade-in safety sweep: images restored from cache may have finished
        // loading before their inline onload ran — reveal them immediately.
        // A 3s timeout also reveals still-pending images: a pending <img>
        // paints nothing, so the card's tinted placeholder shows through
        // (and a late error still hides the img via onerror).
        (function(){
            function reveal(){
                document.querySelectorAll('img.img-fade').forEach(function(img){
                    if(img.complete && img.naturalWidth > 0){ img.classList.add('img-loaded'); }
                });
            }
            reveal();
            window.addEventListener('pageshow', reveal);
            setTimeout(function(){
                document.querySelectorAll('img.img-fade:not(.img-loaded)').forEach(function(img){
                    img.classList.add('img-loaded');
                });
            }, 3000);
        })();
        // Smooth anchors
        document.addEventListener('DOMContentLoaded', ()=>{
            document.querySelectorAll('a[href^="#"]').forEach(a=>{
                a.addEventListener('click', e=>{
                    const id = a.getAttribute('href');
                    if(id.length>1){
                        const el=document.querySelector(id);
                        if(el){ e.preventDefault(); el.scrollIntoView({behavior:'smooth', block:'start'}); }
                    }
                });
            });
        });
    </script>
</body>
</html>
