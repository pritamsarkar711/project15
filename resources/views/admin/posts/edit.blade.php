@extends('layouts.admin')
@section('title','Edit Post')
@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Posts', 'route' => 'admin.posts.index'],
        ['label' => 'Edit Post'],
    ]])
@endsection

@section('content')
<form method="POST" action="{{ route('admin.posts.update', $post) }}" enctype="multipart/form-data" class="space-y-6" data-autosave="admin">
    @csrf @method('PUT')
    {{-- Server-side autosave: enabled only while this post is still a DRAFT
         (the endpoint refuses anything that is not a draft, so published
         posts can never be auto-mutated). --}}
    @if($post->status === 'draft' && $post->review_status === 'draft')
        <input type="hidden" name="autosave_post_id" id="autosave-post-id" value="{{ $post->id }}">
    @else
        <input type="hidden" name="autosave_post_id" id="autosave-post-id" value="">
    @endif
    <div class="grid lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 space-y-5">
            <div class="panel-card p-6">
                <label class="text-sm font-semibold">Title *</label>
                <input type="text" name="title" required value="{{ old('title', $post->title) }}" class="mt-1 w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-sm">
                <div class="grid sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="text-sm font-medium">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $post->slug) }}" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium">Category</label>
                        <select name="category_id" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                            <option value="">Select category</option>
                            @foreach($categories as $cat)<option value="{{ $cat->id }}" @selected(old('category_id', $post->category_id)==$cat->id)>{{ $cat->name }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="text-sm font-medium">Excerpt</label>
                    <textarea name="excerpt" rows="2" maxlength="500" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">{{ old('excerpt', $post->excerpt) }}</textarea>
                </div>
                <div class="mt-4">
                    <label class="text-sm font-semibold">Content *</label>
                    <textarea id="editor" name="content" rows="12" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm min-h-[340px]">{{ old('content', $post->content) }}</textarea>
                </div>
            </div>

            <div class="panel-card p-6">
                <h3 class="font-semibold mb-4">FAQ</h3>
                <div id="faqs" class="space-y-3">
                    @forelse(old('faqs', $post->faqs->map(fn($f)=>['question'=>$f->question,'answer'=>$f->answer])->toArray()) as $idx => $faq)
                        <div class="faq-item rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="faq-num inline-flex items-center gap-2 text-xs font-bold text-[#1F513A] dark:text-[#6FB393]">
                                    <span class="w-5 h-5 rounded-md bg-[#E9F2EE] dark:bg-[#233b30] flex items-center justify-center">{{ $idx + 1 }}</span>
                                    Question
                                </span>
                                <button type="button" onclick="removeFaq(this)" title="Remove" aria-label="Remove question" class="w-7 h-7 rounded-md text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 flex items-center justify-center transition shrink-0">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                </button>
                            </div>
                            <input type="text" name="faqs[{{ $idx }}][question]" value="{{ $faq['question'] ?? '' }}" placeholder="Write the question readers ask" class="mt-3 h-10 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm">
                            <textarea name="faqs[{{ $idx }}][answer]" placeholder="And the answer, in your own words" rows="2" class="mt-2 px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm">{{ $faq['answer'] ?? '' }}</textarea>
                        </div>
                    @empty
                        <div class="faq-item rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="faq-num inline-flex items-center gap-2 text-xs font-bold text-[#1F513A] dark:text-[#6FB393]">
                                    <span class="w-5 h-5 rounded-md bg-[#E9F2EE] dark:bg-[#233b30] flex items-center justify-center">1</span>
                                    Question
                                </span>
                            </div>
                            <input type="text" name="faqs[0][question]" placeholder="Write the question readers ask" class="mt-3 h-10 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm">
                            <textarea name="faqs[0][answer]" placeholder="And the answer, in your own words" rows="2" class="mt-2 px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm"></textarea>
                        </div>
                    @endforelse
                </div>
                <button type="button" onclick="addFaq()" class="mt-3 w-full h-10 rounded-lg border border-dashed border-slate-300 dark:border-slate-600 text-sm font-semibold text-slate-500 dark:text-slate-400 hover:border-[#2E7856] hover:text-[#1F513A] dark:hover:text-[#6FB393] inline-flex items-center justify-center gap-1.5 transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Add question
                </button>
            </div>

            <div class="panel-card p-6">
                <h3 class="font-semibold mb-3">SEO</h3>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium">Meta Title</label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium">Tags <span class="text-slate-400 font-normal">(comma separated)</span></label>
                        <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $post->meta_keywords) }}" placeholder="budget travel, packing list" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    </div>
                </div>
                <label class="text-sm font-medium mt-4 block">Meta Description</label>
                <textarea name="meta_description" rows="2" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">{{ old('meta_description', $post->meta_description) }}</textarea>
            </div>

            {{-- Focus keyword + live SEO score + AI assistant --}}
            @include('partials.seo-ai-panel', ['post' => $post, 'aiEndpoint' => route('author.ai.generate'), 'aiEnabled' => app(\App\Services\Ai\AiAssistantService::class)->enabled()])
        </div>

        <div class="lg:col-span-4 space-y-5">
            <div class="panel-card p-6">
                <h3 class="font-semibold mb-3">Publish</h3>
                <select name="status" class="w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    <option value="draft" @selected(old('status', $post->status)=='draft')>Draft</option>
                    <option value="published" @selected(old('status', $post->status)=='published')>Published</option>
                    <option value="archived" @selected(old('status', $post->status)=='archived')>Archived</option>
                </select>
                <label class="text-sm font-medium mt-3 block">Schedule (optional)</label>
                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $post->scheduled_at?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ $post->reading_time }} min read · {{ number_format($post->views) }} views</div>
                <label class="flex items-center gap-2 mt-3 text-sm"><input type="checkbox" name="is_featured" value="1" @checked($post->is_featured) class="border-slate-300 dark:border-slate-600 text-[#27654A]"> Featured post</label>
                <label class="flex items-center gap-2 mt-2 text-sm"><input type="checkbox" name="allow_comments" value="1" @checked($post->allow_comments) class="border-slate-300 dark:border-slate-600 text-[#27654A]"> Allow comments</label>
                <button type="submit" class="w-full mt-4 h-11 rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white font-semibold transition">Update Post</button>
                <p id="autosave-status" class="mt-3 text-[11px] font-medium text-slate-400 dark:text-slate-500" aria-live="polite"></p>
                <a href="{{ route('admin.posts.index') }}" class="block text-center mt-2 text-sm text-slate-500 dark:text-slate-400 hover:underline">Cancel</a>
            </div>

            <div class="panel-card p-6">
                <h3 class="font-semibold mb-3">Featured Image</h3>
                @if($post->featured_image)
                    <img id="featured-preview" src="{{ str_starts_with($post->featured_image,'http') ? $post->featured_image : '/storage/'.$post->featured_image }}" class="w-full h-40 object-cover mb-3 border border-slate-200 dark:border-slate-700" alt="" loading="lazy" decoding="async">
                @else
                    <img id="featured-preview" src="#" alt="" class="hidden w-full h-40 object-cover mb-3 border border-slate-200 dark:border-slate-700" loading="lazy" decoding="async">
                @endif
                <label class="block w-full cursor-pointer border border-dashed border-slate-300 dark:border-slate-600 py-3 text-center text-sm text-slate-500 dark:text-slate-400 hover:border-[#2E7856] hover:text-[#1F513A] dark:hover:text-[#6FB393] transition">
                    <svg class="w-5 h-5 mx-auto mb-1 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 3h6m0 0v6m0-6L10 14M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                    Replace image (auto-converted to WebP)
                    <input type="file" name="featured_image" accept="image/*" class="hidden" onchange="previewFeatured(this)">
                </label>
                <label class="text-xs font-medium mt-3 block text-slate-500 dark:text-slate-400">Or image URL</label>
                <input type="text" name="featured_image_url" value="{{ old('featured_image_url') }}" placeholder="https://..." class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
            </div>

        </div>
    </div>
