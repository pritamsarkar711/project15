@extends('layouts.app')
@php
    $metaTitle = $category->name . ' Articles, Guides & Tips | ' . setting('site_name','huvanti.com');
    $metaDescription = $category->description ?: ('Latest ' . $category->name . ' articles, guides and tips from the Huvanti editorial team.');
@endphp
@section('content')
<div class="border-b border-[#e6e8ee] dark:border-[#22262e] bg-white dark:bg-[#0f1115]">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 page-head !pb-7">
        <nav class="flex items-center gap-1.5 text-[13px] text-slate-400 dark:text-slate-500 mb-2.5" aria-label="Breadcrumb">
            <a href="/" class="hover:text-[#2E7856] dark:hover:text-[#6FB393] transition">Home</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 6 6 6-6 6"/></svg>
            <a href="/blog" class="hover:text-[#2E7856] dark:hover:text-[#6FB393] transition">Blog</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 6 6 6-6 6"/></svg>
            <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $category->name }}</span>
        </nav>
        <div class="flex items-start gap-4">
            <span class="icon-tile w-[52px] h-[52px] !rounded-xl">
                @include('partials.category-icon', ['category' => $category, 'class' => 'w-6 h-6'])
            </span>
            <div class="min-w-0 flex-1">
                <h1>{{ $category->name }}</h1>
                <p class="lede">{{ $category->description }}</p>
            </div>
            <span class="badge badge-slate !text-xs !px-2.5 !py-1 shrink-0 mt-1">{{ $posts->total() }} posts</span>
        </div>
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
                            <span>{{ $p->published_at->format('M d, Y') }}</span>
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
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg>
            </span>
            <p>No posts in this category yet.</p>
            <a href="/blog" class="btn btn-primary mt-4">Browse all posts</a>
        </div>
    @endif
</div>
@endsection
