@php
    $categories = \App\Models\Category::where('is_active',true)->orderBy('sort_order')->take(6)->get();
@endphp
<header class="sticky top-0 z-40 bg-white/95 dark:bg-[#1e1e1e]/95 backdrop-blur shadow-[0_2px_4px_rgba(0,0,0,0.08)] dark:shadow-[0_2px_4px_rgba(0,0,0,0.5)]">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6">
        <div class="flex items-center h-16 gap-3">
            <!-- Mobile menu -->
            <button id="mobile-menu-btn" class="w-10 h-10 flex items-center justify-center lg:hidden text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#2a2a2a] transition" aria-label="Open menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <!-- Logo: text-only huvanti.com (or uploaded logos) -->
            <a href="/" class="flex items-center shrink-0" aria-label="huvanti.com home">
                @include('partials.logo', ['class' => 'h-8'])
            </a>

            <!-- Desktop Nav -->
            <nav class="hidden lg:flex items-center gap-1 ml-6 flex-1">
                <a href="/" class="px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#2a2a2a] transition {{ request()->is('/') ? '!text-[#0C3B2E] dark:!text-emerald-300 bg-emerald-50 dark:bg-emerald-400/10' : '' }}">Home</a>
                <div class="relative">
                    <button id="categories-btn" class="px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#2a2a2a] transition inline-flex items-center gap-1.5" aria-haspopup="true" aria-expanded="false">
                        Categories
                        <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="categories-menu" class="hidden absolute left-0 top-full mt-2 w-[480px] card-elev ! shadow-[0_8px_30px_rgba(0,0,0,0.16)] dark:shadow-black/50 p-2 z-50">
                        <div class="grid grid-cols-2 gap-1">
                            @foreach($categories as $cat)
                                <a href="{{ route('category.show',$cat->slug) }}" class="flex items-center gap-3 p-2.5 hover:bg-slate-50 dark:hover:bg-[#2a2a2a] transition">
                                    <span class="w-9 h-9 bg-emerald-50 dark:bg-emerald-400/10 flex items-center justify-center text-[#0C3B2E] dark:text-emerald-300 shrink-0">
                                        @include('partials.category-icon', ['category' => $cat, 'class' => 'w-[18px] h-[18px]'])
                                    </span>
                                    <span class="text-sm font-medium text-slate-900 dark:text-white">{{ $cat->name }}</span>
                                </a>
                            @endforeach
                        </div>
                        <a href="/blog" class="block text-center text-xs font-semibold text-[#0C3B2E] dark:text-emerald-300 hover:text-[#072A20] dark:hover:text-emerald-200 py-2.5 mt-1 border-t border-slate-100 dark:border-[#2f2f2f]">View all posts</a>
                    </div>
                </div>
                <a href="/blog" class="px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#2a2a2a] transition {{ request()->is('blog*') ? '!text-[#0C3B2E] dark:!text-emerald-300 bg-emerald-50 dark:bg-emerald-400/10' : '' }}">Blog</a>
                <a href="/about" class="px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#2a2a2a] transition {{ request()->is('about') ? '!text-[#0C3B2E] dark:!text-emerald-300 bg-emerald-50 dark:bg-emerald-400/10' : '' }}">About</a>
                <a href="/contact" class="px-3 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-[#2a2a2a] transition {{ request()->is('contact') ? '!text-[#0C3B2E] dark:!text-emerald-300 bg-emerald-50 dark:bg-emerald-400/10' : '' }}">Contact</a>
            </nav>

            <!-- Right actions -->
            <div class="ml-auto flex items-center gap-1.5">
                <button onclick="openSearch()" class="w-10 h-10 flex items-center justify-center text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#2a2a2a] transition" aria-label="Search">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg>
                </button>
                <button onclick="toggleTheme()" class="w-10 h-10 flex items-center justify-center text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#2a2a2a] transition" aria-label="Toggle theme">
                    <svg class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.002 9.002 0 0012 21a9.002 9.002 0 008.354-5.646z"/></svg>
                    <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Drawer (repo pattern: 280px) -->
    <div id="mobile-drawer" class="fixed inset-0 z-50 hidden lg:hidden">
        <div id="mobile-backdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <div class="absolute left-0 top-0 bottom-0 w-[280px] bg-white dark:bg-[#1e1e1e] shadow-2xl overflow-y-auto">
            <div class="h-16 px-4 flex items-center justify-between border-b border-slate-200 dark:border-[#2f2f2f]">
                <a href="/" class="flex items-center">
                    @include('partials.logo', ['class' => 'h-7', 'textClass' => 'text-[19px]'])
                </a>
                <button id="mobile-close" class="w-9 h-9 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-[#2a2a2a]" aria-label="Close menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <nav class="p-4 space-y-4">
                <a href="/" class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-[#2a2a2a] text-slate-700 dark:text-slate-300 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z"/></svg> Home
                </a>
                <button onclick="closeDrawer(); openSearch();" class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-[#2a2a2a] text-slate-700 dark:text-slate-300 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg> Search
                </button>
                <div>
                    <div class="px-3 py-2 text-xs font-bold tracking-widest text-[#0C3B2E] dark:text-emerald-300 uppercase">Categories</div>
                    <div class="space-y-1">
                        @foreach($categories as $cat)
                            <a href="{{ route('category.show',$cat->slug) }}" class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 dark:hover:bg-[#2a2a2a] text-slate-700 dark:text-slate-300">
                                <span class="w-8 h-8 bg-emerald-50 dark:bg-emerald-400/10 flex items-center justify-center text-[#0C3B2E] dark:text-emerald-300">
                                    @include('partials.category-icon', ['category' => $cat, 'class' => 'w-4 h-4'])
                                </span>
                                <span class="text-sm font-medium">{{ $cat->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
                <a href="/blog" class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-[#2a2a2a] text-slate-700 dark:text-slate-300 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path stroke-linecap="round" stroke-linejoin="round" d="M18 14h-8"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 18h-5"/></svg> Blog
                </a>
                <a href="/about" class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-[#2a2a2a] text-slate-700 dark:text-slate-300 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 16v-4"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01"/></svg> About
                </a>
                <a href="/contact" class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-[#2a2a2a] text-slate-700 dark:text-slate-300 font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m22 7-8.5 5.5a4 4 0 0 1-3 0L2 7"/><rect width="20" height="14" x="2" y="5" rx="2"/></svg> Contact
                </a>
            </nav>
        </div>
    </div>
</header>

<script>
(function(){
    const btn = document.getElementById('categories-btn');
    const menu = document.getElementById('categories-menu');
    if(btn && menu){
        btn.addEventListener('click', (e)=>{
            e.stopPropagation();
            menu.classList.toggle('hidden');
        });
        document.addEventListener('click', (e)=>{
            if(!menu.contains(e.target) && !btn.contains(e.target)) menu.classList.add('hidden');
        });
    }
    const drawer = document.getElementById('mobile-drawer');
    const openBtn = document.getElementById('mobile-menu-btn');
    const closeBtn = document.getElementById('mobile-close');
    const backdrop = document.getElementById('mobile-backdrop');
    function openDrawer(){ drawer.classList.remove('hidden'); document.body.style.overflow='hidden'; }
    function closeDrawer(){ drawer.classList.add('hidden'); document.body.style.overflow=''; }
    window.closeDrawer = closeDrawer;
    openBtn && openBtn.addEventListener('click', openDrawer);
    closeBtn && closeBtn.addEventListener('click', closeDrawer);
    backdrop && backdrop.addEventListener('click', closeDrawer);
})();
</script>
