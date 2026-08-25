@extends('frontend.author-dashboard.layout')

@section('title', 'Write a post')

@section('content')
<div class="max-w-[800px]">
    <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6 mb-5">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Write a new post</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Be helpful, be specific, write like a human. <a href="{{ route('author.rules') }}" class="text-emerald-700 dark:text-emerald-300 hover:underline font-semibold">Read the posting rules</a> before your first submission.</p>
    </div>

    @include('frontend.author-dashboard._post-form')
</div>
@endsection
