@extends('layouts.app')
@push('head')<meta name="robots" content="noindex, nofollow">@endpush

@section('content')
<div class="max-w-[460px] mx-auto px-4 py-12">
    <div class="bg-white dark:bg-[#141815] border-2 border-[#141A16] dark:border-[#3A443D] shadow-[8px_8px_0_0_#F5C445] p-7 sm:p-8">
        <div class="text-center mb-7">
            <div class="flex justify-center">@include('partials.logo', ['class' => 'h-8', 'textClass' => 'text-[22px]'])</div>
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
                    class="field mt-1.5 h-11 px-3 text-sm"
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
