@php
$reviewBadge = match($post->review_status) {
    'draft'          => '<span class="inline-flex items-center px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">Draft</span>',
    'pending_review' => '<span class="inline-flex items-center px-2 py-0.5 text-[11px] font-semibold bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">In review</span>',
    'returned'       => '<span class="inline-flex items-center px-2 py-0.5 text-[11px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">Returned</span>',
    'approved'       => '<span class="inline-flex items-center px-2 py-0.5 text-[11px] font-semibold bg-[#E3F0E9] text-[#1F513A] dark:bg-[#2E7856]/20 dark:text-[#6FB393]">Published</span>',
    default          => '',
};
@endphp
<div class="flex items-center gap-4 p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
    <div class="w-14 h-14 bg-slate-100 dark:bg-slate-800 shrink-0 overflow-hidden">
        @if($post->featured_image)
            <img src="{{ str_starts_with($post->featured_image, 'http') ? $post->featured_image : '/storage/'.$post->featured_image }}" class="w-full h-full object-cover" alt="" loading="lazy" onerror="this.onerror=null;this.style.visibility='hidden'">
        @endif
    </div>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap">
            {!! $reviewBadge !!}
            @if($post->is_affiliate)<span class="inline-flex items-center px-2 py-0.5 text-[11px] font-semibold bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300">Affiliate</span>@endif
        </div>
        <a href="{{ route('author.posts.edit', $post->id) }}" class="block font-semibold text-slate-900 dark:text-white hover:text-[#173A2A] dark:hover:text-[#6FB393] text-sm mt-1 truncate">{{ $post->title }}</a>
        <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
            {{ $post->category?->name ?? 'Uncategorized' }} · {{ $post->reading_time }} min read · {{ $post->views }} views
        </div>
        @if($post->review_status === 'returned' && $post->reviewer_note)
            <div class="mt-1.5 text-xs text-amber-700 dark:text-amber-300 italic">
                Admin note: "{{ $post->reviewer_note }}"
            </div>
        @endif
    </div>
    <div class="flex items-center gap-1">
        @if($post->review_status === 'draft' || $post->review_status === 'returned')
            <a href="{{ route('author.posts.edit', $post->id) }}" class="px-3 h-9 inline-flex items-center text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">Edit</a>
            <form method="POST" action="{{ route('author.posts.submit', $post->id) }}">@csrf
                <button type="submit" class="px-3 h-9 inline-flex items-center text-xs font-semibold rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white">Submit</button>
            </form>
            <form method="POST" action="{{ route('author.posts.destroy', $post->id) }}" onsubmit="return confirm('Delete this draft permanently?')">@csrf @method('DELETE')
                <button type="submit" class="px-3 h-9 inline-flex items-center text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10">Delete</button>
            </form>
        @elseif($post->review_status === 'pending_review')
            <span class="text-xs text-slate-500 px-3">Awaiting review</span>
        @elseif($post->review_status === 'approved')
            @php
                $seoColor2 = ($post->seo_score ?? 0) >= 70 ? 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400' : (($post->seo_score ?? 0) >= 40 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' : 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400');
            @endphp
            @if(!is_null($post->seo_score))<span class="inline-flex items-center text-[10px] font-bold px-1.5 py-0.5 rounded {{ $seoColor2 }}" title="On-page SEO score">SEO {{ $post->seo_score }}</span>@endif
            <a href="{{ route('author.posts.share', $post->id) }}" class="px-3 h-9 inline-flex items-center text-xs font-semibold text-[#173A2A] dark:text-[#6FB393] hover:underline gap-1">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="m8.59 13.51 6.83 3.98m-.01-10.98-6.82 3.98"/></svg>
                Share
            </a>
            <form method="POST" action="{{ route('author.posts.instant-index', $post->id) }}">@csrf
                <button type="submit" title="Ping search engines (IndexNow){{ $post->instant_indexed_at ? ' — last pinged '.$post->instant_indexed_at->format('M d, H:i') : '' }}" class="px-3 h-9 inline-flex items-center text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 gap-1">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0-6.75 6.75M12 4.5l6.75 6.75"/></svg>
                    Index now
                </button>
            </form>
            <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="px-3 h-9 inline-flex items-center text-xs font-semibold text-[#173A2A] dark:text-[#6FB393] hover:underline">View live</a>
        @endif
    </div>
</div>
