@extends('frontend.author-dashboard.layout')

@section('title', 'Profile')

@section('content')
<form method="POST" action="{{ route('author.profile.update') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf

    {{-- Identity --}}
    <div class="panel-card p-6">
        <h2 class="font-semibold mb-4 text-slate-900 dark:text-white">Profile</h2>
        <div class="grid lg:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Display name *</label>
                <input type="text" name="name" required value="{{ old('name', $user->name) }}" maxlength="60"
                    class="w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-sm text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Role / title</label>
                <input type="text" name="role_title" value="{{ old('role_title', $user->role_title) }}" maxlength="60" placeholder="Tech writer"
                    class="w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-sm text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Username</label>
                @if($user->username)
                    <div class="flex items-center gap-2">
                        <input type="text" value="{{ $user->username }}" disabled
                            class="flex-1 h-11 px-3 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-500 dark:text-slate-400 font-mono">
                        <span class="text-xs text-slate-500">Locked</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Your author page is at <a class="break-all" href="{{ route('author.profile', $user->username) }}" class="text-[#1F513A] dark:text-[#6FB393] hover:underline" target="_blank">{{ url('/author/'.$user->username) }}</a></p>
                @else
                    <input type="text" name="username" required value="{{ old('username') }}" minlength="3" maxlength="30" pattern="[a-zA-Z0-9._\-]+" autocomplete="off"
                        class="w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-sm font-mono text-slate-900 dark:text-white"
                        title="Letters, numbers, dot, underscore, hyphen only"
                        placeholder="e.g. joe-goldberg">
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">Permanent — can’t be changed later.</p>
                @endif
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Avatar</label>
                <div class="flex items-center gap-3">
                    @if($user->author_avatar_path)
                        <img src="{{ '/storage/'.$user->author_avatar_path }}" class="w-14 h-14 rounded-full object-cover border border-slate-200 dark:border-slate-700" alt="" loading="lazy">
                    @endif
                    <label class="flex-1 cursor-pointer border border-dashed border-slate-300 dark:border-slate-600 py-3 text-center text-sm text-slate-500 dark:text-slate-400 hover:border-[#2E7856] hover:text-[#1F513A] dark:hover:text-[#6FB393] transition">
                        Upload photo
                        <input type="file" name="avatar" accept="image/*" class="hidden">
                    </label>
                </div>
            </div>
            <div class="lg:col-span-2">
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Bio *</label>
                <textarea name="bio" required rows="3" maxlength="600" placeholder="Tell readers who you are and what you write about"
                    class="w-full p-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-sm text-slate-900 dark:text-white">{{ old('bio', $user->bio) }}</textarea>
            </div>
            <div class="lg:col-span-2">
                <label class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5">Portfolio URL</label>
                <input type="url" name="portfolio_url" value="{{ old('portfolio_url', $user->portfolio_url) }}" maxlength="255" placeholder="https://"
                    class="w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-sm text-slate-900 dark:text-white">
            </div>
            {{-- Country picker: tooltip on the label explains what it does; the
                 selected flag icon previews live next to the select. The saved
                 country shows up (with its flag) on the public author profile,
                 the post byline and the author box. --}}
            <div>
                <label for="country-select" class="block text-sm font-semibold text-slate-900 dark:text-white mb-1.5 inline-flex items-center gap-1.5 cursor-help" title="Shown on your profile and post byline.">
                    Country
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 16v-4"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01"/></svg>
                </label>
                <div class="flex items-center gap-2">
                    <img id="country-flag-preview" src="{{ \App\Support\Countries::flagUrl(old('country', $user->country)) }}" alt=""
                        class="{{ \App\Support\Countries::flagUrl(old('country', $user->country)) ? '' : 'hidden' }} w-6 h-4 object-cover border border-slate-200 dark:border-slate-600 shrink-0" loading="lazy" decoding="async">
                    <select id="country-select" name="country"
                        class="flex-1 h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 outline-none text-sm text-slate-900 dark:text-white">
                        <option value="">Not specified</option>
                        @foreach(\App\Support\Countries::ALL as $code => $countryName)
                            <option value="{{ $code }}" @selected(strtoupper(old('country', $user->country)) === $code)>{{ $countryName }}</option>
                        @endforeach
                    </select>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Optional. Shown with a flag icon.</p>
            </div>
        </div>
        <button type="submit" class="mt-5 h-11 px-6 rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white font-semibold text-sm transition">Save profile</button>
    </div>

    {{-- Social links --}}
    <div class="panel-card p-6">
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

