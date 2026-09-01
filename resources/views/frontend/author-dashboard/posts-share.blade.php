@extends('frontend.author-dashboard.layout')

@section('title', 'Share your post')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">
    <div class="panel-card p-6 sm:p-8 text-center">
        <div class="w-14 h-14 rounded-full bg-[#E9F2EE] dark:bg-[#233b30] text-[#1F513A] dark:text-[#6FB393] flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        </div>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">“{{ \Illuminate\Support\Str::limit($post->title, 70) }}” is live!</h2>

        <div class="mt-5 flex items-stretch gap-2 max-w-xl mx-auto">
            <input id="share-url-box" type="text" readonly value="{{ $shareUrl }}" onclick="this.select()"
                   class="flex-1 h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono text-slate-700 dark:text-slate-200 rounded-lg">
            <button type="button" id="share-copy-btn"
                    class="h-11 px-4 rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white font-semibold text-sm transition whitespace-nowrap">
                Copy link
            </button>
            <a href="{{ $shareUrl }}" target="_blank" title="View live post"
               class="h-11 px-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#14171d] text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 inline-flex items-center font-semibold text-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
            </a>
        </div>

        <div class="mt-6 flex justify-center">
            @include('partials.share-buttons', ['shareUrl' => $shareUrl, 'shareTitle' => $post->title])
        </div>
    </div>

    <div class="flex gap-3 justify-center pb-2">
        <a href="{{ route('author.posts.index') }}" class="h-10 px-5 inline-flex items-center rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#14171d] text-slate-600 dark:text-slate-300 font-semibold text-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition">My Posts</a>
        <a href="{{ route('author.posts.create') }}" class="h-10 px-5 inline-flex items-center rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white font-semibold text-sm transition">Write another post</a>
    </div>
</div>

@push('scripts')
<script>
    (function(){
        var btn = document.getElementById('share-copy-btn');
        var box = document.getElementById('share-url-box');
        if (!btn || !box) return;
        btn.addEventListener('click', function(){
            box.select();
            var done = function(ok){
                var prev = btn.textContent;
                btn.textContent = ok ? 'Copied!' : 'Ctrl+C';
                btn.style.background = ok ? '#1F513A' : '';
                setTimeout(function(){ btn.textContent = prev; btn.style.background=''; }, 1500);
            };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(box.value).then(function(){ done(true); }).catch(function(){ done(false); });
            } else {
                try { document.execCommand('copy'); done(true); } catch(e) { done(false); }
            }
        });
    })();
</script>
@endpush
@endsection
