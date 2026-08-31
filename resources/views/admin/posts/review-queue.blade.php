@extends('layouts.admin')

@section('title', 'Review queue')

@section('content')
<div class="mb-5 flex flex-wrap items-center gap-1 panel-card p-1.5 w-fit">
    <a href="{{ route('admin.posts.review-queue') }}" class="px-3 py-1.5 text-xs font-semibold {{ $tab === 'pending' ? 'bg-[#16181d] text-white dark:bg-white dark:text-[#101319]' : 'text-slate-600 dark:text-slate-300 hover:bg-[#f1f3f7] dark:hover:bg-[#1c1f26]' }}">Pending ({{ $counts['pending'] }})</a>
    <a href="{{ route('admin.posts.review-queue', ['tab' => 'returned']) }}" class="px-3 py-1.5 text-xs font-semibold {{ $tab === 'returned' ? 'bg-[#16181d] text-white dark:bg-white dark:text-[#101319]' : 'text-slate-600 dark:text-slate-300 hover:bg-[#f1f3f7] dark:hover:bg-[#1c1f26]' }}">Returned ({{ $counts['returned'] }})</a>
    <a href="{{ route('admin.posts.review-queue', ['tab' => 'approved']) }}" class="px-3 py-1.5 text-xs font-semibold {{ $tab === 'approved' ? 'bg-[#16181d] text-white dark:bg-white dark:text-[#101319]' : 'text-slate-600 dark:text-slate-300 hover:bg-[#f1f3f7] dark:hover:bg-[#1c1f26]' }}">Approved ({{ $counts['approved'] }})</a>
</div>

@if($posts->isEmpty())
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-8 text-center">
        <p class="text-slate-500 text-sm">No posts in this queue.</p>
    </div>
