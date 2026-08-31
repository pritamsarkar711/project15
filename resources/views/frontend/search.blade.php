@extends('layouts.app')
@php
    // Search result URLs must stay OUT of the index (unbounded ?q= space ->
    // Search Console "Crawled - currently not indexed" noise).
    $robots = 'noindex, follow';
    $metaTitle = $q ? ('Search: ' . $q . ' · ' . setting('site_name','huvanti.com')) : ('Search · ' . setting('site_name','huvanti.com'));
@endphp
@section('content')
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 py-12">
    <span class="kicker"><b>?</b> Search</span>
    <h1 class="font-black text-[32px] sm:text-[44px] text-[#141A16] dark:text-[#F0F2EB] tracking-tight leading-[1.05] mt-4">Results for <span class="marker">{{ $q }}</span></h1>
    <p class="text-[14px] font-medium text-[#8B958C] dark:text-[#6B756C] mt-4">{{ $posts->total() }} articles found</p>
    <div class="mt-9 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-7 gap-y-10">
        @forelse($posts as $p)
            <a href="{{ route('blog.show',$p->slug) }}" class="group card-elev lift block">
                <div class="h-[190px] overflow-hidden border-b border-[#E4E4DA] dark:border-[#262C28]">
                    <img src="{{ storage_image_url($p->featured_image) ?: 'https://picsum.photos/seed/'.$p->slug.'/600/400' }}" alt="{{ image_alt_text($p->featured_image, $p->title) }}" class="w-full h-full object-cover group-hover:scale-[1.03] transition duration-500" loading="lazy" decoding="async">
                </div>
                <div class="p-6">
                    <div class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-[#0C3B2E] dark:text-[#34D399]">{{ $p->category->name ?? 'General' }}</div>
                    <h3 class="font-bold text-[17px] text-[#141A16] dark:text-[#F0F2EB] line-clamp-2 mt-2 leading-snug group-hover:text-[#0C3B2E] dark:group-hover:text-[#34D399] transition-colors">{{ $p->title }}</h3>
                </div>
            </a>
        @empty
            <div class="col-span-3 text-center py-16 card-elev"><p class="text-[15px] font-medium text-[#5C665E] dark:text-[#97A199]">No results. Try another keyword.</p><a href="{{ route('blog.index') }}" class="mt-5 btn btn-primary btn-sm">Browse the archive</a></div>
        @endforelse
    </div>
    <div class="mt-10">{{ $posts->appends(['q'=>$q])->links() }}</div>
</div>
@endsection
