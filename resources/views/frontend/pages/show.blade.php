@extends('layouts.app')
@php
    // Policy controllers pass descriptive $seoTitle/$seoDescription (fixes
    // Ahrefs "Title too short"). The layout's Seo::finalize* helpers top up
    // anything still short for custom admin-created pages.
    $metaTitle = $seoTitle ?? ($page->meta_title ?? $page->title);
    $metaDescription = $seoDescription ?? ($page->meta_description ?? null);
@endphp
@section('content')
<div class="bg-slate-50/80 dark:bg-[#0D1411] border-b border-slate-100 dark:border-[#151D19]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-12 sm:py-14">
        <span class="kicker">Huvanti</span>
        <h1 class="mt-4 font-extrabold text-[34px] sm:text-[46px] text-slate-900 dark:text-[#F1F5F4] tracking-tight leading-[1.05]">{{ $page->title }}</h1>
        <div class="mt-3 text-[13px] font-medium text-slate-400 dark:text-[#6B7F75]">Last updated {{ $page->updated_at->format('M d, Y') }}</div>
    </div>
</div>
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
    <div class="prose dark:prose-invert max-w-none">{!! $page->content !!}</div>
</div>
@endsection
