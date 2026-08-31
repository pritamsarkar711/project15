@extends('layouts.admin')
@section('title','Contact Messages')
@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Messages'],
    ]])
@endsection

@section('content')
<div class="flex items-center gap-1.5 mb-5">
    @foreach(['all'=>'All','unread'=>'Unread','read'=>'Read'] as $key => $label)
        <a href="{{ route('admin.contacts.index', $key !== 'all' ? ['filter'=>$key] : []) }}"
           class="h-9 px-3.5 inline-flex items-center gap-2 text-[13px] font-semibold rounded-lg transition {{ ((!request('filter') || request('filter')=='all') && $key=='all') || request('filter')==$key ? 'bg-[#16181d] text-white dark:bg-white dark:text-[#101319]' : 'bg-white dark:bg-[#14171d] border border-[#e6e8ee] dark:border-[#2c313c] text-slate-600 dark:text-slate-300 hover:bg-[#f7f8fa] dark:hover:bg-[#1c1f26]' }}">
            {{ $label }}
            @if($key=='unread' && $unread)<span class="text-xs bg-amber-400 text-slate-900 px-1.5 py-0.5 font-bold">{{ $unread }}</span>@endif
        </a>
    @endforeach
</div>

<div class="panel-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800/60 text-[11px] font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="text-left px-4 py-3">Name</th>
                    <th class="text-left px-4 py-3">Reason</th>
                    <th class="text-left px-4 py-3">Message</th>
                    <th class="text-left px-4 py-3">Date</th>
                    <th class="text-right px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($messages as $m)
                    <tr class="{{ !$m->is_read ? 'bg-[#F0F7F3]/50 dark:bg-[#2E7856]/5' : '' }}">
                        <td class="px-4 py-3">
                            <div class="font-semibold">{{ $m->name }} @unless($m->is_read)<span class="w-2 h-2 bg-[#2E7856] inline-block ml-1 align-middle"></span>@endunless</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $m->email }}</div>
                        </td>
                        <td class="px-4 py-3"><span class="text-xs bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-2 py-1">{{ $m->reason }}</span></td>
                        <td class="px-4 py-3"><div class="truncate max-w-[320px]">{{ Str::limit(strip_tags($m->message), 90) }}</div>@if($m->subject)<div class="text-xs text-slate-500 dark:text-slate-400">{{ $m->subject }}</div>@endif</td>
                        <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ $m->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('admin.contacts.show', $m) }}" title="View" class="w-8 h-8 rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white flex items-center justify-center">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.contacts.read', $m) }}">@csrf
                                    <button type="submit" title="{{ $m->is_read ? 'Mark as unread' : 'Mark as read' }}" class="w-8 h-8 rounded-lg border border-[#e6e8ee] dark:border-[#2c313c] bg-white dark:bg-[#14171d] text-slate-600 dark:text-slate-300 hover:bg-[#f7f8fa] dark:hover:bg-[#1c1f26] flex items-center justify-center">
                                        @if($m->is_read)
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v5h5"/></svg>
                                        @else
                                            <svg class="w-4 h-4 text-[#27654A] dark:text-[#6FB393] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 11 3 3L22 4"/></svg>
                                        @endif
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.contacts.destroy', $m) }}" onsubmit="return confirm('Delete this message?')">@csrf @method('DELETE')
                                    <button type="submit" title="Delete" class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 flex items-center justify-center">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No messages found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-[#eef0f4] dark:border-[#22262e]">{{ $messages->links() }}</div>
</div>
@endsection
