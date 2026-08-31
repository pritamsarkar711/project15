@extends('layouts.app')
@php
    // NOTE: keep this as a BLOCK-style PHP section (open/close pair), NOT
    // the inline single-line directive. This file contains a second PHP
    // block further down; Blade's pre-compiler pairs openers with the first
    // closer it finds GREEDILY, so the inline single-line form here got
    // swallowed into one broken raw block -> PHP parse error -> HTTP 500.
    $metaTitle = 'Top Contributors — The Most Active Writers on Huvanti';
@endphp
@section('content')
<div class="border-b border-[#e6e8ee] dark:border-[#22262e] bg-white dark:bg-[#0f1115]">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 page-head !pb-6">
        <nav class="flex items-center gap-1.5 text-[13px] text-slate-400 dark:text-slate-500 mb-2.5" aria-label="Breadcrumb">
            <a href="/" class="hover:text-[#047a43] dark:hover:text-emerald-300 transition">Home</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 6 6 6-6 6"/></svg>
            <span class="text-slate-700 dark:text-slate-300 font-medium">Top Contributors</span>
        </nav>
        <div class="flex items-end justify-between gap-4 flex-wrap">
            <div>
                <h1>Top Contributors</h1>
                <p class="lede">The twenty most active authors on Huvanti, ranked by published articles.</p>
            </div>
            <span class="badge badge-slate !text-xs !px-2.5 !py-1">{{ $contributors->count() }} writers</span>
        </div>
    </div>
</div>

<div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-8">
    @if($contributors->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-5">
            @foreach($contributors as $i => $author)
                @php
                    $profileUrl = $author->username ? route('author.profile', $author->username) : null;
                    $avatar = $author->author_avatar_path ? asset('storage/'.$author->author_avatar_path) : null;
                @endphp
                <article class="card-elev card-hover p-5 flex items-start gap-4">
                    <div class="relative shrink-0">
                        @if($avatar)
                            <img src="{{ $avatar }}" alt="{{ $author->name }}" class="w-14 h-14 rounded-full object-cover border border-[#e6e8ee] dark:border-[#2c313c]" loading="lazy" decoding="async">
                        @else
                            <div class="avatar w-14 h-14 !bg-[#0C3B2E] dark:!bg-[#E8F8F0] text-white dark:text-[#0C3B2E] font-extrabold text-lg">{{ strtoupper(substr($author->name, 0, 1)) }}</div>
                        @endif
                        @if($i < 3)
                            <span class="absolute -top-2 -left-2 w-6 h-6 rounded-full bg-[#05B762] text-white text-[11px] font-extrabold flex items-center justify-center ring-2 ring-white dark:ring-[#16181d]">{{ $i + 1 }}</span>
                        @else
                            <span class="absolute -top-2 -left-2 w-6 h-6 rounded-full bg-[#16181d] dark:bg-[#e6e8ee] dark:text-[#101319] text-white text-[11px] font-extrabold flex items-center justify-center ring-2 ring-white dark:ring-[#16181d]">{{ $i + 1 }}</span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @if($profileUrl)
                                <a href="{{ $profileUrl }}" class="font-bold text-[15px] text-slate-900 dark:text-white hover:text-[#047a43] dark:hover:text-emerald-300 transition-colors truncate">{{ $author->name }}</a>
                            @else
                                <span class="font-bold text-[15px] text-slate-900 dark:text-white truncate">{{ $author->name }}</span>
                            @endif
                            @include('partials.country-flag', ['user' => $author, 'class' => 'w-4 h-3'])
                            {!! $author->badgeHtml() !!}
                        </div>
                        @if($author->role_title)
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $author->role_title }}</p>
                        @endif
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ number_format($author->posts_count) }} published {{ Str::plural('article', $author->posts_count) }}</p>
                        @if($profileUrl)
                            <a href="{{ $profileUrl }}" class="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-[#047a43] dark:text-emerald-300 hover:underline underline-offset-4">View profile
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12l-7.5 7.5M21 12H3"/></svg>
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="card-elev empty-state">
            <span class="icon-tile">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM4 21v-1a7 7 0 0 1 14 0v1"/></svg>
            </span>
            <p>No contributors yet. Published articles will appear here.</p>
        </div>
    @endif
</div>
@endsection
