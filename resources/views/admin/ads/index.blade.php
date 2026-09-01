@extends('layouts.admin')
@section('title','Advertisements')
@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Advertisements'],
    ]])
@endsection

@section('content')

<div class="panel-card p-5 mb-6">
    <h3 class="font-semibold mb-4">New Ad</h3>
    <form method="POST" action="{{ route('admin.ads.store') }}" class="space-y-3">
        @csrf
        <div class="grid sm:grid-cols-3 gap-3">
            <input type="text" name="title" required placeholder="Title" class="h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm placeholder:text-slate-400">
            <select name="position" class="h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                @foreach($positions as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
            </select>
            <input type="text" name="link" placeholder="Link URL (optional)" class="h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm placeholder:text-slate-400">
        </div>
        <textarea name="code" rows="3" placeholder="HTML / AdSense code" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono placeholder:text-slate-400"></textarea>
        <div class="flex items-center gap-3">
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked class="border-slate-300 dark:border-slate-600 text-[#27654A]"> Active</label>
            <button type="submit" class="ml-auto h-10 px-5 rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white text-sm font-semibold transition">Create Ad</button>
        </div>
    </form>
</div>

<div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4 items-start">
    @forelse($ads->flatten() as $ad)
        <div class="panel-card p-4">
            <div class="flex items-center justify-between gap-2 mb-3">
                <span class="text-xs font-semibold px-2.5 py-1 bg-[#F0F7F3] text-[#1F513A] dark:bg-[#2E7856]/10 dark:text-[#6FB393]">{{ $positions[$ad->position] ?? $ad->position }}</span>
                <form method="POST" action="{{ route('admin.ads.destroy', $ad) }}" onsubmit="return confirm('Delete this ad?')">@csrf @method('DELETE')
                    <button type="submit" title="Delete" class="w-7 h-7 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </form>
            </div>
            <form method="POST" action="{{ route('admin.ads.update', $ad) }}" class="space-y-3">
                @csrf @method('PUT')
                <input type="text" name="title" value="{{ $ad->title }}" required class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-semibold">
                <select name="position" class="w-full h-9 px-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    @foreach($positions as $value => $label)<option value="{{ $value }}" @selected($ad->position == $value)>{{ $label }}</option>@endforeach
                </select>
                <textarea name="code" rows="4" placeholder="HTML / AdSense code" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono placeholder:text-slate-400">{{ $ad->code }}</textarea>
                <input type="text" name="link" value="{{ $ad->link }}" placeholder="Link URL" class="w-full h-9 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm placeholder:text-slate-400">
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" {{ $ad->is_active ? 'checked' : '' }} class="border-slate-300 dark:border-slate-600 text-[#27654A]"> Active</label>
                    <button type="submit" class="h-9 px-4 rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white text-sm font-semibold transition">Save</button>
                </div>
            </form>
        </div>
    @empty
        <p class="col-span-full text-center text-sm text-slate-500 dark:text-slate-400 py-10">No ads yet.</p>
    @endforelse
</div>
@endsection
