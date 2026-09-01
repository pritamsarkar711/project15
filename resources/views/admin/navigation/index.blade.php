@extends('layouts.admin')
@section('title','Navigation')
@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Navigation'],
    ]])
@endsection

@section('content')
<form method="POST" action="{{ route('admin.navigation.store') }}" class="panel-card p-5 mb-6">
    @csrf
    <h3 class="font-semibold mb-3">Add Menu Item</h3>
    <div class="grid sm:grid-cols-4 gap-3">
        <input type="text" name="label" required placeholder="Label" class="h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm placeholder:text-slate-400">
        <input type="text" name="url" required placeholder="URL (e.g. /blog)" class="h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm placeholder:text-slate-400">
        <select name="position" class="h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
            <option value="header">Header (Desktop)</option>
            <option value="mobile">Mobile Hamburger</option>
            <option value="footer">Footer</option>
        </select>
        <button type="submit" class="h-10 px-4 rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white text-sm font-semibold transition">Add</button>
    </div>
</form>

<div class="grid lg:grid-cols-2 gap-5">
    @foreach([['header-list','Header Navigation',$header],['mobile-list','Mobile Navigation',$mobile]] as [$listId,$title,$items])
        <div class="panel-card p-5">
            <h3 class="font-semibold mb-3 flex items-center justify-between">{{ $title }} <span class="text-xs break-all font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 px-2 py-1">{{ $items->count() }} items</span></h3>
            <div id="{{ $listId }}" class="space-y-2">
                @forelse($items as $item)
                    <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700" data-id="{{ $item->id }}">
                        <span class="cursor-move text-slate-400 dark:text-slate-500 shrink-0"><svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg></span>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold">{{ $item->label }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">{{ $item->url }}</span></div>
                        </div>
                        <form method="POST" action="{{ route('admin.navigation.destroy',$item) }}" onsubmit="return confirm('Delete this item?')">@csrf @method('DELETE')
                            <button class="w-7 h-7 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400 py-3 text-center">No items.</p>
                @endforelse
            </div>
        </div>
    @endforeach
</div>

<div class="panel-card p-5 mt-5">
    <h3 class="font-semibold mb-3">Footer Navigation</h3>
    <div id="footer-list" class="flex flex-wrap gap-2">
        @forelse($footer as $item)
            <div class="flex items-center gap-2 px-3 py-2 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-sm" data-id="{{ $item->id }}">
                <span class="cursor-move text-slate-400 dark:text-slate-500"><svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg></span>
                {{ $item->label }}
                <form method="POST" action="{{ route('admin.navigation.destroy',$item) }}" onsubmit="return confirm('Delete this item?')">@csrf @method('DELETE')
                    <button class="text-red-600 dark:text-red-400 ml-1"><svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 6 6 18M6 6l12 12"/></svg></button>
                </form>
            </div>
        @empty
            <span class="text-sm text-slate-500 dark:text-slate-400">No footer items yet.</span>
        @endforelse
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
function makeSortable(id, position){
    const el=document.getElementById(id);
    if(!el) return;
    new Sortable(el, {
        animation:150,
        handle: '.cursor-move',
        onEnd: ()=>{
            const order=[...el.children].map(c=>c.dataset.id);
            fetch('{{ route('admin.navigation.reorder') }}', {
                method:'POST',
                headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'},
                body: JSON.stringify({position, order})
            });
        }
    });
}
makeSortable('header-list','header');
makeSortable('mobile-list','mobile');
makeSortable('footer-list','footer');
</script>
@endpush
@endsection
