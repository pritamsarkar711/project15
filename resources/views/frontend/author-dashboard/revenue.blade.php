@extends('frontend.author-dashboard.layout')

@section('title', 'Revenue')

@section('content')
{{-- Program status banner: short and clear --}}
@if(! $revenueEnabled)
    <div class="panel-card p-6 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 bg-[var(--brand-tint-3)] dark:bg-[var(--brand)]/10 text-[var(--brand-strong)] dark:text-[var(--brand-light)] flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
            </div>
            <div>
                <h2 class="font-bold text-slate-900 dark:text-white">Revenue program is coming soon</h2>
            </div>
        </div>
    </div>
@endif

{{-- Stat cards, full width grid like the dashboard --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="panel-card p-5">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400">Total Views</span>
            <svg class="w-5 h-5 text-[var(--brand-strong)] dark:text-[var(--brand-light)] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
        </div>
        <div class="text-3xl font-extrabold mt-2">{{ number_format($stats['total_views']) }}</div>
        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Across all your posts</div>
    </div>
    <div class="panel-card p-5">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400">Published</span>
            <svg class="w-5 h-5 text-[var(--brand-strong)] dark:text-[var(--brand-light)] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        </div>
        <div class="text-3xl font-extrabold mt-2 text-[var(--brand-strong)] dark:text-[var(--brand-light)]">{{ number_format($stats['published']) }}</div>
        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Live posts</div>
    </div>
    <div class="panel-card p-5">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400">Link Clicks</span>
            <svg class="w-5 h-5 text-[var(--brand-strong)] dark:text-[var(--brand-light)] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
        </div>
        <div class="text-3xl font-extrabold mt-2">{{ number_format($stats['affiliate_clicks']) }}</div>
        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Clicks on your affiliate links</div>
    </div>
    <div class="panel-card p-5">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400">Click Rate</span>
            <svg class="w-5 h-5 text-[var(--brand-strong)] dark:text-[var(--brand-light)] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
        </div>
        <div class="text-3xl font-extrabold mt-2">{{ $stats['click_rate'] }}%</div>
        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Clicks per view on affiliate posts</div>
    </div>
</div>

{{-- Affiliate summary --}}
<div class="panel-card p-6 mt-6">
    <h3 class="font-semibold text-slate-900 dark:text-white mb-4">Affiliate Performance</h3>
    <div class="grid sm:grid-cols-3 gap-4 text-sm">
        <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-4">
            <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['affiliate_posts']) }}</div>
            <div class="text-slate-500 dark:text-slate-400 mt-1">Affiliate posts</div>
        </div>
        <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-4">
            <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['affiliate_clicks']) }}</div>
            <div class="text-slate-500 dark:text-slate-400 mt-1">Total link clicks</div>
        </div>
        <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-4">
            <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $stats['click_rate'] }}%</div>
            <div class="text-slate-500 dark:text-slate-400 mt-1">Click rate</div>
        </div>
    </div>
</div>
@endsection
