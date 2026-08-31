@extends('layouts.admin')
@section('title','Pages')
@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Pages'],
    ]])
@endsection

@section('content')
<div class="flex justify-between items-center mb-5">
    <h2 class="font-semibold">All Pages</h2>
    <a href="{{ route('admin.pages.create') }}" class="h-9 px-4 rounded-lg bg-[#0C3B2E] hover:bg-[#072A20] text-white text-sm font-semibold inline-flex items-center gap-1.5">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"/></svg> New Page
    </a>
</div>
<div class="panel-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/60 text-[11px] font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400">
                <tr><th class="text-left px-4 py-3">Title</th><th class="text-left px-4 py-3">Slug</th><th class="text-left px-4 py-3">Status</th><th class="text-left px-4 py-3">Updated</th><th class="text-right px-4 py-3">Actions</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($pages as $page)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                        <td class="px-4 py-3 font-semibold">{{ $page->title }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">{{ $page->slug }}</td>
                        <td class="px-4 py-3"><span class="text-xs font-semibold px-2.5 py-1 {{ $page->status=='published' ? 'badge-green' : 'badge-slate' }}">{{ $page->status }}</span></td>
                        <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">{{ $page->updated_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('page.show', $page->slug) }}" target="_blank" title="View" class="w-8 h-8 rounded-lg border border-[#e6e8ee] dark:border-[#2c313c] bg-white dark:bg-[#14171d] text-slate-600 dark:text-slate-300 hover:bg-[#f7f8fa] dark:hover:bg-[#1c1f26] flex items-center justify-center">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/></svg>
                                </a>
                                <a href="{{ route('admin.pages.edit', $page) }}" title="Edit" class="w-8 h-8 rounded-lg bg-[#0C3B2E] hover:bg-[#072A20] text-white flex items-center justify-center">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m18 5 2.47 2.47a1 1 0 0 1 0 1.41L18 11.34 12.66 6l2.42-2.42a1 1 0 0 1 1.41 0ZM11.95 6.7 4.7 13.96a1 1 0 0 0-.29.7V18a1 1 0 0 0 1 1h3.32a1 1 0 0 0 .7-.29l7.26-7.25Z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Delete this page?')">@csrf @method('DELETE')
                                    <button type="submit" title="Delete" class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 flex items-center justify-center">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No pages yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-[#eef0f4] dark:border-[#22262e]">{{ $pages->links() }}</div>
</div>
@endsection
