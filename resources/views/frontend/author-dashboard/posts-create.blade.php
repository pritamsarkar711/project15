@extends('frontend.author-dashboard.layout')

@section('title', 'Write a post')

@section('header-actions')
<a href="{{ route('author.posts.create') }}" class="inline-flex items-center gap-2 h-9 px-4 rounded-lg bg-[var(--brand)] hover:bg-[var(--brand-strong)] text-white text-xs font-semibold">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
    New post
</a>
@endsection

@section('content')
<div class="panel-card p-5 sm:p-6 mb-5">
    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Write a new post</h2>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Every post is reviewed before it goes live.</p>
</div>

{{-- Crash / power-cut recovery: an auto-saved draft from an earlier session
     that was never manually saved is offered here, so interrupted writing is
     never lost. --}}
@if(isset($recoveredDraft) && $recoveredDraft)
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 border border-[var(--brand-tint-2)] bg-[var(--brand-tint-3)] dark:border-[var(--brand)]/30 dark:bg-[var(--brand)]/10 p-4">
        <div class="min-w-0">
            <div class="text-sm font-semibold text-[var(--brand-deep)] dark:text-[var(--brand-light)]">Unsaved draft recovered</div>
            <p class="text-sm text-[var(--brand-ink)] dark:text-[var(--brand-mid)] mt-0.5 truncate">
                "{{ \Illuminate\Support\Str::limit($recoveredDraft->title ?: 'Untitled draft', 60) }}" — auto-saved {{ $recoveredDraft->autosaved_at?->diffForHumans() ?? 'recently' }}.
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('author.posts.edit', $recoveredDraft->id) }}" class="inline-flex items-center h-9 px-4 rounded-lg bg-[var(--brand)] hover:bg-[var(--brand-strong)] text-white text-xs font-semibold">Resume writing</a>
            <form method="POST" action="{{ route('author.posts.destroy', $recoveredDraft->id) }}" onsubmit="return confirm('Discard the recovered draft permanently?')">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center h-9 px-3 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10">Discard</button>
            </form>
        </div>
    </div>
@endif

@include('frontend.author-dashboard._post-form')
@endsection
