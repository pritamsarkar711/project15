@extends('layouts.admin')
@section('title','Create Page')
@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Pages', 'route' => 'admin.pages.index'],
        ['label' => 'New Page'],
    ]])
@endsection

@section('content')
<div class="grid lg:grid-cols-12 gap-6">
    <div class="lg:col-span-8">
        <form id="page-form" method="POST" action="{{ route('admin.pages.store') }}" class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-semibold">Title *</label>
                <input type="text" name="title" required value="{{ old('title') }}" class="mt-1 w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 outline-none">
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Slug (auto if empty)</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Status</label>
                    <select name="status" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                        <option value="draft" @selected(old('status')=='draft')>Draft</option>
                        <option value="published" @selected(old('status')=='published')>Published</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="text-sm font-semibold">Content *</label>
                <textarea id="editor" name="content" rows="12" class="mt-1 w-full px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm min-h-[300px]">{{ old('content') }}</textarea>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="h-11 px-6 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold transition">Create Page</button>
                <a href="{{ route('admin.pages.index') }}" class="h-11 px-6 inline-flex items-center font-semibold border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">Cancel</a>
            </div>
        </form>
    </div>
    <div class="lg:col-span-4">
        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <h3 class="font-semibold">SEO</h3>
            <div>
                <label class="text-sm font-medium">Meta Title</label>
                <input type="text" name="meta_title" form="page-form" value="{{ old('meta_title') }}" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Meta Description</label>
                <textarea name="meta_description" form="page-form" rows="3" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">{{ old('meta_description') }}</textarea>
            </div>
            <p class="text-xs text-slate-400 dark:text-slate-500">Meta fields are saved with the page on submit.</p>
        </div>
    </div>
</div>
@push('scripts')
<script src="{{ asset('js/huvanti-editor.js') }}"></script>
<script>
// Self-made Huvanti rich text editor (single small file, no dependencies).
huvantiEditorInit('#editor');
</script>
@endpush
@endsection
