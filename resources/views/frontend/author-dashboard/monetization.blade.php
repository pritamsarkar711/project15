@extends('frontend.author-dashboard.layout')

@section('title', 'Monetization')

@section('content')
<div class="max-w-[680px]">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-8 text-center">
        <div class="w-16 h-16 mx-auto bg-[#0C3B2E] flex items-center justify-center">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-5">Monetization is coming soon</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 leading-relaxed max-w-[420px] mx-auto">
            Soon you'll be able to earn from your posts based on real reader views. No paywalls, no intrusive ads, just a fair revenue share. Applications open once the writer program exits beta.
        </p>
        <div class="mt-5 text-xs text-slate-400">Track your total views from the dashboard. They count toward your future eligibility.</div>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5">
            <div class="text-[11px] font-semibold tracking-wide text-slate-500 uppercase">Total views</div>
            <div class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ \App\Models\Post::byAuthor(auth()->id())->sum('views') }}</div>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5">
            <div class="text-[11px] font-semibold tracking-wide text-slate-500 uppercase">Published posts</div>
            <div class="text-3xl font-bold text-slate-900 dark:text-white mt-1">{{ \App\Models\Post::byAuthor(auth()->id())->where('review_status', 'approved')->count() }}</div>
        </div>
    </div>
</div>
@endsection
