@php
    // $post may be null on create, set on edit. We branch on $isEdit.
    $isEdit = isset($post) && $post?->exists;
@endphp

<form method="POST" action="{{ $isEdit ? route('author.posts.update', $post->id) : route('author.posts.store') }}" enctype="multipart/form-data" class="space-y-6" data-autosave="author">
    @csrf
    @if($isEdit) @method('POST') @endif
    {{-- Server-side autosave target: the first autosave creates a draft and
         JS fills this in; later autosaves and the next manual save update
         that same draft instead of creating a duplicate. --}}
    <input type="hidden" name="autosave_post_id" id="autosave-post-id" value="{{ $isEdit ? $post->id : '' }}">

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
                    <label class="text-sm font-medium text-slate-900 dark:text-white">Tags</label>
                    <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $isEdit ? $post->meta_keywords : '') }}" maxlength="255" placeholder="budget travel, packing list, europe"
                        class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-900 dark:text-white">
                </div>

                <div class="mt-4">
                    <label class="text-sm font-semibold text-slate-900 dark:text-white">Content *</label>
                    {{-- No HTML required attribute here on purpose: CKEditor hides
                         this textarea and the browser would block submit on a
                         hidden empty required field. The submit handler and the
                         server both still validate the content. --}}
                    <textarea id="editor" name="content" rows="14" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm min-h-[360px] text-slate-900 dark:text-white">{{ old('content', $isEdit ? $post->content : '') }}</textarea>
                    <p id="editor-error" class="hidden text-xs text-red-600 dark:text-red-400 mt-1.5">Please write the post content first.</p>
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
                    {{-- Live autosave feedback: reassures the writer that their
                         work survives a crash, power cut or dropped connection. --}}
                    <p id="autosave-status" class="mt-3 text-[11px] font-medium text-slate-400 dark:text-slate-500" aria-live="polite"></p>
                @endif
            </div>

            {{-- Featured image --}}
            <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                <h3 class="font-semibold mb-3 text-slate-900 dark:text-white">Featured Image</h3>
                <img id="featured-preview" src="{{ $isEdit && $post->featured_image ? (str_starts_with($post->featured_image, 'http') ? $post->featured_image : '/storage/'.$post->featured_image) : '#' }}" alt="" class="{{ $isEdit && $post->featured_image ? '' : 'hidden' }} w-full h-40 object-cover mb-3 border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800" loading="lazy" decoding="async">
                <label class="block w-full cursor-pointer border border-dashed border-slate-300 dark:border-slate-600 py-4 text-center text-sm text-slate-500 dark:text-slate-400 hover:border-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300 transition">
                    <svg class="w-5 h-5 mx-auto mb-1 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 3h6m0 0v6m0-6L10 14M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                    Click to upload
                    <input type="file" name="featured_image" accept="image/*" class="hidden" onchange="previewFeatured(this)">
                </label>
            </div>

            {{-- Affiliate toggle --}}
            <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                <h3 class="font-semibold mb-3 text-slate-900 dark:text-white">Affiliate Links</h3>
                <label class="flex items-center justify-between gap-4 cursor-pointer">
                    <span class="text-sm text-slate-700 dark:text-slate-300">This post contains affiliate links</span>
                    <span class="relative inline-flex shrink-0">
                        <input type="checkbox" name="is_affiliate" value="1" @checked(old('is_affiliate', $isEdit && $post->is_affiliate)) class="peer sr-only">
                        <span class="block w-11 h-6 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-[#0C3B2E] transition-colors"></span>
                        <span class="pointer-events-none absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                    </span>
                </label>
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
{{-- Self-made Huvanti rich text editor: single small file, no dependencies.
     The tag is cache-busted (?v=filemtime) so editor fixes actually reach
     browsers that cached the previous version. --}}
{!! \App\Support\ViteAssets::editorScript() !!}
<script>
// Huvanti rich text editor for the author post form.
(function(){
    huvantiEditorInit('#editor');

    // Friendly validation for the content (the textarea itself must stay
    // non-required so the browser never blocks on the hidden field).
    // The rules below MIRROR the server (min 120 characters always, 300 words
    // on submit) so the author gets the message next to the editor instead of
    // a page reload with a generic error box at the top.
    var form = document.querySelector('#editor') ? document.querySelector('#editor').closest('form') : null;
    if (form) {
        form.addEventListener('submit', function(e){
            var ed = document.getElementById('editor');
            var val = ed ? ed.value : '';
            var text = val.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
            var words = text ? text.split(' ').length : 0;
            var submitting = !(e.submitter && e.submitter.value === 'save_draft');
            var err = document.getElementById('editor-error');
            var problem = '';
            if (val.replace(/<[^>]*>/g, '').trim().length < 120) {
                problem = 'The content is too short — at least 120 characters are required.';
            } else if (submitting && words < 300) {
                problem = 'Submitted posts must contain at least 300 words (currently ' + words + '). Save it as a draft or keep writing.';
            }
            if (problem) {
                e.preventDefault();
                if (err) { err.textContent = problem; err.classList.remove('hidden'); }
                if (ed) ed.scrollIntoView({behavior: 'smooth', block: 'center'});
            } else {
                if (err) err.classList.add('hidden');
                // A real submit means the post is being saved for good — stop
                // the autosave loop so it can't fire against a navigating page.
                window.__huvAutosaveStop = true;
                try { localStorage.removeItem(window.__huvFormKey || ''); } catch (err2) {}
            }
        });
    }
})();

