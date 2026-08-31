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
<div class="dotgrid border-b border-[#E4E4DA] dark:border-[#262C28]">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 py-12 sm:py-16">
        <span class="kicker"><b>01</b> The bylines</span>
        <h1 class="mt-4 font-black text-[36px] sm:text-[52px] text-[#141A16] dark:text-[#F0F2EB] tracking-tight leading-[1.03]">Top contributors<span class="text-[#F5C445]">.</span></h1>
        <p class="mt-4 text-[15px] text-[#5C665E] dark:text-[#97A199] max-w-2xl leading-relaxed">The twenty most active authors on Huvanti, ranked by published articles.</p>
    </div>
</div>

<div class="max-w-[1280px] mx-auto px-4 sm:px-6 py-12">
    @if($contributors->count())
        <div class="border-b border-[#E4E4DA] dark:border-[#262C28]">
            @foreach($contributors as $i => $author)
                @php
                    $profileUrl = $author->username ? route('author.profile', $author->username) : null;
                    $avatar = $author->author_avatar_path ? asset('storage/'.$author->author_avatar_path) : null;
                @endphp
                <a {{ $profileUrl ? 'href="'.$profileUrl.'"' : 'href="#"' }} class="index-row group grid grid-cols-[52px_56px_1fr_28px] sm:grid-cols-[72px_64px_1fr_auto_36px] items-center gap-3 sm:gap-5 py-4 px-2 sm:px-4">
                    <span class="text-[26px] sm:text-[30px] font-black tabular-nums leading-none {{ $i < 3 ? 'text-[#F5C445]' : 'text-[#D8D8CC] dark:text-[#3A443D]' }} group-hover:text-[#0C3B2E] dark:group-hover:text-[#34D399] transition-colors select-none">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="relative shrink-0">
                        @if($avatar)
                            <img src="{{ $avatar }}" alt="{{ $author->name }}" class="w-12 h-12 sm:w-14 sm:h-14 object-cover plate" loading="lazy" decoding="async">
                        @else
                            <span class="w-12 h-12 sm:w-14 sm:h-14 bg-[#0C3B2E] text-white flex items-center justify-center font-black text-lg plate">{{ strtoupper(substr($author->name, 0, 1)) }}</span>
                        @endif
                    </span>
                    <span class="min-w-0">
                        <span class="flex items-center gap-2 flex-wrap">
                            <span class="font-extrabold text-[16px] text-[#141A16] dark:text-[#F0F2EB] truncate group-hover:text-[#0C3B2E] dark:group-hover:text-[#34D399] transition-colors">{{ $author->name }}</span>
                            @include('partials.country-flag', ['user' => $author, 'class' => 'w-4 h-3'])
                            {!! $author->badgeHtml() !!}
                        </span>
                        @if($author->role_title)
                            <span class="block text-[12px] text-[#8B958C] dark:text-[#6B756C] mt-0.5 font-medium">{{ $author->role_title }}</span>
                        @endif
                        <span class="block text-[12.5px] text-[#5C665E] dark:text-[#97A199] mt-1 font-semibold">{{ number_format($author->posts_count) }} published {{ Str::plural('article', $author->posts_count) }}</span>
                    </span>
                    <span class="hidden sm:inline-flex text-[11px] font-extrabold tracking-[0.16em] uppercase text-[#8B958C] dark:text-[#6B756C] group-hover:text-[#0C3B2E] dark:group-hover:text-[#34D399] transition">Profile</span>
                    <span class="text-[#141A16] dark:text-[#F0F2EB] justify-self-end transition-transform group-hover:translate-x-1.5">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                </a>
            @endforeach
        </div>
    @else
        <div class="text-center py-16 card-elev max-w-xl mx-auto">
            <p class="text-[15px] font-medium text-[#5C665E] dark:text-[#97A199]">No contributors yet. Published articles will appear here.</p>
        </div>
    @endif
</div>
@endsection