{{-- Live flag preview: picking a country in the dropdown instantly shows its
     flag icon next to the select (and hides it for "Not specified"). --}}
<script>
(function(){
    var select = document.getElementById('country-select');
    var flag = document.getElementById('country-flag-preview');
    if (!select || !flag) return;
    select.addEventListener('change', function(){
        var code = (select.value || '').toLowerCase();
        if (/^[a-z]{2}$/.test(code)) {
            flag.src = 'https://flagcdn.com/w40/' + code + '.png';
            flag.classList.remove('hidden');
        } else {
            flag.classList.add('hidden');
            flag.removeAttribute('src');
        }
    });
})();
</script>

{{-- Two factor authentication --}}
<div class="panel-card p-6 mt-6">
    <h3 class="font-semibold mb-3 text-slate-900 dark:text-white">Two Factor Authentication</h3>

    @if(session('author_2fa_setup_secret') && session('author_2fa_setup_qr'))
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-5 items-start">
                <div class="p-3 bg-white border border-slate-200 dark:border-slate-700 shrink-0">
                    <img src="{{ session('author_2fa_setup_qr') }}" alt="2FA QR code" class="w-[180px] h-[180px]" loading="lazy" decoding="async">
                </div>
                <div class="space-y-3">
                    <p class="text-sm font-medium">1. Scan the QR code with Google Authenticator or any TOTP app.</p>
                    <div>
                        <p class="text-sm font-medium">2. Or enter this secret manually:</p>
                        <code class="block mt-1 px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono break-all">{{ session('author_2fa_setup_secret') }}</code>
                    </div>
                    <p class="text-sm font-medium">3. Enter the 6 digit code from the app to confirm:</p>
                </div>
            </div>
            <form method="POST" action="{{ route('author.2fa.confirm') }}" class="flex flex-wrap gap-2">
                @csrf
                <input type="text" name="two_factor_code" inputmode="numeric" maxlength="6" required placeholder="123456" class="h-11 w-40 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono tracking-widest text-center placeholder:tracking-normal placeholder:font-sans">
                <button type="submit" class="h-11 px-6 rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white font-semibold text-sm transition">Confirm & Enable</button>
                <button type="submit" formaction="{{ route('author.2fa.disable') }}" formnovalidate class="h-11 px-6 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 font-semibold text-sm transition">Cancel setup</button>
            </form>
        </div>
    @elseif($user->google2fa_secret)
        <div class="p-4 bg-[#F0F7F3] dark:bg-[#2E7856]/10 border border-[#C7E0D4] dark:border-[#2E7856]/30 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-sm font-semibold text-[#173A2A] dark:text-[#6FB393]">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 11 3 3L22 4"/></svg>
                Enabled. A 6 digit code is required at login.
            </div>
            <form method="POST" action="{{ route('author.2fa.disable') }}">@csrf
                <button type="submit" class="h-9 px-4 text-sm font-semibold border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-800 transition">Disable 2FA</button>
            </form>
        </div>
    @else
        <div class="p-4 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-3">
            <div>
                <span class="text-sm font-semibold text-slate-600 dark:text-slate-300 block">Disabled</span>
                <span class="text-xs text-slate-500 dark:text-slate-400">Extra protection for your account.</span>
            </div>
            <form method="POST" action="{{ route('author.2fa.start') }}">@csrf
                <button type="submit" class="h-9 px-4 rounded-lg bg-[#2E7856] hover:bg-[#27654A] text-white text-sm font-semibold transition">Enable 2FA</button>
            </form>
        </div>
    @endif
</div>

{{-- Account deletion --}}
<div class="border border-red-200 dark:border-red-500/30 bg-white dark:bg-slate-900 p-6 mt-6">
    <h3 class="font-bold text-red-800 dark:text-red-300 text-base">Delete account</h3>
    <p class="text-sm text-red-700 dark:text-red-400 mt-1">Permanent. Drafts are deleted; published posts stay online.</p>
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
