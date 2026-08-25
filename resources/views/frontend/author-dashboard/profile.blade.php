@extends('frontend.author-dashboard.layout')

@section('title', 'Profile')

@section('content')
<form method="POST" action="{{ route('author.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf

    {{-- Identity --}}
    <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
        <h2 class="font-semibold mb-4 text-slate-900 dark:text-white">Profile</h2>
        <div class="grid lg:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Display name *</label>
                <input type="text" name="name" required value="{{ old('name', $user->name) }}" maxlength="60"
                    class="w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 outline-none text-sm text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Role / title</label>
                <input type="text" name="role_title" value="{{ old('role_title', $user->role_title) }}" maxlength="60" placeholder="Tech writer"
                    class="w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 outline-none text-sm text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Username</label>
                @if($user->username)
                    <div class="flex items-center gap-2">
                        <input type="text" value="{{ $user->username }}" disabled
                            class="flex-1 h-11 px-3 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-500 dark:text-slate-400 font-mono">
                        <span class="text-xs text-slate-500">Locked</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Your author page is at <a href="{{ route('author.profile', $user->username) }}" class="text-emerald-700 dark:text-emerald-300 hover:underline" target="_blank">{{ url('/author/'.$user->username) }}</a></p>
                @else
                    <input type="text" name="username" required value="{{ old('username') }}" minlength="3" maxlength="30" pattern="[a-zA-Z0-9._\-]+" autocomplete="off"
                        class="w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 outline-none text-sm font-mono text-slate-900 dark:text-white"
                        placeholder="e.g. joe-goldberg">
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">Choose carefully: this is permanent. Lowercase letters, numbers, dot, underscore and hyphen only.</p>
                @endif
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Avatar</label>
                <div class="flex items-center gap-3">
                    @if($user->author_avatar_path)
                        <img src="{{ '/storage/'.$user->author_avatar_path }}" class="w-14 h-14 rounded-full object-cover border border-slate-200 dark:border-slate-700" alt="" loading="lazy">
                    @endif
                    <label class="flex-1 cursor-pointer border border-dashed border-slate-300 dark:border-slate-600 py-3 text-center text-sm text-slate-500 dark:text-slate-400 hover:border-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-300 transition">
                        Upload photo
                        <input type="file" name="avatar" accept="image/*" class="hidden">
                    </label>
                </div>
            </div>
            <div class="lg:col-span-2">
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Bio *</label>
                <textarea name="bio" required rows="3" maxlength="600" placeholder="Tell readers who you are and what you write about"
                    class="w-full p-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 outline-none text-sm text-slate-900 dark:text-white">{{ old('bio', $user->bio) }}</textarea>
            </div>
            <div class="lg:col-span-2">
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Portfolio URL</label>
                <input type="url" name="portfolio_url" value="{{ old('portfolio_url', $user->portfolio_url) }}" maxlength="255" placeholder="https://"
                    class="w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/15 outline-none text-sm text-slate-900 dark:text-white">
            </div>
        </div>
        <button type="submit" class="mt-5 h-11 px-6 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold text-sm transition">Save profile</button>
    </div>

    {{-- Social links --}}
    <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
        <h2 class="font-semibold mb-4 text-slate-900 dark:text-white">Social links</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @php $existingSocials = $user->social_links ?? []; @endphp
            @foreach(['x', 'facebook', 'linkedin', 'instagram', 'youtube', 'pinterest', 'whatsapp', 'telegram'] as $platform)
                <div>
                    <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1 capitalize">{{ $platform }}</label>
                    <input type="url" name="social_links[{{ $platform }}]" value="{{ $existingSocials[$platform] ?? '' }}" maxlength="255" placeholder="https://"
                        class="w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-sm text-slate-900 dark:text-white">
                </div>
            @endforeach
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-3">Leave blank to hide an icon.</p>
    </div>
</form>

{{-- Account deletion --}}
<div class="border border-red-200 dark:border-red-500/30 bg-white dark:bg-slate-900 p-6 mt-6">
    <h3 class="font-bold text-red-800 dark:text-red-300 text-base">Delete account</h3>
    <p class="text-sm text-red-700 dark:text-red-400 mt-1">This is permanent. Drafts are removed. Published posts stay online under a former author name.</p>
    <form method="POST" action="{{ route('author.account.delete') }}" class="mt-4 space-y-3" onsubmit="return confirm('Delete your account permanently?')">
        @csrf
        <label class="flex items-start gap-2 text-sm text-red-700 dark:text-red-300">
            <input type="checkbox" name="confirm" value="1" required class="mt-0.5 w-5 h-5">
            <span>I understand this cannot be undone.</span>
        </label>
        <div>
            <label class="block text-xs font-medium text-red-700 dark:text-red-300 mb-1">Confirm with your password</label>
            <input type="password" name="password" required autocomplete="current-password" class="w-full sm:max-w-sm h-11 px-3 bg-white dark:bg-slate-900 border border-red-200 dark:border-red-500/30 outline-none text-sm text-slate-900 dark:text-white">
        </div>
        <button type="submit" class="h-10 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold text-sm">Delete my account</button>
    </form>
</div>
@endsection
