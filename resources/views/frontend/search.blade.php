@extends('layouts.app')
@php
    // Search result URLs must stay OUT of the index (unbounded ?q= space ->
    // Search Console "Crawled - currently not indexed" noise).
    $robots = 'noindex, follow';
    $metaTitle = $q ? ('Search: ' . $q . ' · ' . setting('site_name','huvanti.com')) : ('Search · ' . setting('site_name','huvanti.com'));
@endphp
@section('content')
<div class="border-b border-[#e6e8ee] dark:border-[#22262e] bg-white dark:bg-[#0f1115]">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 page-head !pb-6">
        <nav class="flex items-center gap-1.5 text-[13px] text-slate-400 dark:text-slate-500 mb-2.5" aria-label="Breadcrumb">
            <a href="/" class="hover:text-[var(--brand)] dark:hover:text-[var(--brand-light)] transition">Home</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 6 6 6-6 6"/></svg>
            <span class="text-slate-700 dark:text-slate-300 font-medium">Search</span>
        </nav>
        <h1>Search results @if($q)for <span class="text-[var(--brand)] dark:text-[var(--brand-light)]">“{{ $q }}”</span>@endif</h1>
        <p class="lede">{{ $posts->total() }} articles found</p>
        <form action="{{ route('search') }}" method="GET" class="mt-5 flex gap-2.5 max-w-[560px]" role="search">
            <div class="relative flex-1 min-w-0">
                <svg class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg>
                <input type="text" name="q" value="{{ $q }}" placeholder="Search articles..." autocomplete="off" class="input !pl-10">
            </div>
            <button type="submit" class="btn btn-primary shrink-0">Search</button>
        </form>
    </div>
</div>
<div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-5">
        @forelse($posts as $p)
            <article class="group card-elev card-hover overflow-hidden flex flex-col">
                <a href="{{ route('blog.show',$p->slug) }}" class="relative h-[170px] overflow-hidden block bg-[#f1f3f7] dark:bg-[#1c1f26]">
                    <img src="{{ storage_image_url($p->featured_image) ?: 'https://picsum.photos/seed/'.$p->slug.'/600/400' }}" alt="{{ image_alt_text($p->featured_image, $p->title) }}" class="img-fade w-full h-full object-cover group-hover:scale-[1.03] transition duration-300" loading="lazy" decoding="async" onload="this.classList.add('img-loaded')" onerror="this.onerror=null;this.removeAttribute('src');this.style.display='none'">
                    <span class="absolute top-3 left-3 chip chip-white shadow-sm">{{ $p->category->name ?? 'General' }}</span>
                </a>
                <div class="p-5 flex flex-col flex-1">
                    <a href="{{ route('blog.show',$p->slug) }}" class="text-[16px] font-bold text-slate-900 dark:text-white leading-snug tracking-[-0.01em] line-clamp-2 group-hover:text-[var(--brand)] dark:group-hover:text-[var(--brand-light)] transition-colors">{{ $p->title }}</a>
                    <p class="text-[13.5px] text-slate-500 dark:text-slate-400 mt-2 leading-relaxed line-clamp-2">{{ $p->excerpt }}</p>
                    <div class="flex items-center gap-2 mt-auto pt-4 text-xs text-slate-400 dark:text-slate-500">
                        <span class="tabular-nums">{{ $p->published_at?->format('M d, Y') }}</span>
                        <span class="w-1 h-1 bg-slate-300 dark:bg-slate-600 rounded-full"></span>
                        <span>{{ $p->reading_time }} min read</span>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-3 card-elev empty-state">
                <span class="icon-tile">
                    <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg>
                </span>
                <p>No results. Try another keyword.</p>
                <a href="/" class="btn btn-primary mt-4">Go home</a>
            </div>
        @endforelse
    </div>
    <div class="mt-8">{{ $posts->appends(['q'=>$q])->links() }}</div>
</div>
@endsection
