<!DOCTYPE html>
<html lang="en" data-site-theme="{{ \App\Support\SiteThemes::validOrDefault(setting('site_theme')) }}" data-site-template="{{ \App\Support\SiteTemplates::validOrDefault(setting('site_template')) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','Dashboard') - Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ \App\Support\SiteFont::googleUrl() }}" rel="stylesheet">
    {!! \App\Support\ViteAssets::tags(['resources/css/app.css', 'resources/js/app.js']) !!}
    {{-- Root-level font rule: unlayered CSS always beats Tailwind's layered
         preflight, so the admin-chosen font applies everywhere with no chance
         of any stylesheet overriding it. --}}
    <style>html{font-family:{!! \App\Support\SiteFont::cssStack() !!}</style>
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
<body class="panel-ui min-h-screen bg-[#f6f7fa] text-slate-900 dark:bg-[#0d0f13] dark:text-slate-100 flex overflow-x-hidden" style="font-family:{{ \App\Support\SiteFont::cssStack() }}">
    <!-- Sidebar -->
    <aside id="admin-sidebar" class="panel-sidebar fixed inset-y-0 left-0 w-[250px] flex flex-col z-40 transform lg:translate-x-0 -translate-x-full transition-transform duration-300 overflow-y-auto no-scrollbar">
        <div class="h-[64px] flex items-center gap-3 px-5 border-b border-[var(--sb-border)] shrink-0">
            <div class="sb-tile w-9 h-9 rounded-lg flex items-center justify-center" aria-hidden="true">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
            </div>
            <div class="font-extrabold text-white leading-none tracking-tight">Admin</div>
        </div>

        <nav class="flex-1 p-3 space-y-1 text-sm font-medium">
            <a href="{{ route('admin.dashboard') }}" class="sb-link flex items-center gap-3 px-3 py-2.5 mx-2 rounded-lg transition {{ request()->routeIs('admin.dashboard*') ? 'is-active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m3 12 2-2m0 0 7-7 7 7M5 10v10a1 1 0 0 0 1 1h3m10-11 2 2m-2-2v10a1 1 0 0 1-1 1h-3m-6 0a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1m-6 0h6"/></svg>
                Dashboard
            </a>

            <div class="pt-3 pb-1 px-3 text-[10px] font-bold tracking-[0.18em] sb-label uppercase">Content</div>
            <a href="{{ route('admin.posts.index') }}" class="sb-link flex items-center gap-3 px-3 py-2.5 mx-2 rounded-lg transition {{ request()->routeIs('admin.posts.index') ? 'is-active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                Posts
            </a>
            <a href="{{ route('admin.posts.review-queue') }}" class="sb-link flex items-center gap-3 px-3 py-2.5 mx-2 rounded-lg transition {{ request()->routeIs('admin.posts.review-queue') ? 'is-active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Review Queue
                @php
                    // Guarded: a missing review_status column (unmigrated DB) must
                    // never take down the whole admin panel with a 500.
                    try { $pendingCount = \App\Models\Post::where('review_status', 'pending_review')->count(); }
                    catch (\Throwable $e) { $pendingCount = 0; }
                @endphp
                @if($pendingCount)<span class="ml-auto text-[11px] font-bold bg-amber-400 text-slate-900 px-2 py-0.5 rounded-full">{{ $pendingCount }}</span>@endif
            </a>
            <a href="{{ route('admin.categories.index') }}" class="sb-link flex items-center gap-3 px-3 py-2.5 mx-2 rounded-lg transition {{ request()->routeIs('admin.categories*') ? 'is-active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.84Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path stroke-linecap="round" stroke-linejoin="round" d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/></svg>
                Categories
            </a>
            <a href="{{ route('admin.pages.index') }}" class="sb-link flex items-center gap-3 px-3 py-2.5 mx-2 rounded-lg transition {{ request()->routeIs('admin.pages*') ? 'is-active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 11.5V4a2 2 0 0 1 2-2h8l6 6v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 17h8M8 13.5h5"/></svg>
                Pages
            </a>
            <a href="{{ route('admin.ads.index') }}" class="sb-link flex items-center gap-3 px-3 py-2.5 mx-2 rounded-lg transition {{ request()->routeIs('admin.ads*') ? 'is-active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8.5 8 5m-4 3.5L8 12m-4-3.5h16M20 15.5 16 19m4-3.5L16 12m4 3.5H4"/></svg>
                Advertisements
            </a>

            <div class="pt-3 pb-1 px-3 text-[10px] font-bold tracking-[0.18em] sb-label uppercase">Engagement</div>
            <a href="{{ route('admin.comments.index') }}" class="sb-link flex items-center gap-3 px-3 py-2.5 mx-2 rounded-lg transition {{ request()->routeIs('admin.comments*') ? 'is-active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.9 9.9 0 0 1-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z"/></svg>
                Comments
                @php
                    try { $pendingComments = \App\Models\Comment::where('status','pending')->count(); }
                    catch (\Throwable $e) { $pendingComments = 0; }
                @endphp
                @if($pendingComments)<span class="ml-auto text-[11px] font-bold bg-amber-400 text-slate-900 px-2 py-0.5 rounded-full">{{ $pendingComments }}</span>@endif
            </a>
            <a href="{{ route('admin.contacts.index') }}" class="sb-link flex items-center gap-3 px-3 py-2.5 mx-2 rounded-lg transition {{ request()->routeIs('admin.contacts*') ? 'is-active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/></svg>
                Messages
                @php
                    try { $unreadMessages = \App\Models\ContactMessage::where('is_read',false)->count(); }
                    catch (\Throwable $e) { $unreadMessages = 0; }
                @endphp
                @if($unreadMessages)<span class="ml-auto text-[11px] font-bold bg-amber-400 text-slate-900 px-2 py-0.5 rounded-full">{{ $unreadMessages }}</span>@endif
            </a>
            <a href="{{ route('admin.feedback.index') }}" class="sb-link flex items-center gap-3 px-3 py-2.5 mx-2 rounded-lg transition {{ request()->routeIs('admin.feedback*') ? 'is-active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 01-.923 1.785A5.969 5.969 0 006 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337z"/></svg>
                Feedback
                @php
                    try { $feedbackCount = \App\Models\Feedback::count(); }
                    catch (\Throwable $e) { $feedbackCount = 0; }
                @endphp
                @if($feedbackCount)<span class="ml-auto text-[11px] font-bold bg-sky-400 text-slate-900 px-2 py-0.5 rounded-full">{{ $feedbackCount }}</span>@endif
            </a>

            <div class="pt-3 pb-1 px-3 text-[10px] font-bold tracking-[0.18em] sb-label uppercase">People</div>
            <a href="{{ route('admin.users.index') }}" class="sb-link flex items-center gap-3 px-3 py-2.5 mx-2 rounded-lg transition {{ request()->routeIs('admin.users*') ? 'is-active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v4M16 2v4M3 9h18"/><circle cx="9.5" cy="13.5" r="1.75"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.5 17.5a3.2 3.2 0 0 1 6 0M15 12.5h3.5M15 16h2.5"/></svg>
                Users
            </a>

            <div class="pt-3 pb-1 px-3 text-[10px] font-bold tracking-[0.18em] sb-label uppercase">System</div>
            <a href="{{ route('admin.social.index') }}" class="sb-link flex items-center gap-3 px-3 py-2.5 mx-2 rounded-lg transition {{ request()->routeIs('admin.social*') ? 'is-active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="m8.59 13.51 6.83 3.98m-.01-10.98-6.82 3.98"/></svg>
                Social Auto-Post
            </a>
            <a href="{{ route('admin.ai.index') }}" class="sb-link flex items-center gap-3 px-3 py-2.5 mx-2 rounded-lg transition {{ request()->routeIs('admin.ai*') ? 'is-active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/></svg>
                AI Assistant
            </a>
            <a href="{{ route('admin.navigation.index') }}" class="sb-link flex items-center gap-3 px-3 py-2.5 mx-2 rounded-lg transition {{ request()->routeIs('admin.navigation*') ? 'is-active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                Navigation
            </a>
            <a href="{{ route('admin.profile.edit') }}" class="sb-link flex items-center gap-3 px-3 py-2.5 mx-2 rounded-lg transition {{ request()->routeIs('admin.profile*') ? 'is-active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Profile
            </a>
            <a href="{{ route('admin.settings.index') }}" class="sb-link flex items-center gap-3 px-3 py-2.5 mx-2 rounded-lg transition {{ request()->routeIs('admin.settings.index') ? 'is-active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                Settings
            </a>
            <a href="{{ route('admin.settings.security') }}" class="sb-link flex items-center gap-3 px-3 py-2.5 mx-2 rounded-lg transition {{ request()->routeIs('admin.settings.security') ? 'is-active' : '' }}">
                <svg class="w-[18px] h-[18px] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="7.5" cy="15.5" r="5.5"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 2-9.6 9.6"/><path stroke-linecap="round" stroke-linejoin="round" d="m15.5 7.5 3 3L22 7l-3-3"/></svg>
                Security
            </a>
        </nav>

        <div class="p-3 border-t border-[var(--sb-border)]">
            <div class="flex items-center gap-3 px-1">
                @php $avatarPath = auth()->user()->author_avatar_path; @endphp
                @if($avatarPath)
                    <img src="{{ '/storage/'.$avatarPath }}" class="w-9 h-9 rounded-full object-cover bg-slate-800" alt="" loading="lazy" decoding="async">
                @else
                    <div class="w-9 h-9 rounded-full bg-[var(--brand)] text-white flex items-center justify-center font-bold text-sm ring-2 ring-white/20">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
                @endif
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm font-semibold sb-name truncate">{{ auth()->user()->name }}</span>
                        {!! auth()->user()->badgeHtml() !!}
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">@csrf
                    <button type="submit" title="Log out" class="w-8 h-8 rounded-lg sb-logout flex items-center justify-center transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 17l5-5m0 0-5-5m5 5H9m6-9V5a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-2"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex-1 lg:ml-[250px] min-w-0 flex flex-col min-h-screen">
        <!-- Topbar -->
        <header class="sticky top-0 z-30 h-[64px] flex items-center justify-between px-4 sm:px-6 gap-4 bg-white dark:bg-[#14171d] border-b border-[#e6e8ee] dark:border-[#22262e]">
            <div class="flex items-center gap-3 min-w-0">
                <button id="admin-menu-toggle" class="lg:hidden w-9 h-9 rounded-lg bg-[#f1f3f7] dark:bg-[#1c1f26] flex items-center justify-center text-slate-700 dark:text-slate-200">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="font-bold text-[17px] leading-none truncate tracking-tight min-w-0">@yield('title','Dashboard')</h1>
            </div>
            <div class="flex items-center gap-2 sm:gap-2.5">
                <button type="button" onclick="toggleAdminTheme(event)" id="admin-theme-btn" aria-label="Switch between light and dark mode" title="Toggle theme" class="theme-toggle">
                    <span class="theme-toggle-knob">
                        <svg class="theme-toggle-sun shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="12" r="4.5"/><path d="M12 2v2.5M12 19.5V22M4.2 4.2l1.8 1.8M18 18l1.8 1.8M2 12h2.5M19.5 12H22M4.2 19.8 6 18M18 6l1.8-1.8" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" fill="none"/></svg>
                        <svg class="theme-toggle-moon shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
                    </span>
                </button>
                <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 h-9 px-3.5 text-[13px] font-semibold text-white rounded-lg bg-[var(--brand)] hover:bg-[var(--brand-strong)] transition" aria-label="View Site" title="View Site">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 3h6m0 0v6m0-6L10 14M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                    <span class="hidden sm:inline">View Site</span>
                </a>
                {{-- Admin ⇄ User switch: browse the site + user panel as a regular
                     user from your own admin account, then switch straight back.
                     Only visible to the real admin; never exposes admin to users. --}}
                <form method="POST" action="{{ route('admin.switch-role') }}" class="inline">
                    @csrf
                    <button type="submit" title="Browse the site as a regular user from your own account" aria-label="Switch to User" class="inline-flex items-center justify-center gap-2 h-9 px-3.5 text-[13px] font-semibold rounded-lg text-[var(--brand)] dark:text-[var(--brand-light)] border border-[var(--brand)]/50 dark:border-[var(--brand)]/40 hover:bg-[var(--brand)]/5 dark:hover:bg-[var(--brand)]/10 transition cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="m16 11 2 2 4-4"/></svg>
                        <span class="hidden lg:inline">Switch to User</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 lg:p-7 w-full">
            {{-- Breadcrumbs slot (pages can push `admin-breadcrumbs` with a list) --}}
            @yield('admin-breadcrumbs')
            @if(session('success'))
                <div class="mb-4 flex items-center justify-between gap-3 border border-[var(--brand-tint-2)] bg-[var(--brand-tint-3)] px-4 py-3 text-sm font-medium text-[var(--brand-deep)] rounded-xl dark:border-[var(--brand)]/30 dark:bg-[var(--brand)]/10 dark:text-[var(--brand-light)]">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 11 3 3L22 4"/></svg>
                        {{ session('success') }}
                    </span>
                    <button onclick="this.parentElement.remove()" class="text-[var(--brand-strong)] dark:text-[var(--brand-mid)]"><svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg></button>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 rounded-xl dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 rounded-xl dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
                    <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>

        <footer class="px-6 py-3.5 text-center text-[11px] font-medium tracking-wide text-slate-400 dark:text-slate-600 bg-white dark:bg-[#14171d] border-t border-[#e6e8ee] dark:border-[#22262e]">
            © {{ date('Y') }} {{ setting('site_name', 'Huvanti') }}
        </footer>
    </div>

    {{-- Back-to-top FAB — appears after scrolling, MUI-style --}}
    <button id="admin-back-top" type="button" aria-label="Back to top" title="Back to top"
        class="fixed bottom-5 right-5 w-11 h-11 rounded-full bg-[var(--brand)] hover:bg-[var(--brand-strong)] dark:bg-[var(--brand-mid)] dark:hover:bg-[var(--brand)] dark:text-slate-900 text-white shadow-lg shadow-[var(--brand)]/30 flex items-center justify-center transition-opacity duration-200 opacity-0 pointer-events-none z-30">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m18 15-6-6-6 6"/></svg>
    </button>

    <div id="admin-backdrop" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-30 hidden lg:hidden"></div>

    <script>
        // Theme: sliding rocker switch + a circular reveal of the new theme
        // (View Transitions API, where supported — instant switch otherwise).
        function huvantiThemeReveal(apply, e){
            var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            if (!document.startViewTransition || reduced) { apply(); return; }
            var x = (e && e.clientX) || (window.innerWidth - 60);
            var y = (e && e.clientY) || 40;
            var radius = Math.hypot(Math.max(x, window.innerWidth - x), Math.max(y, window.innerHeight - y));
            var vt = document.startViewTransition(apply);
            vt.ready.then(function(){
                document.documentElement.animate(
                    { clipPath: ['circle(0px at ' + x + 'px ' + y + 'px)', 'circle(' + radius + 'px at ' + x + 'px ' + y + 'px)'] },
                    { duration: 480, easing: 'cubic-bezier(0.4, 0, 0.2, 1)', pseudoElement: '::view-transition-new(root)' }
                );
            }).catch(function(){});
        }
        function toggleAdminTheme(e){
            const html = document.documentElement;
            const dark = !html.classList.contains('dark');
            huvantiThemeReveal(function(){
                html.classList.toggle('dark', dark);
                html.setAttribute('data-theme', dark ? 'dark' : 'light');
                localStorage.setItem('huvanti-admin-theme', dark ? 'dark' : 'light');
            }, e);
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
