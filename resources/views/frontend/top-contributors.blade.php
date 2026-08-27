@extends('layouts.app')
@php($metaTitle = 'Top Contributors · ' . setting('site_name','huvanti.com'))
@section('content')
<div class="bg-emerald-50/70 dark:bg-[#1e1e1e] border-b border-emerald-100 dark:border-[#2f2f2f]">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-8 sm:py-10">
        <h1 class="text-[30px] sm:text-[36px] font-extrabold text-slate-900 dark:text-white tracking-tight">Top Contributors</h1>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-2 max-w-2xl">The twenty most active authors on Huvanti, ranked by published articles.</p>
    </div>
</div>

<div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-10">
    @if($contributors->count())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($contributors as $i => $author)
                @php
                    $profileUrl = $author->username ? route('author.profile', $author->username) : null;
                    $avatar = $author->author_avatar_path ? asset('storage/'.$author->author_avatar_path) : null;
                @endphp
                <article class="card-elev p-5 flex items-start gap-4 hover:shadow-xl transition-all duration-200">
                    <div class="relative shrink-0">
                        @if($avatar)
                            <img src="{{ $avatar }}" alt="{{ $author->name }}" class="w-14 h-14 object-cover border border-slate-200 dark:border-[#383838]" loading="lazy" decoding="async">
                        @else
                            <div class="w-14 h-14 bg-[#0C3B2E] text-white flex items-center justify-center font-extrabold text-lg">{{ strtoupper(substr($author->name, 0, 1)) }}</div>
                        @endif
                        <span class="absolute -top-2 -left-2 w-6 h-6 bg-[#F5C445] text-slate-900 text-[11px] font-extrabold flex items-center justify-center">{{ $i + 1 }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @if($profileUrl)
                                <a href="{{ $profileUrl }}" class="font-bold text-slate-900 dark:text-white hover:text-[#0C3B2E] dark:hover:text-emerald-300 truncate">{{ $author->name }}</a>
                            @else
                                <span class="font-bold text-slate-900 dark:text-white truncate">{{ $author->name }}</span>
                            @endif
                            {!! $author->badgeHtml() !!}
                        </div>
                        @if($author->role_title)
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $author->role_title }}</p>
                        @endif
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ number_format($author->posts_count) }} published {{ Str::plural('article', $author->posts_count) }}</p>
                        @if($profileUrl)
                            <a href="{{ $profileUrl }}" class="inline-flex items-center gap-1 mt-2 text-xs font-semibold text-[#0C3B2E] dark:text-emerald-300 hover:underline">View profile
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12l-7.5 7.5M21 12H3"/></svg>
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="text-center py-12 card-elev">
            <p class="text-sm text-slate-600 dark:text-slate-400">No contributors yet. Published articles will appear here.</p>
        </div>
    @endif
</div>
@endsection
