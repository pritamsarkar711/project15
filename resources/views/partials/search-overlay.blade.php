{{-- Full width search overlay: opened from header search icon --}}
<div id="search-overlay" class="hidden-overlay fixed inset-0 z-[60]" role="dialog" aria-modal="true" aria-label="Search Huvanti">
    <div class="absolute inset-0 bg-slate-900/60 dark:bg-black/70 backdrop-blur-sm" onclick="closeSearch()"></div>
    <div class="search-panel relative bg-white dark:bg-[#1e1e1e] shadow-2xl border-b border-slate-200 dark:border-[#383838]">
        <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-8 sm:py-10">
            <div class="flex items-center gap-3">
                <span class="w-11 h-11 bg-emerald-50 dark:bg-emerald-400/10 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-[#0C3B2E] dark:text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg>
                </span>
                <form action="{{ route('search') }}" method="GET" class="flex-1 flex items-center bg-slate-100 dark:bg-[#2a2a2a] px-4 sm:px-5" onsubmit="return submitSearch(this)">
                    <input type="text" id="search-overlay-input" name="q" autocomplete="off" placeholder="Search articles, topics, ideas..." class="flex-1 h-12 sm:h-14 bg-transparent border-0 outline-none text-[15px] sm:text-lg text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 min-w-0" aria-label="Search">
                    <button type="submit" class="h-9 sm:h-10 px-4 sm:px-6 ml-3 shrink-0 bg-[#0C3B2E] hover:bg-[#072A20] text-white text-sm font-semibold transition">Search</button>
                </form>
                <button onclick="closeSearch()" class="w-11 h-11 bg-slate-100 dark:bg-[#2a2a2a] hover:bg-slate-200 dark:hover:bg-[#333] flex items-center justify-center text-slate-600 dark:text-slate-300 shrink-0" aria-label="Close search">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="mt-5">
                <div class="text-xs font-semibold tracking-widest uppercase text-slate-400 dark:text-slate-500 mb-2.5">Popular categories</div>
                <div class="flex flex-wrap gap-2">
                    @foreach(\App\Models\Category::where('is_active',true)->orderBy('sort_order')->take(6)->get() as $cat)
                        <a href="{{ route('category.show',$cat->slug) }}" onclick="closeSearch()" class="inline-flex items-center gap-2 px-3.5 h-10 bg-emerald-50 dark:bg-[#2a2a2a] border border-emerald-100 dark:border-[#383838] text-sm font-medium text-[#0C3B2E] dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-[#333] transition">
                            <span class="text-[#0C3B2E] dark:text-emerald-300">@include('partials.category-icon', ['category' => $cat, 'class' => 'w-4 h-4'])</span>
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function openSearch(){
        const o = document.getElementById('search-overlay');
        o.classList.remove('hidden-overlay');
        document.body.style.overflow = 'hidden';
        setTimeout(()=>{ document.getElementById('search-overlay-input').focus(); }, 120);
    }
    function closeSearch(){
        document.getElementById('search-overlay').classList.add('hidden-overlay');
        document.body.style.overflow = '';
    }
    function submitSearch(form){
        closeSearch();
        return true;
    }
    document.addEventListener('keydown', e=>{
        if(e.key === 'Escape') closeSearch();
        // Ctrl+K / Cmd+K opens search (repo quality pattern)
        if((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k'){
            e.preventDefault();
            openSearch();
        }
    });
</script>
