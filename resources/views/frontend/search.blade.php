@extends('layouts.app')
@php
    // Search result URLs must stay OUT of the index (unbounded ?q= space ->
    // Search Console "Crawled - currently not indexed" noise).
    $robots = 'noindex, follow';
    $metaTitle = $q ? ('Search: ' . $q . ' · ' . setting('site_name','huvanti.com')) : ('Search · ' . setting('site_name','huvanti.com'));
@endphp
@section('content')
<div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-8">
    <h1 class="font-extrabold text-2xl sm:text-[30px] text-slate-900 dark:text-white tracking-tight">Search results for "<span class="text-[#049A53] dark:text-emerald-300">{{ $q }}</span>"</h1>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $posts->total() }} articles found</p>
    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($posts as $p)
            <a href="{{ route('blog.show',$p->slug) }}" class="group card-elev card-hover overflow-hidden block">
                <img src="{{ storage_image_url($p->featured_image) ?: 'https://picsum.photos/seed/'.$p->slug.'/600/400' }}" alt="{{ image_alt_text($p->featured_image, $p->title) }}" class="w-full h-44 object-cover group-hover:scale-[1.03] transition duration-300" loading="lazy" decoding="async">
                <div class="p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-[#049A53] dark:text-emerald-300">{{ $p->category->name ?? 'General' }}</div>
                    <h3 class="font-semibold text-slate-900 dark:text-white line-clamp-2 mt-1 group-hover:text-[#049A53] dark:group-hover:text-emerald-300 transition-colors">{{ $p->title }}</h3>
                </div>
            </a>
        @empty
            <div class="col-span-3 text-center py-12 card-elev"><p class="text-slate-500 dark:text-slate-400">No results. Try another keyword.</p><a href="/" class="btn btn-primary mt-4">Go home</a></div>
        @endforelse
    </div>
    <div class="mt-8">{{ $posts->appends(['q'=>$q])->links() }}</div>
</div>
@endsection