// ---------------------------------------------------------------------------
// Crash-safe autosave — two independent layers, so a browser crash, power cut
// ("load shedding") or dead network can never lose more than a few seconds of
// writing:
//   Layer 1 (local): every 3 s the WHOLE form is snapshotted to localStorage.
//     On reopening the page with an empty form, the snapshot is restored —
//     works fully OFFLINE.
//   Layer 2 (server): every 45 s (and once on tab close) the form is POSTed
//     to the autosave endpoint, which stores it as a real draft row. Survives
//     browser resets, other devices and cleared storage.
// ---------------------------------------------------------------------------
(function(){
    var form = document.querySelector('form[data-autosave]');
    if (!form) return;

    var STORAGE_KEY = 'huv-form-draft:' + location.pathname;
    window.__huvFormKey = STORAGE_KEY;
    var statusEl = document.getElementById('autosave-status');
    var idEl = document.getElementById('autosave-post-id');
    var dirtyLocal = false;   // snapshot needs a refresh
    var dirtyServer = false;  // server copy needs a refresh
    var inFlight = false;
    var stopped = false;

    function setStatus(msg) {
        if (statusEl) statusEl.textContent = msg;
    }

    // Fields that carry the author's work (exclude file input, tokens, the
    // submit-button name/value pair and the autosave id itself).
    function snapshot() {
        var fd = new FormData(form);
        var data = {};
        fd.forEach(function (value, key) {
            if (key === '_token' || key === '_method' || key === 'action' || key === 'featured_image' || key === 'autosave_post_id') return;
            data[key] = typeof value === 'string' ? value : '';
        });
        data.__savedAt = new Date().toISOString();
        return data;
    }

    function formatTime(iso) {
        try { return new Date(iso).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }); }
        catch (e) { return ''; }
    }

    function restore() {
        // Only restore into a pristine form (never overwrite server-loaded
        // or validation-returned values).
        var title = form.querySelector('[name="title"]');
        var ed = document.getElementById('editor');
        var exc = form.querySelector('[name="excerpt"]');
        var pristine = title && !title.value && ed && !ed.value && (!exc || !exc.value);
        if (!pristine) return;
        var raw = null;
        try { raw = localStorage.getItem(STORAGE_KEY); } catch (e) { return; }
        if (!raw) return;
        var data;
        try { data = JSON.parse(raw); } catch (e) { return; }
        var restored = 0;
        Object.keys(data).forEach(function (key) {
            if (key === '__savedAt') return;
            var field = form.querySelector('[name="' + key + '"]');
            if (field && field.type !== 'file') { field.value = data[key]; restored++; }
        });
        if (restored) {
            // Push content into the rich text editor too. The editor never
            // listens to textarea events, so plain value assignment leaves
            // the editor VISUALLY EMPTY while the hidden textarea holds the
            // text — __huvSet is the editor's official restore bridge.
            if (ed) {
                if (typeof ed.__huvSet === 'function') { ed.__huvSet(data['content'] || ''); }
                else {
                    ed.value = data['content'] || '';
                    ed.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
            setStatus('Your last unsaved work in this browser was restored (saved ' + formatTime(data.__savedAt) + ').');
            dirtyLocal = false; dirtyServer = true; // push the restored copy to the server too
        }
    }

    form.addEventListener('input', function () { dirtyLocal = true; dirtyServer = true; }, true);
    form.addEventListener('change', function () { dirtyLocal = true; dirtyServer = true; }, true);

    // ---- Layer 1: local snapshot every 3 s --------------------------------
    setInterval(function () {
        if (stopped || window.__huvAutosaveStop || !dirtyLocal || document.hidden) return;
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(snapshot()));
            dirtyLocal = false;
        } catch (e) { /* storage full/blocked — layer 2 still covers us */ }
    }, 3000);

    // ---- Layer 2: server autosave every 45 s ------------------------------
    function buildAutosaveBody() {
        var fd = new FormData(form);
        // Never re-upload file inputs every 45 s — the featured image rides
        // only on the real submit. SendBeacon also cannot carry files.
        var drop = [];
        fd.forEach(function (v, k) { if (typeof v !== 'string') drop.push(k); });
        drop.forEach(function (k) { fd.delete(k); });
        if (idEl && idEl.value) fd.set('autosave_post_id', idEl.value);
        return fd;
    }

    function sendToServer(useBeacon) {
        if (stopped || window.__huvAutosaveStop || inFlight || !navigator.onLine) {
            if (!navigator.onLine) setStatus('You are offline — your work is saved in this browser and will sync when the connection returns.');
            return;
        }
        var body = buildAutosaveBody();
        if (useBeacon && navigator.sendBeacon) {
            // Fire-and-forget; CSRF rides along via the hidden _token field.
            navigator.sendBeacon('{{ route("author.posts.autosave") }}', body);
            return;
        }
        inFlight = true;
        fetch('{{ route("author.posts.autosave") }}', {
            method: 'POST',
            body: body,
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.json(); }).then(function (j) {
            inFlight = false;
            if (j && j.ok && j.autosave_post_id) {
                if (idEl) idEl.value = j.autosave_post_id;
                dirtyServer = false;
                setStatus('Draft auto-saved' + (j.saved_at ? ' at ' + j.saved_at : '') + '. Safe from crashes and power cuts.');
            } else if (j && j.locked) {
                stopped = true;
                setStatus(j.message || 'This post is locked — autosave paused.');
            }
        }).catch(function () {
            inFlight = false;
            setStatus('Offline or server busy — your work is saved in this browser.');
        });
    }

    setInterval(function () {
        if (stopped || window.__huvAutosaveStop || !dirtyServer) return;
        sendToServer(false);
    }, 45000);

    // Last flush when the tab is hidden or closed (covers power cut mid-typing).
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            try { localStorage.setItem(STORAGE_KEY, JSON.stringify(snapshot())); dirtyLocal = false; } catch (e) {}
            if (dirtyServer) sendToServer(true);
        }
    });
    window.addEventListener('pagehide', function () {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(snapshot())); dirtyLocal = false; } catch (e) {}
        if (dirtyServer) sendToServer(true);
    });

    // Restore any local snapshot from a previous crashed session.
    restore();
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
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];
    // Client-side guards so the author gets instant feedback instead of a
    // server error page: max 4 MB (server limit) and real image types only.
    var okTypes = ['image/jpeg','image/png','image/gif','image/webp','image/bmp'];
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
