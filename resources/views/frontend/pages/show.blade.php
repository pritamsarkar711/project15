@extends('layouts.app')
@php
    // Policy controllers pass descriptive $seoTitle/$seoDescription (fixes
    // Ahrefs "Title too short"). The layout's Seo::finalize* helpers top up
    // anything still short for custom admin-created pages.
    $metaTitle = $seoTitle ?? ($page->meta_title ?? $page->title);
    $metaDescription = $seoDescription ?? ($page->meta_description ?? null);
@endphp
@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-10">
    <div class="page-head !pt-2 !pb-0">
        <nav class="flex items-center gap-1.5 text-[13px] text-slate-400 dark:text-slate-500 mb-2.5" aria-label="Breadcrumb">
            <a href="/" class="hover:text-[#047a43] dark:hover:text-emerald-300 transition">Home</a>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 6 6 6-6 6"/></svg>
            <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $page->title }}</span>
        </nav>
        <h1>{{ $page->title }}</h1>
        <p class="lede">Last updated {{ $page->updated_at->format('M d, Y') }}</p>
    </div>
    <div class="card-elev p-6 sm:p-9 mt-4">
        <div class="prose dark:prose-invert max-w-none">{!! $page->content !!}</div>
    </div>
</div>
@endsection
