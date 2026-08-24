@extends('frontend.author-dashboard.layout')

@section('title', 'Write a post')

@section('content')
<div class="max-w-[800px]">
    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-1">Write a new post</h2>
    <p class="text-sm text-slate-500 mb-5">Be helpful, be specific, no AI slop. <a href="{{ route('author.rules') }}" class="text-[#0C3B2E] dark:text-emerald-300 hover:underline">Read the rules →</a></p>

    @if(session('error'))
        <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 px-4 py-3 text-sm mb-4">{{ session('error') }}</div>
    @endif

    @include('frontend.author-dashboard._post-form')
</div>
@endsection
