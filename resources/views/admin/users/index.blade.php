@extends('layouts.admin')
@section('title','Users')
@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Users'],
    ]])
@endsection

@section('content')
@php
    // One lookup shared by every row: how many ACTIVE admins exist right now.
    // The last active admin is protected against demotion, suspension and
    // deletion so the panel can never lock itself out.
    try {
        $activeAdmins = \Illuminate\Support\Facades\Schema::hasColumn('users', 'status')
            ? \App\Models\User::where('role', 'admin')->where('status', 'active')->count()
            : \App\Models\User::where('role', 'admin')->count();
    } catch (\Throwable $e) {
        $activeAdmins = \App\Models\User::where('role', 'admin')->count();
    }

    $roleTabs = [
        ''        => 'All roles',
        'admin'   => 'Admins',
        'author'  => 'Authors',
        'reader'  => 'Readers',
    ];
    $currentRole     = request('role', '');
    $currentStatus   = request('status', '');
    $currentVerified = request('verified', '');
@endphp

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 mb-5">
    @php
        $statCards = [
            ['label' => 'Total users', 'value' => $stats['total'],     'href' => route('admin.users.index'), 'active' => !$currentRole && !$currentStatus && !$currentVerified, 'tone' => 'brand', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>'],
            ['label' => 'Admins',      'value' => $stats['admins'],    'href' => route('admin.users.index', ['role' => 'admin']), 'active' => $currentRole === 'admin', 'tone' => 'ink', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>'],
            ['label' => 'Authors',     'value' => $stats['authors'],   'href' => route('admin.users.index', ['role' => 'author']), 'active' => $currentRole === 'author', 'tone' => 'brand', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>'],
            ['label' => 'Readers',     'value' => $stats['readers'],   'href' => route('admin.users.index', ['role' => 'reader']), 'active' => $currentRole === 'reader', 'tone' => 'blue', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>'],
            ['label' => 'Verified',    'value' => $stats['verified'],  'href' => route('admin.users.index', ['verified' => 'yes']), 'active' => $currentVerified === 'yes', 'tone' => 'green', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"/>'],
            ['label' => 'Suspended',   'value' => $stats['suspended'], 'href' => route('admin.users.index', ['status' => 'suspended']), 'active' => $currentStatus === 'suspended', 'tone' => 'red', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/>'],
        ];
    @endphp
    @foreach($statCards as $card)
        <a href="{{ $card['href'] }}" class="panel-card p-4 flex items-center gap-3 transition {{ $card['active'] ? 'ring-2 ring-[var(--brand)]/60 dark:ring-[var(--brand)]/50' : 'hover:-translate-y-0.5' }}">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 @if($card['tone']==='red') bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 @elseif($card['tone']==='green') bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 @elseif($card['tone']==='blue') bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400 @elseif($card['tone']==='ink') bg-[#16181d] dark:bg-white/10 text-white dark:text-slate-200 @else bg-[var(--brand-tint)] dark:bg-[var(--brand)]/10 text-[var(--brand)] dark:text-[var(--brand-light)] @endif">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">{!! $card['icon'] !!}</svg>
            </div>
            <div class="min-w-0">
                <div class="text-xl font-extrabold leading-none tabular-nums">{{ number_format($card['value']) }}</div>
                <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mt-1 truncate">{{ $card['label'] }}</div>
            </div>
        </a>
    @endforeach
</div>

{{-- Filters: role tabs + search + status + email + sort in one GET form --}}
<form method="GET" action="{{ route('admin.users.index') }}" id="users-filter" class="flex flex-wrap items-center gap-2 mb-4">
    <div class="flex flex-wrap items-center gap-1.5">
        @foreach($roleTabs as $key => $label)
            <button type="submit" name="role" value="{{ $key }}"
                    class="h-9 px-3.5 inline-flex items-center gap-2 text-[13px] font-semibold rounded-lg transition {{ $currentRole === $key ? 'bg-[#16181d] text-white dark:bg-white dark:text-[#101319]' : 'bg-white dark:bg-[#14171d] border border-[#e6e8ee] dark:border-[#2c313c] text-slate-600 dark:text-slate-300 hover:bg-[#f7f8fa] dark:hover:bg-[#1c1f26]' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>
    <div class="flex-1 min-w-[180px]"></div>
    <div class="relative">
        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, email, username"
               class="h-9 w-full sm:w-64 pl-9 pr-3 text-sm rounded-lg bg-white dark:bg-[#14171d] border border-[#e6e8ee] dark:border-[#2c313c] placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-[var(--brand)]/40">
    </div>
    <select name="status" onchange="document.getElementById('users-filter').submit()" title="Filter by account status"
            class="h-9 px-2.5 text-[13px] font-medium rounded-lg bg-white dark:bg-[#14171d] border border-[#e6e8ee] dark:border-[#2c313c] text-slate-600 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-[var(--brand)]/40">
        <option value="">All statuses</option>
        <option value="active" @selected($currentStatus === 'active')>Active</option>
        <option value="suspended" @selected($currentStatus === 'suspended')>Suspended</option>
    </select>
    <select name="verified" onchange="document.getElementById('users-filter').submit()" title="Filter by email verification"
            class="h-9 px-2.5 text-[13px] font-medium rounded-lg bg-white dark:bg-[#14171d] border border-[#e6e8ee] dark:border-[#2c313c] text-slate-600 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-[var(--brand)]/40">
        <option value="">Any email</option>
        <option value="yes" @selected($currentVerified === 'yes')>Verified email</option>
        <option value="no" @selected($currentVerified === 'no')>Unverified email</option>
    </select>
    <select name="sort" onchange="document.getElementById('users-filter').submit()" title="Sort users"
            class="h-9 px-2.5 text-[13px] font-medium rounded-lg bg-white dark:bg-[#14171d] border border-[#e6e8ee] dark:border-[#2c313c] text-slate-600 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-[var(--brand)]/40">
        <option value="newest" @selected($sort === 'newest')>Newest first</option>
        <option value="oldest" @selected($sort === 'oldest')>Oldest first</option>
        <option value="name" @selected($sort === 'name')>Name A to Z</option>
        <option value="posts" @selected($sort === 'posts')>Most posts</option>
    </select>
</form>

{{-- Bulk bar: select users with the checkboxes, then run one action on all --}}
<div id="users-bulk-bar" class="mb-3 flex flex-wrap items-center gap-2 bg-[#f8f9fb] dark:bg-[#14171d] border border-[#e6e8ee] dark:border-[#262a33] px-3 py-2">
    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 11 3 3L22 4"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
    <span class="text-sm text-slate-600 dark:text-slate-300"><strong id="bulk-count" class="text-[var(--brand-deep)] dark:text-[var(--brand-mid)]">0</strong> selected</span>
    <div class="flex-1"></div>
    <select id="bulk-action" form="users-bulk-form" name="bulk_action"
            class="h-8 px-2.5 text-[13px] font-medium rounded-lg bg-white dark:bg-[#14171d] border border-[#e6e8ee] dark:border-[#2c313c] text-slate-600 dark:text-slate-300 focus:outline-none">
        <option value="" selected>Choose an action</option>
        <optgroup label="Email">
            <option value="verify">Mark verified</option>
            <option value="unverify">Mark unverified</option>
        </optgroup>
        <optgroup label="Access">
            <option value="suspend">Suspend</option>
            <option value="unsuspend">Unsuspend</option>
        </optgroup>
        <optgroup label="Role">
            <option value="role_author">Set role to Author</option>
            <option value="role_reader">Set role to Reader</option>
        </optgroup>
        <optgroup label="Danger">
            <option value="delete">Delete accounts</option>
        </optgroup>
    </select>
    <button type="submit" form="users-bulk-form" disabled id="bulk-apply-btn"
            class="h-8 px-4 text-xs font-semibold inline-flex items-center gap-1.5 rounded-lg text-white bg-[var(--brand)] hover:bg-[var(--brand-strong)] dark:bg-[var(--brand-mid)] dark:hover:bg-[var(--brand)] dark:text-slate-900 transition disabled:opacity-40 disabled:cursor-not-allowed">
        Apply
    </button>
</div>
<form method="POST" action="{{ route('admin.users.bulk') }}" id="users-bulk-form">
    @csrf
</form>

<div class="panel-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table-pro">
            <thead>
                <tr>
                    <th class="w-10 !px-4">
                        <input type="checkbox" id="select-all-users" class="w-4 h-4 shrink-0 text-[var(--brand-strong)] border-slate-300 dark:border-slate-600" aria-label="Select all users on this page">
                    </th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Posts</th>
                    <th>Joined</th>
                    <th>Last login</th>
                    <th class="!text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    @php
                        $isSelf      = $user->id === auth()->id();
                        $isSuspended = ($user->status ?? 'active') === 'suspended';
                        $isLastAdmin = $user->role === 'admin' && !$isSuspended && $activeAdmins <= 1;
                    @endphp
                    <tr>
                        <td class="!px-4">
                            @if($isSelf)
                                <span class="inline-block w-6 h-4 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 text-[9px] font-bold leading-4 text-center" title="Your own account is managed from Profile">you</span>
                            @else
                                <input type="checkbox" name="ids[]" value="{{ $user->id }}" form="users-bulk-form" class="bulk-user-check w-4 h-4 shrink-0 text-[var(--brand-strong)] border-slate-300 dark:border-slate-600" aria-label="Select {{ $user->name }}">
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                @if($user->author_avatar_path)
                                    <img src="/storage/{{ $user->author_avatar_path }}" class="w-10 h-10 rounded-full object-cover bg-[#f1f3f7] dark:bg-[#1c1f26] border border-[#eef0f4] dark:border-[#2c313c]" alt="" loading="lazy" decoding="async" onerror="this.onerror=null;this.style.display='none'">
                                @elseif($user->avatar)
                                    <img src="{{ $user->avatar }}" class="w-10 h-10 rounded-full object-cover bg-[#f1f3f7] dark:bg-[#1c1f26] border border-[#eef0f4] dark:border-[#2c313c]" alt="" loading="lazy" decoding="async" onerror="this.onerror=null;this.style.display='none'">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-[var(--brand-tint)] dark:bg-[var(--brand)]/10 text-[var(--brand)] dark:text-[var(--brand-light)] flex items-center justify-center font-bold text-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                @endif
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-semibold text-slate-900 dark:text-white truncate max-w-[200px]">{{ $user->name }}</span>
                                        @if($isSelf)<span class="badge badge-slate">you</span>@endif
                                        @if($user->role === 'admin')<span class="badge badge-ink">Admin</span>@endif
                                        @if($user->email_verified_at)
                                            <svg class="w-3.5 h-3.5 text-green-600 dark:text-green-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-label="Email verified"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-400 dark:text-slate-500 truncate max-w-[240px]">{{ $user->email }}@if($user->username) · {{ '@'.$user->username }}@endif</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($isSelf || $isLastAdmin)
                                <span class="text-[13px] font-semibold capitalize {{ $isLastAdmin ? 'text-slate-400 dark:text-slate-500' : 'text-slate-600 dark:text-slate-300' }}"
                                      @if($isLastAdmin) title="The only active admin cannot be changed. Promote another admin first." @endif>{{ $user->role }}@if($isLastAdmin)<svg class="w-3.5 h-3.5 inline-block ml-1 align-text-bottom" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>@endif</span>
                            @else
                                <form method="POST" action="{{ route('admin.users.role', $user) }}">
                                    @csrf @method('PATCH')
                                    <select name="role" onchange="this.form.submit()" title="Change role"
                                            class="h-8 px-2 text-[13px] font-medium capitalize rounded-lg bg-white dark:bg-[#14171d] border border-[#e6e8ee] dark:border-[#2c313c] text-slate-700 dark:text-slate-200 hover:border-[var(--brand)]/50 focus:outline-none focus:ring-2 focus:ring-[var(--brand)]/40 cursor-pointer">
                                        <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                        <option value="author" @selected($user->role === 'author')>Author</option>
                                        <option value="reader" @selected($user->role === 'reader')>Reader</option>
                                    </select>
                                </form>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $isSuspended ? 'badge-red' : 'badge-green' }}">{{ $isSuspended ? 'suspended' : 'active' }}</span>
                        </td>
                        <td class="tabular-nums whitespace-nowrap">
                            {{ number_format($user->posts_count) }}
                            @if(!$isSelf && $user->posts_count > 0)
                                <form method="POST" action="{{ route('admin.users.reassign', $user) }}" class="inline">
                                    @csrf
                                    <button type="submit" title="Reassign their {{ number_format($user->posts_count) }} {{ $user->posts_count === 1 ? 'post' : 'posts' }} to you"
                                            onclick="return confirm(@js('Move '.$user->posts_count.' '.($user->posts_count === 1 ? 'post' : 'posts').' by '.$user->name.' to your account?'))"
                                            class="ml-1 w-6 h-6 rounded-md align-middle inline-flex items-center justify-center text-slate-400 hover:text-[var(--brand)] hover:bg-[var(--brand-tint)] dark:hover:bg-[var(--brand)]/10 transition">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="text-xs !text-slate-400 dark:!text-slate-500 whitespace-nowrap">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="text-xs !text-slate-400 dark:!text-slate-500 whitespace-nowrap">
                            @if($user->last_login_at)
                                <span title="{{ $user->last_login_at->format('M d, Y H:i') }}">{{ $user->last_login_at->diffForHumans() }}</span>
                            @else
                                <span class="italic">Never</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if(!$isSelf)
                                <div class="flex items-center justify-end gap-1">
                                    <form method="POST" action="{{ route('admin.users.verify', $user) }}">
                                        @csrf
                                        <button type="submit" title="{{ $user->email_verified_at ? 'Mark email as unverified' : 'Mark email as verified' }}"
                                                class="w-8 h-8 rounded-lg border {{ $user->email_verified_at ? 'border-green-200 dark:border-green-500/30 bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400' : 'border-[#e6e8ee] dark:border-[#2c313c] bg-white dark:bg-[#14171d] text-slate-400 dark:text-slate-500 hover:text-green-600' }} flex items-center justify-center transition">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.reset', $user) }}">
                                        @csrf
                                        <button type="submit" title="Send a password reset email"
                                                class="w-8 h-8 rounded-lg border border-[#e6e8ee] dark:border-[#2c313c] bg-white dark:bg-[#14171d] text-slate-600 dark:text-slate-300 hover:bg-[#f7f8fa] dark:hover:bg-[#1c1f26] flex items-center justify-center transition">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.status', $user) }}"
                                          onsubmit="@if(!$isSuspended && $isLastAdmin)alert('This is the only active admin. Promote another admin first.');return false;@else return confirm(@js($isSuspended ? 'Let '.$user->name.' sign in again?' : 'Suspend '.$user->name.'? They are signed out everywhere immediately.'))@endif">
                                        @csrf
                                        <button type="submit" title="{{ $isSuspended ? 'Remove suspension' : 'Suspend: signs them out everywhere' }}"
                                                class="w-8 h-8 rounded-lg border {{ $isSuspended ? 'border-green-200 dark:border-green-500/30 bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400' : 'border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-500' }} flex items-center justify-center transition">
                                            @if($isSuspended)
                                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="m16 11 2 2 4-4"/></svg>
                                            @else
                                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="m17 8 5 5m0-5-5 5"/></svg>
                                            @endif
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="@if($isLastAdmin)alert('This is the only active admin. Promote another admin first.');return false;@else return confirm(@js('Delete '.$user->name.' permanently?'.($user->posts_count > 0 ? ' Their '.$user->posts_count.' '.($user->posts_count === 1 ? 'post stays' : 'posts stay').' published without an author.' : '')))@endif">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Delete this user"
                                                class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-500/30 flex items-center justify-center">
                                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="flex items-center justify-end text-xs text-slate-400 dark:text-slate-500 pr-2">
                                    <a href="{{ route('admin.profile.edit') }}" class="text-[var(--brand-ink)] dark:text-[var(--brand-light)] hover:underline font-medium">Edit in Profile</a>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-10 text-center text-sm text-slate-500 dark:text-slate-400">
                            No users match the current filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-[#eef0f4] dark:border-[#22262e]">{{ $users->links() }}</div>
</div>

@push('scripts')
<script>
(function () {
    const form   = document.getElementById('users-bulk-form');
    const btn    = document.getElementById('bulk-apply-btn');
    const action = document.getElementById('bulk-action');
    const counter = document.getElementById('bulk-count');
    const selectAll = document.getElementById('select-all-users');
    if (!form || !btn || !counter) return;

    function refresh() {
        const checked = document.querySelectorAll('.bulk-user-check:checked').length;
        counter.textContent = checked;
        btn.disabled = checked === 0;
        if (selectAll) {
            const all = document.querySelectorAll('.bulk-user-check');
            selectAll.checked = all.length > 0 && checked === all.length;
        }
    }

    document.querySelectorAll('.bulk-user-check').forEach((cb) => {
        cb.addEventListener('change', refresh);
    });

    if (selectAll) {
        selectAll.addEventListener('change', () => {
            document.querySelectorAll('.bulk-user-check').forEach((cb) => {
                cb.checked = selectAll.checked;
            });
            refresh();
        });
    }

    form.addEventListener('submit', (e) => {
        if (document.querySelectorAll('.bulk-user-check:checked').length === 0) {
            e.preventDefault();
            return;
        }
        if (!action || !action.value) {
            e.preventDefault();
            return;
        }
        if (action.value === 'delete' && !confirm('Delete ALL selected users permanently? Their posts stay published without an author.')) {
            e.preventDefault();
        }
    });
})();
</script>
@endpush
@endsection
