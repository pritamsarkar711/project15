@extends('layouts.app')
@php
    $metaTitle = $category->name . ' Articles, Guides & Tips | ' . setting('site_name','huvanti.com');
    $metaDescription = $category->description ?: ('Latest ' . $category->name . ' articles, guides and tips from the Huvanti editorial team.');
@endphp
@section('content')
<div class="bg-slate-50/80 dark:bg-[#0D1411] border-b border-slate-100 dark:border-[#151D19]">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 py-12 sm:py-16">
        <span class="kicker">Section</span>
        <div class="flex items-center gap-5 mt-4">
            <span class="chip w-16 h-16 rounded-2xl">
                @include('partials.category-icon', ['category' => $category, 'class' => 'w-8 h-8'])
            </span>
            <div class="min-w-0">
                <h1 class="font-extrabold text-[34px] sm:text-[48px] text-slate-900 dark:text-[#F1F5F4] tracking-tight leading-none">{{ $category->name }}</h1>
            </div>
        </div>
        <p class="text-[15px] text-slate-500 dark:text-[#8FA398] mt-4 max-w-2xl leading-relaxed">{{ $category->description }}</p>
    </div>
</div>
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 py-12">
    @if($posts->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-8">
            @foreach($posts as $p)
                <article class="group card-elev lift overflow-hidden flex flex-col">
                    <a href="{{ route('blog.show',$p->slug) }}" class="relative h-[200px] overflow-hidden block"><img src="{{ storage_image_url($p->featured_image) ?: 'https://picsum.photos/seed/'.$p->slug.'/600/400' }}" alt="{{ image_alt_text($p->featured_image, $p->title) }}" class="w-full h-full object-cover group-hover:scale-[1.04] transition duration-500" loading="lazy" decoding="async"></a>
                    <div class="p-5 flex flex-col flex-1">
                        <a href="{{ route('blog.show',$p->slug) }}" class="text-[17.5px] font-bold text-slate-900 dark:text-[#F1F5F4] hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors line-clamp-2 leading-snug">{{ $p->title }}</a>
                        <p class="text-[13.5px] text-slate-500 dark:text-[#8FA398] line-clamp-2 mt-2.5 leading-relaxed flex-1">{{ $p->excerpt }}</p>
                        <div class="mt-auto pt-4 border-t border-slate-100 dark:border-[#1F2925] text-xs font-medium text-slate-400 dark:text-[#6B7F75] flex items-center gap-2">{{ $p->published_at->format('M d, Y') }} <span class="w-1 h-1 rounded-full bg-slate-300 dark:bg-[#3A4A42]"></span> {{ $p->reading_time }} min read</div>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-10">{{ $posts->links() }}</div>
    @else
        <div class="text-center py-16 card-elev max-w-xl mx-auto"><p class="text-[15px] font-medium text-slate-500 dark:text-[#8FA398]">No posts in this category yet.</p></div>
    @endif
</div>
@endsection