@else
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 divide-y divide-slate-100 dark:divide-slate-800">
        @foreach($posts as $post)
            <div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 bg-slate-100 dark:bg-slate-800 shrink-0 overflow-hidden">
                        @if($post->featured_image)
                            <img src="{{ str_starts_with($post->featured_image, 'http') ? $post->featured_image : '/storage/'.$post->featured_image }}" class="w-full h-full object-cover" alt="" loading="lazy">
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-semibold text-slate-500">{{ $post->user?->username ?? 'admin' }}</span>
                            @if($post->user && ($post->user->is_verified ?? false))
                                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3 3 0 001.968-.523 3 3 0 003.527 0 3 3 0 001.968.523 3 3 0 003.002 3.002c0 .665.21 1.286.523 1.968a3 3 0 000 3.527 3 3 0 00-.523 1.968 3 3 0 00-3.002 3.002 3 3 0 00-1.968-.523 3 3 0 00-3.527 0 3 3 0 00-1.968.523 3 3 0 00-3.002-3.002 3 3 0 00.523-1.968 3 3 0 000-3.527 3 3 0 00-.523-1.968A3 3 0 006.267 3.455zM9.5 13.5l3 3 5-6" clip-rule="evenodd"/></svg>
                            @endif
                            @if($post->is_affiliate)<span class="inline-flex items-center px-2 py-0.5 text-[11px] font-semibold bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300">Affiliate</span>@endif
                            <span class="text-xs text-slate-500">{{ $post->submitted_at?->format('M d, H:i') }}</span>
                            <span class="text-xs text-slate-500">· {{ $post->reading_time }} min · {{ str_word_count(strip_tags($post->content)) }} words</span>
                        </div>
                        <h3 class="font-semibold text-slate-900 dark:text-white text-sm mt-1">{{ $post->title }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">{{ $post->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($post->content), 160) }}</p>
                        @if($post->review_status === 'returned')
                            <div class="mt-2 text-xs text-amber-700 dark:text-amber-300 italic">Returned with note: "{{ $post->reviewer_note }}"</div>
                        @endif
                    </div>
                    <div class="flex flex-col gap-2 shrink-0">
                        @if($post->review_status === 'pending_review' || $post->review_status === 'returned')
                            <a href="{{ route('admin.posts.edit', $post) }}" class="px-3 h-9 inline-flex items-center text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">Open editor</a>
                            <button type="button" data-open-review="{{ $post->id }}" class="px-3 h-9 inline-flex items-center text-xs font-semibold rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white">Review & approve</button>
                            <button type="button" data-open-return="{{ $post->id }}" class="px-3 h-9 inline-flex items-center text-xs font-semibold text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10">Return with note</button>
                        @else
                            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="px-3 h-9 inline-flex items-center text-xs font-semibold text-[#173A2A] dark:text-[#6FB393] hover:underline">View live</a>
                        @endif
                    </div>
                </div>

                {{-- Inline review/return panel (hidden by default) --}}
                @if($post->review_status === 'pending_review' || $post->review_status === 'returned')
                <div data-review-panel="{{ $post->id }}" class="hidden mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <form method="POST" action="{{ route('admin.posts.approve', $post) }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Title</label>
                                <input type="text" name="title" required value="{{ $post->title }}" class="w-full h-10 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-sm text-slate-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Category</label>
                                <select name="category_id" class="w-full h-10 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-sm text-slate-900 dark:text-white">
                                    <option value="">None</option>
                                    @foreach(\App\Models\Category::orderBy('sort_order')->get() as $cat)
                                        <option value="{{ $cat->id }}" @selected($post->category_id == $cat->id)>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Excerpt</label>
                            <textarea name="excerpt" rows="2" class="w-full p-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-sm text-slate-900 dark:text-white">{{ $post->excerpt }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Content (edit before publishing)</label>
                            <textarea name="content" rows="14" class="w-full p-2 font-mono text-[12px] leading-relaxed bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-slate-900 dark:text-white">{{ $post->content }}</textarea>
                        </div>
                        <div class="flex flex-wrap gap-4 text-sm">
                            <label class="flex items-center gap-2"><input type="checkbox" name="is_featured" value="1" @checked($post->is_featured)> Featured</label>
                            <label class="flex items-center gap-2"><input type="checkbox" name="is_affiliate" value="1" @checked($post->is_affiliate)> Affiliate</label>
                            <label class="flex items-center gap-2"><input type="checkbox" name="allow_comments" value="1" @checked($post->allow_comments)> Allow comments</label>
                        </div>
                        <button type="submit" class="h-10 px-4 bg-[#27654A] hover:bg-[#1F513A] text-white text-xs font-semibold">Approve & publish</button>
                    </form>

                    <form method="POST" action="{{ route('admin.posts.return', $post) }}" class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
                        @csrf
                        <label class="block text-xs font-medium text-amber-700 dark:text-amber-400">Note to author (required). Be specific about what to change.</label>
                        <textarea name="reviewer_note" rows="3" required maxlength="500" class="w-full p-2 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 outline-none text-sm text-amber-900 dark:text-amber-100" placeholder="e.g. Intro is too generic, add a concrete example. The third section repeats points from the first.">{{ $post->reviewer_note ?? '' }}</textarea>
                        <button type="submit" class="h-10 px-4 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold">Return to author</button>
                    </form>
                </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-4">{{ $posts->links() }}</div>
@endif

@push('scripts')
<script>
document.querySelectorAll('[data-open-review]').forEach(function(btn){
    btn.addEventListener('click', function(){
        var id = this.dataset.openReview;
        var panel = document.querySelector('[data-review-panel="' + id + '"]');
        if (panel) {
            panel.classList.toggle('hidden');
            if (!panel.classList.contains('hidden')) {
                panel.scrollIntoView({behavior: 'smooth', block: 'nearest'});
            }
        }
    });
});
document.querySelectorAll('[data-open-return]').forEach(function(btn){
    btn.addEventListener('click', function(){
        var id = this.dataset.openReturn;
        var panel = document.querySelector('[data-review-panel="' + id + '"]');
        if (panel) {
            panel.classList.toggle('hidden');
            if (!panel.classList.contains('hidden')) {
                var textarea = panel.querySelector('textarea[name="reviewer_note"]');
                if (textarea) setTimeout(() => textarea.focus(), 100);
                panel.scrollIntoView({behavior: 'smooth', block: 'nearest'});
            }
        }
    });
});
</script>
@endpush
@endsection
