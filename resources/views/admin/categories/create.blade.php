@extends('layouts.admin')
@section('title','New Category')
@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Categories', 'route' => 'admin.categories.index'],
        ['label' => 'New Category'],
    ]])
@endsection

@section('content')
<div class="w-full">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('admin.categories.index') }}" class="w-9 h-9 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-600 dark:text-slate-300">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7-7-7 7 7 7"/></svg>
        </a>
        <h2 class="font-semibold">New Category</h2>
    </div>

    <form method="POST" action="{{ route('admin.categories.store') }}" class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-5">
        @csrf
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Name *</label>
                <input type="text" name="name" required value="{{ old('name') }}" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Slug (auto if empty)</label>
                <input type="text" name="slug" value="{{ old('slug') }}" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
            </div>
        </div>
        <div>
            <label class="text-sm font-medium">Description</label>
            <textarea name="description" rows="2" maxlength="255" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="text-sm font-medium block mb-2">Icon <span class="text-xs font-normal text-slate-500">full-width picker, 76 icons, searchable</span></label>
            @include('admin.partials.icon-picker', ['current' => old('icon', 'newspaper')])
        </div>

        <div class="grid sm:grid-cols-2 gap-4 items-end">
            <div>
                <label class="text-sm font-medium">Sort order</label>
                <input type="number" name="sort_order" min="0" value="{{ old('sort_order') }}" placeholder="auto" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
            </div>
            <label class="flex items-center gap-2 text-sm h-10"><input type="checkbox" name="is_active" value="1" checked class="border-slate-300 dark:border-slate-600 text-emerald-600 focus:ring-emerald-500"> Active (visible on site)</label>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="h-11 px-6 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold transition">Create Category</button>
            <a href="{{ route('admin.categories.index') }}" class="h-11 px-6 inline-flex items-center font-semibold border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">Cancel</a>
        </div>
    </form>
</div>
@endsection
