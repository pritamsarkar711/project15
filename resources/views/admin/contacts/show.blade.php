@extends('layouts.admin')
@section('title','Message from '.$contact->name)
@section('content')
<div class="w-full">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('admin.contacts.index') }}" class="w-9 h-9 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 flex items-center justify-center text-slate-600 dark:text-slate-300">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7-7-7 7 7 7"/></svg>
        </a>
        <h2 class="font-semibold truncate">Message from {{ $contact->name }}</h2>
    </div>

    <div class="panel-card p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="font-semibold text-lg">{{ $contact->name }}</h3>
                <div class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $contact->email }}</div>
                <div class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $contact->reason }}@if($contact->subject) · {{ $contact->subject }}@endif · {{ $contact->created_at->format('M d, Y h:i A') }}</div>
            </div>
            <span class="text-xs font-semibold px-3 py-1 {{ $contact->is_read ? 'badge-slate' : 'badge-green' }}">{{ $contact->is_read ? 'Read' : 'Unread' }}</span>
        </div>

        <div class="mt-5 p-4 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 text-sm leading-relaxed whitespace-pre-line">{{ $contact->message }}</div>

        <div class="mt-6 flex flex-wrap gap-2">
            <a href="mailto:{{ $contact->email }}?subject=Re: {{ $contact->subject ?? $contact->reason }}" class="h-10 px-5 rounded-lg bg-[#0C3B2E] hover:bg-[#072A20] text-white text-sm font-semibold inline-flex items-center gap-2 transition">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/></svg>
                Reply via Email
            </a>
            <form method="POST" action="{{ route('admin.contacts.read', $contact) }}">@csrf
                <button type="submit" class="h-10 px-5 text-sm font-semibold border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    {{ $contact->is_read ? 'Mark as Unread' : 'Mark as Read' }}
                </button>
            </form>
            <form method="POST" action="{{ route('admin.contacts.destroy', $contact) }}" onsubmit="return confirm('Delete this message?')">@csrf @method('DELETE')
                <button type="submit" class="h-10 px-5 text-sm font-semibold bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 hover:bg-red-100 dark:hover:bg-red-500/20 transition">Delete</button>
            </form>
        </div>
    </div>
</div>
@endsection
