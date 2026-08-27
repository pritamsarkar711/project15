@extends('layouts.admin')
@section('title','Settings')
@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Settings'],
    ]])
@endsection

@section('content')
<div class="flex items-center gap-1.5 mb-5 flex-wrap">
    @foreach(['general' => 'General', 'appearance' => 'Appearance & Fonts', 'hero' => 'Hero Section', 'ads' => 'Ad Placement', 'email' => 'Email / SMTP', 'integrations' => 'Integrations & SEO'] as $key => $label)
        <a href="{{ route('admin.settings.index', $key !== 'general' ? ['tab'=>$key] : []) }}"
           class="h-9 px-4 inline-flex items-center text-sm font-medium border transition {{ (request('tab', 'general') === $key) ? 'bg-[#0C3B2E] text-white border-[#0C3B2E]' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">{{ $label }}</a>
    @endforeach
</div>

@if(request('tab') === 'integrations')
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5 w-full">
        @csrf
        <input type="hidden" name="tab" value="integrations">
        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <h3 class="font-semibold">Analytics & Verification</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">GA Measurement ID</label>
                    <input type="text" name="ga_measurement_id" value="{{ old('ga_measurement_id', $settings['ga_measurement_id']->value ?? '') }}" placeholder="G-XXXXXXXXXX" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono placeholder:font-sans">
                </div>
                <div>
                    <label class="text-sm font-medium">Search Console Token</label>
                    <input type="text" name="search_console_token" value="{{ old('search_console_token', $settings['search_console_token']->value ?? '') }}" placeholder="google-site-verification value" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono placeholder:font-sans">
                </div>
            </div>
            <div>
                <label class="text-sm font-medium">Ahrefs Verification Token</label>
                <input type="text" name="ahrefs_verification_token" value="{{ old('ahrefs_verification_token', $settings['ahrefs_verification_token']->value ?? '') }}" placeholder="ahrefs-site-verification value" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono placeholder:font-sans">
            </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <h3 class="font-semibold">ads.txt</h3>
            <textarea name="ads_txt_content" rows="5" placeholder="google.com, pub-0000000000000000, DIRECT, f08c47fec0942fa0" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono placeholder:font-sans">{{ old('ads_txt_content', $settings['ads_txt_content']->value ?? '') }}</textarea>
            <p class="text-xs text-slate-400 dark:text-slate-500">Served live at <a href="{{ url('ads.txt') }}" target="_blank" class="text-emerald-700 dark:text-emerald-300 hover:underline">{{ url('ads.txt') }}</a></p>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <h3 class="font-semibold">robots.txt</h3>
            <textarea name="robots_txt_content" rows="5" placeholder="User-agent: *
Disallow: /manage" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono placeholder:font-sans">{{ old('robots_txt_content', $settings['robots_txt_content']->value ?? '') }}</textarea>
            <p class="text-xs text-slate-400 dark:text-slate-500">Served live at <a href="{{ url('robots.txt') }}" target="_blank" class="text-emerald-700 dark:text-emerald-300 hover:underline">{{ url('robots.txt') }}</a></p>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <h3 class="font-semibold">llms.txt</h3>
            <textarea name="llms_txt_content" rows="5" placeholder="Extra markdown appended to the auto-generated llms.txt" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">{{ old('llms_txt_content', $settings['llms_txt_content']->value ?? '') }}</textarea>
            <p class="text-xs text-slate-400 dark:text-slate-500">Served live at <a href="{{ url('llms.txt') }}" target="_blank" class="text-emerald-700 dark:text-emerald-300 hover:underline">{{ url('llms.txt') }}</a></p>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold">Google Sign In</h3>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <span class="relative inline-flex shrink-0">
                        <input type="checkbox" name="google_enabled" value="1" {{ old('google_enabled', $settings['google_enabled']->value ?? '0') === '1' ? 'checked' : '' }} class="peer sr-only">
                        <span class="block w-11 h-6 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-[#0C3B2E] transition-colors"></span>
                        <span class="pointer-events-none absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                    </span>
                    <span class="text-sm font-medium {{ ($settings['google_enabled']->value ?? '0') === '1' ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-500' }}">{{ ($settings['google_enabled']->value ?? '0') === '1' ? 'Enabled' : 'Disabled' }}</span>
                </label>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Allow users to sign in and sign up with their Google account. Create credentials at <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener" class="text-emerald-700 dark:text-emerald-300 hover:underline">Google Cloud Console</a> and set the redirect URI to <span class="font-mono bg-slate-100 dark:bg-slate-800 px-1">{{ url('/auth/google/callback') }}</span></p>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Client ID</label>
                    <input type="text" name="google_client_id" value="{{ old('google_client_id', $settings['google_client_id']->value ?? '') }}" placeholder="123456...apps.googleusercontent.com" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono placeholder:font-sans">
                </div>
                <div>
                    <label class="text-sm font-medium">Client Secret</label>
                    <input type="password" name="google_client_secret" value="{{ old('google_client_secret', $settings['google_client_secret']->value ?? '') }}" placeholder="GOCSPX-..." class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono placeholder:font-sans">
                </div>
            </div>
        </div>

        <button type="submit" class="h-11 px-6 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold transition">Save Integrations</button>
    </form>

