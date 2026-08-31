@extends('frontend.author-dashboard.layout')

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
    <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400">Drafts</span>
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
        </div>
        <div class="text-3xl font-extrabold mt-2">{{ $stats['draft'] }}</div>
        <a href="{{ route('author.posts.index', ['tab' => 'draft']) }}" class="mt-1 inline-flex text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:underline">Open drafts</a>
    </div>
    <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400">In Review</span>
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        </div>
        <div class="text-3xl font-extrabold mt-2">{{ $stats['pending'] }}</div>
        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Awaiting admin decision</div>
    </div>
    <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400">Returned</span>
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
        </div>
        <div class="text-3xl font-extrabold mt-2 {{ $stats['returned'] > 0 ? 'text-amber-600 dark:text-amber-300' : '' }}">{{ $stats['returned'] }}</div>
        <a href="{{ route('author.posts.index', ['tab' => 'returned']) }}" class="mt-1 inline-flex text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:underline">Fix and resubmit</a>
    </div>
    <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400">Published</span>
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        </div>
        <div class="text-3xl font-extrabold mt-2 text-emerald-600 dark:text-emerald-300">{{ $stats['published'] }}</div>
        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Live on the site</div>
    </div>
    <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400">Total Posts</span>
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
        </div>
        <div class="text-3xl font-extrabold mt-2">{{ $stats['total'] }}</div>
        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">All time</div>
    </div>
    <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400">Total Views</span>
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
        </div>
        <div class="text-3xl font-extrabold mt-2">{{ number_format($stats['views']) }}</div>
        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Across your posts</div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-5 mt-6">
    <div class="lg:col-span-2 space-y-5">
        {{-- Submission allowance --}}
        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
                <div class="text-sm">
                    @if($stats['can_submit'])
                        <p class="font-semibold text-slate-900 dark:text-white">You can submit 1 post today</p>
                    @else
                        <p class="font-semibold text-slate-900 dark:text-white">Next submission {{ $stats['next_submit_at']?->format('M d, H:i') }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent posts --}}
        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold">Recent Posts</h3>
                <a href="{{ route('author.posts.index') }}" class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:underline">View all</a>
            </div>
            @if($recent->isEmpty())
                <div class="text-center py-8">
                    <div class="w-12 h-12 mx-auto bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-3">You have not written any posts yet.</p>
                    <a href="{{ route('author.posts.create') }}" class="inline-flex items-center gap-2 mt-4 h-10 px-5 bg-[#0C3B2E] hover:bg-[#072A20] text-white text-sm font-semibold">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Write your first post
                    </a>
                </div>
            @else
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($recent as $post)
                        @include('frontend.author-dashboard._post-row', ['post' => $post])
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Quick actions: calm card in the shared design language --}}
    <div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-5 shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
            <h3 class="font-semibold text-slate-900 dark:text-white">Quick Actions</h3>
            <div class="mt-4 grid grid-cols-2 gap-2.5">
                <a href="{{ route('author.posts.create') }}" class="group flex flex-col items-start gap-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40 p-3.5 hover:border-[#05B762]/50 hover:bg-white dark:hover:bg-slate-800 transition">
                    <span class="icon-tile w-8 h-8 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                    </span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-[#049A53] dark:group-hover:text-emerald-300 transition">New Post</span>
                </a>
                <a href="{{ route('author.posts.index') }}" class="group flex flex-col items-start gap-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40 p-3.5 hover:border-[#05B762]/50 hover:bg-white dark:hover:bg-slate-800 transition">
                    <span class="icon-tile w-8 h-8 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    </span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-[#049A53] dark:group-hover:text-emerald-300 transition">My Posts</span>
                </a>
                <a href="{{ route('author.rules') }}" class="group flex flex-col items-start gap-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40 p-3.5 hover:border-[#05B762]/50 hover:bg-white dark:hover:bg-slate-800 transition">
                    <span class="icon-tile w-8 h-8 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                    </span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-[#049A53] dark:group-hover:text-emerald-300 transition">Posting Rules</span>
                </a>
                <a href="{{ route('author.profile.edit') }}" class="group flex flex-col items-start gap-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40 p-3.5 hover:border-[#05B762]/50 hover:bg-white dark:hover:bg-slate-800 transition">
                    <span class="icon-tile w-8 h-8 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-[#049A53] dark:group-hover:text-emerald-300 transition">Profile</span>
                </a>
            </div>
        </div>
        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 mt-5">
            <h3 class="font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
                Writing Tips
            </h3>
            <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                <li class="flex gap-2"><span class="text-emerald-600 dark:text-emerald-300 font-bold">1</span> Write at least 300 words.</li>
                <li class="flex gap-2"><span class="text-emerald-600 dark:text-emerald-300 font-bold">2</span> Add a featured image.</li>
                <li class="flex gap-2"><span class="text-emerald-600 dark:text-emerald-300 font-bold">3</span> Answer a real reader question.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
