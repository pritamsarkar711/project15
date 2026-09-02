@extends('layouts.app')

@php
    // The layout reads $metaTitle/$metaDescription/$ogImage directly (the old
    // @section('title')/@section('meta-description') blocks were dead code —
    // the layout defines no such yields). og:image must be ABSOLUTE for
    // social scrapers, so build it from the request host like the layout does.
    $metaTitle = $author->name . ' — Author Profile & Articles | ' . setting('site_name', 'Huvanti');
    $metaDescription = \Illuminate\Support\Str::limit(strip_tags($author->bio ?? ''), 150)
        ?: ('Browse all articles published by ' . $author->name . ' on ' . setting('site_name', 'Huvanti') . ' — profile, stats and latest posts in one place.');
    if ($author->author_avatar_path) {
        $ogImage = request()->getSchemeAndHttpHost() . asset('storage/' . $author->author_avatar_path);
    }
@endphp

@section('content')
<section class="max-w-[900px] mx-auto px-4 sm:px-6 py-8">
    {{-- Profile header card --}}
    <div class="card-elev p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row gap-6 items-start">
            @php
                $avatarUrl = $author->author_avatar_path
                    ? asset('storage/'.$author->author_avatar_path)
                    : 'https://ui-avatars.com/api/?name='.urlencode($author->name).'&size=200&background=173A2A&color=fff&font-size=0.45&bold=true';
            @endphp
            <img src="{{ $avatarUrl }}" alt="{{ $author->name }}" class="w-28 h-28 rounded-full object-cover border-4 border-[#E3F0E9] dark:border-[#383838] shadow-sm shrink-0" loading="lazy" decoding="async" onerror="this.style.display='none'">

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-2xl sm:text-[28px] font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $author->name }}</h1>
                    {{-- Country flag icon + name — unique profile decoration. The
                         tooltip spells out the full country name on hover. --}}
                    @include('partials.country-flag', ['user' => $author, 'class' => 'w-6 h-4', 'showName' => true])
                    {{-- Achievement badge: purple for admins, green at 10+ posts, yellow at 100+ --}}
                    {!! $author->badgeHtml() !!}
                </div>

                @php $niche = $author->nicheCategory(); @endphp
                @if($author->role_title || $niche)
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1 flex items-center flex-wrap gap-x-2 gap-y-1">
                    @if($author->role_title)<span>{{ $author->role_title }}</span>@endif
                    @if($niche)
                        {{-- Primary niche chip: the category the author mainly writes in --}}
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#1F513A] dark:text-[#8CC7AA] bg-[#E9F2EE] dark:bg-[#233b30] rounded-full px-2.5 py-0.5">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 15.004 13.5 3.483l1.5 4.02 4.02 1.5-11.452 3.932M9.568 15.004 3.483 13.5l3.932-11.452m2.153 12.956 4.02 1.5L17.5 2.5l-11.452 3.932"/></svg>
                            {{ $niche->name }}
                        </span>
                    @endif
                </p>
                @endif

                @if($author->bio)
                <p class="text-sm text-slate-700 dark:text-slate-300 mt-3 leading-relaxed">{{ $author->bio }}</p>
                @endif

                {{-- Stats grid: one card per stat, wraps cleanly on every screen
                     size (the old inline flex row overlapped on mobile). --}}
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2 mt-4">
                    <div class="bg-[#f8f9fb] dark:bg-[#14171d] border border-[#e6e8ee] dark:border-[#2c313c] rounded-lg px-3 py-2.5 text-center">
                        <div class="text-lg font-bold text-slate-900 dark:text-white leading-tight tabular-nums">{{ number_format($publishedCount) }}</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">Posts</div>
                    </div>
                    <div class="bg-[#f8f9fb] dark:bg-[#14171d] border border-[#e6e8ee] dark:border-[#2c313c] rounded-lg px-3 py-2.5 text-center">
                        <div class="text-lg font-bold text-slate-900 dark:text-white leading-tight tabular-nums">{{ number_format($totalLikes) }}</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">Likes</div>
                    </div>
                    <div class="bg-[#f8f9fb] dark:bg-[#14171d] border border-[#e6e8ee] dark:border-[#2c313c] rounded-lg px-3 py-2.5 text-center">
                        <div class="text-lg font-bold text-slate-900 dark:text-white leading-tight tabular-nums">{{ number_format($totalDislikes) }}</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">Dislikes</div>
                    </div>
                    <div class="bg-[#f8f9fb] dark:bg-[#14171d] border border-[#e6e8ee] dark:border-[#2c313c] rounded-lg px-3 py-2.5 text-center">
                        <div class="text-lg font-bold text-slate-900 dark:text-white leading-tight tabular-nums">{{ number_format($author->followers_count) }}</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">Followers</div>
                    </div>
                    <div class="bg-[#f8f9fb] dark:bg-[#14171d] border border-[#e6e8ee] dark:border-[#2c313c] rounded-lg px-3 py-2.5 text-center">
                        <div class="text-lg font-bold text-slate-900 dark:text-white leading-tight tabular-nums">{{ number_format($author->following_count) }}</div>
                        <div class="text-[11px] text-slate-500 dark:text-slate-400">Following</div>
                    </div>
                </div>

                {{-- Action row: follow button + social icons.
                     The follow control is ALWAYS visible: guests are sent to
                     login, and on your own profile it becomes an edit link. --}}
                <div class="flex items-center gap-2 mt-5 flex-wrap">
                    @if(auth()->check() && auth()->id() === $author->id)
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.profile.edit') }}" class="h-9 px-5 rounded-lg inline-flex items-center gap-2 text-sm font-semibold bg-[#2E7856] text-white hover:bg-[#27654A] transition">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                                Edit profile
                            </a>
                        @else
                            <a href="{{ route('author.profile.edit') }}" class="h-9 px-5 rounded-lg inline-flex items-center gap-2 text-sm font-semibold bg-[#2E7856] text-white hover:bg-[#27654A] transition">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                                Edit profile
                            </a>
                        @endif
                    @elseif(auth()->check())
                        <form method="POST" action="{{ route('author.follow', $author->username) }}">
                            @csrf
                            <button type="submit" class="h-9 px-5 inline-flex items-center gap-2 text-sm font-semibold {{ $isFollowing ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700' : 'bg-[#2E7856] text-white hover:bg-[#27654A]' }} rounded-lg transition cursor-pointer">
                                <svg class="w-4 h-4" fill="{{ $isFollowing ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/></svg>
                                {{ $isFollowing ? 'Following' : 'Follow' }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="h-9 px-5 rounded-lg inline-flex items-center gap-2 text-sm font-semibold bg-[#2E7856] text-white hover:bg-[#27654A] transition">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/></svg>
                            Follow
                        </a>
                    @endif
                    @if($author->portfolio_url)
                    <a href="{{ $author->portfolio_url }}" target="_blank" rel="noopener nofollow" class="btn btn-outline">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.6 9h16.8M3.6 15h16.8M11.5 3a17 17 0 0 0 0 18M12.5 3a17 17 0 0 1 0 18"/></svg>
                        Portfolio
                    </a>
                    @endif
                    @foreach($socials as $s)
                    <a href="{{ $s['url'] }}" target="_blank" rel="noopener nofollow" class="btn-icon border border-[#e6e8ee] dark:border-[#2c313c]" aria-label="{{ $s['label'] }}">
                        @include('partials.social-icon', ['platform' => $s['platform'], 'class' => 'w-4 h-4'])
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Posts grid --}}
    <div class="mt-8">
        <h2 class="text-[17px] font-bold text-slate-900 dark:text-white mb-4 tracking-tight">Posts by {{ $author->name }}</h2>
        @if($posts->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-5">
            @foreach($posts as $post)
            <article class="group card-elev card-hover overflow-hidden flex flex-col">
                <a href="{{ route('blog.show', $post->slug) }}" class="relative h-[180px] overflow-hidden block bg-[#f1f3f7] dark:bg-[#1c1f26]">
                    <img src="{{ storage_image_url($post->featured_image) ?: 'https://picsum.photos/seed/'.$post->slug.'/600/400' }}" alt="{{ image_alt_text($post->featured_image, $post->title) }}" class="img-fade w-full h-full object-cover group-hover:scale-[1.03] transition duration-300" loading="lazy" decoding="async" onload="this.classList.add('img-loaded')" onerror="this.onerror=null;this.removeAttribute('src');this.style.display='none'">
                    <span class="absolute top-3 left-3 chip chip-white shadow-sm">{{ $post->category->name ?? 'General' }}</span>
                </a>
                <div class="p-5 flex flex-col flex-1">
                    <a href="{{ route('blog.show', $post->slug) }}" class="text-[16px] font-bold text-slate-900 dark:text-white leading-snug tracking-[-0.01em] line-clamp-2 group-hover:text-[#2E7856] dark:group-hover:text-[#6FB393] transition-colors">{{ $post->title }}</a>
                    <div class="mt-auto pt-4 flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500">
                        <span>{{ $post->published_at?->format('M d, Y') }}</span>
                        <span class="w-1 h-1 bg-slate-300 dark:bg-slate-600 rounded-full"></span>
                        <span>{{ $post->reading_time }} min read</span>
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
            <p>{{ $author->name }} hasn't published any posts yet.</p>
        </div>
        @endif
    </div>
</section>
@endsection
