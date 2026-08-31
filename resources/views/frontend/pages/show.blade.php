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
    <div class="card-elev p-6 sm:p-10">
        <h1 class="font-extrabold text-3xl sm:text-4xl text-slate-900 dark:text-white tracking-tight">{{ $page->title }}</h1>
        <div class="text-sm text-slate-500 dark:text-slate-400 mt-1">Last updated: {{ $page->updated_at->format('M d, Y') }}</div>
        <div class="prose dark:prose-invert max-w-none mt-8">{!! $page->content !!}</div>
    </div>
</div>
@endsection
