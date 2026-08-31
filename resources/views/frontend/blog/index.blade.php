@extends('layouts.app')
@php($metaTitle = 'Blog — Latest Articles & Expert Insights | ' . setting('site_name','huvanti.com'))
@section('content')
<div class="dotgrid border-b border-[#E4E4DA] dark:border-[#262C28]">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 py-12 sm:py-16">
        <span class="kicker"><b>01</b> The Journal</span>
        <h1 class="mt-4 text-[40px] sm:text-[56px] font-black text-[#141A16] dark:text-[#F0F2EB] leading-[1.02]">Every story,<br class="sm:hidden"> in one place<span class="text-[#F5C445]">.</span></h1>
        <p class="mt-4 text-[15px] sm:text-base text-[#5C665E] dark:text-[#97A199] max-w-2xl leading-relaxed">Technology, health, finance, travel, lifestyle and education — reported clearly, reviewed by humans, published weekly.</p>

        <form action="{{ route('blog.index') }}" method="GET" class="mt-8 max-w-[720px]">
            <div class="bg-white dark:bg-[#141815] border-2 border-[#141A16] dark:border-[#3A443D] shadow-[7px_7px_0_0_#F5C445] p-2 flex flex-col sm:flex-row gap-2">
                <div class="flex-1 relative min-w-0">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search posts by title or topic..." autocomplete="off" class="w-full h-11 px-4 bg-[#FAFAF7] dark:bg-[#0D100E] border border-[#E4E4DA] dark:border-[#3A443D] text-[#141A16] dark:text-[#EDEFEA] placeholder:text-[#8B958C] dark:placeholder:text-[#6B756C] text-sm font-medium outline-none focus:border-[#0C3B2E] dark:focus:border-[#34D399]">
                </div>
                <select name="category" class="h-11 px-3 bg-[#FAFAF7] dark:bg-[#0D100E] border border-[#E4E4DA] dark:border-[#3A443D] text-[#141A16] dark:text-[#EDEFEA] text-sm font-medium outline-none focus:border-[#0C3B2E] dark:focus:border-[#34D399] sm:w-[190px] shrink-0">
                    <option value="">All categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected(request('category')==$cat->slug)>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="h-11 px-7 bg-[#141A16] hover:bg-[#0C3B2E] text-white text-[12px] font-extrabold uppercase tracking-[0.12em] transition shrink-0">Filter</button>
                @if(request('q') || request('category'))
                    <a href="{{ route('blog.index') }}" class="h-11 px-5 bg-white dark:bg-[#141815] border border-[#E4E4DA] dark:border-[#3A443D] text-[#3D463F] dark:text-[#C2C9C0] text-[12px] font-extrabold uppercase tracking-[0.12em] flex items-center justify-center hover:border-[#141A16] dark:hover:border-[#F5C445] transition shrink-0">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="max-w-[1280px] mx-auto px-4 sm:px-6 py-12">
    @if($posts->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-7 gap-y-10">
            @foreach($posts as $p)
                <article class="group card-elev lift flex flex-col">
                    <a href="{{ route('blog.show',$p->slug) }}" class="relative h-[200px] overflow-hidden block border-b border-[#E4E4DA] dark:border-[#262C28]">
                        <img src="{{ storage_image_url($p->featured_image) ?: 'https://picsum.photos/seed/'.$p->slug.'/600/400' }}" alt="{{ image_alt_text($p->featured_image, $p->title) }}" class="w-full h-full object-cover group-hover:scale-[1.03] transition duration-500" loading="lazy" decoding="async">
                        <span class="absolute top-0 left-0 bg-white dark:bg-[#141815] text-[#0C3B2E] dark:text-[#34D399] text-[10px] font-extrabold tracking-[0.18em] uppercase px-3 py-1.5 border-b border-r border-[#E4E4DA] dark:border-[#262C28]">{{ $p->category->name ?? 'General' }}</span>
                    </a>
                    <div class="p-6 flex flex-col flex-1">
                        <a href="{{ route('blog.show',$p->slug) }}" class="text-[18px] font-bold text-[#141A16] dark:text-[#F0F2EB] leading-snug line-clamp-2 group-hover:text-[#0C3B2E] dark:group-hover:text-[#34D399] transition-colors">{{ $p->title }}</a>
                        <p class="text-[13.5px] text-[#5C665E] dark:text-[#97A199] mt-3 leading-relaxed line-clamp-2">{{ $p->excerpt }}</p>
                        <div class="mt-auto pt-4 border-t border-[#E4E4DA] dark:border-[#262C28] flex items-center gap-2 text-[12px] font-medium text-[#8B958C] dark:text-[#6B756C]">
                            <span>{{ $p->published_at?->format('M d, Y') }}</span>
                            <span class="w-1 h-1 bg-[#F5C445]"></span>
                            <span>{{ $p->reading_time }} min read</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-10">{{ $posts->links() }}</div>
    @else
        <div class="text-center py-16 card-elev max-w-xl mx-auto">
            <span class="chip w-14 h-14 mx-auto mb-4">
                <svg class="w-7 h-7 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><circle cx="11" cy="11" r="7"/></svg>
            </span>
            <p class="text-[15px] font-medium text-[#5C665E] dark:text-[#97A199]">No posts found. Try a different search or category.</p>
            <a href="{{ route('blog.index') }}" class="mt-5 btn btn-primary btn-sm mx-auto">View all posts</a>
        </div>
    @endif
</div>
@endsection
