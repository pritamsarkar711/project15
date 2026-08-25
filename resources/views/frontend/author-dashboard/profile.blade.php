@extends('frontend.author-dashboard.layout')

@section('title', 'Profile')

@section('content')
<div class="max-w-[760px]">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 mb-6">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Author profile</h2>
        <p class="text-sm text-slate-500 mt-1">This is what readers see on your author page and at the bottom of every published post.</p>

        <form method="POST" action="{{ route('author.profile.update') }}" enctype="multipart/form-data" class="space-y-5 mt-6">
            @csrf

            {{-- Display name --}}
            <div>
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Display name</label>
                <input type="text" name="name" required value="{{ old('name', $user->name) }}" maxlength="60"
                    class="w-full h-11 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-[#0C3B2E] focus:ring-4 focus:ring-[#0C3B2E]/15 outline-none text-sm text-slate-900 dark:text-white">
            </div>

            {{-- Username — locked once set --}}
            <div>
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Username <span class="text-slate-400 font-normal">(permanent)</span></label>
                @if($user->username)
                    <div class="flex items-center gap-2">
                        <input type="text" value="{{ $user->username }}" disabled
                            class="flex-1 h-11 px-3 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-500 dark:text-slate-400 font-mono">
                        <span class="text-xs text-slate-500">Locked</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Your author page is at <a href="{{ route('author.profile', $user->username) }}" class="text-[#0C3B2E] dark:text-emerald-300 hover:underline" target="_blank">{{ url('/author/'.$user->username) }}</a></p>
                @else
                    <input type="text" name="username" required value="{{ old('username') }}" minlength="3" maxlength="30" pattern="[a-zA-Z0-9._\-]+" autocomplete="off"
                        class="w-full h-11 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-[#0C3B2E] focus:ring-4 focus:ring-[#0C3B2E]/15 outline-none text-sm font-mono text-slate-900 dark:text-white"
                        placeholder="e.g. pritam.sarkar">
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">Set this carefully: once chosen it can never be changed. Lowercase letters, numbers, dot, underscore and hyphen only. Min 3, max 30.</p>
                @endif
            </div>

            {{-- Role/title --}}
            <div>
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Role / title <span class="text-slate-400 font-normal">(optional)</span></label>
                <input type="text" name="role_title" value="{{ old('role_title', $user->role_title) }}" maxlength="60" placeholder="e.g. Tech writer"
                    class="w-full h-11 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-[#0C3B2E] focus:ring-4 focus:ring-[#0C3B2E]/15 outline-none text-sm text-slate-900 dark:text-white">
            </div>

            {{-- Bio --}}
            <div>
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Bio <span class="text-slate-400 font-normal">(optional)</span></label>
                <textarea name="bio" rows="3" maxlength="600" class="w-full p-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-[#0C3B2E] focus:ring-4 focus:ring-[#0C3B2E]/15 outline-none text-sm text-slate-900 dark:text-white">{{ old('bio', $user->bio) }}</textarea>
            </div>

            {{-- Avatar --}}
            <div>
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Avatar <span class="text-slate-400 font-normal">(auto-optimised, max 4MB)</span></label>
                <input type="file" name="avatar" accept="image/*" class="w-full text-sm file:mr-3 file:py-2 file:px-4 file:border-0 file:bg-[#0C3B2E] file:text-white file:font-semibold file:text-xs file:cursor-pointer">
                @if($user->author_avatar_path)
                    <div class="mt-2 flex items-center gap-2">
                        <img src="{{ '/storage/'.$user->author_avatar_path }}" class="w-12 h-12 object-cover" alt="" loading="lazy">
                        <span class="text-xs text-slate-500">Current avatar</span>
                    </div>
                @endif
            </div>

            {{-- Portfolio --}}
            <div>
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Portfolio URL <span class="text-slate-400 font-normal">(optional)</span></label>
                <input type="url" name="portfolio_url" value="{{ old('portfolio_url', $user->portfolio_url) }}" maxlength="255" placeholder="https://"
                    class="w-full h-11 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-[#0C3B2E] focus:ring-4 focus:ring-[#0C3B2E]/15 outline-none text-sm text-slate-900 dark:text-white">
            </div>

            {{-- Social links --}}
            <details class="bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 p-4">
                <summary class="cursor-pointer text-sm font-semibold text-slate-900 dark:text-white">Social links (leave blank to hide)</summary>
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @php $existingSocials = $user->social_links ?? []; @endphp
                    @foreach(['x', 'facebook', 'linkedin', 'instagram', 'youtube', 'pinterest', 'whatsapp', 'telegram'] as $platform)
                        <div>
                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1 capitalize">{{ $platform }}</label>
                            <input type="url" name="social_links[{{ $platform }}]" value="{{ $existingSocials[$platform] ?? '' }}" maxlength="255" placeholder="https://{{ $platform }}.com/..."
                                class="w-full h-10 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-sm text-slate-900 dark:text-white">
                        </div>
                    @endforeach
                </div>
            </details>

            <div class="pt-2">
                <button type="submit" class="h-11 px-5 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold text-sm">Save profile</button>
            </div>
        </form>
    </div>

    {{-- Account deletion --}}
    <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 p-6">
        <h3 class="font-bold text-red-800 dark:text-red-300 text-base">Delete account</h3>
        <p class="text-sm text-red-700 dark:text-red-400 mt-1 leading-relaxed">This is permanent. Your drafts and returned posts are deleted. Your published posts stay on the site but show "Former author" as the author. This cannot be undone.</p>

        <form method="POST" action="{{ route('author.account.delete') }}" class="mt-4 space-y-3" onsubmit="return confirm('This is the final step. Your account and drafts will be permanently deleted. Continue?')">
            @csrf
            <label class="flex items-start gap-2 text-sm text-red-700 dark:text-red-300">
                <input type="checkbox" name="confirm" value="1" required class="mt-0.5 w-5 h-5 border-red-300 text-red-600">
                <span>I understand this action is irreversible.</span>
            </label>
            <div>
                <label class="block text-xs font-medium text-red-700 dark:text-red-300 mb-1">Confirm with your password</label>
                <input type="password" name="password" required autocomplete="current-password" class="w-full h-11 px-3 bg-white dark:bg-slate-900 border border-red-200 dark:border-red-500/30 outline-none text-sm text-slate-900 dark:text-white">
            </div>
            <button type="submit" class="h-10 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold text-sm">Delete my account</button>
        </form>
    </div>
</div>
@endsection
