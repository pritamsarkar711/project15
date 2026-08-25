@extends('frontend.author-dashboard.layout')

@section('title', 'Write a post')

@section('header-actions')
<a href="{{ route('author.posts.create') }}" class="inline-flex items-center gap-2 h-9 px-4 bg-[#0C3B2E] hover:bg-[#072A20] text-white text-xs font-semibold">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
    New post
</a>
@endsection

@section('content')
<div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6 mb-5">
    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Write a new post</h2>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Every submission is reviewed by an admin before it goes live.</p>
</div>

@include('frontend.author-dashboard._post-form')
@endsection
