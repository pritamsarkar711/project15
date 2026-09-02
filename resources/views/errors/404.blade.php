@php($robots = 'noindex')
@extends('layouts.app')

@php($metaTitle = "Sorry, We Couldn't Find That Page | Huvanti")

@section('content')
<div class="max-w-[1200px] mx-auto px-4 sm:px-6 py-20 text-center">
    <p class="text-[64px] sm:text-[96px] font-extrabold leading-none text-[var(--brand)] dark:text-[var(--brand-light)] select-none tracking-tight">404</p>
    <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">We couldn't find that page</h1>
    <p class="mt-3 text-sm sm:text-base text-slate-600 dark:text-slate-400 max-w-xl mx-auto leading-relaxed">
        The link may be broken, or the article may have been moved or deleted.
        Try one of the pages below — or search for what you were looking for.
    </p>

    <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
        <a href="{{ url('/') }}" class="btn btn-primary">
            Go to homepage
        </a>
        <a href="{{ url('/blog') }}" class="btn btn-outline">
            Browse all articles
        </a>
    </div>

    <nav class="mt-10 text-sm text-slate-500 dark:text-slate-400 flex flex-wrap items-center justify-center gap-x-5 gap-y-2">
        <a href="{{ url('/about') }}" class="hover:text-[var(--brand-ink)] dark:hover:text-[var(--brand-light)] hover:underline">About us</a>
        <a href="{{ url('/contact') }}" class="hover:text-[var(--brand-ink)] dark:hover:text-[var(--brand-light)] hover:underline">Contact</a>
        <a href="{{ url('/privacy-policy') }}" class="hover:text-[var(--brand-ink)] dark:hover:text-[var(--brand-light)] hover:underline">Privacy Policy</a>
        <a href="{{ url('/editorial-policy') }}" class="hover:text-[var(--brand-ink)] dark:hover:text-[var(--brand-light)] hover:underline">Editorial Policy</a>
    </nav>
</div>
@endsection