@elseif(request('tab') === 'appearance')
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5 w-full">
        @csrf
        <input type="hidden" name="tab" value="appearance">
        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <h3 class="font-semibold">Site Font</h3>
            <div>
                <label class="text-sm font-medium">Font family</label>
                <select name="site_font_family" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    @foreach($fontOptions as $key => $label)
                        <option value="{{ $key }}" {{ old('site_font_family', $settings['site_font_family']->value ?? 'inter') === $key ? 'selected' : '' }} style="font-family:{{ $fonts[$key]['css'] }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <button type="submit" class="h-11 px-6 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold transition">Save</button>
    </form>

@elseif(request('tab') === 'hero')
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-5 w-full">
        @csrf
        <input type="hidden" name="tab" value="hero">
        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <h3 class="font-semibold">Hero Headline</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Part 1</label>
                    <input type="text" name="hero_phrase_1" value="{{ old('hero_phrase_1', $settings['hero_phrase_1']->value ?? 'Explore Ideas.') }}" maxlength="80" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Part 2</label>
                    <input type="text" name="hero_phrase_2" value="{{ old('hero_phrase_2', $settings['hero_phrase_2']->value ?? 'Inspire Life.') }}" maxlength="80" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                </div>
            </div>
            <div>
                <label class="text-sm font-medium">Subtitle</label>
                <textarea name="hero_subtitle" rows="2" maxlength="500" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">{{ old('hero_subtitle', $settings['hero_subtitle']->value ?? 'Tech, health, finance, travel and more, all in one calm place to read.') }}</textarea>
            </div>
            <div>
                <label class="text-sm font-medium">Search placeholder</label>
                <input type="text" name="hero_search_placeholder" value="{{ old('hero_search_placeholder', $settings['hero_search_placeholder']->value ?? 'Search articles, topics, ideas...') }}" maxlength="120" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
            </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <h3 class="font-semibold">Hero Image</h3>
            <div class="grid sm:grid-cols-2 gap-5 items-start">
                <div>
                    <label class="text-sm font-medium">Upload</label>
                    <label for="hero_person_image_file" class="mt-2 flex items-center justify-center h-24 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg cursor-pointer hover:border-[#0C3B2E] hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                        <input type="file" name="hero_person_image_file" id="hero_person_image_file" accept="image/jpeg,image/png,image/gif,image/webp,image/bmp" class="hidden">
                        <div class="text-center" id="hero-upload-hint">
                            <svg class="w-6 h-6 mx-auto text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                            <p class="text-xs text-slate-500 mt-1">Click to upload</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">JPG, PNG, GIF, WebP or BMP · max 4 MB</p>
                        </div>
                    </label>
                    <p class="mt-2 text-xs font-medium text-emerald-700 dark:text-emerald-300 hidden" id="hero-file-name"></p>
                    <label class="mt-3 inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
                        <span class="relative inline-flex shrink-0">
                            <input type="checkbox" name="hero_remove_image" value="1" class="peer sr-only">
                            <span class="block w-11 h-6 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-[#0C3B2E] transition-colors"></span>
                            <span class="pointer-events-none absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                        </span>
                        Remove image (use default)
                    </label>
                </div>
                <div>
                    <label class="text-sm font-medium">Preview</label>
                    @php $heroImg = $settings['hero_person_image']->value ?? null; @endphp
                    <div class="mt-2 w-32 h-32 rounded-xl overflow-hidden bg-[#0C3B2E] flex items-center justify-center relative">
                        <img src="{{ $heroImg ? asset('storage/'.$heroImg) : asset('images/hero-person-harry.png') }}" alt="" class="w-full h-full object-cover" loading="lazy" decoding="async" id="hero-preview-img"
                             onerror="this.style.display='none'; var h=document.getElementById('hero-load-hint'); if(h){h.classList.remove('hidden');}">
                    </div>
                    <p id="hero-load-hint" class="mt-1.5 text-[11px] text-red-500 hidden">Saved but the file is not reachable. Open /deploy.php once and check that public/storage is writable.</p>
                </div>
            </div>
        </div>

        <button type="submit" class="h-11 px-6 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold transition">Save</button>
    </form>

