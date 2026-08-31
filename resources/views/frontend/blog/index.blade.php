@extends('layouts.app')
@php($metaTitle = 'Blog — Latest Articles & Expert Insights | ' . setting('site_name','huvanti.com'))
@section('content')
<div class="bg-slate-50/80 dark:bg-[#0D1411] border-b border-slate-100 dark:border-[#151D19]">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 py-12 sm:py-16">
        <span class="kicker">The journal</span>
        <h1 class="mt-4 text-[36px] sm:text-[52px] font-extrabold text-slate-900 dark:text-[#F1F5F4] tracking-tight leading-[1.05]">Every story,<br class="sm:hidden"> in one place<span class="text-emerald-500">.</span></h1>
        <p class="mt-4 text-[15px] sm:text-base text-slate-500 dark:text-[#8FA398] max-w-2xl leading-relaxed">Technology, health, finance, travel, lifestyle and education — reported clearly, reviewed by humans, published weekly.</p>

        <form action="{{ route('blog.index') }}" method="GET" class="mt-8 max-w-[760px]">
            <div class="bg-white dark:bg-[#131A17] border border-slate-200 dark:border-[#2C3833] rounded-2xl shadow-sm p-2 flex flex-col sm:flex-row gap-2">
                <div class="flex-1 relative min-w-0">
                    <svg class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search posts by title or topic..." autocomplete="off" class="w-full h-11 pl-11 pr-4 bg-slate-50 dark:bg-[#0D1411] border border-slate-200 dark:border-[#2C3833] rounded-xl text-slate-900 dark:text-[#E5EDE9] placeholder:text-slate-400 dark:placeholder:text-[#6B7F75] text-sm font-medium outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 transition">
                </div>
                <select name="category" class="h-11 px-3.5 bg-slate-50 dark:bg-[#0D1411] border border-slate-200 dark:border-[#2C3833] rounded-xl text-slate-700 dark:text-[#C6D2CB] text-sm font-medium outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 transition sm:w-[190px] shrink-0">
                    <option value="">All categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected(request('category')==$cat->slug)>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="h-11 px-7 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-sm shadow-emerald-600/25 transition shrink-0">Filter</button>
                @if(request('q') || request('category'))
                    <a href="{{ route('blog.index') }}" class="h-11 px-5 rounded-xl border border-slate-200 dark:border-[#2C3833] text-slate-600 dark:text-[#8FA398] text-sm font-semibold flex items-center justify-center hover:bg-slate-50 dark:hover:bg-white/5 transition shrink-0">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="max-w-[1280px] mx-auto px-4 sm:px-6 py-12">
    @if($posts->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8">
            @foreach($posts as $p)
                <article class="group card-elev lift overflow-hidden flex flex-col">
                    <a href="{{ route('blog.show',$p->slug) }}" class="relative h-[200px] overflow-hidden block">
                        <img src="{{ storage_image_url($p->featured_image) ?: 'https://picsum.photos/seed/'.$p->slug.'/600/400' }}" alt="{{ image_alt_text($p->featured_image, $p->title) }}" class="w-full h-full object-cover group-hover:scale-[1.04] transition duration-500" loading="lazy" decoding="async">
                        <span class="absolute top-3 left-3 text-[11.5px] font-bold text-emerald-700 dark:text-emerald-300 bg-white/95 dark:bg-[#0D1411]/95 px-2.5 py-1 rounded-full shadow-sm">{{ $p->category->name ?? 'General' }}</span>
                    </a>
                    <div class="p-5 flex flex-col flex-1">
                        <a href="{{ route('blog.show',$p->slug) }}" class="text-[17.5px] font-bold text-slate-900 dark:text-[#F1F5F4] leading-snug line-clamp-2 group-hover:text-emerald-700 dark:group-hover:text-emerald-300 transition-colors">{{ $p->title }}</a>
                        <p class="text-[13.5px] text-slate-500 dark:text-[#8FA398] mt-2.5 leading-relaxed line-clamp-2 flex-1">{{ $p->excerpt }}</p>
                        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-[#1F2925] flex items-center gap-2 text-xs font-medium text-slate-400 dark:text-[#6B7F75]">
                            <span>{{ $p->published_at?->format('M d, Y') }}</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-[#3A4A42]"></span>
                            <span>{{ $p->reading_time }} min read</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-10">{{ $posts->links() }}</div>
    @else
        <div class="text-center py-16 card-elev max-w-xl mx-auto">
            <span class="chip w-14 h-14 mx-auto mb-4">
                <svg class="w-7 h-7 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg>
            </span>
            <p class="text-[15px] font-medium text-slate-500 dark:text-[#8FA398]">No posts found. Try a different search or category.</p>
            <a href="{{ route('blog.index') }}" class="mt-5 btn btn-primary btn-sm mx-auto">View all posts</a>
        </div>
    @endif
</div>
@endsection
