@extends('layouts.app')
@php($metaTitle = 'Blog — Latest Articles & Expert Insights | ' . setting('site_name','huvanti.com'))
@section('content')
<div class="bg-emerald-50/70 dark:bg-[#1e1e1e] border-b border-emerald-100 dark:border-[#2f2f2f]">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-8 sm:py-10">
        <h1 class="text-[30px] sm:text-[36px] font-extrabold text-slate-900 dark:text-white tracking-tight">Blog</h1>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 max-w-2xl">Discover the latest stories across technology, health, finance, travel, lifestyle and education.</p>
        <form action="{{ route('blog.index') }}" method="GET" class="mt-6 bg-white dark:bg-[#2a2a2a] border border-slate-200 dark:border-[#383838] rounded-2xl p-2 flex flex-col sm:flex-row gap-2">
            <div class="flex-1 relative min-w-0">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search posts by title or topic..." autocomplete="off" class="w-full h-11 pl-4 pr-4 bg-slate-50 dark:bg-[#1e1e1e] border border-slate-200 dark:border-[#383838] rounded-full text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 text-sm focus:border-[#05B762] focus:ring-4 focus:ring-[#05B762]/10 outline-none transition"
            </div>
            <select name="category" class="h-11 px-4 bg-slate-50 dark:bg-[#1e1e1e] border border-slate-200 dark:border-[#383838] rounded-full text-slate-900 dark:text-white text-sm focus:border-[#05B762] outline-none sm:w-[200px] shrink-0 transition"
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}" @selected(request('category')==$cat->slug)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="h-11 px-7 rounded-full bg-[#05B762] hover:bg-[#049A53] text-white text-sm font-semibold transition shrink-0">Filter</button>
            @if(request('q') || request('category'))
                <a href="{{ route('blog.index') }}" class="h-11 px-5 rounded-full bg-white dark:bg-[#1e1e1e] border border-slate-200 dark:border-[#383838] text-slate-700 dark:text-slate-300 text-sm font-medium flex items-center justify-center hover:bg-slate-50 dark:hover:bg-[#333] transition shrink-0">Clear</a>
            @endif
        </form>
    </div>
</div>

<div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-10">
    @if($posts->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($posts as $p)
                <article class="group card-elev card-hover overflow-hidden flex flex-col">
                    <a href="{{ route('blog.show',$p->slug) }}" class="relative h-[180px] overflow-hidden block">
                        <img src="{{ storage_image_url($p->featured_image) ?: 'https://picsum.photos/seed/'.$p->slug.'/600/400' }}" alt="{{ image_alt_text($p->featured_image, $p->title) }}" class="w-full h-full object-cover group-hover:scale-[1.03] transition duration-300" loading="lazy" decoding="async">
                        <span class="absolute top-3 left-3 text-xs font-semibold bg-white/95 dark:bg-[#1e1e1e]/90 text-[#049A53] dark:text-emerald-300 px-3 py-1 rounded-full border border-slate-200 dark:border-[#383838] shadow-sm">{{ $p->category->name ?? 'General' }}</span>
                    </a>
                    <div class="p-4 flex flex-col flex-1">
                        <a href="{{ route('blog.show',$p->slug) }}" class="text-[16px] font-semibold text-slate-900 dark:text-white leading-snug line-clamp-2 group-hover:text-[#049A53] dark:group-hover:text-emerald-300 transition-colors">{{ $p->title }}</a>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 line-clamp-2">{{ $p->excerpt }}</p>
                        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-slate-100 dark:border-[#2f2f2f] text-xs text-slate-500 dark:text-slate-400">
                            <span>{{ $p->published_at?->format('M d, Y') }}</span>
                            <span class="w-1 h-1 bg-slate-300 dark:bg-slate-600 rounded-full"></span>
                            <span>{{ $p->reading_time }} min read</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-8">{{ $posts->links() }}</div>
    @else
        <div class="text-center py-12 card-elev">
            <span class="icon-tile w-14 h-14 items-center justify-center mb-3">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg>
            </span>
            <p class="text-sm text-slate-600 dark:text-slate-400">No posts found. Try a different search or category.</p>
            <a href="{{ route('blog.index') }}" class="btn btn-primary mt-4">View all posts</a>
        </div>
    @endif
</div>
@endsection
