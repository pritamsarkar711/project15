@extends('layouts.admin')
@section('title','Settings')
@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Settings'],
    ]])
@endsection

@section('content')
<div class="flex items-center gap-1.5 mb-5 flex-wrap">
    @foreach(['general' => 'General', 'appearance' => 'Appearance & Fonts', 'hero' => 'Hero Section', 'ads' => 'Ad Placement', 'integrations' => 'Integrations & SEO'] as $key => $label)
        <a href="{{ route('admin.settings.index', $key !== 'general' ? ['tab'=>$key] : []) }}"
           class="h-9 px-4 inline-flex items-center text-sm font-medium border transition {{ (request('tab', 'general') === $key) ? 'bg-[#0C3B2E] text-white border-[#0C3B2E]' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">{{ $label }}</a>
    @endforeach
</div>

@if(request('tab') === 'integrations')
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5 max-w-3xl">
        @csrf
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

        <button type="submit" class="h-11 px-6 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold transition">Save Integrations</button>
    </form>

@elseif(request('tab') === 'appearance')
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5 max-w-3xl">
        @csrf
        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <h3 class="font-semibold">Site Font</h3>
            <div>
                <label class="text-sm font-medium">Font family</label>
                <select name="site_font_family" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    @foreach($fontOptions as $key => $label)
                        <option value="{{ $key }}" {{ old('site_font_family', $settings['site_font_family']->value ?? 'work-sans') === $key ? 'selected' : '' }} style="font-family:{{ $fonts[$key]['css'] }}">{{ $label }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-500 mt-1">Applies to entire site — frontend &amp; admin.</p>
            </div>
        </div>
        <button type="submit" class="h-11 px-6 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold transition">Save</button>
    </form>

@elseif(request('tab') === 'hero')
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-5 max-w-3xl">
        @csrf
        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <h3 class="font-semibold">Hero Headline</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Two phrases that alternate with a typing effect.</p>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Phrase 1</label>
                    <input type="text" name="hero_phrase_1" value="{{ old('hero_phrase_1', $settings['hero_phrase_1']->value ?? 'Explore Ideas.') }}" maxlength="80" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium">Phrase 2</label>
                    <input type="text" name="hero_phrase_2" value="{{ old('hero_phrase_2', $settings['hero_phrase_2']->value ?? 'Inspire Life.') }}" maxlength="80" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                </div>
            </div>
            <div>
                <label class="text-sm font-medium">Subtitle</label>
                <textarea name="hero_subtitle" rows="2" maxlength="500" class="mt-1 w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">{{ old('hero_subtitle', $settings['hero_subtitle']->value ?? 'Tech, health, finance, travel and more — one calm place to read.') }}</textarea>
            </div>
            <div>
                <label class="text-sm font-medium">Search placeholder</label>
                <input type="text" name="hero_search_placeholder" value="{{ old('hero_search_placeholder', $settings['hero_search_placeholder']->value ?? 'Search articles, topics, ideas...') }}" maxlength="120" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
            </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <h3 class="font-semibold">Hero Image</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Displayed in a circular frame. Auto-optimized (WebP, max 1600px wide).</p>
            <div class="grid sm:grid-cols-2 gap-5 items-start">
                <div>
                    <label class="text-sm font-medium">Upload</label>
                    <input type="file" name="hero_person_image_file" accept="image/png,image/jpeg,image/webp" class="mt-2 w-full text-sm">
                    <label class="mt-3 inline-flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300 cursor-pointer">
                        <input type="checkbox" name="hero_remove_image" value="1" class="text-emerald-600">
                        Remove image (use default)
                    </label>
                </div>
                <div>
                    <label class="text-sm font-medium">Current</label>
                    @php $heroImg = $settings['hero_person_image']->value ?? null; @endphp
                    <div class="mt-2 w-32 h-32 rounded-full overflow-hidden bg-[#0C3B2E] flex items-center justify-center">
                        <img src="{{ $heroImg ? asset('storage/'.$heroImg) : asset('images/hero-person.png') }}" alt="" class="w-full h-full object-cover" loading="lazy" decoding="async">
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="h-11 px-6 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold transition">Save</button>
    </form>

@elseif(request('tab') === 'ads')
    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5 max-w-3xl">
        @csrf
        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <div>
                <h3 class="font-semibold">In-Article Ad Frequency</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Controls how often an in-article ad is inserted inside blog post content. The ad is shown after every N paragraphs (where N is the value below).</p>
            </div>
            <div class="grid sm:grid-cols-2 gap-4 items-center">
                <div>
                    <label class="text-sm font-medium">Insert ad every N paragraphs</label>
                    <input type="number" name="ad_paragraph_frequency" min="1" max="10" value="{{ old('ad_paragraph_frequency', $settings['ad_paragraph_frequency']->value ?? '2') }}" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
                    <p class="text-xs text-slate-500 mt-1">Recommended: 2 (one ad after every 2 paragraphs). Range 1–10.</p>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 space-y-1">
                    <p><strong class="text-slate-700 dark:text-slate-300">Positions available:</strong></p>
                    <p>• <code>header</code> — above the homepage hero/categories</p>
                    <p>• <code>sidebar</code> — blog sidebar widget</p>
                    <p>• <code>in_article</code> — inside blog post content (every N paragraphs)</p>
                    <p>• <code>footer</code> — above the global footer</p>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-2 pt-3 border-t border-slate-200 dark:border-slate-700">Manage individual ads (code, position, scheduling) on the <a href="{{ route('admin.ads.index') }}" class="text-emerald-700 dark:text-emerald-300 hover:underline">Advertisements →</a> page.</p>
        </div>
        <button type="submit" class="h-11 px-6 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold transition">Save Ad Settings</button>
    </form>

@else
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-5 max-w-3xl">
        @csrf
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
                <input type="text" name="footer_copyright" value="{{ old('footer_copyright', $settings['footer_copyright']->value ?? '© '.date('Y').' Huvanti. All Rights Reserved.') }}" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm">
            </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold">Footer social media</h3>
                <label class="inline-flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300 cursor-pointer">
                    <input type="checkbox" name="social_enabled" value="1" {{ old('social_enabled', $settings['social_enabled']->value ?? '1') === '1' ? 'checked' : '' }} class="text-emerald-600">
                    Show in footer
                </label>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Leave blank to hide an icon.</p>
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
                    @if(!empty($settings['site_logo_light']->value))
                        <div class="mt-2 h-9 flex items-center bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-2">
                            <img src="{{ asset('storage/'.$settings['site_logo_light']->value) }}" class="h-7 w-auto" alt="" loading="lazy" decoding="async">
                        </div>
                    @endif
                    <input type="file" name="site_logo_light_file" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="mt-2 w-full text-xs">
                </div>
                <div>
                    <label class="text-sm font-medium">Logo · Dark</label>
                    @if(!empty($settings['site_logo_dark']->value))
                        <div class="mt-2 h-9 flex items-center bg-slate-900 border border-slate-700 px-2">
                            <img src="{{ asset('storage/'.$settings['site_logo_dark']->value) }}" class="h-7 w-auto" alt="" loading="lazy" decoding="async">
                        </div>
                    @endif
                    <input type="file" name="site_logo_dark_file" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="mt-2 w-full text-xs">
                </div>
                <div>
                    <label class="text-sm font-medium">Favicon</label>
                    @if(!empty($settings['site_favicon']->value))
                        <div class="mt-2 w-9 h-9 flex items-center justify-center">
                            <img src="{{ asset('storage/'.$settings['site_favicon']->value) }}" class="w-8 h-8 object-contain" alt="" loading="lazy" decoding="async">
                        </div>
                    @endif
                    <input type="file" name="site_favicon_file" accept="image/png,image/jpeg,image/webp,image/x-icon" class="mt-2 w-full text-xs">
                </div>
            </div>
        </div>

        <button type="submit" class="h-11 px-6 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold transition">Save</button>
    </form>
@endif
@endsection