@elseif(request('tab') === 'ads')
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5 w-full">
        @csrf
        <input type="hidden" name="tab" value="ads">
        {{-- Show ads on site: the master switch. Ads stay completely hidden
             (no boxes, no labels, no empty slots) until this is on. --}}
        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h3 class="font-semibold">Show ads on site</h3>
                </div>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <span class="relative inline-flex shrink-0">
                        <input type="checkbox" name="ads_enabled" value="1" {{ old('ads_enabled', $settings['ads_enabled']->value ?? '0') === '1' ? 'checked' : '' }} class="peer sr-only">
                        <span class="block w-11 h-6 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-[#0C3B2E] transition-colors"></span>
                        <span class="pointer-events-none absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                    </span>
                    <span class="text-sm font-semibold {{ ($settings['ads_enabled']->value ?? '0') === '1' ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-500 dark:text-slate-400' }}">{{ ($settings['ads_enabled']->value ?? '0') === '1' ? 'Enabled' : 'Disabled' }}</span>
                </label>
            </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <div>
                <h3 class="font-semibold">Ad Frequency</h3>
            </div>
            <div class="grid sm:grid-cols-2 gap-4 items-center">
                <div>
                    <label class="text-sm font-medium">Insert ad every N paragraphs</label>
                    <input type="number" name="ad_paragraph_frequency" min="1" max="10" value="{{ old('ad_paragraph_frequency', $settings['ad_paragraph_frequency']->value ?? '2') }}" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-2">Manage ads on the <a href="{{ route('admin.ads.index') }}" class="text-emerald-700 dark:text-emerald-300 hover:underline">Advertisements page</a>.</p>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <h3 class="font-semibold">Revenue program</h3>
                </div>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <span class="relative inline-flex shrink-0">
                        <input type="checkbox" name="revenue_enabled" value="1" {{ old('revenue_enabled', $settings['revenue_enabled']->value ?? '0') === '1' ? 'checked' : '' }} class="peer sr-only">
                        <span class="block w-11 h-6 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-[#0C3B2E] transition-colors"></span>
                        <span class="pointer-events-none absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                    </span>
                    <span class="text-sm font-semibold {{ ($settings['revenue_enabled']->value ?? '0') === '1' ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-500 dark:text-slate-400' }}">{{ ($settings['revenue_enabled']->value ?? '0') === '1' ? 'Enabled' : 'Disabled' }}</span>
                </label>
            </div>
        </div>
        <button type="submit" class="h-11 px-6 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold transition">Save Ad Settings</button>
    </form>

