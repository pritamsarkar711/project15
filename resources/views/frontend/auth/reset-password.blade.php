@extends('layouts.app')
@push('head')<meta name="robots" content="noindex, nofollow">@endpush

@section('content')
<div class="max-w-[460px] mx-auto px-4 py-12">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-7 sm:p-8">
        <div class="text-center mb-7">
            <div class="flex justify-center">@include('partials.logo', ['class' => 'h-8', 'textClass' => 'text-[22px]'])</div>
            <h1 class="font-extrabold text-2xl mt-3 text-slate-900 dark:text-white">Set a new password</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1.5">Choose a strong password you haven't used before.</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 px-4 py-3 text-sm mb-4">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label class="text-sm font-medium text-slate-900 dark:text-slate-200">Email</label>
                <input type="email" name="email" required value="{{ old('email', $email ?? '') }}" autocomplete="email" autofocus
                    class="mt-1 w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-[#0C3B2E] focus:ring-4 focus:ring-[#0C3B2E]/15 outline-none text-sm text-slate-900 dark:text-white placeholder:text-slate-400"
                    placeholder="you@example.com">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-900 dark:text-slate-200">New password</label>
                <input type="password" name="password" required autocomplete="new-password"
                    class="mt-1 w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-[#0C3B2E] focus:ring-4 focus:ring-[#0C3B2E]/15 outline-none text-sm text-slate-900 dark:text-white placeholder:text-slate-400"
                    placeholder="At least 8 characters">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-900 dark:text-slate-200">Confirm new password</label>
                <input type="password" name="password_confirmation" required autocomplete="new-password"
                    class="mt-1 w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-[#0C3B2E] focus:ring-4 focus:ring-[#0C3B2E]/15 outline-none text-sm text-slate-900 dark:text-white">
            </div>
            <button type="submit"
                class="w-full h-11 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold text-sm transition">
                Reset password
            </button>
        </form>
    </div>
</div>
@endsection
