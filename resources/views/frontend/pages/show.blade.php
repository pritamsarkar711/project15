@extends('layouts.app')
@php
    // Policy controllers pass descriptive $seoTitle/$seoDescription (fixes
    // Ahrefs "Title too short"). The layout's Seo::finalize* helpers top up
    // anything still short for custom admin-created pages.
    $metaTitle = $seoTitle ?? ($page->meta_title ?? $page->title);
    $metaDescription = $seoDescription ?? ($page->meta_description ?? null);
@endphp
@section('content')
<div class="dotgrid border-b border-[#E4E4DA] dark:border-[#262C28]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-12 sm:py-14">
        <span class="kicker"><b>§</b> {{ $page->title }}</span>
        <h1 class="mt-4 font-black text-[34px] sm:text-[46px] text-[#141A16] dark:text-[#F0F2EB] tracking-tight leading-[1.05]">{{ $page->title }}</h1>
        <div class="mt-3 text-[12px] font-extrabold tracking-[0.16em] uppercase text-[#8B958C] dark:text-[#6B756C]">Last updated {{ $page->updated_at->format('M d, Y') }}</div>
    </div>
</div>
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    <div class="prose dark:prose-invert max-w-none">{!! $page->content !!}</div>
</div>
@endsection
