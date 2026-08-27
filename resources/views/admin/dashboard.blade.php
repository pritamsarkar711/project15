@extends('layouts.admin')
@section('title','Dashboard')
@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => []])
@endsection

@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400">Posts</span>
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
        </div>
        <div class="text-3xl font-extrabold mt-2">{{ number_format($stats['posts']) }}</div>
        <a href="{{ route('admin.posts.index') }}" class="mt-2 inline-flex text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:underline">Manage posts</a>
    </div>
    <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400">Views</span>
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7Z"/></svg>
        </div>
        <div class="text-3xl font-extrabold mt-2">{{ number_format($stats['views']) }}</div>
        <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">All-time article views</div>
    </div>
    <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400">Pending Comments</span>
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.9 9.9 0 0 1-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z"/></svg>
        </div>
        <div class="text-3xl font-extrabold mt-2 {{ $stats['comments_pending'] > 0 ? 'text-amber-600 dark:text-amber-300' : '' }}">{{ $stats['comments_pending'] }}</div>
        <a href="{{ route('admin.comments.index',['status'=>'pending']) }}" class="mt-2 inline-flex text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:underline">Moderate</a>
    </div>
    <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold tracking-widest uppercase text-slate-500 dark:text-slate-400">Unread Messages</span>
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-300 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2Z"/></svg>
        </div>
        <div class="text-3xl font-extrabold mt-2 {{ $stats['contact_unread'] > 0 ? 'text-amber-600 dark:text-amber-300' : '' }}">{{ $stats['contact_unread'] }}</div>
        <a href="{{ route('admin.contacts.index',['filter'=>'unread']) }}" class="mt-2 inline-flex text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:underline">Open inbox</a>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-5 mt-6">
    <div class="lg:col-span-2 space-y-5">
        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold">Recent Posts</h3>
                <a href="{{ route('admin.posts.index') }}" class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($recentPosts as $post)
                    <div class="flex items-center gap-3 py-3">
                        @if($post->featured_image)
                            <img src="{{ str_starts_with($post->featured_image,'http') ? $post->featured_image : '/storage/'.$post->featured_image }}" class="w-11 h-11 object-cover bg-slate-100 dark:bg-slate-800" alt="" loading="lazy" decoding="async">
                        @else
                            <div class="w-11 h-11 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center justify-center">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('admin.posts.edit',$post) }}" class="text-sm font-semibold truncate block hover:text-emerald-700 dark:hover:text-emerald-300">{{ $post->title }}</a>
                            <div class="text-xs text-slate-500 dark:text-slate-400">{{ $post->category->name ?? 'Uncategorized' }} · {{ number_format($post->views) }} views · {{ $post->published_at?->format('M d') ?? 'Not published' }}</div>
                        </div>
                        <span class="text-[11px] font-semibold px-2 py-1 {{ $post->status=='published' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">{{ $post->status }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400 py-4">No posts yet.</p>
                @endforelse
            </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold">Recent Comments</h3>
                <a href="{{ route('admin.comments.index') }}" class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($recentComments as $c)
                    <div class="py-3">
                        <div class="flex items-center gap-2 text-sm">
                            @if($c->parent_id)
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m15 10 5 5-5 5"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v7a4 4 0 0 0 4 4h12"/></svg>
                            @endif
                            <span class="font-semibold">{{ $c->name }}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">on {{ Str::limit($c->post->title ?? '-', 30) }}</span>
                            <span class="ml-auto text-[11px] font-semibold px-2 py-0.5 {{ $c->status=='pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300' : ($c->status=='approved' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300') }}">{{ $c->status }}</span>
                        </div>
                        <div class="text-sm text-slate-600 dark:text-slate-300 mt-1 line-clamp-2">{{ Str::limit($c->content, 140) }}</div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400 py-4">No comments yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div>
        <div class="bg-[#0C3B2E] p-5 text-white">
            <h3 class="font-semibold">Quick Actions</h3>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <a href="{{ route('admin.posts.create') }}" class="bg-white text-[#0C3B2E] py-2.5 text-center text-sm font-semibold hover:bg-emerald-50 transition">New Post</a>
                <a href="{{ route('admin.categories.create') }}" class="bg-white/10 border border-white/20 py-2.5 text-center text-sm font-semibold hover:bg-white/20 transition">New Category</a>
                <a href="{{ route('admin.settings.index') }}" class="bg-white/10 border border-white/20 py-2.5 text-center text-sm font-semibold hover:bg-white/20 transition">Settings</a>
                <a href="{{ route('admin.profile.edit') }}" class="bg-white/10 border border-white/20 py-2.5 text-center text-sm font-semibold hover:bg-white/20 transition">Profile</a>
            </div>
        </div>
    </div>
</div>
@endsection
