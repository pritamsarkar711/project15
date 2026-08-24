@extends('layouts.admin')
@section('title','Categories')
@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Categories'],
    ]])
@endsection

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <h2 class="font-semibold">All Categories</h2>
    <a href="{{ route('admin.categories.create') }}" class="h-9 px-4 bg-[#0C3B2E] hover:bg-[#072A20] text-white text-sm font-semibold inline-flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"/></svg> New Category
    </a>
</div>

<div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 sm:p-5">
    <div id="category-list" class="space-y-2">
        @forelse($categories as $cat)
            <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700" data-id="{{ $cat->id }}">
                <span class="cursor-move text-slate-400 dark:text-slate-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </span>
                <span class="w-9 h-9 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center shrink-0">
                    @include('admin.partials.category-icon', ['icon' => $cat->icon, 'class' => 'w-[18px] h-[18px]'])
                </span>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-sm flex items-center gap-2">
                        {{ $cat->name }}
                        @unless($cat->is_active)<span class="text-[10px] font-bold uppercase px-1.5 py-0.5 bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400">hidden</span>@endunless
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $cat->description ?? $cat->slug }} · {{ $cat->posts_count }} posts</div>
                </div>
                <a href="{{ route('admin.categories.edit', $cat) }}" title="Edit" class="w-8 h-8 bg-[#0C3B2E] hover:bg-[#072A20] text-white flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m18 5 2.47 2.47a1 1 0 0 1 0 1.41L18 11.34 12.66 6l2.42-2.42a1 1 0 0 1 1.41 0ZM11.95 6.7 4.7 13.96a1 1 0 0 0-.29.7V18a1 1 0 0 0 1 1h3.32a1 1 0 0 0 .7-.29l7.26-7.25Z"/></svg>
                </a>
                <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" onsubmit="return confirm('Delete this category?')">@csrf @method('DELETE')
                    <button type="submit" title="Delete" class="w-8 h-8 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </form>
            </div>
        @empty
            <p class="text-sm text-slate-500 dark:text-slate-400 py-6 text-center">No categories yet.</p>
        @endforelse
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    const el = document.getElementById('category-list');
    if (el) {
        new Sortable(el, {
            animation: 150,
            onEnd: function () {
                const order = [...el.children].map(c => c.dataset.id);
                fetch('{{ route('admin.categories.reorder') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order })
                });
            }
        });
    }
</script>
@endpush
@endsection
