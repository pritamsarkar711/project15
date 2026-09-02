@extends('layouts.admin')

@section('title', 'Social Auto-Post')

@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Social Auto-Post'],
    ]])
@endsection

@section('content')
<div class="space-y-5">

    <div class="panel-card p-6">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <h3 class="font-semibold text-[#101319] dark:text-white">Automation Status</h3>
            <label class="group inline-flex items-center gap-2 cursor-pointer shrink-0">
                <span class="relative inline-flex shrink-0">
                    <input type="checkbox" name="social_autopost_enabled" form="social-form" value="1" {{ $enabled ? 'checked' : '' }} class="peer sr-only">
                    <span class="block w-11 h-6 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-[var(--brand)] transition-colors"></span>
                    <span class="pointer-events-none absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                </span>
                <span class="text-sm font-semibold text-slate-500 group-has-checked:hidden">Disabled</span>
                <span class="hidden text-sm font-semibold text-[var(--brand-ink)] dark:text-[var(--brand-light)] group-has-checked:inline">Enabled</span>
            </label>
        </div>
        @if(!$enabled)
            <p class="mt-3 text-xs text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 p-3 rounded-lg">Turn on, then save.</p>
        @endif
    </div>

    <form id="social-form" method="POST" action="{{ route('admin.social.update') }}" class="space-y-5">
        @csrf

        {{-- Message template --}}
        <div class="panel-card p-6 space-y-3">
            <h3 class="font-semibold text-[#101319] dark:text-white">Post Message Template</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">Placeholders: <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1">{{ '{title}' }}</code> <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1">{{ '{url}' }}</code> <code class="font-mono bg-slate-100 dark:bg-slate-800 px-1">{{ '{site}' }}</code></p>
            <textarea name="social_message_template" rows="3" class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono">{{ old('social_message_template', $template) }}</textarea>
        </div>

        {{-- X --}}
        @php $netEnabled = fn($n) => request()->old($n.'_enabled', \App\Models\Setting::get($n.'_enabled', '0')) === '1'; @endphp
        <div class="panel-card p-6 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-lg text-white flex items-center justify-center shrink-0" style="background:#000000"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.9 1.15h3.68l-8.04 9.19L24 22.85h-7.41l-5.8-7.58-6.64 7.58H.47l8.6-9.83L0 1.15h7.59l5.24 6.93 6.07-6.93Zm-1.29 19.5h2.04L6.49 3.24H4.3l13.31 17.41Z"/></svg></span>
                    <div>
                        <h3 class="font-semibold text-[#101319] dark:text-white">X (Twitter)</h3>
                    </div>
                </div>
                <label class="group inline-flex items-center gap-2 cursor-pointer shrink-0">
                    <span class="relative inline-flex shrink-0">
                        <input type="checkbox" name="x_enabled" value="1" {{ $netEnabled('x') ? 'checked' : '' }} class="peer sr-only">
                        <span class="block w-11 h-6 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-[var(--brand)] transition-colors"></span>
                        <span class="pointer-events-none absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                    </span>
                    <span class="text-sm font-semibold text-slate-500 group-has-checked:hidden">Disabled</span>
                    <span class="hidden text-sm font-semibold text-[var(--brand-ink)] dark:text-[var(--brand-light)] group-has-checked:inline">Enabled</span>
                </label>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">API Key (Consumer Key)</label>
                    <input type="password" name="x_consumer_key" placeholder="{{ $social->mask('x_consumer_key') ?: 'Paste your X API key' }}" autocomplete="new-password" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono">
                </div>
                <div>
                    <label class="text-sm font-medium">API Key Secret</label>
                    <input type="password" name="x_consumer_secret" placeholder="{{ $social->mask('x_consumer_secret') ?: 'Paste the secret' }}" autocomplete="new-password" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono">
                </div>
                <div>
                    <label class="text-sm font-medium">Access Token</label>
                    <input type="password" name="x_access_token" placeholder="{{ $social->mask('x_access_token') ?: 'Paste your access token' }}" autocomplete="new-password" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono">
                </div>
                <div>
                    <label class="text-sm font-medium">Access Token Secret</label>
                    <input type="password" name="x_access_token_secret" placeholder="{{ $social->mask('x_access_token_secret') ?: 'Paste the token secret' }}" autocomplete="new-password" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono">
                </div>
            </div>
            <p class="text-xs text-slate-500"><a href="https://developer.x.com" target="_blank" rel="noopener" class="text-[var(--brand-ink)] dark:text-[var(--brand-light)] hover:underline">developer.x.com</a> · <button type="button" class="font-semibold text-[var(--brand-ink)] dark:text-[var(--brand-light)] hover:underline" data-test-network="x">Test connection</button></p>
        </div>

        {{-- Facebook --}}
        <div class="panel-card p-6 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-lg text-white flex items-center justify-center shrink-0" style="background:#1877F2"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.9h2.54V9.85c0-2.52 1.49-3.91 3.77-3.91 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.78-1.63 1.57v1.88h2.78l-.45 2.9h-2.33V22c4.78-.76 8.44-4.92 8.44-9.94Z"/></svg></span>
                    <div>
                        <h3 class="font-semibold text-[#101319] dark:text-white">Facebook Page</h3>
                    </div>
                </div>
                <label class="group inline-flex items-center gap-2 cursor-pointer shrink-0">
                    <span class="relative inline-flex shrink-0">
                        <input type="checkbox" name="facebook_enabled" value="1" {{ $netEnabled('facebook') ? 'checked' : '' }} class="peer sr-only">
                        <span class="block w-11 h-6 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-[var(--brand)] transition-colors"></span>
                        <span class="pointer-events-none absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                    </span>
                    <span class="text-sm font-semibold text-slate-500 group-has-checked:hidden">Disabled</span>
                    <span class="hidden text-sm font-semibold text-[var(--brand-ink)] dark:text-[var(--brand-light)] group-has-checked:inline">Enabled</span>
                </label>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Page ID</label>
                    <input type="text" name="facebook_page_id" value="{{ old('facebook_page_id', \App\Models\Setting::get('facebook_page_id', '')) }}" placeholder="123456789012345" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono">
                </div>
                <div>
                    <label class="text-sm font-medium">Page Access Token</label>
                    <input type="password" name="facebook_page_token" placeholder="{{ $social->mask('facebook_page_token') ?: 'Paste a long-lived Page token' }}" autocomplete="new-password" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono">
                </div>
            </div>
            <p class="text-xs text-slate-500">Token: <a href="https://developers.facebook.com/tools/explorer/" target="_blank" rel="noopener" class="text-[var(--brand-ink)] dark:text-[var(--brand-light)] hover:underline">Graph API Explorer</a> · <button type="button" class="font-semibold text-[var(--brand-ink)] dark:text-[var(--brand-light)] hover:underline" data-test-network="facebook">Test connection</button></p>
        </div>

        {{-- LinkedIn --}}
        <div class="panel-card p-6 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-lg text-white flex items-center justify-center shrink-0" style="background:#0A66C2"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.86 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.47-.9 1.63-1.85 3.36-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28ZM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12ZM7.12 20.45H3.55V9h3.57v11.45Z"/></svg></span>
                    <div>
                        <h3 class="font-semibold text-[#101319] dark:text-white">LinkedIn</h3>
                    </div>
                </div>
                <label class="group inline-flex items-center gap-2 cursor-pointer shrink-0">
                    <span class="relative inline-flex shrink-0">
                        <input type="checkbox" name="linkedin_enabled" value="1" {{ $netEnabled('linkedin') ? 'checked' : '' }} class="peer sr-only">
                        <span class="block w-11 h-6 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-[var(--brand)] transition-colors"></span>
                        <span class="pointer-events-none absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                    </span>
                    <span class="text-sm font-semibold text-slate-500 group-has-checked:hidden">Disabled</span>
                    <span class="hidden text-sm font-semibold text-[var(--brand-ink)] dark:text-[var(--brand-light)] group-has-checked:inline">Enabled</span>
                </label>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Author URN</label>
                    <input type="text" name="linkedin_author_urn" value="{{ old('linkedin_author_urn', \App\Models\Setting::get('linkedin_author_urn', '')) }}" placeholder="urn:li:person:XXX or urn:li:organization:123" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono">
                </div>
                <div>
                    <label class="text-sm font-medium">Access Token</label>
                    <input type="password" name="linkedin_access_token" placeholder="{{ $social->mask('linkedin_access_token') ?: 'Paste your access token' }}" autocomplete="new-password" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono">
                </div>
            </div>
            <p class="text-xs text-slate-500">App: <a href="https://www.linkedin.com/developers/" target="_blank" rel="noopener" class="text-[var(--brand-ink)] dark:text-[var(--brand-light)] hover:underline">linkedin.com/developers</a> · <button type="button" class="font-semibold text-[var(--brand-ink)] dark:text-[var(--brand-light)] hover:underline" data-test-network="linkedin">Test connection</button></p>
        </div>

        {{-- Instagram --}}
        <div class="panel-card p-6 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-lg text-white flex items-center justify-center shrink-0" style="background:radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%)"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.72 3.72 0 0 1-1.38-.9 3.72 3.72 0 0 1-.9-1.38c-.16-.42-.36-1.06-.41-2.23-.06-1.27-.07-1.65-.07-4.85s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41 1.27-.06 1.65-.07 4.85-.07M12 0C8.74 0 8.33.01 7.05.07 5.78.13 4.9.33 4.14.63a5.9 5.9 0 0 0-2.13 1.38A5.9 5.9 0 0 0 .63 4.14C.33 4.9.13 5.78.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.06 1.27.26 2.15.56 2.91.31.8.72 1.47 1.38 2.13a5.9 5.9 0 0 0 2.13 1.38c.76.3 1.64.5 2.91.56C8.33 23.99 8.74 24 12 24s3.67-.01 4.95-.07c1.27-.06 2.15-.26 2.91-.56a5.9 5.9 0 0 0 2.13-1.38 5.9 5.9 0 0 0 1.38-2.13c.3-.76.5-1.64.56-2.91.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.06-1.27-.26-2.15-.56-2.91a5.9 5.9 0 0 0-1.38-2.13A5.9 5.9 0 0 0 19.86.63C19.1.33 18.22.13 16.95.07 15.67.01 15.26 0 12 0Zm0 5.84a6.16 6.16 0 1 0 0 12.32 6.16 6.16 0 0 0 0-12.32ZM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm7.85-10.4a1.44 1.44 0 1 1-2.88 0 1.44 1.44 0 0 1 2.88 0Z"/></svg></span>
                    <div>
                        <h3 class="font-semibold text-[#101319] dark:text-white">Instagram</h3>
                    </div>
                </div>
                <label class="group inline-flex items-center gap-2 cursor-pointer shrink-0">
                    <span class="relative inline-flex shrink-0">
                        <input type="checkbox" name="instagram_enabled" value="1" {{ $netEnabled('instagram') ? 'checked' : '' }} class="peer sr-only">
                        <span class="block w-11 h-6 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-[var(--brand)] transition-colors"></span>
                        <span class="pointer-events-none absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                    </span>
                    <span class="text-sm font-semibold text-slate-500 group-has-checked:hidden">Disabled</span>
                    <span class="hidden text-sm font-semibold text-[var(--brand-ink)] dark:text-[var(--brand-light)] group-has-checked:inline">Enabled</span>
                </label>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Instagram Business Account ID</label>
                    <input type="text" name="instagram_user_id" value="{{ old('instagram_user_id', \App\Models\Setting::get('instagram_user_id', '')) }}" placeholder="1784xxxxxxxxxxx" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono">
                </div>
                <div>
                    <label class="text-sm font-medium">Access Token</label>
                    <input type="password" name="instagram_access_token" placeholder="{{ $social->mask('instagram_access_token') ?: 'Paste the IG/FB token' }}" autocomplete="new-password" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono">
                </div>
            </div>
            <p class="text-xs text-slate-500"><button type="button" class="font-semibold text-[var(--brand-ink)] dark:text-[var(--brand-light)] hover:underline" data-test-network="instagram">Test connection</button></p>
        </div>

        {{-- Telegram --}}
        <div class="panel-card p-6 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-lg text-white flex items-center justify-center shrink-0" style="background:#229ED9"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.91 3.79 20.3 20.84c-.25 1.21-.98 1.5-1.99.94l-5.5-4.07-2.66 2.57c-.3.3-.55.55-1.1.55l.39-5.6 10.19-9.2c.44-.4-.1-.62-.69-.22L6.32 13.21.64 11.44c-1.18-.37-1.2-1.18.25-1.75l21.26-8.2c.99-.37 1.86.22 1.76 2.3Z"/></svg></span>
                    <div>
                        <h3 class="font-semibold text-[#101319] dark:text-white">Telegram</h3>
                    </div>
                </div>
                <label class="group inline-flex items-center gap-2 cursor-pointer shrink-0">
                    <span class="relative inline-flex shrink-0">
                        <input type="checkbox" name="telegram_enabled" value="1" {{ $netEnabled('telegram') ? 'checked' : '' }} class="peer sr-only">
                        <span class="block w-11 h-6 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-[var(--brand)] transition-colors"></span>
                        <span class="pointer-events-none absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                    </span>
                    <span class="text-sm font-semibold text-slate-500 group-has-checked:hidden">Disabled</span>
                    <span class="hidden text-sm font-semibold text-[var(--brand-ink)] dark:text-[var(--brand-light)] group-has-checked:inline">Enabled</span>
                </label>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Bot Token</label>
                    <input type="password" name="telegram_bot_token" placeholder="{{ $social->mask('telegram_bot_token') ?: '123456:ABC-DEF…' }}" autocomplete="new-password" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono">
                </div>
                <div>
                    <label class="text-sm font-medium">Chat ID (@channelusername or -100…)</label>
                    <input type="text" name="telegram_chat_id" value="{{ old('telegram_chat_id', \App\Models\Setting::get('telegram_chat_id', '')) }}" placeholder="@huvanti" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono">
                </div>
            </div>
            <p class="text-xs text-slate-500">Bot: <a href="https://t.me/BotFather" target="_blank" rel="noopener" class="text-[var(--brand-ink)] dark:text-[var(--brand-light)] hover:underline">@BotFather</a> · <button type="button" class="font-semibold text-[var(--brand-ink)] dark:text-[var(--brand-light)] hover:underline" data-test-network="telegram">Test connection</button></p>
        </div>

        {{-- Pinterest --}}
        <div class="panel-card p-6 space-y-4">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-lg text-white flex items-center justify-center shrink-0" style="background:#E60023"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0a12 12 0 0 0-4.37 23.17c-.1-.92-.2-2.34.04-3.35l1.4-5.98s-.36-.72-.36-1.78c0-1.66.96-2.9 2.16-2.9 1.02 0 1.51.77 1.51 1.69 0 1.02-.65 2.56-1 3.98-.28 1.19.6 2.17 1.78 2.17 2.14 0 3.78-2.25 3.78-5.51 0-2.88-2.07-4.9-5.02-4.9a5.2 5.2 0 0 0-5.43 5.22c0 1.03.4 2.14.9 2.75.1.12.11.22.08.34l-.33 1.36c-.05.22-.17.27-.4.16-1.5-.7-2.43-2.88-2.43-4.64 0-3.78 2.74-7.25 7.92-7.25 4.15 0 7.38 2.96 7.38 6.91 0 4.13-2.6 7.45-6.22 7.45-1.21 0-2.36-.63-2.75-1.38l-.75 2.85c-.27 1.04-1 2.35-1.49 3.15A12 12 0 1 0 12 0Z"/></svg></span>
                    <div>
                        <h3 class="font-semibold text-[#101319] dark:text-white">Pinterest</h3>
                    </div>
                </div>
                <label class="group inline-flex items-center gap-2 cursor-pointer shrink-0">
                    <span class="relative inline-flex shrink-0">
                        <input type="checkbox" name="pinterest_enabled" value="1" {{ $netEnabled('pinterest') ? 'checked' : '' }} class="peer sr-only">
                        <span class="block w-11 h-6 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-[var(--brand)] transition-colors"></span>
                        <span class="pointer-events-none absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
                    </span>
                    <span class="text-sm font-semibold text-slate-500 group-has-checked:hidden">Disabled</span>
                    <span class="hidden text-sm font-semibold text-[var(--brand-ink)] dark:text-[var(--brand-light)] group-has-checked:inline">Enabled</span>
                </label>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium">Board ID</label>
                    <input type="text" name="pinterest_board_id" value="{{ old('pinterest_board_id', \App\Models\Setting::get('pinterest_board_id', '')) }}" placeholder="Board id from the API" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono">
                </div>
                <div>
                    <label class="text-sm font-medium">Access Token</label>
                    <input type="password" name="pinterest_access_token" placeholder="{{ $social->mask('pinterest_access_token') ?: 'Paste your token' }}" autocomplete="new-password" class="mt-1 w-full h-10 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono">
                </div>
            </div>
            <p class="text-xs text-slate-500">App: <a href="https://developers.pinterest.com/" target="_blank" rel="noopener" class="text-[var(--brand-ink)] dark:text-[var(--brand-light)] hover:underline">developers.pinterest.com</a> · <button type="button" class="font-semibold text-[var(--brand-ink)] dark:text-[var(--brand-light)] hover:underline" data-test-network="pinterest">Test connection</button></p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <button type="submit" class="h-11 px-6 rounded-lg bg-[var(--brand)] hover:bg-[var(--brand-strong)] text-white font-semibold transition">Save All Settings</button>
            <span class="text-xs text-slate-400">Leave a field blank to keep its saved value.</span>
        </div>
    </form>

    {{-- Delivery log --}}
    <div class="panel-card p-6">
        <h3 class="font-semibold text-[#101319] dark:text-white mb-3">Recent Auto-Posts</h3>
        @if($rows->isEmpty())
            <p class="text-sm text-slate-500 dark:text-slate-400">Nothing shared yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-slate-400 uppercase tracking-wide border-b border-[#e6e8ee] dark:border-[#22262e]">
                            <th class="py-2 pr-3 font-semibold">Post</th>
                            <th class="py-2 pr-3 font-semibold">Network</th>
                            <th class="py-2 pr-3 font-semibold">Status</th>
                            <th class="py-2 pr-3 font-semibold">When</th>
                            <th class="py-2 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $row)
                            <tr class="border-b border-[#eef0f4] dark:border-[#1c1f26]">
                                <td class="py-2.5 pr-3 max-w-[220px] truncate">
                                    @if($row->post)
                                        <a href="{{ route('admin.posts.edit', $row->post->id) }}" class="font-medium text-slate-700 dark:text-slate-200 hover:text-[var(--brand-ink)] dark:hover:text-[var(--brand-light)]">{{ $row->post->title }}</a>
                                    @else <span class="text-slate-400">Deleted post</span> @endif
                                </td>
                                <td class="py-2.5 pr-3 text-slate-600 dark:text-slate-300">{{ \App\Models\SocialPublish::networkLabel($row->network) }}</td>
                                <td class="py-2.5 pr-3">
                                    @if($row->status === 'success')
                                        <span class="inline-flex items-center gap-1 text-[var(--brand-ink)] dark:text-[var(--brand-light)] font-semibold text-xs">Published</span>
                                    @elseif($row->status === 'failed')
                                        <span class="inline-flex items-center gap-1 text-red-600 dark:text-red-400 font-semibold text-xs" title="{{ $row->error }}">Failed — {{ \Illuminate\Support\Str::limit($row->error, 60) }}</span>
                                    @else
                                        <span class="text-slate-400 text-xs">Pending</span>
                                    @endif
                                </td>
                                <td class="py-2.5 pr-3 text-xs text-slate-400">{{ $row->updated_at->diffForHumans() }}</td>
                                <td class="py-2.5 text-right">
                                    @if($row->status !== 'success' && $row->post && $row->post->status === 'published')
                                        <form method="POST" action="{{ route('admin.social.retry', $row) }}">@csrf
                                            <button type="submit" class="text-xs font-semibold px-3 h-8 rounded-lg border border-[#e6e8ee] dark:border-[#2c313c] hover:bg-[#f7f8fa] dark:hover:bg-[#1c1f26]">Retry</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // "Test connection" — pings the network's API without publishing.
    document.querySelectorAll('[data-test-network]').forEach(function(btn){
        btn.addEventListener('click', function(){
            var network = btn.getAttribute('data-test-network');
            var original = btn.textContent;
            btn.textContent = 'Testing…';
            btn.disabled = true;
            fetch('{{ route('admin.social.test') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ network: network })
            }).then(function(r){ return r.json(); }).then(function(data){
                var msg = data.ok ? (data.message || 'Connected!') : (data.error || 'Test failed.');
                alert((data.ok ? 'Connected: ' : 'Failed: ') + msg);
                setTimeout(function(){ btn.textContent = original; btn.disabled = false; }, 1500);
            }).catch(function(){
                btn.textContent = original; btn.disabled = false;
                alert('Network error while testing.');
            });
        });
    });
</script>
@endpush
@endsection
