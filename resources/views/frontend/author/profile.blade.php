@extends('layouts.app')

@section('title', $author->name . ' · Author at ' . (setting('site_name', 'Huvanti')))

@section('meta-description')
<meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($author->bio ?? 'Author at ' . setting('site_name', 'Huvanti')), 150) }}">
<meta property="og:title" content="{{ $author->name }} · Author">
<meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($author->bio ?? ''), 150) }}">
@if($author->author_avatar_path)
<meta property="og:image" content="{{ asset('storage/'.$author->author_avatar_path) }}">
@endif
@endsection

@section('content')
<section class="max-w-[900px] mx-auto px-4 sm:px-6 py-8">
    {{-- Profile header card --}}
    <div class="card-elev p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row gap-6 items-start">
            @php
                $avatarUrl = $author->author_avatar_path
                    ? asset('storage/'.$author->author_avatar_path)
                    : 'https://ui-avatars.com/api/?name='.urlencode($author->name).'&size=200&background=0C3B2E&color=fff&font-size=0.45&bold=true';
            @endphp
            <img src="{{ $avatarUrl }}" alt="{{ $author->name }}" class="w-28 h-28 rounded-full object-cover border-4 border-emerald-100 dark:border-[#383838] shadow-sm shrink-0" loading="lazy" decoding="async">

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-2xl sm:text-[28px] font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $author->name }}</h1>
                    @if($author->is_verified)
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-400/10 border border-emerald-200 dark:border-emerald-400/20 px-2 py-0.5 rounded-full">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1l2.5 5L20 7l-4 4 1 6-5-3-5 3 1-6-4-4 5.5-1z"/></svg>
                        Verified
                    </span>
                    @endif
                </div>

                @if($author->role_title)
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ $author->role_title }}</p>
                @endif

                @if($author->bio)
                <p class="text-sm text-slate-700 dark:text-slate-300 mt-3 leading-relaxed">{{ $author->bio }}</p>
                @endif

                {{-- Stats row: visitor-visible numbers only (published posts,
                     total likes / dislikes received, followers, following) --}}
                <div class="flex flex-wrap items-center gap-x-6 gap-y-3 mt-4 text-sm text-slate-600 dark:text-slate-400">
                    <div>
                        <span class="font-bold text-slate-900 dark:text-white">{{ number_format($publishedCount) }}</span>
                        <span class="ml-1">{{ str()->plural('Post', $publishedCount) }}</span>
                    </div>
                    <div class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.997.75-1.604.75H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083-.205.173-.405.27-.602.197-.394-.154-.8-.569-.8H2.75a.75.75 0 0 0-.75.75v6c0 .414.336.75.75.75h2.75a.75.75 0 0 0 .75-.75v-.916c0-.915.647-1.668 1.404-2.334Z"/></svg>
                        <span class="font-bold text-slate-900 dark:text-white">{{ number_format($totalLikes) }}</span>
                        <span class="ml-1">{{ str()->plural('Like', $totalLikes) }}</span>
                    </div>
                    <div class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="transform:scale(-1)"><path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.997.75-1.604.75H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083-.205.173-.405.27-.602.197-.394-.154-.8-.569-.8H2.75a.75.75 0 0 0-.75.75v6c0 .414.336.75.75.75h2.75a.75.75 0 0 0 .75-.75v-.916c0-.915.647-1.668 1.404-2.334Z"/></svg>
                        <span class="font-bold text-slate-900 dark:text-white">{{ number_format($totalDislikes) }}</span>
                        <span class="ml-1">{{ str()->plural('Dislike', $totalDislikes) }}</span>
                    </div>
                    <div>
                        <span class="font-bold text-slate-900 dark:text-white">{{ number_format($author->followers_count) }}</span>
                        <span class="ml-1">Followers</span>
                    </div>
                    <div>
                        <span class="font-bold text-slate-900 dark:text-white">{{ number_format($author->following_count) }}</span>
                        <span class="ml-1">Following</span>
                    </div>
                </div>

                {{-- Action row: follow button + social icons --}}
                <div class="flex items-center gap-2 mt-5 flex-wrap">
                    @if(auth()->check() && auth()->id() !== $author->id)
                    <form method="POST" action="{{ route('author.follow', $author->username) }}">
                        @csrf
                        <button type="submit" class="h-9 px-5 text-sm font-semibold {{ $isFollowing ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700' : 'bg-[#0C3B2E] text-white hover:bg-[#072A20]' }} transition">
                            {{ $isFollowing ? 'Following' : 'Follow' }}
                        </button>
                    </form>
                    @endif
                    @if($author->portfolio_url)
                    <a href="{{ $author->portfolio_url }}" target="_blank" rel="noopener nofollow" class="h-9 px-4 inline-flex items-center gap-1.5 text-sm font-medium border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.6 9h16.8M3.6 15h16.8M11.5 3a17 17 0 0 0 0 18M12.5 3a17 17 0 0 1 0 18"/></svg>
                        Portfolio
                    </a>
                    @endif
                    @foreach($socials as $s)
                    <a href="{{ $s['url'] }}" target="_blank" rel="noopener nofollow" class="w-9 h-9 inline-flex items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-[#0C3B2E] dark:hover:bg-emerald-500 text-slate-600 dark:text-slate-300 hover:text-white transition" aria-label="{{ $s['label'] }}">
                        @include('partials.social-icon', ['platform' => $s['platform'], 'class' => 'w-4 h-4'])
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Posts grid --}}
    <div class="mt-8">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Posts by {{ $author->name }}</h2>
        @if($posts->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($posts as $post)
            <article class="group card-elev overflow-hidden hover:-translate-y-1 hover:shadow-xl transition-all duration-200 flex flex-col">
                <a href="{{ route('blog.show', $post->slug) }}" class="relative h-[180px] overflow-hidden block">
                    <img src="{{ $post->featured_image ?: 'https://picsum.photos/seed/'.$post->slug.'/600/400' }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-[1.03] transition duration-300" loading="lazy" decoding="async">
                </a>
                <div class="p-5 flex flex-col flex-1">
                    <span class="text-xs font-semibold text-[#0C3B2E] dark:text-emerald-300 uppercase tracking-wide">{{ $post->category->name ?? 'General' }}</span>
                    <a href="{{ route('blog.show', $post->slug) }}" class="mt-2 text-[16px] font-semibold text-slate-900 dark:text-white leading-snug line-clamp-2 group-hover:text-[#0C3B2E] dark:group-hover:text-emerald-300">{{ $post->title }}</a>
                    <div class="mt-4 pt-4 border-t border-slate-100 dark:border-[#2f2f2f] flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                        <span>{{ $post->published_at?->format('M d, Y') }}</span>
                        <span class="w-1 h-1 bg-slate-300 dark:bg-slate-600"></span>
                        <span>{{ $post->reading_time }} min read</span>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
        <div class="mt-8">{{ $posts->links() }}</div>
        @else
        <div class="card-elev p-8 text-center text-sm text-slate-500 dark:text-slate-400">
            {{ $author->name }} hasn't published any posts yet.
        </div>
        @endif
    </div>
</section>
@endsection
