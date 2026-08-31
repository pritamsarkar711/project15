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
<header class="sticky top-0 z-40 backdrop-blur">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16 gap-3">
            <div class="flex items-center gap-1 min-w-0">
                <button id="mobile-menu-btn" class="w-10 h-10 flex items-center justify-center lg:hidden text-[#141A16] dark:text-[#EDEFEA] hover:bg-[#EFEFE8] dark:hover:bg-[#1E2420] transition" aria-label="Open menu" aria-controls="mobile-drawer" aria-expanded="false">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <a href="/" class="flex items-center shrink-0 mr-4" aria-label="huvanti.com home">
                    @include('partials.logo', ['class' => 'h-8'])
                </a>
                <nav class="hidden lg:flex items-center -ml-2">
                    <a href="/" class="navlink {{ request()->is('/') ? 'navlink-active' : '' }}">Home</a>
                    <div class="relative">
                        <button id="categories-btn" class="navlink" aria-haspopup="true" aria-expanded="false">
                            Categories
                            <svg class="w-3.5 h-3.5 opacity-60 shrink-0 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="categories-menu" class="hidden absolute left-0 top-full w-[440px] max-w-[calc(100vw-2rem)] bg-white dark:bg-[#141815] border border-[#141A16] dark:border-[#3A443D] shadow-[8px_8px_0_0_#F5C445] z-50">
                            <div class="grid grid-cols-2">
                                @foreach($categories as $cat)
                                    <a href="{{ route('category.show',$cat->slug) }}" class="group flex items-center gap-3 px-4 py-3.5 border-b border-r border-[#E4E4DA] dark:border-[#262C28] [&:nth-child(2n)]:border-r-0 hover:bg-[#F5C445]/15 dark:hover:bg-[#F5C445]/5 transition">
                                        <span class="chip w-9 h-9">
                                            @include('partials.category-icon', ['category' => $cat, 'class' => 'w-[18px] h-[18px]'])
                                        </span>
                                        <span class="text-[13px] font-bold text-[#141A16] dark:text-[#EDEFEA] group-hover:translate-x-0.5 transition-transform">{{ $cat->name }}</span>
                                    </a>
                                @endforeach
                            </div>
                            <a href="/blog" class="flex items-center justify-between px-4 py-3 text-[11px] font-extrabold tracking-[0.18em] uppercase text-[#0C3B2E] dark:text-[#34D399] hover:bg-[#141A16] hover:text-white dark:hover:text-[#F5C445] transition">
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
            <div class="flex items-center gap-1 shrink-0">
                <button onclick="openSearch()" class="w-10 h-10 flex items-center justify-center text-[#141A16] dark:text-[#EDEFEA] hover:bg-[#EFEFE8] dark:hover:bg-[#1E2420] transition" aria-label="Search">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg>
                </button>
                <button onclick="toggleTheme()" class="w-10 h-10 flex items-center justify-center text-[#141A16] dark:text-[#EDEFEA] hover:bg-[#EFEFE8] dark:hover:bg-[#1E2420] transition" aria-label="Toggle theme">
                    <svg class="w-5 h-5 dark:hidden shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.002 9.002 0 0012 21a9.002 9.002 0 008.354-5.646z"/></svg>
                    <svg class="w-5 h-5 hidden dark:block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
                @if(auth()->check())
                    @if(auth()->user()->browsingAsUser())
                        <form method="POST" action="{{ route('switch-back-to-admin') }}" class="hidden sm:block">
                            @csrf
                            <button type="submit" class="h-9 px-3.5 text-[12px] font-extrabold uppercase tracking-wide bg-[#F5C445] hover:bg-[#EAB63A] text-[#141A16] transition cursor-pointer">
                                Switch to Admin
                            </button>
                        </form>
                        <a href="{{ route('author.dashboard') }}" class="hidden sm:inline-flex items-center h-9 px-3.5 text-[12px] font-extrabold uppercase tracking-wide bg-[#141A16] hover:bg-[#0C3B2E] text-white transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('author.dashboard') }}" class="hidden sm:inline-flex items-center h-9 px-3.5 text-[12px] font-extrabold uppercase tracking-wide bg-[#141A16] hover:bg-[#0C3B2E] text-white transition">
                            {{ auth()->user()->role === 'admin' ? 'Admin' : 'Dashboard' }}
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center h-9 px-3.5 text-[12px] font-extrabold uppercase tracking-wide text-[#141A16] dark:text-[#EDEFEA] hover:bg-[#EFEFE8] dark:hover:bg-[#1E2420] transition">Sign in</a>
                    <a href="{{ route('register') }}" class="hidden sm:inline-flex items-center h-9 px-4 text-[12px] font-extrabold uppercase tracking-wide bg-[#F5C445] hover:bg-[#EAB63A] text-[#141A16] transition">Start writing</a>
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
    <div id="mobile-backdrop" class="absolute inset-0 bg-[#0D100E]/60 backdrop-blur-sm"></div>
    <div id="mobile-panel" class="absolute left-0 top-0 bottom-0 w-[300px] max-w-[85vw] bg-white dark:bg-[#141815] shadow-[8px_0_0_0_rgba(20,26,22,0.15)] overflow-y-auto overscroll-contain border-r-2 border-[#141A16] dark:border-[#3A443D]">
        <div class="h-16 px-4 flex items-center justify-between border-b border-[#E4E4DA] dark:border-[#262C28]">
            <a href="/" class="flex items-center">
                @include('partials.logo', ['class' => 'h-7', 'textClass' => 'text-[19px]'])
            </a>
            <button id="mobile-close" class="w-9 h-9 flex items-center justify-center text-[#5C665E] dark:text-[#97A199] hover:bg-[#EFEFE8] dark:hover:bg-[#1E2420]" aria-label="Close menu">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <nav class="p-4 space-y-1">
            <a href="/" class="flex items-center gap-3 px-3 py-2.5 hover:bg-[#F5C445]/15 dark:hover:bg-[#F5C445]/5 text-[#141A16] dark:text-[#EDEFEA] font-bold text-[15px]">
                Home
            </a>
            <button onclick="closeDrawer(); openSearch();" class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-[#F5C445]/15 dark:hover:bg-[#F5C445]/5 text-[#141A16] dark:text-[#EDEFEA] font-bold text-[15px]">
                Search
            </button>
            <div class="pt-2">
                <div class="px-3 pb-1.5 text-[10px] font-extrabold tracking-[0.22em] text-[#5C665E] dark:text-[#97A199] uppercase flex items-center gap-2"><span class="w-4 h-[3px] bg-[#F5C445] inline-block"></span> Categories</div>
                <div class="space-y-0.5">
                    @foreach($categories as $cat)
                        <a href="{{ route('category.show',$cat->slug) }}" class="flex items-center gap-3 px-3 py-2 hover:bg-[#F5C445]/15 dark:hover:bg-[#F5C445]/5 text-[#141A16] dark:text-[#EDEFEA]">
                            <span class="chip w-8 h-8">
                                @include('partials.category-icon', ['category' => $cat, 'class' => 'w-4 h-4'])
                            </span>
                            <span class="text-sm font-semibold">{{ $cat->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            <a href="/blog" class="flex items-center gap-3 px-3 py-2.5 hover:bg-[#F5C445]/15 dark:hover:bg-[#F5C445]/5 text-[#141A16] dark:text-[#EDEFEA] font-bold text-[15px]">
                Blog
            </a>
            <a href="/about" class="flex items-center gap-3 px-3 py-2.5 hover:bg-[#F5C445]/15 dark:hover:bg-[#F5C445]/5 text-[#141A16] dark:text-[#EDEFEA] font-bold text-[15px]">
                About
            </a>
            @if(setting('top_contributors_enabled', '1') === '1')
            <a href="/top-contributors" class="flex items-center gap-3 px-3 py-2.5 hover:bg-[#F5C445]/15 dark:hover:bg-[#F5C445]/5 text-[#141A16] dark:text-[#EDEFEA] font-bold text-[15px]">
                Top Contributors
            </a>
            @endif
            <a href="/contact" class="flex items-center gap-3 px-3 py-2.5 hover:bg-[#F5C445]/15 dark:hover:bg-[#F5C445]/5 text-[#141A16] dark:text-[#EDEFEA] font-bold text-[15px]">
                Contact
            </a>
            @if(auth()->check())
                <div class="pt-3 mt-2 border-t border-[#E4E4DA] dark:border-[#262C28]">
                @if(auth()->user()->browsingAsUser())
                    <form method="POST" action="{{ route('switch-back-to-admin') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center h-10 px-3 text-[12px] font-extrabold uppercase tracking-wide bg-[#F5C445] hover:bg-[#EAB63A] text-[#141A16] transition cursor-pointer">
                            Switch to Admin
                        </button>
                    </form>
                    <a href="{{ route('author.dashboard') }}" class="mt-2 flex items-center justify-center h-10 px-3 text-[12px] font-extrabold uppercase tracking-wide bg-[#141A16] hover:bg-[#0C3B2E] text-white transition">
                        My Dashboard
                    </a>
                @else
                    <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('author.dashboard') }}" class="flex items-center justify-center h-10 px-3 text-[12px] font-extrabold uppercase tracking-wide bg-[#141A16] hover:bg-[#0C3B2E] text-white transition">
                        {{ auth()->user()->role === 'admin' ? 'Admin Panel' : 'My Dashboard' }}
                    </a>
                @endif
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="mt-2 flex items-center justify-center h-10 px-3 text-[12px] font-extrabold uppercase tracking-wide border border-[#141A16] dark:border-[#3A443D] text-[#141A16] dark:text-[#EDEFEA] hover:bg-[#EFEFE8] dark:hover:bg-[#1E2420] transition">
                        Sign out
                    </a>
                    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">@csrf</form>
                </div>
            @else
                <div class="pt-3 mt-2 border-t border-[#E4E4DA] dark:border-[#262C28] space-y-2">
                    <a href="{{ route('login') }}" class="flex items-center justify-center h-10 px-3 text-[12px] font-extrabold uppercase tracking-wide border border-[#141A16] dark:border-[#3A443D] text-[#141A16] dark:text-[#EDEFEA] hover:bg-[#EFEFE8] dark:hover:bg-[#1E2420] transition">
                        Sign in
                    </a>
                    <a href="{{ route('register') }}" class="flex items-center justify-center h-10 px-3 text-[12px] font-extrabold uppercase tracking-wide bg-[#F5C445] hover:bg-[#EAB63A] text-[#141A16] transition">
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
