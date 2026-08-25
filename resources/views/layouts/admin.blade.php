<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Dashboard') - Admin</title>
    <link href="{{ \App\Support\SiteFont::googleUrl() }}" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
    <script>
        (function(){
            var t = localStorage.getItem('huvanti-admin-theme') || 'light';
            if(t === 'dark'){ document.documentElement.classList.add('dark'); }
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    {{-- Site font: applied on <body> via a style ATTRIBUTE (below). Blade escapes
         {{ }} with HTML entities; inside a <style> block those entities are NOT
         decoded by browsers, which used to silently break the font rule. In an
         attribute they decode correctly, matching the frontend layout. --}}
    @stack('head')
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 dark:bg-[#0f172a] dark:text-slate-100 flex overflow-x-hidden" style="font-family:{{ \App\Support\SiteFont::cssStack() }}">
    <!-- Sidebar -->
    <aside id="admin-sidebar" class="fixed inset-y-0 left-0 w-[250px] bg-slate-900 dark:bg-slate-950 text-slate-300 flex flex-col z-40 transform lg:translate-x-0 -translate-x-full transition-transform duration-300 overflow-y-auto no-scrollbar border-r border-black/30">
        <div class="h-[64px] flex items-center gap-3 px-5 border-b border-white/10 shrink-0">
            <div class="w-9 h-9 bg-[#0C3B2E] flex items-center justify-center text-white" aria-hidden="true">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1 1 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>
            </div>
            <div class="font-extrabold text-white leading-none">Admin</div>
        </div>

        <nav class="flex-1 p-3 space-y-1 text-sm font-medium">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('admin.dashboard*') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m3 12 2-2m0 0 7-7 7 7M5 10v10a1 1 0 0 0 1 1h3m10-11 2 2m-2-2v10a1 1 0 0 1-1 1h-3m-6 0a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1m-6 0h6"/></svg>
                Dashboard
            </a>

            <div class="pt-3 pb-1 px-3 text-[10px] font-bold tracking-[0.18em] text-slate-500 uppercase">Content</div>
            <a href="{{ route('admin.posts.index') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('admin.posts.index') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                Posts
            </a>
            <a href="{{ route('admin.posts.review-queue') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('admin.posts.review-queue') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Review Queue
                @php
                    // Guarded: a missing review_status column (unmigrated DB) must
                    // never take down the whole admin panel with a 500.
                    try { $pendingCount = \App\Models\Post::where('review_status', 'pending_review')->count(); }
                    catch (\Throwable $e) { $pendingCount = 0; }
                @endphp
                @if($pendingCount)<span class="ml-auto text-[11px] font-bold bg-amber-400 text-slate-900 px-2 py-0.5">{{ $pendingCount }}</span>@endif
            </a>
            <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('admin.categories*') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.84Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path stroke-linecap="round" stroke-linejoin="round" d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/></svg>
                Categories
            </a>
            <a href="{{ route('admin.pages.index') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('admin.pages*') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 11.5V4a2 2 0 0 1 2-2h8l6 6v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 17h8M8 13.5h5"/></svg>
                Pages
            </a>
            <a href="{{ route('admin.ads.index') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('admin.ads*') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8.5 8 5m-4 3.5L8 12m-4-3.5h16M20 15.5 16 19m4-3.5L16 12m4 3.5H4"/></svg>
                Advertisements
            </a>

            <div class="pt-3 pb-1 px-3 text-[10px] font-bold tracking-[0.18em] text-slate-500 uppercase">Engagement</div>
            <a href="{{ route('admin.comments.index') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('admin.comments*') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.9 9.9 0 0 1-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z"/></svg>
                Comments
                @php
                    try { $pendingComments = \App\Models\Comment::where('status','pending')->count(); }
                    catch (\Throwable $e) { $pendingComments = 0; }
                @endphp
                @if($pendingComments)<span class="ml-auto text-[11px] font-bold bg-amber-400 text-slate-900 px-2 py-0.5">{{ $pendingComments }}</span>@endif
            </a>
            <a href="{{ route('admin.contacts.index') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('admin.contacts*') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/></svg>
                Messages
                @php
                    try { $unreadMessages = \App\Models\ContactMessage::where('is_read',false)->count(); }
                    catch (\Throwable $e) { $unreadMessages = 0; }
                @endphp
                @if($unreadMessages)<span class="ml-auto text-[11px] font-bold bg-amber-400 text-slate-900 px-2 py-0.5">{{ $unreadMessages }}</span>@endif
            </a>

            <div class="pt-3 pb-1 px-3 text-[10px] font-bold tracking-[0.18em] text-slate-500 uppercase">System</div>
            <a href="{{ route('admin.navigation.index') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('admin.navigation*') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                Navigation
            </a>
            <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('admin.profile*') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Profile
            </a>
            <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('admin.settings.index') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                Settings
            </a>
            <a href="{{ route('admin.settings.security') }}" class="flex items-center gap-3 px-3 py-2.5 transition {{ request()->routeIs('admin.settings.security') ? 'bg-[#0C3B2E] text-white' : 'hover:bg-white/5 hover:text-white' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="7.5" cy="15.5" r="5.5"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 2-9.6 9.6"/><path stroke-linecap="round" stroke-linejoin="round" d="m15.5 7.5 3 3L22 7l-3-3"/></svg>
                Security
            </a>
        </nav>

        <div class="p-3 border-t border-white/10">
            <div class="flex items-center gap-3 px-1">
                @php $avatarPath = auth()->user()->author_avatar_path; @endphp
                @if($avatarPath)
                    <img src="{{ '/storage/'.$avatarPath }}" class="w-9 h-9 object-cover bg-slate-800" alt="" loading="lazy" decoding="async">
                @else
                    <div class="w-9 h-9 bg-[#0C3B2E] text-white flex items-center justify-center font-bold text-sm">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</span>
                        {!! auth()->user()->badgeHtml() !!}
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">@csrf
                    <button type="submit" title="Log out" class="w-8 h-8 bg-white/10 hover:bg-white/20 flex items-center justify-center text-slate-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 17l5-5m0 0-5-5m5 5H9m6-9V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-2"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex-1 lg:ml-[250px] min-w-0 flex flex-col min-h-screen">
        <!-- Topbar -->
        <header class="sticky top-0 z-30 h-[64px] flex items-center justify-between px-4 sm:px-6 gap-4 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-3 min-w-0">
                <button id="admin-menu-toggle" class="lg:hidden w-9 h-9 bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="font-bold text-lg leading-none truncate">@yield('title','Dashboard')</h1>
            </div>
            <div class="flex items-center gap-2 sm:gap-3">
                <button onclick="toggleAdminTheme()" id="admin-theme-btn" title="Toggle theme" class="w-9 h-9 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center justify-center">
                    <svg class="w-[18px] h-[18px] hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
                    <svg class="w-[18px] h-[18px] block dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
                </button>
                <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 h-9 px-3 sm:px-4 text-sm font-semibold text-white bg-[#0C3B2E] hover:bg-[#072A20] transition" aria-label="View Site" title="View Site">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 3h6m0 0v6m0-6L10 14M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                    <span class="hidden sm:inline">View Site</span>
                </a>
                {{-- Admin ⇄ User switch: browse the site + user panel as a regular
                     user from your own admin account, then switch straight back.
                     Only visible to the real admin; never exposes admin to users. --}}
                <form method="POST" action="{{ route('admin.switch-role') }}" class="inline">
                    @csrf
                    <button type="submit" title="Browse the site as a regular user from your own account" aria-label="Switch to User" class="inline-flex items-center justify-center gap-2 h-9 px-3 sm:px-4 text-sm font-semibold text-[#0C3B2E] dark:text-emerald-300 border border-[#0C3B2E] dark:border-emerald-500/40 hover:bg-[#0C3B2E]/5 dark:hover:bg-emerald-500/10 transition cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="m16 11 2 2 4-4"/></svg>
                        <span class="hidden lg:inline">Switch to User</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 lg:p-7">
            {{-- Breadcrumbs slot (pages can push `admin-breadcrumbs` with a list) --}}
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

        <footer class="px-6 py-3 text-center text-[11px] font-medium tracking-wide text-slate-400 dark:text-slate-600 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
            Admin
            @php
                // Build marker: compare with public/deployment.json on GitHub —
                // if these differ, the live server is NOT running the latest
                // code (open https://your-domain/deployment.json to check).
                try { $build = @json_decode(@file_get_contents(public_path('deployment.json')), true); } catch (\Throwable $e) { $build = null; }
            @endphp
            @if(!empty($build['deployment']))<span class="mx-1.5 text-slate-300 dark:text-slate-700">·</span>build {{ $build['deployment'] }}@endif
        </footer>
    </div>

    {{-- Back-to-top FAB — appears after scrolling, MUI-style --}}
    <button id="admin-back-top" type="button" aria-label="Back to top" title="Back to top"
        class="fixed bottom-4 right-4 w-10 h-10 bg-[#0C3B2E] hover:bg-[#072A20] dark:bg-emerald-400 dark:hover:bg-emerald-500 dark:text-slate-900 text-white shadow-lg flex items-center justify-center transition-opacity duration-200 opacity-0 pointer-events-none z-30">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6"/></svg>
    </button>

    <div id="admin-backdrop" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-30 hidden lg:hidden"></div>

    <script>
        function toggleAdminTheme(){
            const html = document.documentElement;
            const dark = !html.classList.contains('dark');
            html.classList.toggle('dark', dark);
            html.setAttribute('data-theme', dark ? 'dark' : 'light');
            localStorage.setItem('huvanti-admin-theme', dark ? 'dark' : 'light');
        }
        const sidebar = document.getElementById('admin-sidebar');
        const backdrop = document.getElementById('admin-backdrop');
        document.getElementById('admin-menu-toggle')?.addEventListener('click', () => {
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
        });
        backdrop?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
        });
        // Back-to-top FAB — fade in/out based on scroll position
        const fab = document.getElementById('admin-back-top');
        const scrollContainer = document.querySelector('.flex-1.lg\\:ml-\\[250px\\]');
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
