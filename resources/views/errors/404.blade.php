@php($robots = 'noindex')
@extends('layouts.app')

@php($metaTitle = "Sorry, We Couldn't Find That Page | Huvanti")

@section('content')
<div class="max-w-[1280px] mx-auto px-4 sm:px-6 py-20 sm:py-28 text-center">
    <p class="kicker justify-center">Error 404</p>
    <p class="mt-6 text-[110px] sm:text-[170px] font-extrabold leading-none text-slate-900 dark:text-[#F1F5F4] select-none tracking-tight">4<span class="text-emerald-500">0</span>4</p>
    <h1 class="mt-4 text-[24px] sm:text-[32px] font-extrabold text-slate-900 dark:text-[#F1F5F4]">We couldn't find that page</h1>
    <p class="mt-3 text-[15px] text-slate-500 dark:text-[#8FA398] max-w-md mx-auto leading-relaxed">
        The link may be broken, or the article may have been moved or deleted.
        Try one of the pages below — or search for what you were looking for.
    </p>

    <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
        <a href="{{ url('/') }}" class="btn btn-primary btn-sm">
            Go to homepage
        </a>
        <a href="{{ url('/blog') }}" class="btn btn-outline btn-sm">
            Browse all articles
        </a>
    </div>

    <nav class="mt-12 text-[13.5px] font-medium text-slate-400 dark:text-[#6B7F75] flex flex-wrap items-center justify-center gap-x-7 gap-y-2">
        <a href="{{ url('/about') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 hover:underline underline-offset-4">About us</a>
        <a href="{{ url('/contact') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 hover:underline underline-offset-4">Contact</a>
        <a href="{{ url('/privacy-policy') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 hover:underline underline-offset-4">Privacy Policy</a>
        <a href="{{ url('/editorial-policy') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 hover:underline underline-offset-4">Editorial Policy</a>
    </nav>
</div>
@endsection
