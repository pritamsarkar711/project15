@extends('layouts.admin')
@section('title','Edit Post')
@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Posts', 'route' => 'admin.posts.index'],
        ['label' => 'Edit Post'],
    ]])
@endsection

@section('content')
<form method="POST" action="{{ route('admin.posts.update', $post) }}" enctype="multipart/form-data" class="space-y-6">
    @csrf @method('PUT')
    <div class="grid lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 space-y-5">
            <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                <label class="text-sm font-semibold">Title *</label>
                <input type="text" name="title" required value="{{ old('title', $post->title) }}" class="mt-1 w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 outline-none text-sm">
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

            <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                <h3 class="font-semibold mb-3">FAQ</h3>
                <div id="faqs">
                    @forelse(old('faqs', $post->faqs->map(fn($f)=>['question'=>$f->question,'answer'=>$f->answer])->toArray()) as $idx => $faq)
                        <div class="faq-item grid grid-cols-1 gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 mb-3">
                            <input type="text" name="faqs[{{ $idx }}][question]" value="{{ $faq['question'] ?? '' }}" placeholder="Question" class="h-10 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm">
                            <textarea name="faqs[{{ $idx }}][answer]" placeholder="Answer" rows="2" class="px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm">{{ $faq['answer'] ?? '' }}</textarea>
                            <button type="button" onclick="this.parentElement.remove()" class="text-xs text-red-600 dark:text-red-400 justify-self-start">Remove</button>
                        </div>
                    @empty
                        <div class="faq-item grid grid-cols-1 gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 mb-3">
                            <input type="text" name="faqs[0][question]" placeholder="Question" class="h-10 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm">
                            <textarea name="faqs[0][answer]" placeholder="Answer" rows="2" class="px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm"></textarea>
                        </div>
                    @endforelse
                </div>
                <button type="button" onclick="addFaq()" class="text-sm font-semibold text-emerald-700 dark:text-emerald-300 hover:underline">+ Add FAQ</button>
            </div>
        </div>

        <div class="lg:col-span-4 space-y-5">
            <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                <h3 class="font-semibold mb-3">Publish</h3>
                <select name="status" class="w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    <option value="draft" @selected(old('status', $post->status)=='draft')>Draft</option>
                    <option value="published" @selected(old('status', $post->status)=='published')>Published</option>
                    <option value="archived" @selected(old('status', $post->status)=='archived')>Archived</option>
                </select>
                <label class="text-sm font-medium mt-3 block">Schedule (optional)</label>
                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $post->scheduled_at?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ $post->reading_time }} min read · {{ number_format($post->views) }} views</div>
                <label class="flex items-center gap-2 mt-3 text-sm"><input type="checkbox" name="is_featured" value="1" @checked($post->is_featured) class="border-slate-300 dark:border-slate-600 text-emerald-600 focus:ring-emerald-500"> Featured post</label>
                <label class="flex items-center gap-2 mt-2 text-sm"><input type="checkbox" name="allow_comments" value="1" @checked($post->allow_comments) class="border-slate-300 dark:border-slate-600 text-emerald-600 focus:ring-emerald-500"> Allow comments</label>
                <button type="submit" class="w-full mt-4 h-11 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold transition">Update Post</button>
                <a href="{{ route('admin.posts.index') }}" class="block text-center mt-2 text-sm text-slate-500 dark:text-slate-400 hover:underline">Cancel</a>
            </div>

            <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                <h3 class="font-semibold mb-3">Featured Image</h3>
                @if($post->featured_image)
                    <img id="featured-preview" src="{{ str_starts_with($post->featured_image,'http') ? $post->featured_image : '/storage/'.$post->featured_image }}" class="w-full h-40 object-cover mb-3 border border-slate-200 dark:border-slate-700" alt="" loading="lazy" decoding="async">
                @else
                    <img id="featured-preview" src="#" alt="" class="hidden w-full h-40 object-cover mb-3 border border-slate-200 dark:border-slate-700" loading="lazy" decoding="async">
                @endif
                <label class="block w-full cursor-pointer border border-dashed border-slate-300 dark:border-slate-600 py-3 text-center text-sm text-slate-500 dark:text-slate-400 hover:border-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300 transition">
                    <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 3h6m0 0v6m0-6L10 14M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                    Replace image (auto-converted to WebP)
                    <input type="file" name="featured_image" accept="image/*" class="hidden" onchange="previewFeatured(this)">
                </label>
                <label class="text-xs font-medium mt-3 block text-slate-500 dark:text-slate-400">Or image URL</label>
                <input type="text" name="featured_image_url" value="{{ old('featured_image_url') }}" placeholder="https://..." class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
            </div>

            <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                <h3 class="font-semibold mb-3">SEO</h3>
                <input type="text" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" placeholder="Meta title" class="w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                <textarea name="meta_description" rows="2" placeholder="Meta description" class="mt-2 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">{{ old('meta_description', $post->meta_description) }}</textarea>
                <label class="text-sm font-medium mt-3 block">Tags <span class="text-slate-400 font-normal">(comma separated)</span></label>
                <input type="text" name="meta_keywords" value="{{ old('meta_keywords', $post->meta_keywords) }}" placeholder="budget travel, packing list" class="mt-2 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script src="{{ asset('js/ckeditor5-super-build.js') }}"></script>
<script src="{{ asset('js/editor-init.js') }}"></script>
<script>
// Full CKEditor 5 editor (local super-build, no CDN dependency).
initHuvantiEditor('#editor');
</script>
<script>
    let faqIdx = {{ count(old('faqs', $post->faqs)) + 1 }};
    function addFaq(){
        const c = document.getElementById('faqs');
        const d = document.createElement('div');
        d.className='faq-item grid grid-cols-1 gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 rounded-lg border border-slate-200 dark:border-slate-700 mb-3';
        d.innerHTML=`<input type="text" name="faqs[${faqIdx}][question]" placeholder="Question" class="h-10 px-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm"><textarea name="faqs[${faqIdx}][answer]" placeholder="Answer" rows="2" class="px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm"></textarea><button type="button" onclick="this.parentElement.remove()" class="text-xs text-red-600 dark:text-red-400 justify-self-start">Remove</button>`;
        c.appendChild(d);
        faqIdx++;
    }
    function previewFeatured(input){
        const img = document.getElementById('featured-preview');
        if(input.files && input.files[0]){
            img.src = URL.createObjectURL(input.files[0]);
            img.classList.remove('hidden');
        }
    }
</script>
@endpush
@endsection
