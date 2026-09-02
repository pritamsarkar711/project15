{{-- Full width search overlay: opened from header search icon --}}
<div id="search-overlay" class="hidden-overlay fixed inset-0 z-[60]" role="dialog" aria-modal="true" aria-label="Search Huvanti">
    <div class="absolute inset-0 bg-slate-900/60 dark:bg-black/70 backdrop-blur-sm" onclick="closeSearch()"></div>
    <div class="search-panel relative bg-white dark:bg-[#0f1115] shadow-2xl border-b border-[#e6e8ee] dark:border-[#22262e] max-h-[90vh] overflow-y-auto overscroll-contain">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-6 sm:py-8">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <form action="{{ route('search') }}" method="GET" class="flex-1 flex items-center bg-[#f7f8fa] dark:bg-[#14171d] border border-[#e6e8ee] dark:border-[#2c313c] focus-within:border-[var(--brand)] rounded-xl px-3 sm:px-4 min-w-0 overflow-hidden transition" onsubmit="return submitSearch(this)">
                    <input type="text" id="search-overlay-input" name="q" autocomplete="off" placeholder="Search articles, topics, ideas..." class="flex-1 min-w-0 h-12 sm:h-14 bg-transparent border-0 outline-none text-[15px] sm:text-lg text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500" aria-label="Search">
                    <button type="submit" class="h-9 sm:h-10 px-4 sm:px-6 ml-2 sm:ml-3 shrink-0 rounded-lg bg-[var(--brand)] hover:bg-[var(--brand-strong)] text-white text-sm font-semibold transition whitespace-nowrap">Search</button>
                </form>
                <button onclick="closeSearch()" class="btn-icon !w-11 !h-11 sm:!w-12 sm:!h-12 border border-[#e6e8ee] dark:border-[#2c313c] shrink-0" aria-label="Close search">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="mt-5">
                <div class="text-xs font-semibold tracking-widest uppercase text-slate-400 dark:text-slate-500 mb-2.5">Popular categories</div>
                <div class="flex flex-wrap gap-2">
                    @php
                        try { $overlayCategories = \App\Models\Category::live()->orderBy('sort_order')->take(6)->get(); }
                        catch (\Throwable $e) { $overlayCategories = collect(); }
                    @endphp
                    @foreach($overlayCategories as $cat)
                        <a href="{{ route('category.show',$cat->slug) }}" onclick="closeSearch()" class="inline-flex items-center gap-2 px-3.5 h-10 bg-white dark:bg-[#14171d] border border-[#e6e8ee] dark:border-[#2c313c] rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:border-[var(--brand)]/50 hover:text-[var(--brand)] dark:hover:text-[var(--brand-light)] hover:bg-[var(--brand-tint)]/40 dark:hover:bg-[#14171d] transition">
                            <span class="text-[var(--brand-strong)] dark:text-[var(--brand-light)]">@include('partials.category-icon', ['category' => $cat, 'class' => 'w-4 h-4'])</span>
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    let searchScrollY = 0;
    function openSearch(){
        const o = document.getElementById('search-overlay');
        o.classList.remove('hidden-overlay');
        if (!document.getElementById('mobile-drawer') || document.getElementById('mobile-drawer').classList.contains('hidden')) {
            searchScrollY = window.scrollY;
            document.documentElement.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.top = `-${searchScrollY}px`;
            document.body.style.left = '0';
            document.body.style.right = '0';
            document.body.style.width = '100%';
        }
        setTimeout(()=>{ document.getElementById('search-overlay-input').focus(); }, 120);
    }
    function closeSearch(){
        document.getElementById('search-overlay').classList.add('hidden-overlay');
        if (!document.getElementById('mobile-drawer') || document.getElementById('mobile-drawer').classList.contains('hidden')) {
            document.documentElement.style.overflow = '';
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.left = '';
            document.body.style.right = '';
            document.body.style.width = '';
            window.scrollTo(0, searchScrollY);
        }
    }
    function submitSearch(form){
        closeSearch();
        return true;
    }
    document.addEventListener('keydown', e=>{
        if(e.key === 'Escape') closeSearch();
        if((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k'){
            e.preventDefault();
            openSearch();
        }
    });
</script>
