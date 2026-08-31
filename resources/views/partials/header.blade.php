@php
    // Categories shown in the menu must be ACTIVE and have at least one
    // published post — empty categories stay hidden from visitors until a
    // post goes live under them (admin can always see them in the panel).
    try {
        $categories = \App\Models\Category::live()->orderBy('sort_order')->take(6)->get();
    } catch (\Throwable $e) {
        $categories = collect();
    }
@endphp
<header class="sticky top-0 z-40 bg-white/92 dark:bg-[#0f1115]/92 backdrop-blur border-b border-[#e6e8ee] dark:border-[#22262e]">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6">
        <div class="flex items-center h-14 sm:h-16 gap-3">
            <button id="mobile-menu-btn" class="w-9 h-9 flex items-center justify-center lg:hidden text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#1c1f26] rounded-lg transition" aria-label="Open menu" aria-controls="mobile-drawer" aria-expanded="false">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <a href="/" class="flex items-center shrink-0" aria-label="huvanti.com home">
                @include('partials.logo', ['class' => 'h-8'])
            </a>
            <nav class="hidden lg:flex items-center gap-1 ml-6 flex-1">
                <a href="/" class="px-3 py-2 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#2a2a2a] transition {{ request()->is('/') ? 'text-slate-900 dark:text-white font-semibold bg-slate-100 dark:bg-[#2a2a2a]' : '' }}">Home</a>
                <div class="relative">
                    <button id="categories-btn" class="px-3 py-2 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#2a2a2a] transition inline-flex items-center gap-1.5 {{ request()->is('category*') ? 'text-slate-900 dark:text-white font-semibold bg-slate-100 dark:bg-[#2a2a2a]' : '' }}" aria-haspopup="true" aria-expanded="false">
                        Categories
                        <svg class="w-4 h-4 opacity-60 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div id="categories-menu" class="hidden absolute left-0 top-full mt-2 w-[480px] max-w-[calc(100vw-2rem)] card p-2 z-50 shadow-[0_12px_36px_-12px_rgba(16,24,40,0.22)] dark:shadow-black/50">
                        <div class="grid grid-cols-2 gap-1">
                            @foreach($categories as $cat)
                                <a href="{{ route('category.show',$cat->slug) }}" class="flex items-center gap-3 p-2.5 hover:bg-slate-50 dark:hover:bg-[#2a2a2a] rounded-lg transition">
                                    <span class="icon-tile w-9 h-9">
                                        @include('partials.category-icon', ['category' => $cat, 'class' => 'w-[18px] h-[18px]'])
                                    </span>
                                    <span class="text-sm font-medium text-slate-900 dark:text-white">{{ $cat->name }}</span>
                                </a>
                            @endforeach
                        </div>
                        <a href="/blog" class="flex items-center justify-center gap-1 text-xs font-semibold text-[#27654A] dark:text-[#6FB393] hover:text-[#1F513A] py-2.5 mt-1 border-t border-slate-100 dark:border-[#2f2f2f]">View all posts
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </a>
                    </div>
                </div>
                <a href="/blog" class="px-3 py-2 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#2a2a2a] transition {{ request()->is('blog*') ? 'text-slate-900 dark:text-white font-semibold bg-slate-100 dark:bg-[#2a2a2a]' : '' }}">Blog</a>
                <a href="/about" class="px-3 py-2 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#2a2a2a] transition {{ request()->is('about') ? 'text-slate-900 dark:text-white font-semibold bg-slate-100 dark:bg-[#2a2a2a]' : '' }}">About</a>
                @if(setting('top_contributors_enabled', '1') === '1')
                <a href="/top-contributors" class="px-3 py-2 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#2a2a2a] transition {{ request()->is('top-contributors') ? 'text-slate-900 dark:text-white font-semibold bg-slate-100 dark:bg-[#2a2a2a]' : '' }}">Top Contributors</a>
                @endif
                <a href="/contact" class="px-3 py-2 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#2a2a2a] transition {{ request()->is('contact') ? 'text-slate-900 dark:text-white font-semibold bg-slate-100 dark:bg-[#2a2a2a]' : '' }}">Contact</a>
            </nav>
            <div class="ml-auto flex items-center gap-1.5">
                <button onclick="openSearch()" class="hidden md:flex items-center gap-2 h-9 pl-3 pr-2 w-52 lg:w-64 rounded-lg border border-[#e6e8ee] bg-[#f7f8fa] hover:border-[#c9cfda] hover:bg-white dark:border-[#2c313c] dark:bg-[#14171d] dark:hover:border-[#3a4150] text-slate-400 dark:text-slate-500 text-sm transition" aria-label="Search">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg>
                    <span class="flex-1 text-left truncate">Search Huvanti...</span>
                </button>
                <button onclick="openSearch()" class="md:hidden w-9 h-9 flex items-center justify-center text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#1c1f26] rounded-lg transition" aria-label="Search">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg>
                </button>
                <button onclick="toggleTheme()" class="w-9 h-9 flex items-center justify-center text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#1c1f26] rounded-lg transition" aria-label="Toggle theme">
                    <svg class="w-5 h-5 dark:hidden shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.002 9.002 0 0012 21a9.002 9.002 0 008.354-5.646z"/></svg>
                    <svg class="w-5 h-5 hidden dark:block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                @if(auth()->check())
                    @if(auth()->user()->browsingAsUser())
                        <form method="POST" action="{{ route('switch-back-to-admin') }}" class="hidden sm:block">
                            @csrf
                            <button type="submit" class="inline-flex items-center h-9 px-4 text-xs font-semibold rounded-lg bg-amber-400 hover:bg-amber-300 text-slate-900 transition cursor-pointer">
                                Switch to Admin
                            </button>
                        </form>
                        <a href="{{ route('author.dashboard') }}" class="hidden sm:inline-flex items-center h-9 px-4 text-xs font-semibold rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('author.dashboard') }}" class="hidden sm:inline-flex items-center h-9 px-4 text-xs font-semibold rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white transition">
                            {{ auth()->user()->role === 'admin' ? 'Admin' : 'Dashboard' }}
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center h-9 px-3.5 text-sm font-medium rounded-lg text-slate-600 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#1c1f26] transition">Sign in</a>
                    <a href="{{ route('register') }}" class="hidden sm:inline-flex items-center h-8 px-4 text-[13px] font-semibold rounded-lg bg-[#16181d] hover:bg-[#2E7856] dark:bg-white dark:hover:bg-[#2E7856] dark:hover:text-white text-white dark:text-[#101319] transition">Sign up</a>
                @endif
            </div>
        </div>
    </div>
