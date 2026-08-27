@extends('frontend.author-dashboard.layout')

@section('title', 'Write a post')

@section('header-actions')
<a href="{{ route('author.posts.create') }}" class="inline-flex items-center gap-2 h-9 px-4 bg-[#0C3B2E] hover:bg-[#072A20] text-white text-xs font-semibold">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
    New post
</a>
@endsection

@section('content')
<div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6 mb-5">
    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Write a new post</h2>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Every submission is reviewed by an admin before it goes live.</p>
</div>

{{-- Crash / power-cut recovery: an auto-saved draft from an earlier session
     that was never manually saved is offered here, so interrupted writing is
     never lost. --}}
@if(isset($recoveredDraft) && $recoveredDraft)
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 border border-emerald-200 bg-emerald-50 dark:border-emerald-500/30 dark:bg-emerald-500/10 p-4">
        <div class="min-w-0">
            <div class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">Unsaved draft recovered</div>
            <p class="text-sm text-emerald-700 dark:text-emerald-400 mt-0.5 truncate">
                "{{ \Illuminate\Support\Str::limit($recoveredDraft->title ?: 'Untitled draft', 60) }}" — auto-saved {{ $recoveredDraft->autosaved_at?->diffForHumans() ?? 'recently' }}.
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('author.posts.edit', $recoveredDraft->id) }}" class="inline-flex items-center h-9 px-4 bg-[#0C3B2E] hover:bg-[#072A20] text-white text-xs font-semibold">Resume writing</a>
            <form method="POST" action="{{ route('author.posts.destroy', $recoveredDraft->id) }}" onsubmit="return confirm('Discard the recovered draft permanently?')">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center h-9 px-3 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10">Discard</button>
            </form>
        </div>
    </div>
@endif

@include('frontend.author-dashboard._post-form')
@endsection
