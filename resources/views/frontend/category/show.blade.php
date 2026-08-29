@extends('layouts.app')
@php
    $metaTitle = $category->name . ' Articles · ' . setting('site_name','huvanti.com');
    $metaDescription = $category->description ?: ('Latest ' . $category->name . ' articles, guides and tips from the Huvanti editorial team.');
@endphp
@section('content')
<div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-8">
    <div class="flex items-center gap-4 mb-8 card-elev p-5">
        <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-400/10 flex items-center justify-center text-[#0C3B2E] dark:text-emerald-300 shrink-0">
            @include('partials.category-icon', ['category' => $category, 'class' => 'w-7 h-7'])
        </div>
        <div class="min-w-0">
            <h1 class="font-extrabold text-2xl sm:text-[30px] text-slate-900 dark:text-white tracking-tight">{{ $category->name }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $category->description }}</p>
        </div>
    </div>
    @if($posts->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($posts as $p)
                <article class="group card-elev overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-200 flex flex-col">
                    <a href="{{ route('blog.show',$p->slug) }}"><img src="{{ storage_image_url($p->featured_image) ?: 'https://picsum.photos/seed/'.$p->slug.'/600/400' }}" alt="{{ image_alt_text($p->featured_image, $p->title) }}" class="w-full h-48 object-cover group-hover:scale-[1.03] transition duration-300" loading="lazy" decoding="async"></a>
                    <div class="p-5 flex flex-col flex-1">
                        <a href="{{ route('blog.show',$p->slug) }}" class="font-semibold text-slate-900 dark:text-white hover:text-[#0C3B2E] dark:hover:text-emerald-300 line-clamp-2">{{ $p->title }}</a>
                        <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2 mt-2">{{ $p->excerpt }}</p>
                        <div class="text-xs text-slate-400 dark:text-slate-500 mt-3 pt-3 border-t border-slate-100 dark:border-[#2f2f2f]">{{ $p->published_at->format('M d, Y') }} <span class="w-1 h-1 bg-slate-300 dark:bg-slate-600 inline-block mx-1 align-middle"></span> {{ $p->reading_time }} min read</div>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-8">{{ $posts->links() }}</div>
    @else
        <div class="text-center py-12 card-elev"><p class="text-slate-500 dark:text-slate-400">No posts in this category yet.</p></div>
    @endif
</div>
@endsection
