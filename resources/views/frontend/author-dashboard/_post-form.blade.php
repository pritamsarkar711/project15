@php
    // $post may be null on create, set on edit. We branch on $isEdit.
    $isEdit = isset($post) && $post?->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('author.posts.update', $post->id) : route('author.posts.store') }}" enctype="multipart/form-data" class="space-y-5">
    @csrf
    @if($isEdit) @method('POST') @endif

    {{-- Title --}}
    <div>
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Title</label>
        <input type="text" name="title" required value="{{ old('title', $isEdit ? $post->title : '') }}" maxlength="255"
            class="w-full h-11 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-[#0C3B2E] focus:ring-4 focus:ring-[#0C3B2E]/15 outline-none text-sm text-slate-900 dark:text-white">
    </div>

    {{-- Excerpt --}}
    <div>
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Excerpt <span class="text-slate-400 font-normal">(optional)</span></label>
        <textarea name="excerpt" rows="2" maxlength="500" class="w-full p-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-[#0C3B2E] focus:ring-4 focus:ring-[#0C3B2E]/15 outline-none text-sm text-slate-900 dark:text-white">{{ old('excerpt', $isEdit ? $post->excerpt : '') }}</textarea>
    </div>

    {{-- Content --}}
    <div>
        <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Content</label>
        <textarea name="content" id="post-content" rows="18" required minlength="120" class="w-full p-3 font-mono text-[13px] leading-relaxed bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-[#0C3B2E] focus:ring-4 focus:ring-[#0C3B2E]/15 outline-none text-slate-900 dark:text-white">{{ old('content', $isEdit ? $post->content : '') }}</textarea>
        <p class="text-xs text-slate-500 mt-1">Use HTML directly. Min 120 chars to save, min 300 words to submit for review.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Category --}}
        <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Category</label>
            <select name="category_id" class="w-full h-11 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-[#0C3B2E] focus:ring-4 focus:ring-[#0C3B2E]/15 outline-none text-sm text-slate-900 dark:text-white">
                <option value="">— None —</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('category_id', $isEdit ? $post->category_id : '') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Featured image --}}
        <div>
            <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Featured image <span class="text-slate-400 font-normal">(optional, auto-optimised)</span></label>
            <input type="file" name="featured_image" accept="image/*" class="w-full text-sm file:mr-3 file:py-2 file:px-4 file:border-0 file:bg-[#0C3B2E] file:text-white file:font-semibold file:text-xs file:cursor-pointer">
            @if($isEdit && $post->featured_image)
                <div class="mt-2 flex items-center gap-2">
                    <img src="{{ str_starts_with($post->featured_image, 'http') ? $post->featured_image : '/storage/'.$post->featured_image }}" class="w-14 h-14 object-cover" alt="" loading="lazy">
                    <span class="text-xs text-slate-500">Current — upload a new file to replace.</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Affiliate disclosure toggle --}}
    <div class="bg-purple-50 dark:bg-purple-500/10 border border-purple-200 dark:border-purple-500/30 p-4">
        <label class="flex items-start gap-3 cursor-pointer">
            <input type="checkbox" name="is_affiliate" value="1" @checked(old('is_affiliate', $isEdit && $post->is_affiliate)) class="mt-0.5 w-5 h-5 border-slate-300 dark:border-slate-700 text-[#0C3B2E]">
            <div>
                <span class="block text-sm font-semibold text-purple-800 dark:text-purple-300">This post contains affiliate links</span>
                <span class="block text-xs text-purple-700 dark:text-purple-400 mt-0.5">If on, a clear disclosure notice board will appear at the top of your published post. Required by FTC and most EU regulators.</span>
            </div>
        </label>
    </div>

    {{-- FAQ accordion (optional) --}}
    <details class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 p-4">
        <summary class="cursor-pointer text-sm font-semibold text-slate-900 dark:text-white">FAQ (optional)</summary>
        <div id="faq-container" class="mt-3 space-y-3">
            @php
                $existingFaqs = $isEdit ? $post->faqs->all() : [];
                $oldFaqs = old('faqs', []);
                $faqList = count($oldFaqs) ? $oldFaqs : (count($existingFaqs) ? $existingFaqs : [['question' => '', 'answer' => '']]);
            @endphp
            @foreach($faqList as $idx => $faq)
                <div class="grid grid-cols-1 gap-2 border-l-2 border-slate-200 dark:border-slate-700 pl-3">
                    <input type="text" name="faqs[{{ $idx }}][question]" value="{{ $faq['question'] ?? '' }}" placeholder="Question" class="h-10 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-sm text-slate-900 dark:text-white">
                    <textarea name="faqs[{{ $idx }}][answer]" rows="2" placeholder="Answer" class="p-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-sm text-slate-900 dark:text-white">{{ $faq['answer'] ?? '' }}</textarea>
                </div>
            @endforeach
        </div>
        <button type="button" id="add-faq" class="mt-2 text-xs font-semibold text-[#0C3B2E] dark:text-emerald-300 hover:underline">+ Add another FAQ</button>
    </details>

    {{-- SEO --}}
    <details class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 p-4">
        <summary class="cursor-pointer text-sm font-semibold text-slate-900 dark:text-white">SEO (optional)</summary>
        <div class="mt-3 space-y-3">
            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Meta title</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $isEdit ? $post->meta_title : '') }}" maxlength="255" class="w-full h-10 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-sm text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Meta description</label>
                <textarea name="meta_description" rows="2" maxlength="500" class="w-full p-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-sm text-slate-900 dark:text-white">{{ old('meta_description', $isEdit ? $post->meta_description : '') }}</textarea>
            </div>
        </div>
    </details>

    {{-- Actions --}}
    @if($isEdit && ($post->review_status === 'pending_review' || $post->review_status === 'approved'))
        <div class="bg-slate-100 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 p-4 text-sm text-slate-600 dark:text-slate-400">
            This post is currently <strong>{{ $post->review_status === 'approved' ? 'published' : 'in review' }}</strong> — it's locked for editing. Wait for the admin decision before making changes.
        </div>
    @else
        @php
            // Pull live submit-availability state so the submit button can be
            // disabled client-side when the daily limit is reached.
            $canSubmit = \App\Models\Post::authorSubmittedRecently(auth()->id()) === false;
            $nextAt = \App\Models\Post::where('user_id', auth()->id())
                ->whereNotNull('submitted_at')
                ->where('submitted_at', '>=', now()->subDay())
                ->orderBy('submitted_at')
                ->value('submitted_at')?->addDay();
        @endphp
        @if(! $canSubmit)
            <div class="bg-amber-50 dark:bg-amber-400/10 border border-amber-200 dark:border-amber-500/30 p-4 text-sm text-amber-800 dark:text-amber-300">
                <strong>Daily limit reached.</strong> You can submit your next post for review{{ $nextAt ? ' in ' . $nextAt->diffForHumans() : ' in 24 hours' }}.
            </div>
        @endif
        <div class="flex flex-wrap items-center gap-3 pt-2">
            <button type="submit" name="action" value="save_draft" class="h-11 px-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-sm">
                Save as draft
            </button>
            <button type="submit" name="action" value="submit" @disabled(! $canSubmit) class="h-11 px-5 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                {{ $isEdit && $post->review_status === 'returned' ? 'Re-submit for review' : 'Submit for review' }}
            </button>
            <a href="{{ route('author.rules') }}" class="ml-auto text-xs text-slate-500 hover:text-[#0C3B2E] dark:hover:text-emerald-300">Read the posting rules first →</a>
        </div>
    @endif
</form>

@push('head')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var addFaqBtn = document.getElementById('add-faq');
    if (addFaqBtn) {
        addFaqBtn.addEventListener('click', function() {
            var container = document.getElementById('faq-container');
            var idx = container.children.length;
            var wrapper = document.createElement('div');
            wrapper.className = 'grid grid-cols-1 gap-2 border-l-2 border-slate-200 dark:border-slate-700 pl-3';
            wrapper.innerHTML = '<input type="text" name="faqs['+idx+'][question]" placeholder="Question" class="h-10 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-sm text-slate-900 dark:text-white">' +
                '<textarea name="faqs['+idx+'][answer]" rows="2" placeholder="Answer" class="p-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-sm text-slate-900 dark:text-white"></textarea>';
            container.appendChild(wrapper);
        });
    }
});
</script>
@endpush
