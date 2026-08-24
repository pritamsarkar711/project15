@extends('layouts.admin')
@section('title','Security')
@section('admin-breadcrumbs')
    @include('admin.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Settings', 'route' => 'admin.settings.index'],
        ['label' => 'Security'],
    ]])
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
        <h3 class="font-semibold">Two-Factor Authentication</h3>

        @if($setupSecret)
            <div class="mt-5 space-y-4">
                <div class="flex flex-col sm:flex-row gap-5 items-start">
                    <div class="p-3 bg-white border border-slate-200 dark:border-slate-700 shrink-0">
                        <img src="{{ $qrUrl }}" alt="2FA QR code" class="w-[200px] h-[200px]" loading="lazy" decoding="async">
                    </div>
                    <div class="space-y-3">
                        <p class="text-sm font-medium">1. Scan the QR code with Google Authenticator (or any TOTP app).</p>
                        <div>
                            <p class="text-sm font-medium">2. Or enter this secret manually:</p>
                            <code class="block mt-1 px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono break-all">{{ $setupSecret }}</code>
                        </div>
                        <p class="text-sm font-medium">3. Enter the 6-digit code from the app to confirm:</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.settings.2fa.confirm') }}" class="flex gap-2">
                    @csrf
                    <input type="text" name="two_factor_code" inputmode="numeric" maxlength="6" required placeholder="123456" class="h-11 w-40 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-mono tracking-widest text-center placeholder:tracking-normal placeholder:font-sans">
                    <button type="submit" class="h-11 px-6 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold transition">Confirm & Enable</button>
                </form>
                <form method="POST" action="{{ route('admin.settings.2fa.disable') }}">@csrf
                    <button type="submit" class="text-sm text-slate-500 dark:text-slate-400 hover:underline">Cancel setup</button>
                </form>
            </div>
        @elseif($user->google2fa_secret)
            <div class="mt-4 p-4 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-sm font-semibold text-emerald-800 dark:text-emerald-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 11 3 3L22 4"/></svg>
                    Enabled — a 6-digit code is required at login.
                </div>
                <form method="POST" action="{{ route('admin.settings.2fa.disable') }}">@csrf
                    <button type="submit" class="h-9 px-4 text-sm font-semibold border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-white dark:hover:bg-slate-800 transition">Disable 2FA</button>
                </form>
            </div>
        @else
            <div class="mt-4 p-4 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-3">
                <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">Disabled</span>
                <form method="POST" action="{{ route('admin.settings.2fa.start') }}">@csrf
                    <button type="submit" class="h-9 px-4 bg-[#0C3B2E] hover:bg-[#072A20] text-white text-sm font-semibold transition">Enable 2FA</button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
