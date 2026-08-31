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
<header class="sticky top-0 z-40 bg-white/85 dark:bg-[#0A0F0D]/85 backdrop-blur-md border-b border-slate-200/80 dark:border-[#1F2925]">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16 gap-3">
            <div class="flex items-center gap-1 min-w-0">
                <button id="mobile-menu-btn" class="w-10 h-10 flex items-center justify-center rounded-lg text-slate-700 hover:bg-slate-100 dark:text-[#C6D2CB] dark:hover:bg-white/5 transition" aria-label="Open menu" aria-controls="mobile-drawer" aria-expanded="false">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <a href="/" class="flex items-center shrink-0 mr-3" aria-label="huvanti.com home">
                    @include('partials.logo', ['class' => 'h-8'])
                </a>
                <nav class="hidden lg:flex items-center gap-0.5">
                    <a href="/" class="navlink {{ request()->is('/') ? 'navlink-active' : '' }}">Home</a>
                    <div class="relative">
                        <button id="categories-btn" class="navlink" aria-haspopup="true" aria-expanded="false">
                            Categories
                            <svg class="w-3.5 h-3.5 opacity-60 shrink-0 ml-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="categories-menu" class="hidden absolute left-0 top-full mt-2 w-[480px] max-w-[calc(100vw-2rem)] popover-card p-2 z-50">
                            <div class="grid grid-cols-2 gap-1">
                                @foreach($categories as $cat)
                                    <a href="{{ route('category.show',$cat->slug) }}" class="group flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition">
                                        <span class="chip w-9 h-9">
                                            @include('partials.category-icon', ['category' => $cat, 'class' => 'w-[18px] h-[18px]'])
                                        </span>
                                        <span class="text-[13.5px] font-semibold text-slate-700 dark:text-[#D5E0D9] group-hover:text-emerald-700 dark:group-hover:text-emerald-300 transition-colors">{{ $cat->name }}</span>
                                    </a>
                                @endforeach
                            </div>
                            <a href="/blog" class="mt-1 flex items-center justify-between px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 text-[12.5px] font-semibold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition">
                                View all posts
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                    <a href="/blog" class="navlink {{ request()->is('blog*') ? 'navlink-active' : '' }}">Blog</a>
                    <a href="/about" class="navlink {{ request()->is('about') ? 'navlink-active' : '' }}">About</a>
                    @if(setting('top_contributors_enabled', '1') === '1')
                    <a href="/top-contributors" class="navlink {{ request()->is('top-contributors') ? 'navlink-active' : '' }}">Contributors</a>
                    @endif
                    <a href="/contact" class="navlink {{ request()->is('contact') ? 'navlink-active' : '' }}">Contact</a>
                </nav>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <button onclick="openSearch()" class="w-10 h-10 flex items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 dark:text-[#C6D2CB] dark:hover:bg-white/5 transition" aria-label="Search">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg>
                </button>
                <button onclick="toggleTheme()" class="w-10 h-10 flex items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 dark:text-[#C6D2CB] dark:hover:bg-white/5 transition" aria-label="Toggle theme">
                    <svg class="w-5 h-5 dark:hidden shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.002 9.002 0 0012 21a9.002 9.002 0 008.354-5.646z"/></svg>
                    <svg class="w-5 h-5 hidden dark:block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                <span class="hidden sm:block w-px h-6 bg-slate-200 dark:bg-[#1F2925] mx-1"></span>
                @if(auth()->check())
                    @if(auth()->user()->browsingAsUser())
                        <form method="POST" action="{{ route('switch-back-to-admin') }}" class="hidden sm:block">
                            @csrf
                            <button type="submit" class="inline-flex items-center h-9 px-4 rounded-lg text-[13px] font-semibold bg-amber-400 hover:bg-amber-300 text-amber-950 shadow-sm transition cursor-pointer">
                                Switch to Admin
                            </button>
                        </form>
                        <a href="{{ route('author.dashboard') }}" class="hidden sm:inline-flex items-center h-9 px-4 rounded-lg text-[13px] font-semibold bg-slate-900 hover:bg-slate-800 text-white shadow-sm transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('author.dashboard') }}" class="hidden sm:inline-flex items-center h-9 px-4 rounded-lg text-[13px] font-semibold bg-slate-900 hover:bg-slate-800 text-white shadow-sm transition">
                            {{ auth()->user()->role === 'admin' ? 'Admin' : 'Dashboard' }}
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center h-9 px-4 rounded-lg text-[13px] font-semibold text-slate-700 hover:bg-slate-100 dark:text-[#C6D2CB] dark:hover:bg-white/5 transition">Sign in</a>
                    <a href="{{ route('register') }}" class="hidden sm:inline-flex items-center h-9 px-4 rounded-lg text-[13px] font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm shadow-emerald-600/25 transition">Start writing</a>
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
    <div id="mobile-panel" class="absolute left-0 top-0 bottom-0 w-[310px] max-w-[85vw] bg-white dark:bg-[#0F1613] shadow-2xl overflow-y-auto overscroll-contain rounded-r-2xl">
        <div class="h-16 px-4 flex items-center justify-between border-b border-slate-100 dark:border-[#1F2925]">
            <a href="/" class="flex items-center">
                @include('partials.logo', ['class' => 'h-7', 'textClass' => 'text-[19px]'])
            </a>
            <button id="mobile-close" class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:text-[#8FA398] dark:hover:bg-white/5 transition" aria-label="Close menu">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <nav class="p-4 space-y-1">
            <a href="/" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 text-slate-800 dark:text-[#D5E0D9] font-semibold text-[15px] transition">
                Home
            </a>
            <button onclick="closeDrawer(); openSearch();" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 text-slate-800 dark:text-[#D5E0D9] font-semibold text-[15px] transition">
                Search
            </button>
            <div class="pt-3">
                <div class="px-3 pb-1.5 text-[11px] font-bold tracking-[0.12em] text-slate-400 dark:text-[#6B7F75] uppercase">Categories</div>
                <div class="space-y-0.5">
                    @foreach($categories as $cat)
                        <a href="{{ route('category.show',$cat->slug) }}" class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-emerald-50 dark:hover:bg-emerald-500/10 transition">
                            <span class="chip w-8 h-8">
                                @include('partials.category-icon', ['category' => $cat, 'class' => 'w-4 h-4'])
                            </span>
                            <span class="text-sm font-medium text-slate-700 dark:text-[#C6D2CB]">{{ $cat->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            <a href="/blog" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 text-slate-800 dark:text-[#D5E0D9] font-semibold text-[15px] transition">
                Blog
            </a>
            <a href="/about" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 text-slate-800 dark:text-[#D5E0D9] font-semibold text-[15px] transition">
                About
            </a>
            @if(setting('top_contributors_enabled', '1') === '1')
            <a href="/top-contributors" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 text-slate-800 dark:text-[#D5E0D9] font-semibold text-[15px] transition">
                Top Contributors
            </a>
            @endif
            <a href="/contact" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 text-slate-800 dark:text-[#D5E0D9] font-semibold text-[15px] transition">
                Contact
            </a>
            @if(auth()->check())
                <div class="pt-4 mt-2 border-t border-slate-100 dark:border-[#1F2925] space-y-2">
                @if(auth()->user()->browsingAsUser())
                    <form method="POST" action="{{ route('switch-back-to-admin') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center h-10 px-3 rounded-xl text-[13px] font-semibold bg-amber-400 hover:bg-amber-300 text-amber-950 transition cursor-pointer">
                            Switch to Admin
                        </button>
                    </form>
                    <a href="{{ route('author.dashboard') }}" class="flex items-center justify-center h-10 px-3 rounded-xl text-[13px] font-semibold bg-slate-900 hover:bg-slate-800 text-white transition">
                        My Dashboard
                    </a>
                @else
                    <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('author.dashboard') }}" class="flex items-center justify-center h-10 px-3 rounded-xl text-[13px] font-semibold bg-slate-900 hover:bg-slate-800 text-white transition">
                        {{ auth()->user()->role === 'admin' ? 'Admin Panel' : 'My Dashboard' }}
                    </a>
                @endif
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="flex items-center justify-center h-10 px-3 rounded-xl text-[13px] font-semibold border border-slate-200 dark:border-[#2C3833] text-slate-700 dark:text-[#C6D2CB] hover:bg-slate-50 dark:hover:bg-white/5 transition">
                        Sign out
                    </a>
                    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">@csrf</form>
                </div>
            @else
                <div class="pt-4 mt-2 border-t border-slate-100 dark:border-[#1F2925] space-y-2">
                    <a href="{{ route('login') }}" class="flex items-center justify-center h-10 px-3 rounded-xl text-[13px] font-semibold border border-slate-200 dark:border-[#2C3833] text-slate-700 dark:text-[#C6D2CB] hover:bg-slate-50 dark:hover:bg-white/5 transition">
                        Sign in
                    </a>
                    <a href="{{ route('register') }}" class="flex items-center justify-center h-10 px-3 rounded-xl text-[13px] font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm shadow-emerald-600/25 transition">
                        Start writing
                    </a>
                </div>
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
