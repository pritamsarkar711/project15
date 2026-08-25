@extends('frontend.author-dashboard.layout')

@section('title', 'My posts')

@section('header-actions')
<a href="{{ route('author.posts.create') }}" class="inline-flex items-center gap-2 h-9 px-4 bg-[#0C3B2E] hover:bg-[#072A20] text-white text-xs font-semibold">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
    New post
</a>
@endsection

@section('content')
<div class="flex flex-wrap items-center gap-1 mb-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-1.5 w-fit">
    <a href="{{ route('author.posts.index') }}" class="px-3 py-1.5 text-xs font-semibold {{ $tab === 'all' ? 'bg-[#0C3B2E] text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">All ({{ $counts['all'] }})</a>
    <a href="{{ route('author.posts.index', ['tab' => 'draft']) }}" class="px-3 py-1.5 text-xs font-semibold {{ $tab === 'draft' ? 'bg-[#0C3B2E] text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">Drafts ({{ $counts['draft'] }})</a>
    <a href="{{ route('author.posts.index', ['tab' => 'pending']) }}" class="px-3 py-1.5 text-xs font-semibold {{ $tab === 'pending' ? 'bg-[#0C3B2E] text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">In review ({{ $counts['pending'] }})</a>
    <a href="{{ route('author.posts.index', ['tab' => 'returned']) }}" class="px-3 py-1.5 text-xs font-semibold {{ $tab === 'returned' ? 'bg-[#0C3B2E] text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">Returned ({{ $counts['returned'] }})</a>
    <a href="{{ route('author.posts.index', ['tab' => 'published']) }}" class="px-3 py-1.5 text-xs font-semibold {{ $tab === 'published' ? 'bg-[#0C3B2E] text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">Published ({{ $counts['published'] }})</a>
</div>

@if($posts->isEmpty())
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-8 text-center">
        <p class="text-slate-500 text-sm">No posts here yet.</p>
        <a href="{{ route('author.posts.create') }}" class="inline-flex items-center gap-2 mt-3 h-10 px-5 bg-[#0C3B2E] hover:bg-[#072A20] text-white text-sm font-semibold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
            Write a post
        </a>
    </div>
@else
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 divide-y divide-slate-100 dark:divide-slate-800">
        @foreach($posts as $post)
            @include('frontend.author-dashboard._post-row', ['post' => $post])
        @endforeach
    </div>
    <div class="mt-4">{{ $posts->links() }}</div>
@endif
@endsection