</form>

@push('scripts')
{{-- Cache-busted editor tag: ?v=filemtime forces browsers to fetch the fixed editor --}}
{!! \App\Support\ViteAssets::editorScript() !!}
{!! \App\Support\ViteAssets::publicScript('js/seo-analyzer.js') !!}
{!! \App\Support\ViteAssets::publicScript('js/ai-assistant.js') !!}
<script>
// Self-made Huvanti rich text editor (single small file, no dependencies).
huvantiEditorInit('#editor');
</script>
@include('admin.posts._autosave')
<script>
    let faqIdx = {{ count(old('faqs', $post->faqs)) + 1 }};
    const FAQ_TRASH = '<svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>';
    function addFaq(){
        const c = document.getElementById('faqs');
        const d = document.createElement('div');
        d.className='faq-item rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/50 p-4';
        d.innerHTML=`<div class="flex items-center justify-between gap-3"><span class="faq-num inline-flex items-center gap-2 text-xs font-bold text-[#1F513A] dark:text-[#6FB393]"><span class="w-5 h-5 rounded-md bg-[#E9F2EE] dark:bg-[#233b30] flex items-center justify-center"></span>Question</span><button type="button" title="Remove" aria-label="Remove question" class="w-7 h-7 rounded-md text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 flex items-center justify-center transition shrink-0">${FAQ_TRASH}</button></div><input type="text" name="faqs[${faqIdx}][question]" placeholder="Write the question readers ask" class="mt-3 h-10 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm"><textarea name="faqs[${faqIdx}][answer]" placeholder="And the answer, in your own words" rows="2" class="mt-2 px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm"></textarea>`;
        d.querySelector('button').onclick = function(){ removeFaq(this); };
        c.appendChild(d);
        faqIdx++;
        renumberFaqs();
    }
    function removeFaq(btn){
        btn.closest('.faq-item').remove();
        renumberFaqs();
    }
    // Keep the question badges numbered in DOM order.
    function renumberFaqs(){
        document.querySelectorAll('#faqs .faq-item').forEach(function(item, i){
            var badge = item.querySelector('.faq-num span');
            if (badge) badge.textContent = i + 1;
        });
    }
    document.addEventListener('DOMContentLoaded', renumberFaqs);
    function previewFeatured(input){
        const img = document.getElementById('featured-preview');
        if(!input.files || !input.files[0]) return;
        const file = input.files[0];
        // Client-side guards mirroring the server (4 MB max, real image
        // types only) so admins get instant feedback instead of an error
        // page after a full post was already written.
        const okTypes = ['image/jpeg','image/png','image/gif','image/webp','image/bmp'];
        if (okTypes.indexOf(file.type) === -1) {
            alert('Unsupported image type ("' + (file.type || 'unknown') + '"). Please use JPG, PNG, GIF, WebP or BMP.');
            input.value = '';
            return;
        }
        if (file.size > 4 * 1024 * 1024) {
            alert('The image is too large (' + Math.round(file.size / 1024 / 1024 * 10) / 10 + ' MB). Maximum size is 4 MB — please use a smaller image.');
            input.value = '';
            return;
        }
        if (img.dataset.objectUrl) { try { URL.revokeObjectURL(img.dataset.objectUrl); } catch(e){} }
        img.dataset.objectUrl = URL.createObjectURL(file);
        img.src = img.dataset.objectUrl;
        img.classList.remove('hidden');
    }
</script>
@endpush
@endsection
