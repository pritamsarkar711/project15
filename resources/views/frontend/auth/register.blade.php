@extends('layouts.app')
@push('head')<meta name="robots" content="noindex, nofollow">@endpush

@section('content')
<div class="max-w-[460px] mx-auto px-4 py-12">
    <div class="bg-white dark:bg-[#141815] border-2 border-[#141A16] dark:border-[#3A443D] shadow-[8px_8px_0_0_#F5C445] p-7 sm:p-8">
        <div class="text-center mb-7">
            <div class="flex justify-center">
                @include('partials.logo', ['class' => 'h-8', 'textClass' => 'text-[22px]'])
            </div>
            <h1 class="font-black text-[26px] mt-3 text-[#141A16] dark:text-[#F0F2EB] tracking-tight">Create your account</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1.5">Start writing on Huvanti in 60 seconds.</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 px-4 py-3 text-sm mb-4">{{ $errors->first() }}</div>
        @endif
        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 px-4 py-3 text-sm mb-4">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('register.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#141A16] dark:text-[#EDEFEA]">Display name</label>
                <input type="text" name="name" required value="{{ old('name') }}" autocomplete="name"
                    class="field mt-1.5 h-11 px-3 text-sm"
                    placeholder="Jane Doe">
            </div>
            <div>
                <label class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#141A16] dark:text-[#EDEFEA]">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}" autocomplete="email"
                    class="field mt-1.5 h-11 px-3 text-sm"
                    placeholder="you@example.com">
            </div>
            <div>
                <label class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#141A16] dark:text-[#EDEFEA]">Password</label>
                <input type="password" name="password" required autocomplete="new-password"
                    class="field mt-1.5 h-11 px-3 text-sm"
                    placeholder="At least 8 characters">
            </div>
            <div>
                <label class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-[#141A16] dark:text-[#EDEFEA]">Confirm password</label>
                <input type="password" name="password_confirmation" required autocomplete="new-password"
                    class="field mt-1.5 h-11 px-3 text-sm">
            </div>

            <button type="submit"
                class="w-full h-12 bg-[#141A16] hover:bg-[#0C3B2E] text-white font-extrabold text-[12.5px] uppercase tracking-[0.14em] transition shadow-[4px_4px_0_0_#F5C445]">
                Create account
            </button>
        </form>

        @if(\App\Models\Setting::get('google_enabled') === '1' && \App\Models\Setting::get('google_client_id'))
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-[#E4E4DA] dark:border-[#262C28]"></div></div>
                <div class="relative flex justify-center text-[10px] font-extrabold uppercase tracking-[0.2em]"><span class="bg-white dark:bg-[#141815] px-2 text-[#8B958C] dark:text-[#6B756C]">Or</span></div>
            </div>
            <a href="{{ route('auth.google.redirect') }}" class="w-full h-11 bg-white dark:bg-[#141815] border border-[#D8D8CC] dark:border-[#3A443D] hover:border-[#141A16] dark:hover:border-[#F5C445] text-[#141A16] dark:text-[#EDEFEA] font-bold text-[13.5px] flex items-center justify-center gap-2.5 transition">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                Continue with Google
            </a>
        @endif

        <p class="text-[13.5px] font-medium text-[#5C665E] dark:text-[#97A199] text-center mt-6">
            Already have an account?
            <a href="{{ route('login') }}" class="font-extrabold text-[#0C3B2E] dark:text-[#34D399] hover:underline underline-offset-4">Sign in</a>
        </p>
        <p class="text-[11.5px] text-[#8B958C] dark:text-[#6B756C] text-center mt-3 leading-relaxed">
            By creating an account you agree to our
            <a href="{{ route('terms') }}" class="underline">Terms</a> and
            <a href="{{ route('editorial') }}" class="underline">Editorial Policy</a>.
        </p>
    </div>
</div>
@endsection
