<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Dashboard') - Author</title>
    <meta name="robots" content="noindex, nofollow">
    <link href="{{ \App\Support\SiteFont::googleUrl() }}" rel="stylesheet">
    {!! \App\Support\ViteAssets::tags(['resources/css/app.css', 'resources/js/app.js']) !!}
    <script>
        (function(){
            var t = localStorage.getItem('huvanti-admin-theme') || 'light';
            if(t === 'dark'){ document.documentElement.classList.add('dark'); }
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    {{-- Site font: applied on <body> via a style ATTRIBUTE. Blade escapes {{ }} with
         HTML entities; inside a <style> block those entities are NOT decoded by
         browsers, which silently breaks the font rule. In an attribute they
         decode correctly, matching the admin + frontend layouts. --}}
    @stack('head')
</head>
<body class="panel-ui min-h-screen bg-slate-100 text-slate-900 dark:bg-[#0f172a] dark:text-slate-100 flex overflow-x-hidden" style="font-family:{{ \App\Support\SiteFont::cssStack() }}">
    {{-- Sidebar: identical structure and styling to the admin panel --}}
    <aside id="author-sidebar" class="fixed inset-y-0 left-0 w-[250px] bg-slate-900 dark:bg-slate-950 text-slate-300 flex flex-col z-40 transform lg:translate-x-0 -translate-x-full transition-transform duration-300 overflow-y-auto no-scrollbar border-r border-black/30">
        <div class="h-[64px] flex items-center gap-3 px-5 border-b border-white/10 shrink-0">
            <div class="w-9 h-9 bg-[#0C3B2E] flex items-center justify-center text-white" aria-hidden="true">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 19.5-3-3"/></svg>
            </div>
            <div>
                <div class="font-extrabold text-white leading-none">Author</div>
                <div class="text-[10px] font-semibold tracking-[0.2em] text-slate-500 mt-1">{{ auth()->user()->name ?? 'Panel' }}</div>
            </div>
        </div>

        <nav class="flex-1 p-3 space-y-1 text-sm font-medium">
            <a href="{{ route('author.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('author.dashboard') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m3 12 2-2m0 0 7-7 7 7M5 10v10a1 1 0 0 0 1 1h3m10-11 2 2m-2-2v10a1 1 0 0 1-1 1h-3m-6 0a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1m-6 0h6"/></svg>
                Dashboard
            </a>

            <div class="pt-3 pb-1 px-3 text-[10px] font-bold tracking-[0.18em] text-slate-500 uppercase">Writing</div>
            <a href="{{ route('author.posts.index') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('author.posts.index') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                My Posts
                @php
                    try { $returnedCount = \App\Models\Post::where('user_id', auth()->id())->where('review_status', 'returned')->count(); }
                    catch (\Throwable $e) { $returnedCount = 0; }
                @endphp
                @if($returnedCount)<span class="ml-auto text-[11px] font-bold bg-amber-400 text-slate-900 px-2 py-0.5">{{ $returnedCount }}</span>@endif
            </a>
            <a href="{{ route('author.posts.create') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('author.posts.create') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                New Post
            </a>
            <a href="{{ route('author.rules') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('author.rules') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                Posting Rules
            </a>
            <a href="{{ route('author.feedback.index') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('author.feedback*') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 01-.923 1.785A5.969 5.969 0 006 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337z"/></svg>
                Feedback
            </a>

            <div class="pt-3 pb-1 px-3 text-[10px] font-bold tracking-[0.18em] text-slate-500 uppercase">Account</div>
            <a href="{{ route('author.profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('author.profile*') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z"/></svg>
                Profile
            </a>
            <a href="{{ route('author.revenue') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('author.revenue') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                Revenue
                <span class="ml-auto text-[10px] font-semibold bg-amber-400 text-slate-900 px-1.5 py-0.5">SOON</span>
            </a>
        </nav>

        <div class="p-3 border-t border-white/10">
            <div class="flex items-center gap-3 px-1">
                @if(auth()->user()->author_avatar_path)
                    <img src="{{ '/storage/'.auth()->user()->author_avatar_path }}" class="w-9 h-9 object-cover bg-slate-800" alt="" loading="lazy" decoding="async">
                @else
                    <div class="w-9 h-9 bg-[#0C3B2E] text-white flex items-center justify-center font-bold text-sm">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</span>
                        {!! auth()->user()->badgeHtml() !!}
                    </div>
                    <a href="{{ url('/') }}" class="text-[11px] text-slate-400 hover:text-white">View site</a>
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" title="Sign out" class="w-8 h-8 bg-white/10 hover:bg-white/20 flex items-center justify-center text-slate-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 17l5-5m0 0-5-5m5 5H9m6-9V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-2"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex-1 lg:ml-[250px] min-w-0 flex flex-col min-h-screen">
        {{-- Topbar: identical to the admin panel --}}
        <header class="sticky top-0 z-30 h-[64px] flex items-center justify-between px-4 sm:px-6 gap-4 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-3 min-w-0">
                <button id="author-menu-toggle" class="lg:hidden w-9 h-9 bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="font-bold text-lg leading-none truncate">@yield('title','Dashboard')</h1>
            </div>
            <div class="flex items-center gap-2 sm:gap-3">
                @stack('header-actions')
                {{-- Admin browsing in user mode: one-click return to the admin panel --}}
                @if(auth()->check() && auth()->user()->browsingAsUser())
                    <form method="POST" action="{{ route('switch-back-to-admin') }}" class="inline">
                        @csrf
                        <button type="submit" title="Return to the admin panel" aria-label="Switch to Admin" class="inline-flex items-center justify-center gap-2 h-9 px-3 sm:px-4 text-sm font-semibold bg-[#0C3B2E] hover:bg-[#072A20] text-white transition cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2zm10-10V7a4 4 0 0 0-8 0v4h8z"/></svg>
                            <span class="hidden sm:inline">Switch to Admin</span>
                        </button>
                    </form>
                @endif
                <button onclick="toggleAuthorTheme()" id="author-theme-btn" title="Toggle theme" class="w-9 h-9 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center justify-center">
                    <svg class="w-[18px] h-[18px] hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
                    <svg class="w-[18px] h-[18px] block dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
                </button>
                <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 h-9 px-3 sm:px-4 text-sm font-semibold text-white bg-[#0C3B2E] hover:bg-[#072A20] transition" aria-label="View Site" title="View Site">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 3h6m0 0v6m0-6L10 14M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                    <span class="hidden sm:inline">View Site</span>
                </a>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 lg:p-7">
            @yield('admin-breadcrumbs')
            @if(session('success'))
                <div class="mb-4 flex items-center justify-between gap-3 border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 11 3 3L22 4"/></svg>
                        {{ session('success') }}
                    </span>
                    <button onclick="this.parentElement.remove()" class="text-emerald-600 dark:text-emerald-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg></button>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
                    <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>

        <footer class="px-6 py-3 text-center text-[11px] font-medium tracking-wide text-slate-400 dark:text-slate-600 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">© {{ date('Y') }} {{ setting('site_name', 'Huvanti') }}</footer>
    </div>

    {{-- Back-to-top FAB, same as the admin panel --}}
    <button id="author-back-top" type="button" aria-label="Back to top" title="Back to top"
        class="fixed bottom-4 right-4 w-10 h-10 bg-[#0C3B2E] hover:bg-[#072A20] dark:bg-emerald-400 dark:hover:bg-emerald-500 dark:text-slate-900 text-white shadow-lg flex items-center justify-center transition-opacity duration-200 opacity-0 pointer-events-none z-30">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6"/></svg>
    </button>

    <div id="author-backdrop" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-30 hidden lg:hidden"></div>

    <script>
        function toggleAuthorTheme(){
            const html = document.documentElement;
            const dark = !html.classList.contains('dark');
            html.classList.toggle('dark', dark);
            html.setAttribute('data-theme', dark ? 'dark' : 'light');
            localStorage.setItem('huvanti-admin-theme', dark ? 'dark' : 'light');
        }
        const sidebar = document.getElementById('author-sidebar');
        const backdrop = document.getElementById('author-backdrop');
        document.getElementById('author-menu-toggle')?.addEventListener('click', () => {
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
        });
        backdrop?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
        });
        const fab = document.getElementById('author-back-top');
        const onScroll = () => {
            const show = (window.scrollY || document.documentElement.scrollTop) > 400;
            if (show) {
                fab.classList.remove('opacity-0','pointer-events-none');
            } else {
                fab.classList.add('opacity-0','pointer-events-none');
            }
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
        fab?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    </script>
    @stack('scripts')
</body>
</html>
