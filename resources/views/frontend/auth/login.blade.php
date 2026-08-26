@extends('layouts.app')
@push('head')<meta name="robots" content="noindex, nofollow">@endpush

@section('content')
<div class="max-w-[460px] mx-auto px-4 py-12">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-7 sm:p-8">
        <div class="text-center mb-7">
            <div class="flex justify-center">
                @include('partials.logo', ['class' => 'h-8', 'textClass' => 'text-[22px]'])
            </div>
            <h1 class="font-extrabold text-2xl mt-3 text-slate-900 dark:text-white">Welcome back</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1.5">Sign in to your Huvanti account.</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 px-4 py-3 text-sm mb-4">{{ $errors->first() }}</div>
        @endif
        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 px-4 py-3 text-sm mb-4">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-slate-900 dark:text-slate-200">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}" autocomplete="email"
                    class="mt-1 w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-[#0C3B2E] focus:ring-4 focus:ring-[#0C3B2E]/15 outline-none text-sm text-slate-900 dark:text-white placeholder:text-slate-400"
                    placeholder="you@example.com">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-900 dark:text-slate-200">Password</label>
                <input type="password" name="password" required autocomplete="current-password"
                    class="mt-1 w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-[#0C3B2E] focus:ring-4 focus:ring-[#0C3B2E]/15 outline-none text-sm text-slate-900 dark:text-white placeholder:text-slate-400">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                <input type="checkbox" name="remember" class="rounded border-slate-300 dark:border-slate-700 text-[#0C3B2E]">
                Keep me signed in
            </label>

            <button type="submit"
                class="w-full h-11 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold text-sm transition">
                Sign in
            </button>
        </form>

        @if(\App\Models\Setting::get('google_enabled') === '1' && \App\Models\Setting::get('google_client_id'))
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200 dark:border-slate-700"></div></div>
                <div class="relative flex justify-center text-xs uppercase"><span class="bg-white dark:bg-slate-900 px-2 text-slate-500">Or</span></div>
            </div>
            <a href="{{ route('auth.google.redirect') }}" class="w-full h-11 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-medium text-sm flex items-center justify-center gap-2.5 transition">
                <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                Continue with Google
            </a>
        @endif

        <p class="text-sm text-slate-600 dark:text-slate-400 text-center mt-6">
            New to Huvanti?
            <a href="{{ route('register') }}" class="font-semibold text-[#0C3B2E] dark:text-emerald-300 hover:underline">Create an account</a>
        </p>
        <p class="text-[11px] text-slate-500 dark:text-slate-500 text-center mt-3">
            <a href="{{ route('password.request') }}" class="hover:text-[#0C3B2E] dark:hover:text-emerald-300">Forgot your password?</a>
        </p>
    </div>
</div>
@endsection
