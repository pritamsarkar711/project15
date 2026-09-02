@extends('layouts.admin')
@section('title','Comments')
@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Comments'],
    ]])
@endsection

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex flex-wrap items-center gap-1.5">
        @php $tabs = ['all'=>'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','spam'=>'Spam']; @endphp
        @foreach($tabs as $key => $label)
            <a href="{{ route('admin.comments.index', $key !== 'all' ? ['status'=>$key] : []) }}"
               class="h-9 px-3.5 inline-flex items-center gap-2 text-[13px] font-semibold rounded-lg transition {{ (request('status')==$key || (!request('status') && $key=='all')) ? 'bg-[#16181d] text-white dark:bg-white dark:text-[#101319]' : 'bg-white dark:bg-[#14171d] border border-[#e6e8ee] dark:border-[#2c313c] text-slate-600 dark:text-slate-300 hover:bg-[#f7f8fa] dark:hover:bg-[#1c1f26]' }}">
                {{ $label }} <span class="text-xs opacity-70">{{ $counts[$key] }}</span>
            </a>
        @endforeach
    </div>
    {{-- Bulk delete bar: select comments with the checkboxes, then remove
         them all at once. --}}
    <form method="POST" action="{{ route('admin.comments.bulk-delete') }}" id="bulk-delete-form"
          onsubmit="return confirm('Delete all selected comments (their replies are removed too)?')">
        @csrf
        <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 dark:text-slate-300 cursor-pointer mr-2">
            <input type="checkbox" id="select-all-comments" class="w-4 h-4 text-[var(--brand-strong)] border-slate-300 dark:border-slate-600">
            Select all
        </label>
        <button type="submit" id="bulk-delete-btn" disabled class="h-9 px-4 text-sm font-semibold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 transition disabled:opacity-40 disabled:cursor-not-allowed">
            Delete selected (<span id="bulk-count">0</span>)
        </button>
    </form>
</div>

<div class="panel-card overflow-hidden">
    <div class="divide-y divide-slate-100 dark:divide-slate-800">
        @forelse($comments as $c)
            <div class="p-4 sm:p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <input type="checkbox" name="ids[]" value="{{ $c->id }}" form="bulk-delete-form" class="bulk-comment-check w-4 h-4 shrink-0 text-[var(--brand-strong)] border-slate-300 dark:border-slate-600" aria-label="Select comment">
                            <span class="text-sm font-semibold">{{ $c->name }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400 break-all">{{ $c->email }}</span>
                            <span class="badge {{ $c->status=='pending' ? 'badge-amber' : ($c->status=='approved' ? 'badge-green' : 'badge-slate') }}">{{ $c->status }}</span>
                        </div>
                        <div class="text-sm text-slate-600 dark:text-slate-300 mt-1 break-words">{{ $c->content }}</div>
                        <div class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                            {{ $c->created_at->diffForHumans() }} · on
                            {{-- Null-safe + status-aware post link: the old link 404'd when
                                 the post was a draft or deleted. --}}
                            @if($c->post)
                                @if($c->post->status === 'published' && !$c->post->trashed())
                                    <a href="{{ route('blog.show', $c->post->slug) }}" target="_blank" class="text-[var(--brand-ink)] dark:text-[var(--brand-light)] hover:underline">{{ Str::limit($c->post->title, 50) }}</a>
                                @else
                                    <span class="font-medium">{{ Str::limit($c->post->title, 50) }}</span>
                                    <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400" title="The post is not publicly visible">{{ $c->post->trashed() ? 'deleted' : $c->post->status }}</span>
                                @endif
                            @else
                                <span class="italic text-slate-400 dark:text-slate-500">post removed</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        @foreach(['approved','rejected','spam','pending'] as $s)
                            @if($c->status != $s)
                                <form method="POST" action="{{ route('admin.comments.status', $c) }}">@csrf @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $s }}">
                                    <button class="h-7 px-2.5 text-xs border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition" title="Mark {{ $s }}">{{ ucfirst($s) }}</button>
                                </form>
                            @endif
                        @endforeach
                        <form method="POST" action="{{ route('admin.comments.destroy', $c) }}" onsubmit="return confirm('Delete this comment and its replies?')">@csrf @method('DELETE')
                            <button class="w-7 h-7 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 flex items-center justify-center" title="Delete">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
                            </button>
                        </form>
                    </div>
                </div>

                @if($c->replies->count())
                    <div class="mt-3 ml-4 pl-4 border-l-2 border-slate-200 dark:border-slate-700 space-y-3">
                        @foreach($c->replies->sortBy('created_at') as $reply)
                            <div class="bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 p-3">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m15 10 5 5-5 5"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v7a4 4 0 0 0 4 4h12"/></svg>
                                            <span class="text-sm font-semibold">{{ $reply->name }}</span>
                                            <span class="badge {{ $reply->status=='pending' ? 'badge-amber' : ($reply->status=='approved' ? 'badge-green' : 'badge-slate') }}">{{ $reply->status }}</span>
                                        </div>
                                        <div class="text-sm text-slate-600 dark:text-slate-300 mt-1 break-words">{{ $reply->content }}</div>
                                        <div class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $reply->created_at->diffForHumans() }}</div>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        @foreach(['approved','rejected','pending'] as $s)
                                            @if($reply->status != $s)
                                                <form method="POST" action="{{ route('admin.comments.status', $reply) }}">@csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="{{ $s }}">
                                                    <button class="h-6 px-2 text-[11px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-700 transition" title="Mark {{ $s }}">{{ ucfirst($s) }}</button>
                                                </form>
                                            @endif
                                        @endforeach
                                        <form method="POST" action="{{ route('admin.comments.destroy', $reply) }}" onsubmit="return confirm('Delete this reply?')">@csrf @method('DELETE')
                                            <button class="w-6 h-6 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 flex items-center justify-center" title="Delete">
                                                <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <p class="p-10 text-center text-sm text-slate-500 dark:text-slate-400">No comments found.</p>
        @endforelse
    </div>
    <div class="p-4 border-t border-[#eef0f4] dark:border-[#22262e]">{{ $comments->links() }}</div>
</div>

{{-- Bulk-selection JS: keeps the "Delete selected (N)" counter in sync --}}
@push('scripts')
<script>
(function () {
    const form = document.getElementById('bulk-delete-form');
    const btn = document.getElementById('bulk-delete-btn');
    const counter = document.getElementById('bulk-count');
    const selectAll = document.getElementById('select-all-comments');
    if (!form || !btn || !counter) return;

    function refresh() {
        const checked = document.querySelectorAll('.bulk-comment-check:checked').length;
        counter.textContent = checked;
        btn.disabled = checked === 0;
        if (selectAll) {
            const all = document.querySelectorAll('.bulk-comment-check');
            selectAll.checked = all.length > 0 && checked === all.length;
        }
    }

    document.querySelectorAll('.bulk-comment-check').forEach((cb) => {
        cb.addEventListener('change', refresh);
    });

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            document.querySelectorAll('.bulk-comment-check').forEach((cb) => {
                cb.checked = selectAll.checked;
            });
            refresh();
        });
    }

    form.addEventListener('submit', (e) => {
        if (document.querySelectorAll('.bulk-comment-check:checked').length === 0) {
            e.preventDefault();
        }
    });
})();
</script>
@endpush
@endsection
