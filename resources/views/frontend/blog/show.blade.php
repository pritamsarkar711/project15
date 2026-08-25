@extends('layouts.app')
@section('content')
@php
    $shareUrl = urlencode(url()->current());
    $shareText = urlencode($post->title);
    $authorName = $post->user->name ?? $post->author_name ?? 'Huvanti Team';
    $authorBio = $post->user->bio ?? $post->author_bio ?? 'Editor at Huvanti';
    $authorAvatar = $post->user->author_avatar_path ? asset('storage/'.$post->user->author_avatar_path) : ($post->author_avatar ?: 'https://i.pravatar.cc/100?img=15');
    $topComments = $post->approvedComments->whereNull('parent_id');
    // Author profile URL: clicking the author's name or photo (byline above
    // the content AND the author box below it) opens their public profile,
    // where visitors can follow / unfollow them.
    $authorProfileUrl = $post->user?->username ? route('author.profile', $post->user->username) : null;
@endphp
<div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-6">
    <nav class="text-sm text-slate-500 dark:text-slate-400 flex items-center gap-2 flex-wrap mb-4" aria-label="Breadcrumb">
        <a href="/" class="hover:text-slate-900 dark:hover:text-white inline-flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z"/></svg> Home</a>
        <span class="text-slate-300 dark:text-slate-600">/</span>
        <a href="{{ route('blog.index') }}" class="hover:text-slate-900 dark:hover:text-white">Blog</a>
        @if($post->category)
            <span class="text-slate-300 dark:text-slate-600">/</span>
            <a href="{{ route('category.show',$post->category->slug) }}" class="hover:text-slate-900 dark:hover:text-white">{{ $post->category->name }}</a>
        @endif
        <span class="text-slate-300 dark:text-slate-600">/</span>
        <span class="text-slate-900 dark:text-white font-medium line-clamp-1">{{ $post->title }}</span>
    </nav>

    <div class="grid lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8 space-y-6">
            {{-- Article card --}}
            <article class="card-elev overflow-hidden">
                <div class="relative h-[240px] sm:h-[360px] overflow-hidden">
                    <img src="{{ $post->featured_image ?: 'https://picsum.photos/seed/'.$post->slug.'/1200/700' }}" alt="{{ $post->title }}" class="w-full h-full object-cover" loading="lazy" decoding="async">
                    <div class="absolute top-3 left-3 flex items-center gap-2">
                        @if($post->category)<span class="text-xs font-semibold bg-white/95 dark:bg-[#1e1e1e]/90 text-[#0C3B2E] dark:text-emerald-300 px-2.5 py-1 border border-slate-200 dark:border-[#383838] shadow-sm">{{ $post->category->name }}</span>@endif
                        @if($post->is_featured)<span class="text-xs font-bold bg-[#F5C445] text-slate-900 px-2.5 py-1">Popular</span>@endif
                    </div>
                </div>

                <div class="p-6">
                    <h1 class="text-[28px] sm:text-[32px] font-extrabold leading-tight tracking-tight text-slate-900 dark:text-white">{{ $post->title }}</h1>
                    @if($post->excerpt)<p class="text-[15px] leading-relaxed text-slate-600 dark:text-slate-400 mt-3">{{ $post->excerpt }}</p>@endif

                    <div class="flex flex-wrap items-center gap-3 mt-6 p-4 bg-emerald-50/80 dark:bg-[#2a2a2a]/60 border border-emerald-100 dark:border-[#383838]">
                        @if($authorProfileUrl)<a href="{{ $authorProfileUrl }}" class="flex items-center gap-3 group" aria-label="View author profile">@else<div class="flex items-center gap-3">@endif
                            <img src="{{ $authorAvatar }}" alt="{{ $authorName }}" class="w-10 h-10 object-cover border border-white dark:border-[#383838] shadow-sm {{ $authorProfileUrl ? 'group-hover:ring-2 group-hover:ring-[#0C3B2E] dark:group-hover:ring-emerald-400 transition' : '' }}" loading="lazy" decoding="async">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap {{ $authorProfileUrl ? 'group-hover:text-[#0C3B2E] dark:group-hover:text-emerald-300 transition' : '' }}">
                                    <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $authorName }}</span>
                                    {{-- Verified badge right next to the name --}}
                                    @if($post->user)
                                        {!! $post->user->badgeHtml() !!}
                                    @endif
                                </div>
                                <div class="text-xs text-slate-600 dark:text-slate-400 line-clamp-1">{{ $authorBio }}</div>
                            </div>
                        @if($authorProfileUrl)</a>@else</div>@endif
                        <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400 flex-wrap">
                            <span class="inline-flex items-center gap-1.5 bg-white dark:bg-[#1e1e1e] border border-slate-200 dark:border-[#383838] px-2.5 py-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v4"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18"/></svg> {{ $post->published_at?->format('M d, Y') }}</span>
                            <span class="inline-flex items-center gap-1.5 bg-white dark:bg-[#1e1e1e] border border-slate-200 dark:border-[#383838] px-2.5 py-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg> {{ $post->reading_time }} min read</span>
                            <span class="inline-flex items-center gap-1.5 bg-white dark:bg-[#1e1e1e] border border-slate-200 dark:border-[#383838] px-2.5 py-1"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg> {{ $post->views }} views</span>
                        </div>
                    </div>

                    {{-- Share: circular icons + working copy link --}}
                    <div class="flex flex-wrap items-center gap-2.5 mt-5">
                        <span class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase mr-1">Share:</span>
                        <a href="https://twitter.com/intent/tweet?text={{ $shareText }}&url={{ $shareUrl }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 flex items-center justify-center hover:scale-110 transition shadow-sm" aria-label="Share on X">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-[#1877F2] text-white flex items-center justify-center hover:scale-110 transition shadow-sm" aria-label="Share on Facebook">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="https://pinterest.com/pin/create/button/?url={{ $shareUrl }}&description={{ $shareText }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-[#E60023] text-white flex items-center justify-center hover:scale-110 transition shadow-sm" aria-label="Share on Pinterest">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-[#0A66C2] text-white flex items-center justify-center hover:scale-110 transition shadow-sm" aria-label="Share on LinkedIn">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.634-1.85 3.364-1.85 3.604 0 4.268 2.37 4.268 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.777 13.019H3.56V9h3.554v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.454C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                        <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener" class="w-10 h-10 rounded-full bg-[#25D366] text-white flex items-center justify-center hover:scale-110 transition shadow-sm" aria-label="Share on WhatsApp">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                        <button type="button" id="copy-link-btn" data-url="{{ url()->current() }}" class="ml-1 text-xs font-semibold inline-flex items-center gap-1.5 bg-white dark:bg-[#2a2a2a] border border-slate-200 dark:border-[#383838] text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-[#333] px-4 h-10 rounded-full transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                            <span id="copy-link-label">Copy link</span>
                        </button>
                    </div>

                    @if(count($toc) > 0)
                        <div class="mt-6 bg-emerald-50/80 dark:bg-[#2a2a2a]/60 border border-emerald-100 dark:border-[#383838] p-4">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white flex items-center gap-2"><svg class="w-4 h-4 text-[#0C3B2E] dark:text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 18h16"/></svg> Table of Contents</h3>
                            <ol class="mt-2 space-y-1 list-decimal list-inside text-sm text-slate-700 dark:text-slate-300">
                                @foreach($toc as $item)<li><a href="#{{ $item['id'] }}" class="hover:text-[#0C3B2E] dark:hover:text-emerald-300 hover:underline">{{ $item['title'] }}</a></li>@endforeach
                            </ol>
                        </div>
                    @endif

                    @if($post->is_affiliate)
                        <div class="mt-6 bg-purple-600 text-white p-4 rounded-xl">
                            <div class="flex items-start gap-3">
                                <span class="w-9 h-9 bg-white/15 flex items-center justify-center shrink-0 rounded-full">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m15 9-6 6"/><circle cx="9.5" cy="9.5" r=".5" fill="currentColor"/><circle cx="14.5" cy="14.5" r=".5" fill="currentColor"/></svg>
                                </span>
                                <div class="text-sm leading-relaxed">
                                    <strong>Affiliate disclosure:</strong> Some links on this page are affiliate links. If you buy through them, we may earn a small commission at no extra cost to you. Read our <a href="{{ route('editorial') }}" class="underline font-semibold hover:text-emerald-100">full disclosure</a>.
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="prose dark:prose-invert max-w-none mt-6">{!! $contentWithAnchors !!}</div>

                    {{-- Tags (meta keywords shown as chips) --}}
                    @php
                        $tags = array_values(array_filter(array_map('trim', explode(',', (string) $post->meta_keywords)), fn ($t) => $t !== ''));
                        $tags = array_slice($tags, 0, 8);
                    @endphp
                    @if(count($tags) > 0)
                        <div class="flex items-center gap-2 flex-wrap mt-6 pt-5 border-t border-slate-100 dark:border-[#383838]">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Tags</span>
                            @foreach($tags as $tag)
                                <a href="{{ route('search', ['q' => $tag]) }}" class="text-xs font-medium bg-slate-100 dark:bg-[#2a2a2a] text-slate-700 dark:text-slate-300 px-3 py-1.5 rounded-full hover:bg-emerald-50 dark:hover:bg-emerald-400/10 hover:text-emerald-700 dark:hover:text-emerald-300 transition"># {{ $tag }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </article>

            {{-- FAQ separate container (new icon: help-circle style) --}}
            @if($post->faqs->count() > 0)
            <div class="card-elev p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
                    <span class="w-8 h-8 bg-emerald-50 dark:bg-emerald-400/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-[#0C3B2E] dark:text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 17h.01"/></svg>
                    </span>
                    Frequently Asked Questions
                </h3>
                <div class="space-y-2" id="faq-accordion">
                    @foreach($post->faqs as $faq)
                        <div class="border border-slate-200 dark:border-[#383838] overflow-hidden bg-white dark:bg-[#2a2a2a]">
                            <button type="button" onclick="toggleFaq(this)" class="w-full flex items-center justify-between p-4 text-left hover:bg-slate-50 dark:hover:bg-[#333]/60 transition">
                                <span class="text-sm font-medium text-slate-900 dark:text-white pr-4">{{ $faq->question }}</span>
                                <span class="w-7 h-7 bg-slate-100 dark:bg-slate-700 flex items-center justify-center shrink-0"><svg class="w-4 h-4 text-slate-600 dark:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></span>
                            </button>
                            <div class="hidden px-4 pb-4 text-sm leading-relaxed text-slate-600 dark:text-slate-400 border-t border-slate-100 dark:border-[#383838] pt-3 bg-slate-50 dark:bg-[#2a2a2a]/50">{{ $faq->answer }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Was this helpful? Like / Dislike reactions, shown right
                 after the content + FAQ. Clicking the active button removes
                 your reaction; the other button switches it. --}}
<div class="card-elev py-8 text-center">
    <h3 class="text-base font-bold text-slate-900 dark:text-white">Was this helpful?</h3>
    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Your feedback helps other readers.</p>
    <div class="flex items-center justify-center gap-3 mt-5">
        @auth
            <form method="POST" action="{{ route('blog.react', $post->slug) }}" class="inline">
                @csrf
                <input type="hidden" name="reaction" value="like">
                <button type="submit" title="{{ $myReaction === 'like' ? 'Remove your like' : 'Like this post' }}" class="inline-flex items-center gap-2 h-11 px-5 rounded-full text-sm font-bold transition cursor-pointer border {{ $myReaction === 'like' ? 'bg-emerald-600 border-emerald-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-emerald-400 hover:text-emerald-600 dark:hover:text-emerald-300' }}">
                    <svg class="w-[18px] h-[18px]" fill="{{ $myReaction === 'like' ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="{{ $myReaction === 'like' ? '0' : '2' }}" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"/></svg>
                    Helpful
                    <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-bold {{ $myReaction === 'like' ? 'bg-white/20 text-white' : 'bg-emerald-50 dark:bg-emerald-400/10 text-emerald-700 dark:text-emerald-300' }}">{{ number_format($likesCount) }}</span>
                </button>
            </form>
            <form method="POST" action="{{ route('blog.react', $post->slug) }}" class="inline">
                @csrf
                <input type="hidden" name="reaction" value="dislike">
                <button type="submit" title="{{ $myReaction === 'dislike' ? 'Remove your dislike' : 'Dislike this post' }}" class="inline-flex items-center gap-2 h-11 px-5 rounded-full text-sm font-bold transition cursor-pointer border {{ $myReaction === 'dislike' ? 'bg-rose-600 border-rose-600 text-white shadow-sm' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-rose-400 hover:text-rose-600 dark:hover:text-rose-300' }}">
                    <svg class="w-[18px] h-[18px]" fill="{{ $myReaction === 'dislike' ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="{{ $myReaction === 'dislike' ? '0' : '2' }}" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M17 14V2"/><path d="M9 18.12 10 14H4.17a2 2 0 0 1-1.92-2.56l2.33-8A2 2 0 0 1 6.5 2H20a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2.76a2 2 0 0 0-1.79 1.11L12 22a3.13 3.13 0 0 1-3-3.88Z"/></svg>
                    Not for me
                    <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-bold {{ $myReaction === 'dislike' ? 'bg-white/20 text-white' : 'bg-rose-50 dark:bg-rose-400/10 text-rose-600 dark:text-rose-300' }}">{{ number_format($dislikesCount) }}</span>
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" title="Sign in to react" class="inline-flex items-center gap-2 h-11 px-5 rounded-full text-sm font-bold transition border bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-emerald-400 hover:text-emerald-600 dark:hover:text-emerald-300">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2a3.13 3.13 0 0 1 3 3.88Z"/></svg>
                Helpful
                <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-400/10 text-emerald-700 dark:text-emerald-300">{{ number_format($likesCount) }}</span>
            </a>
            <a href="{{ route('login') }}" title="Sign in to react" class="inline-flex items-center gap-2 h-11 px-5 rounded-full text-sm font-bold transition border bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:border-rose-400 hover:text-rose-600 dark:hover:text-rose-300">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M17 14V2"/><path d="M9 18.12 10 14H4.17a2 2 0 0 1-1.92-2.56l2.33-8A2 2 0 0 1 6.5 2H20a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2.76a2 2 0 0 0-1.79 1.11L12 22a3.13 3.13 0 0 1-3-3.88Z"/></svg>
                Not for me
                <span class="ml-1 px-2 py-0.5 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-400/10 text-rose-600 dark:text-rose-300">{{ number_format($dislikesCount) }}</span>
            </a>
        @endauth
    </div>
</div>

{{-- Author bio separate container (user profile driven) --}}
            @php
                $author = $post->user;
                $authorUsername = $author?->username;
                $authorVerified = $author?->is_verified;
                $authorSocials = $author ? $author->socialProfiles() : [];
            @endphp
            <div class="card-elev p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 bg-emerald-50 dark:bg-emerald-400/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-[#0C3B2E] dark:text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </span>
                    About the Author
                </h3>
                <div class="flex gap-4">
                    @if($authorProfileUrl)<a href="{{ $authorProfileUrl }}" class="shrink-0 group" aria-label="View author profile">@endif
                        <img src="{{ $authorAvatar }}" alt="{{ $authorName }}" class="w-14 h-14 rounded-full object-cover border-2 border-emerald-100 dark:border-[#383838] shadow-sm {{ $authorProfileUrl ? 'group-hover:ring-2 group-hover:ring-[#0C3B2E] dark:group-hover:ring-emerald-400 transition' : '' }}" loading="lazy" decoding="async">
                    @if($authorProfileUrl)</a>@endif
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @if($authorUsername)
                                <a href="{{ route('author.profile', $authorUsername) }}" class="text-sm font-bold text-slate-900 dark:text-white hover:text-[#0C3B2E] dark:hover:text-emerald-300">{{ $authorName }}</a>
                            @else
                                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $authorName }}</span>
                            @endif
                            {{-- Achievement badge: purple for admins, green at 10+ posts, yellow at 100+ --}}
                            @if($post->user)
                                {!! $post->user->badgeHtml() !!}
                            @endif
                            @if($author?->role_title)
                                <span class="text-xs text-slate-500 dark:text-slate-400">· {{ $author->role_title }}</span>
                            @endif
                        </div>
                        <div class="text-sm text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">{{ $authorBio }}</div>
                        <div class="flex items-center gap-2 mt-3 flex-wrap">
                            @if($authorProfileUrl)
                                <a href="{{ $authorProfileUrl }}" class="inline-flex items-center gap-1.5 h-9 px-4 text-xs font-semibold bg-[#0C3B2E] hover:bg-[#072A20] text-white transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                                    View profile
                                </a>
                            @endif
                            {{-- Follow / unfollow straight from the post --}}
                            @if(auth()->check() && $post->user && auth()->id() !== $post->user->id)
                                <form method="POST" action="{{ route('author.follow', $post->user->username) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 h-9 px-4 text-xs font-semibold border transition cursor-pointer {{ $followingAuthor ? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700' : 'bg-white dark:bg-[#2a2a2a] text-[#0C3B2E] dark:text-emerald-300 border-[#0C3B2E] dark:border-emerald-400/40 hover:bg-[#0C3B2E]/5 dark:hover:bg-emerald-400/10' }}">
                                        <svg class="w-3.5 h-3.5" fill="{{ $followingAuthor ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/></svg>
                                        {{ $followingAuthor ? 'Following' : 'Follow' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                        @if(count($authorSocials) > 0)
                        <div class="flex items-center gap-2 mt-3">
                            @foreach($authorSocials as $s)
                                <a href="{{ $s['url'] }}" target="_blank" rel="noopener nofollow" class="w-7 h-7 flex items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-[#0C3B2E] dark:hover:bg-emerald-500 text-slate-600 dark:text-slate-300 hover:text-white transition" aria-label="{{ $s['label'] }}">
                                    @include('partials.social-icon', ['platform' => $s['platform'], 'class' => 'w-3.5 h-3.5'])
                                </a>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Comments separate container (new icon + reply system) --}}
            <div class="card-elev p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span class="w-8 h-8 bg-emerald-50 dark:bg-emerald-400/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-[#0C3B2E] dark:text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
                    </span>
                    Comments <span class="text-sm font-normal text-slate-500 dark:text-slate-400">({{ $topComments->count() }})</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Your email stays private. Comments are moderated.</p>
                @if(session('success'))<div class="mt-3 bg-emerald-50 dark:bg-emerald-400/10 border border-emerald-200 dark:border-emerald-400/20 text-emerald-800 dark:text-emerald-300 px-4 py-3 text-sm">{{ session('success') }}</div>@endif

                {{-- Comment form --}}
                <form action="{{ route('blog.comment.store',$post->slug) }}" method="POST" class="mt-4 bg-slate-50 dark:bg-[#2a2a2a] border border-slate-200 dark:border-[#383838] p-4" id="comment-form">
                    @csrf
                    <input type="hidden" name="parent_id" id="reply-parent-id" value="">
                    <div id="replying-to" class="hidden mb-3 text-xs flex items-center gap-2 bg-emerald-50 dark:bg-emerald-400/10 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-400/20 px-3 py-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m15 10 5 5-5 5"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v7a4 4 0 0 0 4 4h12"/></svg>
                        Replying to <b id="replying-to-name" class="font-semibold"></b>
                        <button type="button" onclick="cancelReply()" class="ml-auto" aria-label="Cancel reply">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-3">
                        <div><label class="text-sm font-medium text-slate-700 dark:text-slate-300">Name *</label><input type="text" name="name" required value="{{ old('name') }}" class="mt-1 w-full h-10 px-3 bg-white dark:bg-[#1e1e1e] border border-slate-200 dark:border-[#383838] text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-emerald-300 dark:focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:focus:ring-emerald-400/10 outline-none text-sm">@error('name')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror</div>
                        <div><label class="text-sm font-medium text-slate-700 dark:text-slate-300">Email * <span class="text-slate-400 font-normal">(private)</span></label><input type="email" name="email" required value="{{ old('email') }}" class="mt-1 w-full h-10 px-3 bg-white dark:bg-[#1e1e1e] border border-slate-200 dark:border-[#383838] text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-emerald-300 dark:focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:focus:ring-emerald-400/10 outline-none text-sm">@error('email')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror</div>
                    </div>
                    <div class="mt-3"><label class="text-sm font-medium text-slate-700 dark:text-slate-300">Comment *</label><textarea name="content" required rows="4" class="mt-1 w-full px-3 py-2 bg-white dark:bg-[#1e1e1e] border border-slate-200 dark:border-[#383838] text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-emerald-300 dark:focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 dark:focus:ring-emerald-400/10 outline-none text-sm" placeholder="Share your thoughts...">{{ old('content') }}</textarea>@error('content')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror</div>
                    <button type="submit" class="mt-3 h-10 px-6 bg-[#0C3B2E] hover:bg-[#072A20] text-white text-sm font-semibold transition">Post Comment</button>
                </form>

                {{-- Threaded comments --}}
                <div class="mt-6 space-y-3">
                    @forelse($topComments as $c)
                        <div class="bg-slate-50 dark:bg-[#2a2a2a] border border-slate-200 dark:border-[#383838] p-4">
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 bg-[#0C3B2E] text-white flex items-center justify-center text-sm font-bold">{{ strtoupper(substr($c->name,0,1)) }}</span>
                                <div><div class="text-sm font-medium text-slate-900 dark:text-white">{{ $c->name }}</div><div class="text-xs text-slate-500 dark:text-slate-400">{{ $c->created_at->format('M d, Y') }}</div></div>
                                <button type="button" onclick="replyTo({{ $c->id }}, '{{ addslashes($c->name) }}')" class="ml-auto text-xs font-semibold text-[#0C3B2E] dark:text-emerald-300 hover:underline inline-flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m15 10 5 5-5 5"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v7a4 4 0 0 0 4 4h12"/></svg> Reply
                                </button>
                            </div>
                            <p class="text-sm text-slate-700 dark:text-slate-300 mt-2 leading-relaxed">{{ $c->content }}</p>
                            @php $replies = $post->approvedComments->where('parent_id', $c->id); @endphp
                            @if($replies->count())
                                <div class="mt-3 ml-10 space-y-2 border-l-2 border-emerald-100 dark:border-[#383838] pl-4">
                                    @foreach($replies as $r)
                                        <div class="bg-white dark:bg-[#1e1e1e] p-3 border border-slate-200 dark:border-[#383838]">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m15 10 5 5-5 5"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v7a4 4 0 0 0 4 4h12"/></svg>
                                                <span class="w-6 h-6 bg-emerald-100 dark:bg-emerald-400/20 text-[#0C3B2E] dark:text-emerald-300 flex items-center justify-center text-xs font-bold">{{ strtoupper(substr($r->name,0,1)) }}</span>
                                                <div><div class="text-xs font-medium text-slate-900 dark:text-white">{{ $r->name }}</div><div class="text-[11px] text-slate-500 dark:text-slate-400">{{ $r->created_at->format('M d, Y') }}</div></div>
                                            </div>
                                            <p class="text-sm text-slate-700 dark:text-slate-300 mt-1.5 leading-relaxed">{{ $r->content }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-6 bg-slate-50 dark:bg-[#2a2a2a] border border-dashed border-slate-200 dark:border-[#383838]"><p class="text-sm text-slate-500 dark:text-slate-400">No comments yet. Be the first to share your thoughts!</p></div>
                    @endforelse
                </div>
            </div>
        </div>

        <aside class="lg:col-span-4 space-y-4">
            @if($related->count())
                <div class="card-elev p-4 lg:sticky lg:top-20">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#0C3B2E] dark:text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 2v4a2 2 0 0 0 2 2h4"/><path stroke-linecap="round" stroke-linejoin="round" d="M10 9H8"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 13H8"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 17H8"/></svg>
                        Related Posts
                    </h3>
                    <div class="space-y-3">
                        @foreach($related as $r)
                            <a href="{{ route('blog.show',$r->slug) }}" class="flex gap-3 group">
                                <img src="{{ $r->featured_image ?: 'https://picsum.photos/seed/'.$r->slug.'/200/200' }}" class="w-14 h-14 object-cover shrink-0" alt="{{ $r->title }}" loading="lazy" decoding="async">
                                <div><h4 class="text-sm font-medium text-slate-900 dark:text-white group-hover:text-[#0C3B2E] dark:group-hover:text-emerald-300 line-clamp-2 leading-snug">{{ $r->title }}</h4><span class="text-xs text-slate-500 dark:text-slate-400">{{ $r->reading_time }} min read</span></div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
            {{-- Popular posts: most viewed across the site --}}
            @if($popular->count())
                <div class="card-elev p-4">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#0C3B2E] dark:text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z"/></svg>
                        Popular Posts
                    </h3>
                    <div class="space-y-3">
                        @foreach($popular as $i => $p)
                            <a href="{{ route('blog.show',$p->slug) }}" class="flex gap-3 group">
                                <span class="w-6 h-6 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">{{ $i + 1 }}</span>
                                <div class="min-w-0"><h4 class="text-sm font-medium text-slate-900 dark:text-white group-hover:text-[#0C3B2E] dark:group-hover:text-emerald-300 line-clamp-2 leading-snug">{{ $p->title }}</h4><span class="text-xs text-slate-500 dark:text-slate-400">{{ number_format($p->views) }} views</span></div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
            {{-- Sidebar categories (live icons) --}}
            <div class="card-elev p-4">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#0C3B2E] dark:text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/></svg>
                    Categories
                </h3>
                <div class="space-y-1">
                    {{-- Live categories only (active + has published posts), with
                         published-post counts. Avoids empty category links and
                         the N+1 query the old ->posts->count() caused. --}}
                    @foreach(\App\Models\Category::live()->withCount(['posts as published_posts_count' => fn ($q) => $q->published()])->orderBy('sort_order')->get() as $cat)
                        <a href="{{ route('category.show',$cat->slug) }}" class="flex items-center justify-between p-2 hover:bg-slate-50 dark:hover:bg-[#2a2a2a] transition group">
                            <span class="flex items-center gap-2.5 text-slate-700 dark:text-slate-300">
                                <span class="w-8 h-8 bg-emerald-50 dark:bg-emerald-400/10 flex items-center justify-center text-[#0C3B2E] dark:text-emerald-300 shrink-0">
                                    @include('partials.category-icon', ['category' => $cat, 'class' => 'w-4 h-4'])
                                </span>
                                <span class="text-sm font-medium group-hover:text-[#0C3B2E] dark:group-hover:text-emerald-300">{{ $cat->name }}</span>
                            </span>
                            <span class="text-xs bg-slate-100 dark:bg-[#2a2a2a] text-slate-600 dark:text-slate-400 px-2 py-0.5">{{ $cat->published_posts_count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            {{-- Sidebar ad: renders only when ads are switched on in admin
                 settings AND the ad actually has code. No empty boxes, ever. --}}
            @php $ad = setting('ads_enabled') === '1' ? \App\Models\Advertisement::active()->position('sidebar')->first() : null; @endphp
            @if($ad && trim(strip_tags($ad->code ?? '')) !== '')
                <div class="card-elev p-3 ad-slot-wrap"><div class="ad-slot-label text-xs font-semibold tracking-wide text-slate-400 dark:text-slate-500 uppercase text-center mb-2">Advertisement</div>{!! $ad->code !!}</div>
            @endif
        </aside>
    </div>
</div>
@push('scripts')
<script>
function toggleFaq(btn){
    const content = btn.nextElementSibling;
    const isHidden = content.classList.contains('hidden');
    document.querySelectorAll('#faq-accordion > div > div:last-child').forEach(el=>{ if(el!==content){ el.classList.add('hidden'); }});
    if(isHidden){ content.classList.remove('hidden'); } else { content.classList.add('hidden'); }
}
// Reply system
function replyTo(id, name){
    document.getElementById('reply-parent-id').value = id;
    const box = document.getElementById('replying-to');
    document.getElementById('replying-to-name').textContent = name;
    box.classList.remove('hidden');
    document.getElementById('comment-form').scrollIntoView({behavior:'smooth', block:'center'});
}
function cancelReply(){
    document.getElementById('reply-parent-id').value = '';
    document.getElementById('replying-to').classList.add('hidden');
}
// Hide blank/unfilled ad slots: when an ad network serves nothing (site not
// approved yet, no fill), the slot collapses invisibly instead of showing an
// ugly empty box. The "Advertisement" label itself never counts as content,
// so a labeled but unfilled slot still collapses.
(function(){
    function collapseEmptyAdSlots(){
        document.querySelectorAll('.ad-slot, .ad-slot-wrap').forEach(function(el){
            if (el.dataset.adChecked) return;
            // text WITHOUT the label: an unfilled slot has nothing else
            var text = (el.innerText || '').replace(/advertisement/gi, '').trim();
            var hasContent = text.length > 2;
            var media = el.querySelectorAll('img, iframe, ins');
            var visibleMedia = false;
            media.forEach(function(m){ if (m.getBoundingClientRect().height > 4) visibleMedia = true; });
            // any real sized child content (e.g. ad text served late)
            if (!visibleMedia) {
                el.querySelectorAll('a, span, div').forEach(function(n){
                    var r = n.getBoundingClientRect();
                    if (r.height > 30 && (n.textContent || '').trim().length > 3) visibleMedia = true;
                });
            }
            if (!hasContent && !visibleMedia) {
                el.style.display = 'none';
                el.dataset.adChecked = '1';
            }
        });
    }
    window.addEventListener('load', function(){
        setTimeout(collapseEmptyAdSlots, 800);
        setTimeout(collapseEmptyAdSlots, 2500);
        setTimeout(collapseEmptyAdSlots, 5000);
    });
})();

// Affiliate / outbound click tracking: records clicks on external links so
// authors can see click counts and click rate on their Revenue page.
(function(){
    var postSlug = {{ json_encode($post->slug) }};
    if (!postSlug) return;
    document.querySelectorAll('.prose a[href], .ad-in-article a[href], a.ad-link').forEach(function(a){
        a.addEventListener('click', function(){
            try {
                var href = a.getAttribute('href') || '';
                if (!/^https?:\/\//i.test(href)) return;
                var token = document.querySelector('meta[name=csrf-token]');
                fetch('/blog/' + encodeURIComponent(postSlug) + '/click', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token ? token.getAttribute('content') : ''
                    },
                    body: JSON.stringify({ url: href }),
                    keepalive: true
                }).catch(function(){});
            } catch (e) { /* tracking must never break the click */ }
        });
    });
})();

// Copy link (fixed: clipboard API with fallback + feedback)
(function(){
    const btn = document.getElementById('copy-link-btn');
    if(!btn) return;
    btn.addEventListener('click', async ()=>{
        const url = btn.getAttribute('data-url');
        const label = document.getElementById('copy-link-label');
        let ok = false;
        try{
            if(navigator.clipboard && window.isSecureContext){
                await navigator.clipboard.writeText(url);
                ok = true;
            } else { throw new Error('fallback'); }
        }catch(e){
            // fallback for http contexts / older browsers
            const ta = document.createElement('textarea');
            ta.value = url;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try{ ok = document.execCommand('copy'); }catch(_){}
            document.body.removeChild(ta);
        }
        label.textContent = ok ? 'Copied!' : 'Press Ctrl+C';
        setTimeout(()=>{ label.textContent = 'Copy link'; }, 1800);
    });
})();
</script>
@endpush
@endsection
