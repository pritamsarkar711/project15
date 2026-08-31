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
<div class="bg-slate-50/80 dark:bg-[#0D1411] border-b border-slate-100 dark:border-[#151D19]">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 py-12 sm:py-16">
        <span class="kicker">The bylines</span>
        <h1 class="mt-4 font-extrabold text-[36px] sm:text-[52px] text-slate-900 dark:text-[#F1F5F4] tracking-tight leading-[1.05]">Top contributors<span class="text-emerald-500">.</span></h1>
        <p class="mt-4 text-[15px] text-slate-500 dark:text-[#8FA398] max-w-2xl leading-relaxed">The twenty most active authors on Huvanti, ranked by published articles.</p>
    </div>
</div>

<div class="max-w-[1280px] mx-auto px-4 sm:px-6 py-12">
    @if($contributors->count())
        <div class="grid gap-3">
            @foreach($contributors as $i => $author)
                @php
                    $profileUrl = $author->username ? route('author.profile', $author->username) : null;
                    $avatar = $author->author_avatar_path ? asset('storage/'.$author->author_avatar_path) : null;
                @endphp
                <a {{ $profileUrl ? 'href="'.$profileUrl.'"' : 'href="#"' }} class="index-row group card-elev grid grid-cols-[44px_52px_1fr_28px] sm:grid-cols-[64px_60px_1fr_auto_36px] items-center gap-3 sm:gap-5 py-4 px-4 sm:px-5 hover:border-emerald-300 dark:hover:border-emerald-500/40">
                    <span class="text-[24px] sm:text-[28px] font-extrabold tabular-nums leading-none {{ $i < 3 ? 'text-amber-400' : 'text-slate-200 dark:text-[#2C3833]' }} group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors select-none">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="relative shrink-0">
                        @if($avatar)
                            <img src="{{ $avatar }}" alt="{{ $author->name }}" class="w-12 h-12 sm:w-14 sm:h-14 rounded-full object-cover ring-2 ring-white dark:ring-[#1F2925] shadow-sm" loading="lazy" decoding="async">
                        @else
                            <span class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">{{ strtoupper(substr($author->name, 0, 1)) }}</span>
                        @endif
                    </span>
                    <span class="min-w-0">
                        <span class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-[16px] text-slate-900 dark:text-[#F1F5F4] truncate group-hover:text-emerald-700 dark:group-hover:text-emerald-300 transition-colors">{{ $author->name }}</span>
                            @include('partials.country-flag', ['user' => $author, 'class' => 'w-4 h-3'])
                            {!! $author->badgeHtml() !!}
                        </span>
                        @if($author->role_title)
                            <span class="block text-[12px] text-slate-400 dark:text-[#6B7F75] mt-0.5 font-medium">{{ $author->role_title }}</span>
                        @endif
                        <span class="block text-[12.5px] text-slate-500 dark:text-[#8FA398] mt-1 font-semibold">{{ number_format($author->posts_count) }} published {{ Str::plural('article', $author->posts_count) }}</span>
                    </span>
                    <span class="hidden sm:inline-flex text-[12px] font-semibold text-slate-400 dark:text-[#6B7F75] group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition">Profile</span>
                    <span class="text-slate-400 dark:text-[#6B7F75] justify-self-end transition-transform group-hover:translate-x-1.5 group-hover:text-emerald-600">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </a>
            @endforeach
        </div>
    @else
        <div class="text-center py-16 card-elev max-w-xl mx-auto">
            <p class="text-[15px] font-medium text-slate-500 dark:text-[#8FA398]">No contributors yet. Published articles will appear here.</p>
        </div>
    @endif
</div>
@endsection