</header>
{{--
    Mobile Drawer.
    MUST live OUTSIDE the <header> element: the header uses backdrop-blur,
    and a backdrop-filter on an ancestor creates a new containing block for
    position:fixed children.
--}}
<div id="mobile-drawer" class="fixed inset-0 z-50 hidden lg:hidden" aria-hidden="true">
    <div id="mobile-backdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
    <div id="mobile-panel" class="absolute left-0 top-0 bottom-0 w-[300px] max-w-[85vw] bg-white dark:bg-[#0f1115] shadow-2xl overflow-y-auto overscroll-contain">
        <div class="h-16 px-4 flex items-center justify-between border-b border-[#e6e8ee] dark:border-[#22262e]">
            <a href="/" class="flex items-center">
                @include('partials.logo', ['class' => 'h-7', 'textClass' => 'text-[19px]'])
            </a>
            <button id="mobile-close" class="w-9 h-9 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-[#2a2a2a] rounded-lg" aria-label="Close menu">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <nav class="p-4 space-y-1">
            <a href="/" class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-[#2a2a2a] rounded-lg text-slate-700 dark:text-slate-300 font-medium">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z"/></svg> Home
            </a>
            <button onclick="closeDrawer(); openSearch();" class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-[#2a2a2a] rounded-lg text-slate-700 dark:text-slate-300 font-medium">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg> Search
            </button>
            <div>
                <div class="px-3 py-2 text-xs font-bold tracking-widest text-[#173A2A] dark:text-[#6FB393] uppercase">Categories</div>
                <div class="space-y-1">
                    @foreach($categories as $cat)
                        <a href="{{ route('category.show',$cat->slug) }}" class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 dark:hover:bg-[#2a2a2a] rounded-lg text-slate-700 dark:text-slate-300">
                            <span class="icon-tile w-8 h-8">
                                @include('partials.category-icon', ['category' => $cat, 'class' => 'w-4 h-4'])
                            </span>
                            <span class="text-sm font-medium">{{ $cat->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            <a href="/blog" class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-[#2a2a2a] rounded-lg text-slate-700 dark:text-slate-300 font-medium">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path stroke-linecap="round" stroke-linejoin="round" d="M18 14h-8"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 18h-5"/></svg> Blog
            </a>
            <a href="/about" class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-[#2a2a2a] rounded-lg text-slate-700 dark:text-slate-300 font-medium">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 16v-4"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01"/></svg> About
            </a>
            @if(setting('top_contributors_enabled', '1') === '1')
            <a href="/top-contributors" class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-[#2a2a2a] rounded-lg text-slate-700 dark:text-slate-300 font-medium">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM4 21v-1a7 7 0 0 1 14 0v1"/></svg> Top Contributors
            </a>
            @endif
            <a href="/contact" class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-[#2a2a2a] rounded-lg text-slate-700 dark:text-slate-300 font-medium">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m22 7-8.5 5.5a4 4 0 0 1-3 0L2 7"/><rect width="20" height="14" x="2" y="5" rx="2"/></svg> Contact
            </a>
            @if(auth()->check())
                @if(auth()->user()->browsingAsUser())
                    <form method="POST" action="{{ route('switch-back-to-admin') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 text-slate-900 font-semibold bg-amber-400 hover:bg-amber-300 transition cursor-pointer">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2zm10-10V7a4 4 0 0 0-8 0v4h8z"/></svg>
                            Switch to Admin
                        </button>
                    </form>
                    <a href="{{ route('author.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 hover:bg-[#27654A] rounded-lg text-white font-semibold bg-[#2E7856]">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18"/></svg>
                        My Dashboard
                    </a>
                @else
                    <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('author.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 hover:bg-[#27654A] rounded-lg text-white font-semibold bg-[#2E7856]">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18"/></svg>
                        {{ auth()->user()->role === 'admin' ? 'Admin Panel' : 'My Dashboard' }}
                    </a>
                @endif
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-[#2a2a2a] text-slate-700 dark:text-slate-300 font-medium">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sign out
                </a>
                <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">@csrf</form>
            @else
                <a href="{{ route('login') }}" class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-[#2a2a2a] rounded-lg text-slate-700 dark:text-slate-300 font-medium">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3m0 0l4 4m-4-4l4-4"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 4v16a2 2 0 01-2 2H9"/></svg>
                    Sign in
                </a>
                <a href="{{ route('register') }}" class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-800 rounded-lg text-white font-semibold bg-slate-900">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2M9 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Create account
                </a>
            @endif
        </nav>
    </div>
</div>
<script>
(function(){
    const btn = document.getElementById('categories-btn');
    const menu = document.getElementById('categories-menu');
    if (btn && menu) {
        btn.addEventListener('click', (e)=>{
            e.stopPropagation();
            menu.classList.toggle('hidden');
        });
        document.addEventListener('click', (e)=>{
            if(!menu.contains(e.target) && !btn.contains(e.target)) menu.classList.add('hidden');
        });
    }
    const drawer = document.getElementById('mobile-drawer');
    const panel = document.getElementById('mobile-panel');
    const openBtn = document.getElementById('mobile-menu-btn');
    const closeBtn = document.getElementById('mobile-close');
    const backdrop = document.getElementById('mobile-backdrop');
    let scrollY = 0;
    function lockScroll(){
        scrollY = window.scrollY;
        document.documentElement.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.top = `-${scrollY}px`;
        document.body.style.left = '0';
        document.body.style.right = '0';
        document.body.style.width = '100%';
    }
    function unlockScroll(){
        document.documentElement.style.overflow = '';
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        document.body.style.width = '';
        window.scrollTo(0, scrollY);
    }
    if (drawer && openBtn) {
        function openDrawer(){
            drawer.classList.remove('hidden');
            drawer.setAttribute('aria-hidden', 'false');
            openBtn.setAttribute('aria-expanded', 'true');
            lockScroll();
        }
        function closeDrawer(){
            drawer.classList.add('hidden');
            drawer.setAttribute('aria-hidden', 'true');
            if (openBtn) openBtn.setAttribute('aria-expanded', 'false');
            if (!document.getElementById('search-overlay') || document.getElementById('search-overlay').classList.contains('hidden-overlay')) {
                unlockScroll();
            }
        }
        window.closeDrawer = closeDrawer;
        openBtn.addEventListener('click', openDrawer);
        if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
        if (backdrop) backdrop.addEventListener('click', closeDrawer);
        if (panel) {
            let startY = 0;
            panel.addEventListener('touchstart', e=>{ startY = e.touches[0].clientY; }, {passive:true});
            panel.addEventListener('touchmove', e=>{
                const el = panel;
                const atTop = el.scrollTop <= 0;
                const atBottom = el.scrollTop + el.clientHeight >= el.scrollHeight - 1;
                const goingUp = e.touches[0].clientY > startY;
                const goingDown = e.touches[0].clientY < startY;
                if ((atTop && goingUp) || (atBottom && goingDown)) e.preventDefault();
            }, {passive:false});
        }
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !drawer.classList.contains('hidden')) closeDrawer();
        });
        window.addEventListener('resize', ()=>{
            if (window.innerWidth >= 1024 && !drawer.classList.contains('hidden')) closeDrawer();
        });
    }
})();
</script>
