<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Author dashboard') · Huvanti</title>
    <link href="{{ \App\Support\SiteFont::googleUrl() }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function(){
            try{
                var t = localStorage.getItem('huvanti-theme');
                if(t === 'dark'){ document.documentElement.classList.add('dark'); }
            }catch(e){}
        })();
    </script>
    @stack('head')
</head>
<body class="min-h-screen bg-slate-100 dark:bg-[#0f172a] text-slate-900 dark:text-slate-100 flex overflow-x-hidden" style="font-family:{{ \App\Support\SiteFont::cssStack() }}">
    <aside class="fixed inset-y-0 left-0 w-[250px] bg-slate-900 dark:bg-slate-950 text-slate-300 flex flex-col z-40 transform lg:translate-x-0 -translate-x-full transition-transform duration-300 overflow-y-auto border-r border-black/30">
        <div class="h-[64px] flex items-center gap-3 px-5 border-b border-white/10 shrink-0">
            <div class="w-9 h-9 bg-[#0C3B2E] flex items-center justify-center font-extrabold text-white">H</div>
            <div>
                <div class="font-extrabold text-white leading-none">Huvanti</div>
                <div class="text-[10px] font-semibold tracking-[0.2em] text-slate-500">AUTHOR</div>
            </div>
        </div>

        <nav class="flex-1 p-3 space-y-1 text-sm font-medium">
            <a href="{{ route('author.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('author.dashboard') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m3 12 2-2m0 0 7-7 7 7M5 10v10a1 1 0 0 0 1 1h3m10-11 2 2m-2-2v10a1 1 0 0 1-1 1h-3m-6 0a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('author.posts.index') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('author.posts*') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                My Posts
            </a>
            <a href="{{ route('author.posts.create') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('author.posts.create*') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Post
            </a>
            <a href="{{ route('author.rules') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('author.rules') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/></svg>
                Posting Rules
            </a>

            <div class="pt-3 pb-1 px-3 text-[10px] font-bold tracking-[0.18em] text-slate-500 uppercase">Account</div>
            <a href="{{ route('author.profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('author.profile*') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 21v-1a8 8 0 0 1 16 0v1"/></svg>
                Profile
            </a>
            <a href="{{ route('author.monetization') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('author.monetization') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12h18M5 12V7a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v5m-9 4h.01M5 17h14"/></svg>
                Monetization
                <span class="ml-auto text-[10px] font-semibold bg-amber-400 text-slate-900 px-1.5 py-0.5">SOON</span>
            </a>
        </nav>

        <div class="p-3 border-t border-white/10">
            <div class="flex items-center gap-3 px-1">
                @if(auth()->user()->author_avatar_path)
                    <img src="{{ '/storage/'.auth()->user()->author_avatar_path }}" class="w-9 h-9 object-cover bg-slate-800" alt="" loading="lazy">
                @else
                    <div class="w-9 h-9 bg-[#0C3B2E] text-white flex items-center justify-center font-bold text-sm">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                    <a href="{{ route('home') }}" class="text-[11px] text-slate-400 hover:text-white">View site →</a>
                </div>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" title="Sign out" class="w-8 h-8 bg-white/10 hover:bg-white/20 flex items-center justify-center text-slate-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <main class="flex-1 lg:ml-[250px] min-w-0">
        <header class="sticky top-0 z-30 bg-white/95 dark:bg-slate-900/95 backdrop-blur border-b border-slate-200 dark:border-slate-800 h-16 flex items-center px-4 sm:px-6 gap-3">
            <button data-toggle-sidebar type="button" class="lg:hidden w-10 h-10 flex items-center justify-center text-slate-700 dark:text-slate-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <h1 class="font-bold text-slate-900 dark:text-white text-lg sm:text-xl">@yield('title', 'Dashboard')</h1>
            <div class="ml-auto flex items-center gap-2">
                {{-- Admin browsing in user mode: one-click return to the admin panel --}}
                @if(auth()->check() && auth()->user()->browsingAsUser())
                    <form method="POST" action="{{ route('switch-back-to-admin') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 h-9 px-4 text-sm font-semibold text-white bg-[#0C3B2E] hover:bg-[#072A20] transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2zm10-10V7a4 4 0 0 0-8 0v4h8z"/></svg>
                            Switch to Admin
                        </button>
                    </form>
                @endif
                @stack('header-actions')
            </div>
        </header>

        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-500/10 border-b border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 px-4 sm:px-6 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error') || $errors?->any())
            <div class="bg-red-50 dark:bg-red-500/10 border-b border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 px-4 sm:px-6 py-3 text-sm">
                @if(session('error')){{ session('error') }} @endif
                @if($errors?->any()){{ $errors->first() }}@endif
            </div>
        @endif

        <div class="p-4 sm:p-6 max-w-[1200px] mx-auto">
            @yield('content')
        </div>
    </main>
</body>
</html>
