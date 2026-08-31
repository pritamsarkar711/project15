@extends('layouts.admin')
@section('title','Profile')
@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Profile'],
    ]])
@endsection

@section('content')
<div class="w-full space-y-6">
    {{-- Author profile --}}
    <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="panel-card p-6 space-y-5">
        @csrf
        <h3 class="font-semibold text-slate-900 dark:text-white">Author profile</h3>
        <div class="flex items-center gap-4">
            @if($user->author_avatar_path)
                <img id="avatar-preview" src="{{ '/storage/'.$user->author_avatar_path }}" class="w-20 h-20 rounded-full object-cover border border-slate-200 dark:border-slate-700" alt="" loading="lazy" decoding="async">
            @else
                <img id="avatar-preview" src="#" alt="" class="hidden w-20 h-20 rounded-full object-cover border border-slate-200 dark:border-slate-700" loading="lazy" decoding="async">
                <div id="avatar-initial" class="w-20 h-20 rounded-full bg-[#0C3B2E] text-white flex items-center justify-center text-2xl font-extrabold">{{ strtoupper(substr($user->name,0,1)) }}</div>
            @endif
            <div>
                <label class="cursor-pointer inline-flex items-center gap-2 h-10 px-4 text-sm font-semibold border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 3h6m0 0v6m0-6L10 14M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                    Upload avatar
                    <input type="file" name="avatar" accept="image/*" class="hidden" onchange="previewAvatar(this)">
                </label>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1.5">Auto-optimized WebP.</p>
            </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Display name *</label>
                <input type="text" name="name" required value="{{ old('name', $user->name) }}" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Username</label>
                @if($user->username)
                    <input type="text" value="{{ $user->username }}" disabled class="mt-1 w-full h-10 px-3 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-500">
                    <p class="text-xs text-slate-400 mt-1">Locked. Profile at <code>/{{ $user->username }}</code></p>
                @else
                    <input type="text" name="username" value="{{ old('username') }}" pattern="[a-z0-9._-]+" placeholder="pritam.sarkar" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    <p class="text-xs text-slate-400 mt-1">URL: /your-username. Cannot be changed.</p>
                @endif
            </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Role / title</label>
                <input type="text" name="role_title" value="{{ old('role_title', $user->role_title) }}" placeholder="Editor-in-Chief" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Portfolio URL</label>
                <input type="url" name="portfolio_url" value="{{ old('portfolio_url', $user->portfolio_url) }}" placeholder="https://your-portfolio.com" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
            </div>
        </div>
        <div>
            <label class="text-sm font-medium">Bio</label>
            <textarea name="bio" rows="3" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">{{ old('bio', $user->bio) }}</textarea>
        </div>
        <div class="border-t border-slate-200 dark:border-slate-700 pt-4">
            <p class="text-sm font-medium mb-1">Social links</p>
            <p class="text-xs text-slate-400 mb-3">Shown in the About-the-author card on your posts.</p>
            <div class="grid sm:grid-cols-2 gap-3">
                @php
                    $rawLinks = $user->social_links;
                    $existing = is_array($rawLinks) ? $rawLinks : (json_decode((string)$rawLinks, true) ?: []);
                    $platforms = [
                        'x' => 'X (Twitter)',
                        'facebook' => 'Facebook',
                        'linkedin' => 'LinkedIn',
                        'instagram' => 'Instagram',
                        'youtube' => 'YouTube',
                        'pinterest' => 'Pinterest',
                        'github' => 'GitHub',
                        'website' => 'Website',
                    ];
                @endphp
                @foreach($platforms as $key => $label)
                    <div>
                        <label class="text-xs font-medium text-slate-600 dark:text-slate-400">{{ $label }}</label>
                        <input type="url" name="social_links[{{ $key }}]" value="{{ $existing[$key] ?? '' }}" placeholder="https://..." class="mt-1 w-full h-9 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    </div>
                @endforeach
            </div>
        </div>
        <button type="submit" class="h-11 px-6 rounded-lg bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold transition">Save profile</button>
    </form>

    {{-- Login credentials --}}
    <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="panel-card p-6 space-y-5">
        @csrf
        <input type="hidden" name="name" value="{{ $user->name }}">
        <input type="hidden" name="bio" value="{{ $user->bio }}">
        <h3 class="font-semibold text-slate-900 dark:text-white">Login credentials</h3>
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="text-sm font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
            </div>
            <div>
                <label class="text-sm font-medium">Current password *</label>
                <input type="password" name="current_password" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm" autocomplete="current-password">
            </div>
            <div>
                <label class="text-sm font-medium">New password</label>
                <input type="password" name="password" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm" autocomplete="new-password">
            </div>
            <div>
                <label class="text-sm font-medium">Confirm</label>
                <input type="password" name="password_confirmation" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm" autocomplete="new-password">
            </div>
        </div>
        <button type="submit" class="h-11 px-6 rounded-lg bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold transition">Update credentials</button>
    </form>
</div>
@push('scripts')
<script>
function previewAvatar(input){
    if(input.files && input.files[0]){
        const img = document.getElementById('avatar-preview');
        img.src = URL.createObjectURL(input.files[0]);
        img.classList.remove('hidden');
        document.getElementById('avatar-initial')?.classList.add('hidden');
    }
}
</script>
@endpush
@endsection
