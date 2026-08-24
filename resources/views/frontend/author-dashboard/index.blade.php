@extends('frontend.author-dashboard.layout')

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
        <div class="text-[11px] font-semibold tracking-wide text-slate-500 uppercase">Drafts</div>
        <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['draft'] }}</div>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
        <div class="text-[11px] font-semibold tracking-wide text-slate-500 uppercase">In Review</div>
        <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['pending'] }}</div>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
        <div class="text-[11px] font-semibold tracking-wide text-slate-500 uppercase">Returned</div>
        <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $stats['returned'] }}</div>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
        <div class="text-[11px] font-semibold tracking-wide text-slate-500 uppercase">Published</div>
        <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $stats['published'] }}</div>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
        <div class="text-[11px] font-semibold tracking-wide text-slate-500 uppercase">Total Posts</div>
        <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['total'] }}</div>
    </div>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
        <div class="text-[11px] font-semibold tracking-wide text-slate-500 uppercase">Total Views</div>
        <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $stats['views'] }}</div>
    </div>
</div>

<div class="mt-6 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 p-4 text-sm text-amber-800 dark:text-amber-300 rounded">
    @if($stats['can_submit'])
        You can submit <strong>1 post for review</strong> today.
    @else
        Next submission allowed: <strong>{{ $stats['next_submit_at']?->format('M d, H:i') }}</strong> (one submission per 24h).
    @endif
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <a href="{{ route('author.posts.create') }}" class="inline-flex items-center gap-2 h-11 px-5 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold text-sm">
        Write a new post
    </a>
    <a href="{{ route('author.rules') }}" class="inline-flex items-center gap-2 h-11 px-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-sm">
        Read the posting rules
    </a>
</div>

<div class="mt-8">
    <div class="flex items-baseline justify-between mb-3">
        <h2 class="font-bold text-slate-900 dark:text-white text-lg">Recent posts</h2>
        <a href="{{ route('author.posts.index') }}" class="text-sm font-semibold text-[#0C3B2E] dark:text-emerald-300 hover:underline">View all →</a>
    </div>
    @if($recent->isEmpty())
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-8 text-center text-slate-500 text-sm">
            You haven't written any posts yet. <a href="{{ route('author.posts.create') }}" class="font-semibold text-[#0C3B2E] dark:text-emerald-300 hover:underline">Write your first one →</a>
        </div>
    @else
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 divide-y divide-slate-100 dark:divide-slate-800">
            @foreach($recent as $post)
                @include('frontend.author-dashboard._post-row', ['post' => $post])
            @endforeach
        </div>
    @endif
</div>
@endsection
