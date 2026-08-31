@extends('layouts.app')
@php
    $metaTitle = $category->name . ' Articles, Guides & Tips | ' . setting('site_name','huvanti.com');
    $metaDescription = $category->description ?: ('Latest ' . $category->name . ' articles, guides and tips from the Huvanti editorial team.');
@endphp
@section('content')
<div class="dotgrid border-b border-[#E4E4DA] dark:border-[#262C28]">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 py-12 sm:py-16">
        <span class="kicker"><b>§</b> Section</span>
        <div class="flex items-center gap-5 mt-4">
            <span class="chip w-16 h-16 border-2 border-[#141A16] dark:border-[#3A443D]">
                @include('partials.category-icon', ['category' => $category, 'class' => 'w-8 h-8'])
            </span>
            <div class="min-w-0">
                <h1 class="font-black text-[34px] sm:text-[48px] text-[#141A16] dark:text-[#F0F2EB] tracking-tight leading-none">{{ $category->name }}<span class="text-[#F5C445]">.</span></h1>
            </div>
        </div>
        <p class="text-[15px] text-[#5C665E] dark:text-[#97A199] mt-4 max-w-2xl leading-relaxed">{{ $category->description }}</p>
    </div>
</div>
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 py-12">
    @if($posts->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-7 gap-y-10">
            @foreach($posts as $p)
                <article class="group card-elev lift flex flex-col">
                    <a href="{{ route('blog.show',$p->slug) }}" class="relative h-[200px] overflow-hidden block border-b border-[#E4E4DA] dark:border-[#262C28]"><img src="{{ storage_image_url($p->featured_image) ?: 'https://picsum.photos/seed/'.$p->slug.'/600/400' }}" alt="{{ image_alt_text($p->featured_image, $p->title) }}" class="w-full h-full object-cover group-hover:scale-[1.03] transition duration-500" loading="lazy" decoding="async"></a>
                    <div class="p-6 flex flex-col flex-1">
                        <a href="{{ route('blog.show',$p->slug) }}" class="text-[18px] font-bold text-[#141A16] dark:text-[#F0F2EB] hover:text-[#0C3B2E] dark:hover:text-[#34D399] transition-colors line-clamp-2 leading-snug">{{ $p->title }}</a>
                        <p class="text-[13.5px] text-[#5C665E] dark:text-[#97A199] line-clamp-2 mt-3 leading-relaxed">{{ $p->excerpt }}</p>
                        <div class="mt-auto pt-4 border-t border-[#E4E4DA] dark:border-[#262C28] text-[12px] font-medium text-[#8B958C] dark:text-[#6B756C] flex items-center gap-2">{{ $p->published_at->format('M d, Y') }} <span class="w-1 h-1 bg-[#F5C445]"></span> {{ $p->reading_time }} min read</div>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-10">{{ $posts->links() }}</div>
    @else
        <div class="text-center py-16 card-elev max-w-xl mx-auto"><p class="text-[15px] font-medium text-[#5C665E] dark:text-[#97A199]">No posts in this category yet.</p></div>
    @endif
</div>
@endsection
