@extends('layouts.admin')
@section('title','Posts')
@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Posts'],
    ]])
@endsection

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex flex-wrap items-center gap-1.5">
        @php
            $tabs = [
                'all' => 'All', 'draft' => 'Draft', 'published' => 'Published',
                'scheduled' => 'Scheduled', 'trash' => 'Trash',
            ];
        @endphp
        @foreach($tabs as $key => $label)
            <a href="{{ route('admin.posts.index', array_filter(['tab'=>$key !== 'all' ? $key : null, 'search'=>request('search'),'category'=>request('category')])) }}"
               class="h-9 px-4 inline-flex items-center gap-2 text-sm font-medium border transition {{ ($tab === $key) ? 'bg-[#0C3B2E] text-white border-[#0C3B2E]' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                @if($key === 'trash')
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
                @elseif($key === 'scheduled')
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/></svg>
                @endif
                {{ $label }} <span class="text-xs opacity-70">{{ $counts[$key] }}</span>
            </a>
        @endforeach
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="relative">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search posts" class="h-9 pl-9 pr-3 w-[190px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-sm placeholder:text-slate-400">
            </div>
            <select name="category" onchange="this.form.submit()" class="h-9 px-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-sm">
                <option value="">All categories</option>
                @foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(request('category')==$cat->id)>{{ $cat->name }}</option>@endforeach
            </select>
            <button type="submit" class="h-9 px-4 bg-[#0C3B2E] hover:bg-[#072A20] text-white text-sm font-semibold">Filter</button>
        </form>
        <a href="{{ route('admin.posts.create') }}" class="h-9 px-4 bg-[#0C3B2E] hover:bg-[#072A20] text-white text-sm font-semibold inline-flex items-center gap-1.5">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"/></svg> New Post
        </a>
    </div>
</div>

{{-- Bulk-action form. It sits OUTSIDE the table because the rows already contain their own per-post <form> elements (HTML forbids nested forms). Every checkbox and bulk button joins THIS form through the form="posts-bulk-form" attribute — the same battle-tested pattern the admin comments list uses. The clicked submit button's name/value carries the chosen action. --}}
<form method="POST" action="{{ route('admin.posts.bulk') }}" id="posts-bulk-form" class="hidden">@csrf</form>

<div id="posts-bulk-bar" class="mb-3 flex flex-wrap items-center gap-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 px-3 py-2">
    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 11 3 3L22 4"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
    <span class="text-sm text-slate-600 dark:text-slate-300"><strong id="bulk-count" class="text-[#0C3B2E] dark:text-emerald-400">0</strong> selected</span>
    @if($tab === 'trash')
        <button type="submit" name="bulk_action" value="restore" form="posts-bulk-form" id="bulk-restore-btn" disabled
                class="h-8 px-3 text-xs font-semibold inline-flex items-center gap-1.5 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition disabled:opacity-40 disabled:cursor-not-allowed">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v5h5"/></svg>
            Restore selected
        </button>
        <button type="submit" name="bulk_action" value="delete" form="posts-bulk-form" id="bulk-delete-btn" disabled
                onclick="return confirm('Permanently delete ALL selected posts? This cannot be undone.')"
                class="h-8 px-3 text-xs font-semibold inline-flex items-center gap-1.5 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 transition disabled:opacity-40 disabled:cursor-not-allowed">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
            Delete forever
        </button>
    @else
        <button type="submit" name="bulk_action" value="trash" form="posts-bulk-form" id="bulk-trash-btn" disabled
                onclick="return confirm('Move ALL selected posts to trash?')"
                class="h-8 px-3 text-xs font-semibold inline-flex items-center gap-1.5 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 hover:bg-red-100 dark:hover:bg-red-500/20 transition disabled:opacity-40 disabled:cursor-not-allowed">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            Trash selected
        </button>
    @endif
</div>

<div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/60 text-[11px] font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" id="select-all-posts" class="w-4 h-4 shrink-0 text-emerald-600 border-slate-300 dark:border-slate-600" aria-label="Select all posts on this page">
                    </th>
                    <th class="text-left px-4 py-3">Post</th>
                    <th class="text-left px-4 py-3">Category</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Views</th>
                    <th class="text-left px-4 py-3">Date</th>
                    <th class="text-right px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($posts as $post)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                        <td class="px-4 py-3">
                            <input type="checkbox" name="ids[]" value="{{ $post->id }}" form="posts-bulk-form" class="bulk-post-check w-4 h-4 shrink-0 text-emerald-600 border-slate-300 dark:border-slate-600" aria-label="Select post: {{ $post->title }}">
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($post->featured_image)
                                    <img src="{{ str_starts_with($post->featured_image,'http') ? $post->featured_image : '/storage/'.$post->featured_image }}" class="w-10 h-10 object-cover bg-slate-100 dark:bg-slate-800" alt="" loading="lazy" decoding="async">
                                @else
                                    <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="font-semibold truncate max-w-[260px]">{{ $post->title }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 truncate max-w-[260px]">{{ $post->slug }} · {{ $post->reading_time }} min</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3"><span class="text-xs px-2 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $post->category->name ?? '-' }}</span></td>
                        <td class="px-4 py-3">
                            @if($post->trashed())
                                <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">trashed</span>
                            @elseif($post->scheduled_at && $post->scheduled_at->isFuture())
                                <span class="text-xs font-semibold px-2.5 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">scheduled</span>
                            @else
                                <span class="text-xs font-semibold px-2.5 py-1 {{ $post->status=='published' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">{{ $post->status }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ number_format($post->views) }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">
                            @if($post->scheduled_at && $post->scheduled_at->isFuture() && !$post->trashed())
                                {{ $post->scheduled_at->format('M d, Y H:i') }}
                            @else
                                {{ $post->published_at?->format('M d, Y') ?? $post->created_at->format('M d, Y') }}
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                @if($tab === 'trash')
                                    <form method="POST" action="{{ route('admin.posts.restore', $post->id) }}">@csrf
                                        <button type="submit" title="Restore" class="w-8 h-8 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center justify-center">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v5h5"/></svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.posts.destroy.permanent', $post->id) }}" onsubmit="return confirm('Permanently delete this post? This cannot be undone.')">@csrf
                                        <button type="submit" title="Delete permanently" class="w-8 h-8 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 flex items-center justify-center">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('blog.show', $post->slug) }}" target="_blank" title="View" class="w-8 h-8 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center justify-center">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.posts.edit', $post) }}" title="Edit" class="w-8 h-8 bg-[#0C3B2E] hover:bg-[#072A20] text-white flex items-center justify-center">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m18 5 2.47 2.47a1 1 0 0 1 0 1.41L18 11.34 12.66 6l2.42-2.42a1 1 0 0 1 1.41 0ZM11.95 6.7 4.7 13.96a1 1 0 0 0-.29.7V18a1 1 0 0 0 1 1h3.32a1 1 0 0 0 .7-.29l7.26-7.25Z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.posts.toggle', $post) }}">@csrf
                                        <button type="submit" title="Toggle publish status" class="w-8 h-8 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 flex items-center justify-center">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0-4-4m4 4-4 4m0 6H4m0 0 4 4m-4-4 4-4"/></svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" onsubmit="return confirm('Move this post to trash?')">@csrf @method('DELETE')
                                        <button type="submit" title="Move to trash" class="w-8 h-8 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 flex items-center justify-center">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">{{ $tab === 'trash' ? 'Trash is empty.' : 'No posts found.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-100 dark:border-slate-800">{{ $posts->links() }}</div>
</div>

{{-- Bulk-selection JS: live counter + select-all parity (same pattern as the admin comments list) --}}
@push('scripts')
<script>
(function () {
    const form = document.getElementById('posts-bulk-form');
    const counter = document.getElementById('bulk-count');
    const selectAll = document.getElementById('select-all-posts');
    const actionBtns = ['bulk-trash-btn', 'bulk-restore-btn', 'bulk-delete-btn']
        .map((id) => document.getElementById(id)).filter(Boolean);
    if (!form || !counter) return;

    function checked() { return document.querySelectorAll('.bulk-post-check:checked'); }

    function refresh() {
        const n = checked().length;
        counter.textContent = n;
        actionBtns.forEach((b) => { b.disabled = n === 0; });
        if (selectAll) {
            const all = document.querySelectorAll('.bulk-post-check');
            selectAll.checked = all.length > 0 && n === all.length;
        }
    }

    document.querySelectorAll('.bulk-post-check').forEach((cb) => {
        cb.addEventListener('change', refresh);
    });

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            document.querySelectorAll('.bulk-post-check').forEach((cb) => {
                cb.checked = selectAll.checked;
            });
            refresh();
        });
    }

    // Last line of defence: never submit with zero selection.
    form.addEventListener('submit', (e) => {
        if (checked().length === 0) e.preventDefault();
    });
})();
</script>
@endpush
@endsection
