{{-- Full width search overlay: opened from header search icon --}}
<div id="search-overlay" class="hidden-overlay fixed inset-0 z-[60]" role="dialog" aria-modal="true" aria-label="Search Huvanti">
    <div class="absolute inset-0 bg-slate-900/60 dark:bg-black/75 backdrop-blur-sm" onclick="closeSearch()"></div>
    <div class="search-panel relative bg-white dark:bg-[#0A0F0D] border-b border-slate-200 dark:border-[#1F2925] max-h-[90vh] overflow-y-auto overscroll-contain shadow-2xl">
        <div class="max-w-[900px] mx-auto px-4 sm:px-6 py-7 sm:py-9">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <form action="{{ route('search') }}" method="GET" class="flex-1 flex items-center bg-white dark:bg-[#131A17] border border-slate-200 dark:border-[#2C3833] rounded-2xl shadow-lg shadow-slate-900/5 px-3 sm:px-5 min-w-0 overflow-hidden focus-within:border-emerald-500 focus-within:ring-4 focus-within:ring-emerald-500/15 transition" onsubmit="return submitSearch(this)">
                    <svg class="w-5 h-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg>
                    <input type="text" id="search-overlay-input" name="q" autocomplete="off" placeholder="Search articles, topics, ideas..." class="flex-1 min-w-0 h-12 sm:h-14 bg-transparent border-0 outline-none text-[15px] sm:text-lg font-medium text-slate-900 dark:text-[#E5EDE9] placeholder:text-slate-400 dark:placeholder:text-[#6B7F75] px-3" aria-label="Search">
                    <button type="submit" class="h-9 sm:h-10 px-5 shrink-0 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[13px] font-semibold shadow-sm shadow-emerald-600/25 transition whitespace-nowrap">Search</button>
                </form>
                <button onclick="closeSearch()" class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-white dark:bg-[#131A17] border border-slate-200 dark:border-[#2C3833] hover:bg-slate-50 dark:hover:bg-white/5 flex items-center justify-center text-slate-500 dark:text-[#8FA398] shrink-0 transition" aria-label="Close search">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="mt-6">
                <div class="text-[11px] font-bold tracking-[0.12em] uppercase text-slate-400 dark:text-[#6B7F75] mb-3">Popular categories</div>
                <div class="flex flex-wrap gap-2">
                    @php
                        try { $overlayCategories = \App\Models\Category::live()->orderBy('sort_order')->take(6)->get(); }
                        catch (\Throwable $e) { $overlayCategories = collect(); }
                    @endphp
                    @foreach($overlayCategories as $cat)
                        <a href="{{ route('category.show',$cat->slug) }}" onclick="closeSearch()" class="inline-flex items-center gap-2 px-4 h-10 rounded-full bg-slate-50 dark:bg-[#131A17] border border-slate-200 dark:border-[#2C3833] text-[13.5px] font-medium text-slate-700 dark:text-[#D5E0D9] hover:border-emerald-400 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-300 transition">
                            <span class="text-emerald-600 dark:text-emerald-400">@include('partials.category-icon', ['category' => $cat, 'class' => 'w-4 h-4'])</span>
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
