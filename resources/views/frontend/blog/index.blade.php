@extends('layouts.app')
@php($metaTitle = 'Blog — Latest Articles & Expert Insights | ' . setting('site_name','huvanti.com'))
@section('content')
<div class="border-b border-[#e6e8ee] dark:border-[#22262e] bg-white dark:bg-[#0f1115]">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 page-head !pb-6">
        <div class="flex items-end justify-between gap-4 flex-wrap">
            <div>
                <nav class="flex items-center gap-1.5 text-[13px] text-slate-400 dark:text-slate-500 mb-2.5" aria-label="Breadcrumb">
                    <a href="/" class="hover:text-[#2E7856] dark:hover:text-[#6FB393] transition">Home</a>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 6 6 6-6 6"/></svg>
                    <span class="text-slate-700 dark:text-slate-300 font-medium">Blog</span>
                </nav>
                <h1>Blog</h1>
                <p class="lede">Discover the latest stories across technology, health, finance, travel, lifestyle and education.</p>
            </div>
            <span class="badge badge-slate !text-xs !px-2.5 !py-1">{{ $posts->total() }} articles</span>
        </div>

        <form action="{{ route('blog.index') }}" method="GET" class="mt-6 flex flex-col sm:flex-row gap-2.5" role="search">
            <div class="relative flex-1 min-w-0">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search posts by title or topic..." autocomplete="off" class="input !pl-10">
            </div>
            <select name="category" class="input sm:w-[200px] shrink-0" aria-label="Filter by category">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->slug }}" @selected(request('category')==$cat->slug)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary shrink-0">Filter</button>
            @if(request('q') || request('category'))
                <a href="{{ route('blog.index') }}" class="btn btn-outline shrink-0">Clear</a>
            @endif
        </form>
    </div>
</div>

<div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-8">
    @if($posts->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-5">
            @foreach($posts as $p)
                <article class="group card-elev card-hover overflow-hidden flex flex-col">
                    <a href="{{ route('blog.show',$p->slug) }}" class="relative h-[180px] overflow-hidden block bg-[#f1f3f7] dark:bg-[#1c1f26]">
                        <img src="{{ storage_image_url($p->featured_image) ?: 'https://picsum.photos/seed/'.$p->slug.'/600/400' }}" alt="{{ image_alt_text($p->featured_image, $p->title) }}" class="w-full h-full object-cover group-hover:scale-[1.03] transition duration-300" loading="lazy" decoding="async" onerror="this.onerror=null;this.removeAttribute('src');this.style.display='none'">
                        <span class="absolute top-3 left-3 chip chip-white shadow-sm">{{ $p->category->name ?? 'General' }}</span>
                    </a>
                    <div class="p-5 flex flex-col flex-1">
                        <a href="{{ route('blog.show',$p->slug) }}" class="text-[16px] font-bold text-slate-900 dark:text-white leading-snug tracking-[-0.01em] line-clamp-2 group-hover:text-[#2E7856] dark:group-hover:text-[#6FB393] transition-colors">{{ $p->title }}</a>
                        <p class="text-[13.5px] text-slate-500 dark:text-slate-400 mt-2 leading-relaxed line-clamp-2">{{ $p->excerpt }}</p>
                        <div class="flex items-center gap-2 mt-auto pt-4 text-xs text-slate-400 dark:text-slate-500">
                            <span>{{ $p->published_at?->format('M d, Y') }}</span>
                            <span class="w-1 h-1 bg-slate-300 dark:bg-slate-600 rounded-full"></span>
                            <span>{{ $p->reading_time }} min read</span>
                            <svg class="w-4 h-4 ml-auto text-slate-300 dark:text-slate-600 group-hover:text-[#2E7856] group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-8">{{ $posts->links() }}</div>
    @else
        <div class="card-elev empty-state">
            <span class="icon-tile">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg>
            </span>
            <p>No posts found. Try a different search or category.</p>
            <a href="{{ route('blog.index') }}" class="btn btn-primary mt-4">View all posts</a>
        </div>
    @endif
</div>
@endsection
