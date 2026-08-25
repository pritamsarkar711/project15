@php
    // $post may be null on create, set on edit. We branch on $isEdit.
    $isEdit = isset($post) && $post?->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('author.posts.update', $post->id) : route('author.posts.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if($isEdit) @method('POST') @endif

    <div class="grid lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 space-y-5">
            {{-- Main writing card, mirrors the admin post editor --}}
            <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                <label class="text-sm font-semibold text-slate-900 dark:text-white">Title *</label>
                <input type="text" name="title" required value="{{ old('title', $isEdit ? $post->title : '') }}" maxlength="255"
                    class="mt-1 w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 outline-none text-sm text-slate-900 dark:text-white">

                <div class="grid sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="text-sm font-medium text-slate-900 dark:text-white">Category *</label>
                        <select name="category_id" required class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white">
                            <option value="">Select category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id', $isEdit ? $post->category_id : '') == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-900 dark:text-white">Excerpt</label>
                        <input type="text" name="excerpt" value="{{ old('excerpt', $isEdit ? $post->excerpt : '') }}" maxlength="500" placeholder="One line summary"
                            class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="text-sm font-semibold text-slate-900 dark:text-white">Content *</label>
                    <textarea id="editor" name="content" rows="14" required class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm min-h-[360px] text-slate-900 dark:text-white">{{ old('content', $isEdit ? $post->content : '') }}</textarea>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Minimum 300 words to submit for review.</p>
                </div>
            </div>

            {{-- FAQ: required section, always visible --}}
            <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="font-semibold text-slate-900 dark:text-white">FAQ *</h3>
                    <span class="text-xs text-slate-500 dark:text-slate-400">At least one question and answer</span>
                </div>
                <div id="faqs" class="mt-3 space-y-3">
                    @php
                        $existingFaqs = $isEdit ? $post->faqs->all() : [];
                        $oldFaqs = old('faqs', []);
                        $faqList = count($oldFaqs) ? $oldFaqs : (count($existingFaqs) ? $existingFaqs : [['question' => '', 'answer' => '']]);
                    @endphp
                    @foreach($faqList as $idx => $faq)
                        <div class="faq-item grid grid-cols-1 gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                            <input type="text" name="faqs[{{ $idx }}][question]" value="{{ $faq['question'] ?? '' }}" placeholder="Question" class="h-10 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white">
                            <textarea name="faqs[{{ $idx }}][answer]" rows="2" placeholder="Answer" class="px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white">{{ $faq['answer'] ?? '' }}</textarea>
                            @if($idx > 0)
                                <button type="button" onclick="this.closest('.faq-item').remove()" class="text-xs font-semibold text-red-600 dark:text-red-400 justify-self-start hover:underline">Remove</button>
                            @endif
                        </div>
                    @endforeach
                </div>
                <button type="button" onclick="addFaq()" class="mt-3 text-sm font-semibold text-emerald-700 dark:text-emerald-300 hover:underline">Add another FAQ</button>
            </div>
        </div>

        <div class="lg:col-span-4 space-y-5">
            {{-- Publish --}}
            <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                <h3 class="font-semibold mb-3 text-slate-900 dark:text-white">Publish</h3>
                @if($isEdit && ($post->review_status === 'pending_review' || $post->review_status === 'approved'))
                    <div class="bg-slate-100 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 p-4 text-sm text-slate-600 dark:text-slate-400">
                        This post is <strong>{{ $post->review_status === 'approved' ? 'published' : 'in review' }}</strong>, so it is locked for editing.
                    </div>
                @else
                    @php
                        $canSubmit = \App\Models\Post::authorSubmittedRecently(auth()->id()) === false;
                        $nextAt = \App\Models\Post::where('user_id', auth()->id())
                            ->whereNotNull('submitted_at')
                            ->where('submitted_at', '>=', now()->subDay())
                            ->orderBy('submitted_at')
                            ->value('submitted_at')?->addDay();
                    @endphp
                    @if(! $canSubmit)
                        <div class="bg-amber-50 dark:bg-amber-400/10 border border-amber-200 dark:border-amber-500/30 p-3 text-xs text-amber-800 dark:text-amber-300 mb-3">
                            Next submission {{ $nextAt ? $nextAt->diffForHumans() : 'in 24 hours' }}.
                        </div>
                    @endif
                    <div class="space-y-3">
                        <button type="submit" name="action" value="save_draft" class="w-full h-11 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold text-sm transition">
                            Save as draft
                        </button>
                        <button type="submit" name="action" value="submit" @disabled(! $canSubmit) class="w-full h-11 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold text-sm transition disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ $isEdit && $post->review_status === 'returned' ? 'Resubmit for review' : 'Submit for review' }}
                        </button>
                    </div>
                @endif
            </div>

            {{-- Featured image --}}
            <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                <h3 class="font-semibold mb-3 text-slate-900 dark:text-white">Featured Image</h3>
                <img id="featured-preview" src="{{ $isEdit && $post->featured_image ? (str_starts_with($post->featured_image, 'http') ? $post->featured_image : '/storage/'.$post->featured_image) : '#' }}" alt="" class="{{ $isEdit && $post->featured_image ? '' : 'hidden' }} w-full h-40 object-cover mb-3 border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" loading="lazy" decoding="async">
                <label class="block w-full cursor-pointer border border-dashed border-slate-300 dark:border-slate-600 py-4 text-center text-sm text-slate-500 dark:text-slate-400 hover:border-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300 transition">
                    <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 3h6m0 0v6m0-6L10 14M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                    Click to upload
                    <input type="file" name="featured_image" accept="image/*" class="hidden" onchange="previewFeatured(this)">
                </label>
            </div>

            {{-- Affiliate toggle --}}
            <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                <h3 class="font-semibold mb-3 text-slate-900 dark:text-white">Affiliate Links</h3>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="is_affiliate" value="1" @checked(old('is_affiliate', $isEdit && $post->is_affiliate)) class="mt-0.5 w-5 h-5 text-[#0C3B2E]">
                    <span class="text-sm text-slate-600 dark:text-slate-400">This post contains affiliate links</span>
                </label>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">A disclosure notice is added to the published post automatically.</p>
            </div>

            {{-- SEO: required section, always visible --}}
            <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                <h3 class="font-semibold mb-3 text-slate-900 dark:text-white">SEO *</h3>
                <label class="text-sm font-medium text-slate-900 dark:text-white">Meta title</label>
                <input type="text" name="meta_title" required value="{{ old('meta_title', $isEdit ? $post->meta_title : '') }}" maxlength="255" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white">
                <label class="text-sm font-medium mt-3 block text-slate-900 dark:text-white">Meta description</label>
                <textarea name="meta_description" required rows="3" maxlength="500" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white">{{ old('meta_description', $isEdit ? $post->meta_description : '') }}</textarea>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/super-build/ckeditor.js"></script>
<script>
// Full CKEditor 5 toolbar: headings, formatting, fonts, colors, lists,
// alignment, links, tables, media, images (base64), code blocks, find and
// replace, source editing and more.
(function(){
    var CK = window.CKEDITOR || {};
    var Editor = CK.ClassicEditor;
    if (!Editor) { console.warn('CKEditor failed to load'); return; }

    // Base64 upload adapter: lets authors paste or upload images without
    // any server-side upload configuration.
    var base64 = null;
    try {
        base64 = (CK.upload && CK.upload.Base64UploadAdapter)
            || (window.CKEditor5 && window.CKEditor5.upload && window.CKEditor5.upload.Base64UploadAdapter);
    } catch (e) {}

    var config = {
        toolbar: {
            items: [
                'heading', '|',
                'bold', 'italic', 'underline', 'strikethrough', '|',
                'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                'bulletedList', 'numberedList', 'todoList', 'outdent', 'indent', 'alignment', '|',
                'link', 'blockQuote', 'insertTable', 'mediaEmbed', 'insertImage', 'horizontalLine', 'specialCharacters', 'codeBlock', '|',
                'findAndReplace', 'selectAll', 'removeFormat', 'sourceEditing', '|',
                'undo', 'redo'
            ]
        },
        image: {
            toolbar: ['imageTextAlternative', 'toggleImageCaption', 'imageStyle:inline', 'imageStyle:block', 'imageStyle:side']
        },
        table: {
            contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties', 'tableCellProperties']
        },
        heading: {
            options: [
                { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                { model: 'heading1', view: 'h2', title: 'Heading 1', class: 'ck-heading_heading1' },
                { model: 'heading2', view: 'h3', title: 'Heading 2', class: 'ck-heading_heading2' },
                { model: 'heading3', view: 'h4', title: 'Heading 3', class: 'ck-heading_heading3' }
            ]
        }
    };
    if (base64) { config.extraPlugins = [base64]; }

    Editor.create(document.querySelector('#editor'), config).catch(function(e){ console.error(e); });
})();

// FAQ rows
var faqIdx = 100;
function addFaq(){
    var container = document.getElementById('faqs');
    var div = document.createElement('div');
    div.className = 'faq-item grid grid-cols-1 gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700';
    div.innerHTML = '<input type="text" name="faqs['+faqIdx+'][question]" placeholder="Question" class="h-10 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white">'
        + '<textarea name="faqs['+faqIdx+'][answer]" rows="2" placeholder="Answer" class="px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white"></textarea>'
        + '<button type="button" class="text-xs font-semibold text-red-600 dark:text-red-400 justify-self-start hover:underline">Remove</button>';
    div.querySelector('button').onclick = function(){ div.remove(); };
    container.appendChild(div);
    faqIdx++;
}
function previewFeatured(input){
    var img = document.getElementById('featured-preview');
    if (input.files && input.files[0]) {
        img.src = URL.createObjectURL(input.files[0]);
        img.classList.remove('hidden');
    }
}
</script>
@endpush
