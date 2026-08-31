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
<section class="max-w-[1100px] mx-auto px-4 sm:px-6 py-12">
    {{-- Profile header card --}}
    <div class="card-elev p-6 sm:p-9 border-t-4 border-t-[#F5C445]">
        <div class="flex flex-col sm:flex-row gap-6 items-start">
            @php
                $avatarUrl = $author->author_avatar_path
                    ? asset('storage/'.$author->author_avatar_path)
                    : 'https://ui-avatars.com/api/?name='.urlencode($author->name).'&size=200&background=0C3B2E&color=fff&font-size=0.45&bold=true';
            @endphp
            <img src="{{ $avatarUrl }}" alt="{{ $author->name }}" class="w-28 h-28 object-cover border-2 border-[#141A16] dark:border-[#3A443D] shadow-[6px_6px_0_0_#F5C445] shrink-0" loading="lazy" decoding="async">

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-[28px] sm:text-[36px] font-black text-[#141A16] dark:text-[#F0F2EB] tracking-tight leading-none">{{ $author->name }}</h1>
                    {{-- Country flag icon + name — unique profile decoration. The
                         tooltip spells out the full country name on hover. --}}
                    @include('partials.country-flag', ['user' => $author, 'class' => 'w-6 h-4', 'showName' => true])
                    {{-- Achievement badge: purple for admins, green at 10+ posts, yellow at 100+ --}}
                    {!! $author->badgeHtml() !!}
                </div>

                @if($author->role_title)
                <p class="text-[12px] font-extrabold uppercase tracking-[0.14em] text-[#8B958C] dark:text-[#6B756C] mt-2">{{ $author->role_title }}</p>
                @endif

                @if($author->bio)
                <p class="text-[14.5px] text-[#5C665E] dark:text-[#97A199] mt-3 leading-relaxed">{{ $author->bio }}</p>
                @endif

                {{-- Stats grid: one card per stat, wraps cleanly on every screen
                     size (the old inline flex row overlapped on mobile). --}}
                <div class="grid grid-cols-3 sm:grid-cols-5 gap-2 mt-4">
                    <div class="bg-[#FAFAF7] dark:bg-[#0D100E] border border-[#E4E4DA] dark:border-[#262C28] px-3 py-2.5 text-center">
                        <div class="text-xl font-black text-[#141A16] dark:text-[#F0F2EB] leading-tight tabular-nums">{{ number_format($publishedCount) }}</div>
                        <div class="text-[10px] font-extrabold uppercase tracking-[0.12em] text-[#8B958C] dark:text-[#6B756C]">Posts</div>
                    </div>
                    <div class="bg-[#FAFAF7] dark:bg-[#0D100E] border border-[#E4E4DA] dark:border-[#262C28] px-3 py-2.5 text-center">
                        <div class="text-xl font-black text-[#141A16] dark:text-[#F0F2EB] leading-tight tabular-nums">{{ number_format($totalLikes) }}</div>
                        <div class="text-[10px] font-extrabold uppercase tracking-[0.12em] text-[#8B958C] dark:text-[#6B756C]">Likes</div>
                    </div>
                    <div class="bg-[#FAFAF7] dark:bg-[#0D100E] border border-[#E4E4DA] dark:border-[#262C28] px-3 py-2.5 text-center">
                        <div class="text-xl font-black text-[#141A16] dark:text-[#F0F2EB] leading-tight tabular-nums">{{ number_format($totalDislikes) }}</div>
                        <div class="text-[10px] font-extrabold uppercase tracking-[0.12em] text-[#8B958C] dark:text-[#6B756C]">Dislikes</div>
                    </div>
                    <div class="bg-[#FAFAF7] dark:bg-[#0D100E] border border-[#E4E4DA] dark:border-[#262C28] px-3 py-2.5 text-center">
                        <div class="text-xl font-black text-[#141A16] dark:text-[#F0F2EB] leading-tight tabular-nums">{{ number_format($author->followers_count) }}</div>
                        <div class="text-[10px] font-extrabold uppercase tracking-[0.12em] text-[#8B958C] dark:text-[#6B756C]">Followers</div>
                    </div>
                    <div class="bg-[#FAFAF7] dark:bg-[#0D100E] border border-[#E4E4DA] dark:border-[#262C28] px-3 py-2.5 text-center">
                        <div class="text-xl font-black text-[#141A16] dark:text-[#F0F2EB] leading-tight tabular-nums">{{ number_format($author->following_count) }}</div>
                        <div class="text-[10px] font-extrabold uppercase tracking-[0.12em] text-[#8B958C] dark:text-[#6B756C]">Following</div>
                    </div>
                </div>

                {{-- Action row: follow button + social icons.
                     The follow control is ALWAYS visible: guests are sent to
                     login, and on your own profile it becomes an edit link. --}}
                <div class="flex items-center gap-2 mt-5 flex-wrap">
                    @if(auth()->check() && auth()->id() === $author->id)
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.profile.edit') }}" class="h-10 px-5 inline-flex items-center gap-2 text-[11.5px] font-extrabold uppercase tracking-wide bg-[#141A16] text-white hover:bg-[#0C3B2E] transition shadow-[3px_3px_0_0_#F5C445]">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                                Edit profile
                            </a>
                        @else
                            <a href="{{ route('author.profile.edit') }}" class="h-10 px-5 inline-flex items-center gap-2 text-[11.5px] font-extrabold uppercase tracking-wide bg-[#141A16] text-white hover:bg-[#0C3B2E] transition shadow-[3px_3px_0_0_#F5C445]">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                                Edit profile
                            </a>
                        @endif
                    @elseif(auth()->check())
                        <form method="POST" action="{{ route('author.follow', $author->username) }}">
                            @csrf
                            <button type="submit" class="h-9 px-5 inline-flex items-center gap-2 text-sm font-semibold {{ $isFollowing ? 'bg-[#EFEFE8] dark:bg-[#1E2420] text-[#3D463F] dark:text-[#C2C9C0] border border-[#D8D8CC] dark:border-[#3A443D]' : 'bg-[#141A16] text-white hover:bg-[#0C3B2E] shadow-[3px_3px_0_0_#F5C445]' }} h-10 px-5 text-[11.5px] font-extrabold uppercase tracking-wide transition cursor-pointer">
                                <svg class="w-4 h-4" fill="{{ $isFollowing ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/></svg>
                                {{ $isFollowing ? 'Following' : 'Follow' }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="h-10 px-5 inline-flex items-center gap-2 text-[11.5px] font-extrabold uppercase tracking-wide bg-[#141A16] text-white hover:bg-[#0C3B2E] transition shadow-[3px_3px_0_0_#F5C445]">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/></svg>
                            Follow
                        </a>
                    @endif
                    @if($author->portfolio_url)
                    <a href="{{ $author->portfolio_url }}" target="_blank" rel="noopener nofollow" class="h-10 px-4 inline-flex items-center gap-1.5 text-[11.5px] font-extrabold uppercase tracking-wide border border-[#D8D8CC] dark:border-[#3A443D] text-[#3D463F] dark:text-[#C2C9C0] hover:border-[#141A16] dark:hover:border-[#F5C445] transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.6 9h16.8M3.6 15h16.8M11.5 3a17 17 0 0 0 0 18M12.5 3a17 17 0 0 1 0 18"/></svg>
                        Portfolio
                    </a>
                    @endif
                    @foreach($socials as $s)
                    <a href="{{ $s['url'] }}" target="_blank" rel="noopener nofollow" class="w-10 h-10 inline-flex items-center justify-center border border-[#D8D8CC] dark:border-[#3A443D] bg-white dark:bg-[#141815] hover:bg-[#0C3B2E] dark:hover:bg-[#34D399] text-[#3D463F] dark:text-[#C2C9C0] hover:text-white dark:hover:text-[#141A16] hover:border-[#0C3B2E] dark:hover:border-[#34D399] transition" aria-label="{{ $s['label'] }}">
                        @include('partials.social-icon', ['platform' => $s['platform'], 'class' => 'w-4 h-4'])
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Posts grid --}}
    <div class="mt-8">
        <h2 class="text-[22px] font-black text-[#141A16] dark:text-[#F0F2EB] tracking-tight mb-6">Articles by {{ $author->name }}<span class="text-[#F5C445]">.</span></h2>
        @if($posts->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($posts as $post)
            <article class="group card-elev lift flex flex-col">
                <a href="{{ route('blog.show', $post->slug) }}" class="relative h-[180px] overflow-hidden block border-b border-[#E4E4DA] dark:border-[#262C28]">
                    <img src="{{ storage_image_url($post->featured_image) ?: 'https://picsum.photos/seed/'.$post->slug.'/600/400' }}" alt="{{ image_alt_text($post->featured_image, $post->title) }}" class="w-full h-full object-cover group-hover:scale-[1.03] transition duration-300" loading="lazy" decoding="async">
                </a>
                <div class="p-5 flex flex-col flex-1">
                    <span class="text-[10px] font-extrabold text-[#0C3B2E] dark:text-[#34D399] uppercase tracking-[0.2em]">{{ $post->category->name ?? 'General' }}</span>
                    <a href="{{ route('blog.show', $post->slug) }}" class="mt-2 text-[16.5px] font-bold text-[#141A16] dark:text-[#F0F2EB] leading-snug line-clamp-2 group-hover:text-[#0C3B2E] dark:group-hover:text-[#34D399] transition-colors">{{ $post->title }}</a>
                    <div class="mt-auto pt-4 border-t border-[#E4E4DA] dark:border-[#262C28] flex items-center gap-2 text-[11.5px] font-medium text-[#8B958C] dark:text-[#6B756C]">
                        <span>{{ $post->published_at?->format('M d, Y') }}</span>
                        <span class="w-1 h-1 bg-[#F5C445]"></span>
                        <span>{{ $post->reading_time }} min read</span>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
        <div class="mt-8">{{ $posts->links() }}</div>
        @else
        <div class="card-elev p-10 text-center text-[14px] font-medium text-[#5C665E] dark:text-[#97A199]">
            {{ $author->name }} hasn't published any posts yet.
        </div>
        @endif
    </div>
</section>
@endsection
