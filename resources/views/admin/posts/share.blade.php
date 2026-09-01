@extends('layouts.admin')

@section('title', 'Share Post')

@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Posts', 'url' => route('admin.posts.index')],
        ['label' => 'Share'],
    ]])
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-5">
    <div class="panel-card p-6 sm:p-8 text-center">
        <div class="w-14 h-14 rounded-full bg-[#E9F2EE] dark:bg-[#233b30] text-[#1F513A] dark:text-[#6FB393] flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        </div>
        <h2 class="text-xl font-bold text-[#101319] dark:text-white">“{{ \Illuminate\Support\Str::limit($post->title, 70) }}” is live!</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Your post URL is below — share it on your socials to bring readers in fast.</p>

        {{-- URL box --}}
        <div class="mt-5 flex items-stretch gap-2 max-w-xl mx-auto">
            <input id="share-url-box" type="text" readonly value="{{ $shareUrl }}" onclick="this.select()"
                   class="flex-1 h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono text-slate-700 dark:text-slate-200 rounded-lg">
            <button type="button" id="share-copy-btn"
                    class="h-11 px-4 rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white font-semibold text-sm transition whitespace-nowrap">
                Copy link
            </button>
            <a href="{{ $shareUrl }}" target="_blank" title="Open post"
               class="h-11 px-4 rounded-lg border border-[#e6e8ee] dark:border-[#2c313c] bg-white dark:bg-[#14171d] text-slate-600 dark:text-slate-300 hover:bg-[#f7f8fa] dark:hover:bg-[#1c1f26] inline-flex items-center font-semibold text-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
            </a>
        </div>

        {{-- Social icons --}}
        <div class="mt-6 flex justify-center">
            @include('partials.share-buttons', ['shareUrl' => $shareUrl, 'shareTitle' => $post->title])
        </div>
        <p class="text-xs text-slate-400 dark:text-slate-500 mt-4">Each icon opens that network with your post pre-filled. The copy button copies the link.</p>
    </div>

    {{-- Auto-post delivery log (only when automation is configured) --}}
    <div class="panel-card p-6">
        <div class="flex items-center justify-between gap-3 flex-wrap mb-3">
            <h3 class="font-semibold text-[#101319] dark:text-white">Social Media Auto-Post</h3>
            <div class="flex items-center gap-3">
                @if($autopostEnabled)
                    <form method="POST" action="{{ route('admin.social.push', $post) }}">@csrf
                        <button type="submit" class="h-8 px-3 text-xs font-semibold rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white transition">Push to socials now</button>
                    </form>
                @endif
                <a href="{{ route('admin.social.index') }}" class="text-xs font-semibold text-[#1F513A] dark:text-[#6FB393] hover:underline">Manage automation →</a>
            </div>
        </div>
        @if(!$autopostEnabled)
            <p class="text-sm text-slate-500 dark:text-slate-400">Auto-posting is currently <strong>disabled</strong>. Turn it on under <a href="{{ route('admin.social.index') }}" class="text-[#1F513A] dark:text-[#6FB393] hover:underline font-medium">Social Auto-Post</a> to publish new posts to X, Facebook, LinkedIn, Instagram, Telegram & Pinterest automatically — no manual sharing needed.</p>
        @elseif($activeNetworks && count($activeNetworks))
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">This post was pushed automatically to: <strong>{{ implode(', ', array_map(fn($n) => \App\Models\SocialPublish::networkLabel($n), $activeNetworks)) }}</strong>. Delivery status per network:</p>
            @php $rows = $post->socialPublishes->whereIn('network', $activeNetworks); @endphp
            @if($rows->isEmpty())
                <p class="text-sm text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 p-3">Delivery in progress — refresh this page in a few seconds.</p>
            @else
                <ul class="space-y-2">
                    @foreach($rows as $row)
                        <li class="flex items-center justify-between gap-3 text-sm border border-slate-200 dark:border-slate-800 p-3">
                            <span class="font-medium text-slate-700 dark:text-slate-200">{{ \App\Models\SocialPublish::networkLabel($row->network) }}</span>
                            <span class="flex items-center gap-2 min-w-0">
                                @if($row->status === 'success')
                                    <span class="inline-flex items-center gap-1 text-[#1F513A] dark:text-[#6FB393] font-semibold text-xs"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>Published</span>
                                    @if($row->external_url)<a href="{{ $row->external_url }}" target="_blank" rel="noopener" class="text-xs text-[#1F513A] dark:text-[#6FB393] hover:underline">view</a>@endif
                                @elseif($row->status === 'failed')
                                    <span class="text-red-600 dark:text-red-400 font-semibold text-xs" title="{{ $row->error }}">Failed — {{ \Illuminate\Support\Str::limit($row->error, 80) }}</span>
                                    <form method="POST" action="{{ route('admin.social.retry', $row) }}">@csrf
                                        <button type="submit" class="text-xs font-semibold px-2.5 h-7 rounded-lg border border-[#e6e8ee] dark:border-[#2c313c] hover:bg-[#f7f8fa] dark:hover:bg-[#1c1f26]">Retry</button>
                                    </form>
                                @else
                                    <span class="text-slate-400 text-xs">Pending…</span>
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        @else
            <p class="text-sm text-slate-500 dark:text-slate-400">Auto-posting is enabled, but no network is fully configured yet. Add credentials under <a href="{{ route('admin.social.index') }}" class="text-[#1F513A] dark:text-[#6FB393] hover:underline font-medium">Social Auto-Post</a>.</p>
        @endif
    </div>

    <div class="flex gap-3 justify-center pb-2">
        <a href="{{ route('admin.posts.index') }}" class="h-10 px-5 inline-flex items-center rounded-lg border border-[#e6e8ee] dark:border-[#2c313c] bg-white dark:bg-[#14171d] text-slate-600 dark:text-slate-300 font-semibold text-sm hover:bg-[#f7f8fa] dark:hover:bg-[#1c1f26] transition">Back to Posts</a>
        <a href="{{ route('admin.posts.create') }}" class="h-10 px-5 inline-flex items-center rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white font-semibold text-sm transition">Write another post</a>
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