@elseif(request('tab') === 'email')
    @php
        $mailSettings = [
            'mail_mailer'       => $settings['mail_mailer']->value ?? config('mail.default'),
            'mail_host'         => $settings['mail_host']->value ?? config('mail.mailers.smtp.host'),
            'mail_port'         => $settings['mail_port']->value ?? config('mail.mailers.smtp.port'),
            'mail_username'     => $settings['mail_username']->value ?? config('mail.mailers.smtp.username'),
            'mail_password'     => $settings['mail_password']->value ?? '',
            'mail_encryption'   => $settings['mail_encryption']->value ?? (string) (config('mail.mailers.smtp.scheme') ?? 'tls'),
            'mail_from_address' => $settings['mail_from_address']->value ?? config('mail.from.address'),
            'mail_from_name'    => $settings['mail_from_name']->value ?? config('mail.from.name'),
        ];
        $passwordIsSet = !empty($settings['mail_password']->value);
    @endphp

    <form method="POST" action="{{ route('admin.settings.smtp.update') }}" class="space-y-5 w-full">
        @csrf

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <h3 class="font-semibold">Mailer</h3>
            <div>
                <label class="text-sm font-medium">Default mailer</label>
                <select name="mail_mailer" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    @foreach(['smtp' => 'SMTP', 'log' => 'Log file', 'sendmail' => 'Sendmail', 'array' => 'Array (test)'] as $value => $label)
                        <option value="{{ $value }}" {{ old('mail_mailer', $mailSettings['mail_mailer']) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

            </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <h3 class="font-semibold">SMTP Server</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="text-sm font-medium">Host</label>
                    <input type="text" name="mail_host" value="{{ old('mail_host', $mailSettings['mail_host']) }}" placeholder="smtp.gmail.com" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono placeholder:font-sans">
                </div>
                <div>
                    <label class="text-sm font-medium">Port</label>
                    <input type="number" name="mail_port" min="1" max="65535" value="{{ old('mail_port', $mailSettings['mail_port']) }}" placeholder="587" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono placeholder:font-sans">

                </div>
                <div>
                    <label class="text-sm font-medium">Encryption</label>
                    <select name="mail_encryption" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                        @foreach(['tls' => 'TLS (port 587)', 'ssl' => 'SSL (port 465)', 'none' => 'None (port 25)'] as $value => $label)
                            <option value="{{ $value === 'none' ? 'none' : $value }}" {{ old('mail_encryption', $mailSettings['mail_encryption']) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Username</label>
                    <input type="text" name="mail_username" value="{{ old('mail_username', $mailSettings['mail_username']) }}" placeholder="you@example.com" autocomplete="off" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono placeholder:font-sans">
                </div>
                <div>
                    <label class="text-sm font-medium">Password</label>
                    <input type="password" name="mail_password" value="" placeholder="{{ $passwordIsSet ? '••••••••• (configured, leave blank to keep)' : 'Paste SMTP password' }}" autocomplete="new-password" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono placeholder:font-sans">
                    <label class="mt-2 inline-flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" name="mail_remove_password" value="1" class="text-emerald-600">
                        Remove stored password
                    </label>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-1 pt-2 border-t border-slate-200 dark:border-slate-700">
                Gmail: use an <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener" class="text-emerald-700 dark:text-emerald-300 hover:underline">App Password</a>.
            </p>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <h3 class="font-semibold">From Address</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Email</label>
                    <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $mailSettings['mail_from_address']) }}" placeholder="noreply@huvanti.com" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Name</label>
                    <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $mailSettings['mail_from_name']) }}" placeholder="Huvanti" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                </div>
            </div>
        </div>

        <button type="submit" class="h-11 px-6 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold transition">Save SMTP Settings</button>
    </form>

    {{-- Test email form — separate POST so it doesn't conflict with the save form. --}}
    <form method="POST" action="{{ route('admin.settings.test-email') }}" class="border border-dashed border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 p-6 space-y-4 w-full">
        @csrf
        <h3 class="font-semibold">Test Email</h3>
        <div class="flex gap-3">
            <input type="email" name="test_email_to" value="" required placeholder="you@example.com" class="flex-1 h-10 px-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
            <button type="submit" class="h-10 px-5 bg-slate-700 dark:bg-slate-700 hover:bg-slate-800 dark:hover:bg-slate-600 text-white font-medium text-sm transition">Send test</button>
        </div>
    </form>

@else
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-5 w-full">
        @csrf
        <input type="hidden" name="tab" value="general">
        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <h3 class="font-semibold">Site</h3>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Name</label>
                    <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name']->value ?? 'Huvanti') }}" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Tagline</label>
                    <input type="text" name="site_tagline" value="{{ old('site_tagline', $settings['site_tagline']->value ?? '') }}" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                </div>
            </div>
            <div>
                <label class="text-sm font-medium">Meta description</label>
                <textarea name="site_description" rows="2" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">{{ old('site_description', $settings['site_description']->value ?? '') }}</textarea>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Keywords</label>
                    <input type="text" name="site_keywords" value="{{ old('site_keywords', $settings['site_keywords']->value ?? '') }}" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Contact email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email']->value ?? '') }}" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                </div>
            </div>
            <div>
                <label class="text-sm font-medium">Footer copyright</label>
                <input type="text" name="footer_copyright" value="{{ old('footer_copyright', $settings['footer_copyright']->value ?? '© {year} Huvanti. All Rights Reserved.') }}" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">Use {year} to show the current year automatically. Example: © {year} Huvanti. All Rights Reserved.</p>
            </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold">Social Links</h3>
                <label class="inline-flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300 cursor-pointer">
                    <input type="checkbox" name="social_enabled" value="1" {{ old('social_enabled', $settings['social_enabled']->value ?? '1') === '1' ? 'checked' : '' }} class="text-emerald-600">
                    Show in footer
                </label>
            </div>
            <div class="grid sm:grid-cols-2 gap-3">
                @php
                    $socialFields = [
                        'social_x' => 'X (Twitter)',
                        'social_facebook' => 'Facebook',
                        'social_pinterest' => 'Pinterest',
                        'social_linkedin' => 'LinkedIn',
                        'social_whatsapp' => 'WhatsApp',
                        'social_youtube' => 'YouTube',
                        'social_instagram' => 'Instagram',
                    ];
                @endphp
                @foreach($socialFields as $key => $label)
                    <div>
                        <label class="text-xs font-medium text-slate-600 dark:text-slate-400">{{ $label }}</label>
                        <input type="url" name="{{ $key }}" value="{{ old($key, $settings[$key]->value ?? '') }}" placeholder="https://..." class="mt-1 w-full h-9 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    </div>
                @endforeach
            </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <h3 class="font-semibold">Logo &amp; Favicon</h3>
            <div class="grid sm:grid-cols-3 gap-5">
                <div>
                    <label class="text-sm font-medium">Logo · Light</label>
                    <div class="mt-2 h-9 flex items-center bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2" id="logo-light-preview-wrap">
                        @if(!empty($settings['site_logo_light']->value))
                            <img src="{{ asset('storage/'.$settings['site_logo_light']->value) }}" class="h-7 w-auto" alt="" loading="lazy" decoding="async" id="logo-light-preview">
                        @else
                            <span class="text-xs text-slate-400" id="logo-light-preview">No logo uploaded yet</span>
                        @endif
                    </div>
                    <label class="mt-2 flex items-center justify-center h-16 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg cursor-pointer hover:border-[#0C3B2E] hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                        <input type="file" name="site_logo_light_file" accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml" class="hidden" data-preview="logo-light">
                        <span class="text-xs text-slate-500">Upload (JPG/PNG/WebP/SVG)</span>
                    </label>
                </div>
                <div>
                    <label class="text-sm font-medium">Logo · Dark</label>
                    <div class="mt-2 h-9 flex items-center bg-slate-900 border border-slate-700 px-2" id="logo-dark-preview-wrap">
                        @if(!empty($settings['site_logo_dark']->value))
                            <img src="{{ asset('storage/'.$settings['site_logo_dark']->value) }}" class="h-7 w-auto" alt="" loading="lazy" decoding="async" id="logo-dark-preview">
                        @else
                            <span class="text-xs text-slate-500" id="logo-dark-preview">No logo uploaded yet</span>
                        @endif
                    </div>
                    <label class="mt-2 flex items-center justify-center h-16 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg cursor-pointer hover:border-[#0C3B2E] hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                        <input type="file" name="site_logo_dark_file" accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml" class="hidden" data-preview="logo-dark">
                        <span class="text-xs text-slate-500">Upload (JPG/PNG/WebP/SVG)</span>
                    </label>
                </div>
                <div>
                    <label class="text-sm font-medium">Favicon</label>
                    <div class="mt-2 w-9 h-9 flex items-center justify-center">
                        @if(!empty($settings['site_favicon']->value))
                            <img src="{{ asset('storage/'.$settings['site_favicon']->value) }}" class="w-8 h-8 object-contain" alt="" loading="lazy" decoding="async" id="favicon-preview">
                        @else
                            <img src="{{ asset('images/favicon.png') }}" class="w-8 h-8 object-contain" alt="" id="favicon-preview">
                        @endif
                    </div>
                    <label class="mt-2 flex items-center justify-center h-16 border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-lg cursor-pointer hover:border-[#0C3B2E] hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                        <input type="file" name="site_favicon_file" accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml,image/x-icon" class="hidden" data-preview="favicon">
                        <span class="text-xs text-slate-500">Upload (PNG/JPG/SVG/ICO)</span>
                    </label>
                </div>
            </div>
        </div>

        <button type="submit" class="h-11 px-6 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold transition">Save</button>
    </form>
@endif

{{-- Live upload previews: show the selected image BEFORE saving, so the admin
     immediately sees what the hero image / logo / favicon will look like.
     After Save, the controller redirects back to the same tab and the
     "Preview" boxes show the freshly stored image. --}}
<script>
(function () {
    // Hero image preview
    var heroInput = document.getElementById('hero_person_image_file');
    var heroPreview = document.getElementById('hero-preview-img');
    var heroName = document.getElementById('hero-file-name');
    if (heroInput && heroPreview) {
        heroInput.addEventListener('change', function () {
            if (heroInput.files && heroInput.files[0]) {
                var file = heroInput.files[0];
                if (!file.type.startsWith('image/') || file.type === 'image/heic' || file.type === 'image/heif' || file.type === 'image/avif') {
                    heroName.textContent = '"' + file.name + '" is not a supported format. Please use JPG, PNG, GIF, WebP or BMP.';
                    heroName.classList.remove('hidden', 'text-emerald-700', 'dark:text-emerald-300');
                    heroName.classList.add('text-red-600', 'dark:text-red-400');
                    heroInput.value = '';
                    return;
                }
                if (file.size > 4 * 1024 * 1024) {
                    heroName.textContent = '"' + file.name + '" is ' + (file.size / (1024 * 1024)).toFixed(1) + ' MB. Please pick an image smaller than 4 MB, then click Save.';
                    heroName.classList.remove('hidden', 'text-emerald-700', 'dark:text-emerald-300');
                    heroName.classList.add('text-red-600', 'dark:text-red-400');
                    heroInput.value = '';
                    return;
                }
                heroPreview.src = URL.createObjectURL(file);
                if (heroName) {
                    heroName.textContent = file.name + ' selected. Click Save to apply.';
                    heroName.classList.remove('hidden', 'text-red-600', 'dark:text-red-400');
                    heroName.classList.add('text-emerald-700', 'dark:text-emerald-300');
                }
            }
        });
    }

    // Logo / favicon previews (inputs marked with data-preview)
    document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
        input.addEventListener('change', function () {
            if (!input.files || !input.files[0]) return;
            var file = input.files[0];
            var el = document.getElementById(input.dataset.preview + '-preview');
            var wrap = document.getElementById(input.dataset.preview + '-preview-wrap');
            if (file.type === 'image/svg+xml' || file.type === 'image/x-icon') {
                // SVG/ICO: just confirm the pick (object URLs work for these too)
                if (el && el.tagName === 'IMG') { el.src = URL.createObjectURL(file); }
                else if (wrap) { wrap.innerHTML = '<span class="text-xs font-medium text-emerald-700 dark:text-emerald-300">' + file.name + ' selected. Click Save.</span>'; }
                return;
            }
            if (el && el.tagName === 'IMG') {
                el.src = URL.createObjectURL(file);
            } else if (wrap) {
                wrap.innerHTML = '<img src="' + URL.createObjectURL(file) + '" class="h-7 w-auto" alt="">';
            }
            var label = input.parentElement.querySelector('span');
            if (label) { label.textContent = file.name; }
        });
    });
})();
</script>
@endsection
