@extends('layouts.app')
@php
    // Search result URLs must stay OUT of the index (unbounded ?q= space ->
    // Search Console "Crawled - currently not indexed" noise).
    $robots = 'noindex, follow';
    $metaTitle = $q ? ('Search: ' . $q . ' · ' . setting('site_name','huvanti.com')) : ('Search · ' . setting('site_name','huvanti.com'));
@endphp
@section('content')
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 py-12">
    <span class="kicker">Search</span>
    <h1 class="font-extrabold text-[32px] sm:text-[44px] text-slate-900 dark:text-[#F1F5F4] tracking-tight leading-[1.05] mt-4">Results for <span class="marker">{{ $q }}</span></h1>
    <p class="text-[14px] font-medium text-slate-400 dark:text-[#6B7F75] mt-4">{{ $posts->total() }} articles found</p>
    <div class="mt-9 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8">
        @forelse($posts as $p)
            <a href="{{ route('blog.show',$p->slug) }}" class="group card-elev lift block overflow-hidden">
                <div class="h-[190px] overflow-hidden">
                    <img src="{{ storage_image_url($p->featured_image) ?: 'https://picsum.photos/seed/'.$p->slug.'/600/400' }}" alt="{{ image_alt_text($p->featured_image, $p->title) }}" class="w-full h-full object-cover group-hover:scale-[1.04] transition duration-500" loading="lazy" decoding="async">
                </div>
                <div class="p-5">
                    <div class="text-[11.5px] font-bold uppercase tracking-wide text-emerald-600 dark:text-emerald-400">{{ $p->category->name ?? 'General' }}</div>
                    <h3 class="font-bold text-[17px] text-slate-900 dark:text-[#F1F5F4] line-clamp-2 mt-2 leading-snug group-hover:text-emerald-700 dark:group-hover:text-emerald-300 transition-colors">{{ $p->title }}</h3>
                </div>
            </a>
        @empty
            <div class="col-span-3 text-center py-16 card-elev"><p class="text-[15px] font-medium text-slate-500 dark:text-[#8FA398]">No results. Try another keyword.</p><a href="{{ route('blog.index') }}" class="mt-5 btn btn-primary btn-sm">Browse the archive</a></div>
        @endforelse
    </div>
    <div class="mt-10">{{ $posts->appends(['q'=>$q])->links() }}</div>
</div>
@endsection
