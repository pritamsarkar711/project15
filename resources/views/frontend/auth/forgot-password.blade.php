@extends('layouts.app')

@section('content')
<div class="max-w-[460px] mx-auto px-4 py-12">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-7 sm:p-8">
        <div class="text-center mb-7">
            <div class="w-12 h-12 bg-[#0C3B2E] flex items-center justify-center mx-auto font-extrabold text-white text-xl">H</div>
            <h1 class="font-extrabold text-2xl mt-3 text-slate-900 dark:text-white">Reset your password</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1.5">We'll email you a secure link to set a new password.</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 px-4 py-3 text-sm mb-4">{{ $errors->first() }}</div>
        @endif
        @if(session('status'))
            <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300 px-4 py-3 text-sm mb-4">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-slate-900 dark:text-slate-200">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}" autocomplete="email" autofocus
                    class="mt-1 w-full h-11 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 focus:border-[#0C3B2E] focus:ring-4 focus:ring-[#0C3B2E]/15 outline-none text-sm text-slate-900 dark:text-white placeholder:text-slate-400"
                    placeholder="you@example.com">
            </div>
            <button type="submit"
                class="w-full h-11 bg-[#0C3B2E] hover:bg-[#072A20] text-white font-semibold text-sm transition">
                Send reset link
            </button>
        </form>

        <p class="text-sm text-slate-600 dark:text-slate-400 text-center mt-6">
            <a href="{{ route('login') }}" class="font-semibold text-[#0C3B2E] dark:text-emerald-300 hover:underline">Back to sign in</a>
        </p>
    </div>
</div>
@endsection
