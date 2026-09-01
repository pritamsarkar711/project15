@extends('layouts.admin')
@section('title','Dashboard')
@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => []])
@endsection

@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <span class="stat-label">Posts</span>
            <span class="icon-tile w-8 h-8 !rounded-lg">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
            </span>
        </div>
        <div class="stat-value">{{ number_format($stats['posts']) }}</div>
        <a href="{{ route('admin.posts.index') }}" class="mt-2 inline-flex text-xs font-semibold text-[#2E7856] dark:text-[#6FB393] hover:underline underline-offset-4">Manage posts</a>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <span class="stat-label">Views</span>
            <span class="icon-tile w-8 h-8 !rounded-lg">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/></svg>
            </span>
        </div>
        <div class="stat-value">{{ number_format($stats['views']) }}</div>
        <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">All-time</div>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <span class="stat-label">Pending Comments</span>
            <span class="icon-tile w-8 h-8 !rounded-lg" style="background:#FEF3C7;color:#92600a;">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.9 9.9 0 0 1-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z"/></svg>
            </span>
        </div>
        <div class="stat-value {{ $stats['comments_pending'] > 0 ? 'text-amber-600 dark:text-amber-300' : '' }}">{{ $stats['comments_pending'] }}</div>
        <a href="{{ route('admin.comments.index',['status'=>'pending']) }}" class="mt-2 inline-flex text-xs font-semibold text-[#2E7856] dark:text-[#6FB393] hover:underline underline-offset-4">Moderate</a>
    </div>
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <span class="stat-label">Unread Messages</span>
            <span class="icon-tile w-8 h-8 !rounded-lg" style="background:#DBEAFE;color:#1d4ed8;">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/></svg>
            </span>
        </div>
        <div class="stat-value {{ $stats['contact_unread'] > 0 ? 'text-amber-600 dark:text-amber-300' : '' }}">{{ $stats['contact_unread'] }}</div>
        <a href="{{ route('admin.contacts.index',['filter'=>'unread']) }}" class="mt-2 inline-flex text-xs font-semibold text-[#2E7856] dark:text-[#6FB393] hover:underline underline-offset-4">Open inbox</a>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-5 mt-6">
    <div class="lg:col-span-2 space-y-5">
        <div class="panel-card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold tracking-tight">Recent Posts</h3>
                <a href="{{ route('admin.posts.index') }}" class="text-xs font-semibold text-[#2E7856] dark:text-[#6FB393] hover:underline underline-offset-4">View all</a>
            </div>
            <div class="divide-y divide-[#eef0f4] dark:divide-[#22262e]">
                @forelse($recentPosts as $post)
                    <div class="flex items-center gap-3 py-3">
                        @if($post->featured_image)
                            <img src="{{ str_starts_with($post->featured_image,'http') ? $post->featured_image : '/storage/'.$post->featured_image }}" class="w-11 h-11 object-cover rounded-lg border border-[#eef0f4] dark:border-[#2c313c] bg-[#f1f3f7] dark:bg-[#1c1f26]" alt="" loading="lazy" decoding="async" onerror="this.onerror=null;this.style.visibility='hidden'">
                        @else
                            <div class="w-11 h-11 rounded-lg bg-[#E9F2EE] dark:bg-[#2E7856]/10 text-[#2E7856] dark:text-[#6FB393] flex items-center justify-center">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('admin.posts.edit',$post) }}" class="text-sm font-semibold truncate block hover:text-[#2E7856] dark:hover:text-[#6FB393]">{{ $post->title }}</a>
                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $post->category->name ?? 'Uncategorized' }} · {{ number_format($post->views) }} views · {{ $post->published_at?->format('M d') ?? 'Not published' }}</div>
                        </div>
                        <span class="badge {{ $post->status=='published' ? 'badge-green' : 'badge-slate' }}">{{ $post->status }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400 py-4">No posts yet.</p>
                @endforelse
            </div>
        </div>

        <div class="panel-card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold">Recent Comments</h3>
                <a href="{{ route('admin.comments.index') }}" class="text-xs font-semibold text-[#1F513A] dark:text-[#6FB393] hover:underline">View all</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($recentComments as $c)
                    <div class="py-3">
                        <div class="flex flex-wrap items-center gap-2 text-sm">
                            @if($c->parent_id)
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m15 10 5 5-5 5"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v7a4 4 0 0 0 4 4h12"/></svg>
                            @endif
                            <span class="font-semibold">{{ $c->name }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">on {{ Str::limit($c->post->title ?? '-', 30) }}</span>
                            <span class="ml-auto text-[11px] font-semibold px-2 py-0.5 {{ $c->status=='pending' ? 'badge-amber' : ($c->status=='approved' ? 'badge-green' : 'badge-slate') }}">{{ $c->status }}</span>
                        </div>
                        <div class="text-sm text-slate-600 dark:text-slate-300 mt-1 line-clamp-2">{{ Str::limit($c->content, 140) }}</div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400 py-4">No comments yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="space-y-5">
        <div class="panel-card p-5">
            <h3 class="font-semibold text-slate-900 dark:text-white">Quick Actions</h3>
            <div class="mt-4 grid grid-cols-2 gap-2.5">
                <a href="{{ route('admin.posts.create') }}" class="group flex flex-col items-start gap-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40 p-3.5 hover:border-[#2E7856]/50 hover:bg-white dark:hover:bg-slate-800 transition">
                    <span class="icon-tile w-8 h-8 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                    </span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-[#27654A] dark:group-hover:text-[#6FB393] transition">New Post</span>
                </a>
                <a href="{{ route('admin.categories.create') }}" class="group flex flex-col items-start gap-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40 p-3.5 hover:border-[#2E7856]/50 hover:bg-white dark:hover:bg-slate-800 transition">
                    <span class="icon-tile w-8 h-8 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/></svg>
                    </span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-[#27654A] dark:group-hover:text-[#6FB393] transition">New Category</span>
                </a>
                <a href="{{ route('admin.settings.index') }}" class="group flex flex-col items-start gap-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40 p-3.5 hover:border-[#2E7856]/50 hover:bg-white dark:hover:bg-slate-800 transition">
                    <span class="icon-tile w-8 h-8 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-[#27654A] dark:group-hover:text-[#6FB393] transition">Settings</span>
                </a>
                <a href="{{ route('admin.profile.edit') }}" class="group flex flex-col items-start gap-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-800/40 p-3.5 hover:border-[#2E7856]/50 hover:bg-white dark:hover:bg-slate-800 transition">
                    <span class="icon-tile w-8 h-8 rounded-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-[#27654A] dark:group-hover:text-[#6FB393] transition">Profile</span>
                </a>
            </div>
        </div>

        <div class="panel-card p-5">
            <h3 class="font-semibold text-slate-900 dark:text-white">Automation</h3>
            <div class="mt-3 space-y-1.5 text-sm">
                <a href="{{ route('admin.social.index') }}" class="flex items-center gap-2.5 px-2 py-2 -mx-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-[#f2f4f8] dark:hover:bg-[#1c1f26] hover:text-[#101319] dark:hover:text-white transition">
                    <svg class="w-4 h-4 shrink-0 text-[#2E7856] dark:text-[#6FB393]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="m8.59 13.51 6.83 3.98m-.01-10.98-6.82 3.98"/></svg>
                    Social Auto-Post
                </a>
                <a href="{{ route('admin.ai.index') }}" class="flex items-center gap-2.5 px-2 py-2 -mx-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-[#f2f4f8] dark:hover:bg-[#1c1f26] hover:text-[#101319] dark:hover:text-white transition">
                    <svg class="w-4 h-4 shrink-0 text-[#2E7856] dark:text-[#6FB393]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                    AI Assistant
                </a>
                <a href="{{ route('admin.settings.index', ['tab' => 'integrations']) }}" class="flex items-center gap-2.5 px-2 py-2 -mx-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-[#f2f4f8] dark:hover:bg-[#1c1f26] hover:text-[#101319] dark:hover:text-white transition">
                    <svg class="w-4 h-4 shrink-0 text-[#2E7856] dark:text-[#6FB393]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0-6.75 6.75M12 4.5l6.75 6.75"/></svg>
                    Instant Indexing
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
