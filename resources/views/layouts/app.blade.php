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
    <title>{{ $metaTitle ?? ($post->meta_title ?? ($page->meta_title ?? (setting('site_name','huvanti.com') . ' · ' . setting('site_tagline','Explore Ideas. Inspire Life.')))) }}</title>
    <meta name="description" content="{{ $metaDescription ?? ($post->meta_description ?? ($page->meta_description ?? setting('site_description','Huvanti is a multi niche blog covering technology, health, finance, travel, lifestyle and education.'))) }}">
    <meta name="keywords" content="{{ setting('site_keywords','huvanti, blog, technology, health, finance, travel, lifestyle') }}">
    <link rel="canonical" href="{{ config('app.url') . request()->getRequestUri() }}">
    <meta property="og:title" content="{{ $metaTitle ?? setting('site_name','huvanti.com') }}">
    <meta property="og:description" content="{{ $metaDescription ?? setting('site_description') }}">
    <meta property="og:url" content="{{ config('app.url') . request()->getRequestUri() }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ $ogImage ?? asset('images/og-huvanti.jpg') }}">
    <meta name="twitter:card" content="summary_large_image">
    @if(setting('search_console_token'))<meta name="google-site-verification" content="{{ setting('search_console_token') }}">@endif
    @if(setting('ahrefs_verification_token'))<meta name="ahrefs-site-verification" content="{{ setting('ahrefs_verification_token') }}">@endif
    @php
        // Derive favicon MIME from extension (don't hardcode image/png since
        // ImageService now converts uploads to WebP, and admins may upload .ico).
        $favSetting = setting('site_favicon');
        $favUrl = $favSetting ? asset('storage/'.$favSetting) : asset('images/favicon.png');
        $favMime = match (strtolower(pathinfo(parse_url($favUrl, PHP_URL_PATH), PATHINFO_EXTENSION))) {
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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-[#fafafa] dark:bg-[#121212] text-slate-800 dark:text-slate-100 antialiased overflow-x-hidden" style="font-family:{{ \App\Support\SiteFont::cssStack() }}">
    <div class="min-h-screen flex flex-col w-full max-w-[100vw] overflow-x-hidden">
        @include('partials.header')

        <main class="flex-1 w-full">
            @if(session('success'))
                <div class="max-w-[1200px] mx-auto px-4 sm:px-6 mt-4">
                    <div class="card-elev text-emerald-800 dark:text-emerald-300 px-4 py-3 flex items-center justify-between text-sm !shadow-none border border-emerald-200 dark:border-emerald-400/20 dark:!bg-[#1e2b24]">
                        <span class="font-medium">{{ session('success') }}</span>
                        <button onclick="this.parentElement.remove()" class="ml-4" aria-label="Dismiss">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div class="max-w-[1200px] mx-auto px-4 sm:px-6 mt-4">
                    <div class="card-elev text-red-700 dark:text-red-300 px-4 py-3 text-sm !shadow-none border border-red-200 dark:border-red-400/20 dark:!bg-[#2b1e1e]">{{ session('error') }}</div>
                </div>
            @endif
            @yield('content')
        </main>

        @include('partials.footer')
    </div>

    @include('partials.search-overlay')

    <!-- Scroll top (repo pattern: FAB appears after 100px) -->
    <button id="scroll-top" onclick="window.scrollTo({top:0,behavior:'smooth'})" class="fixed bottom-4 right-4 w-10 h-10 bg-[#0C3B2E] dark:bg-emerald-400 text-white dark:text-slate-900 shadow-lg hidden items-center justify-center hover:bg-[#072A20] dark:hover:bg-emerald-300 transition" aria-label="Scroll to top">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
    </button>

    @if(setting('ga_measurement_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ setting('ga_measurement_id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ setting('ga_measurement_id') }}');
    </script>
    @endif

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
