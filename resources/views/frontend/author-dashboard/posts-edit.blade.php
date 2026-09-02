@extends('frontend.author-dashboard.layout')

@section('title', 'Edit post')

@section('content')
<div class="panel-card p-5 sm:p-6 mb-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Edit post</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Status:
                @if($post->review_status === 'draft') <span class="font-semibold text-slate-700 dark:text-slate-300">Draft</span>
                @elseif($post->review_status === 'pending_review') <span class="font-semibold text-blue-600 dark:text-blue-400">In review</span>
                @elseif($post->review_status === 'returned') <span class="font-semibold text-amber-600 dark:text-amber-400">Returned</span>
                @elseif($post->review_status === 'approved') <span class="font-semibold text-[var(--brand-strong)] dark:text-[var(--brand-mid)]">Published</span>
                @endif
            </p>
        </div>
        @if($post->review_status === 'approved')
            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 h-9 px-4 rounded-lg bg-[var(--brand)] hover:bg-[var(--brand-strong)] text-white text-xs font-semibold">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 3h6m0 0v6m0-6L10 14M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                View live
            </a>
        @endif
    </div>
</div>

@if($post->review_status === 'returned' && $post->reviewer_note)
    <div class="bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 p-4 mb-5">
        <div class="text-xs font-semibold tracking-wide text-amber-700 dark:text-amber-400 uppercase mb-1">Reviewer note</div>
        <p class="text-sm text-amber-800 dark:text-amber-200">{{ $post->reviewer_note }}</p>
    </div>
@endif

@include('frontend.author-dashboard._post-form', ['post' => $post, 'isEdit' => true])

@if($post->review_status === 'approved' || $post->review_status === 'pending_review')
@push('scripts')
<script>
// This post is locked (published / awaiting review). Make the lock REAL:
// every control is disabled so the author cannot type edits that can never
// be saved, and Enter in a field can no longer fire a doomed submit. The
// autosave layers are stopped too — the server refuses them with 409.
(function(){
    var form = document.querySelector('form[data-autosave]');
    if (!form) return;
    window.__huvAutosaveStop = true;
    form.querySelectorAll('input:not([type="hidden"]), select, textarea, button').forEach(function (el) { el.disabled = true; });
    var c = document.querySelector('.huv-rte-content');
    if (c) { c.setAttribute('contenteditable', 'false'); c.style.opacity = '0.75'; }
    var src = document.querySelector('.huv-rte-src');
    if (src) src.disabled = true;
    document.querySelectorAll('.huv-rte-btn, .huv-rte-select, .huv-rte-dd-btn').forEach(function (el) { el.disabled = true; });
})();
</script>
@endpush
@endif
@endsection
