@extends('layouts.app')
@push('head')<meta name="robots" content="noindex, nofollow">@endpush

@section('content')
<div class="max-w-[460px] mx-auto px-4 py-12">
    <div class="bg-white dark:bg-[#131A17] border border-slate-200 dark:border-[#2C3833] rounded-2xl shadow-xl shadow-slate-900/5 p-7 sm:p-8">
        <div class="text-center mb-7">
            <div class="flex justify-center">@include('partials.logo', ['class' => 'h-8', 'textClass' => 'text-[22px]'])</div>
            <h1 class="font-extrabold text-2xl mt-4 text-slate-900 dark:text-[#F1F5F4]">Reset your password</h1>
            <p class="text-sm text-slate-500 dark:text-[#8FA398] mt-1.5">We'll email you a secure link to set a new password.</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/25 text-red-700 dark:text-red-400 px-4 py-3 rounded-xl text-sm mb-4">{{ $errors->first() }}</div>
        @endif
        @if(session('status'))
            <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/25 text-emerald-800 dark:text-emerald-300 px-4 py-3 rounded-xl text-sm mb-4">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-[13px] font-semibold text-slate-700 dark:text-[#C6D2CB]">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}" autocomplete="email" autofocus
                    class="field mt-1.5 h-11 px-3.5 text-sm"
                    placeholder="you@example.com">
            </div>
            <button type="submit"
                class="w-full h-12 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-[15px] shadow-sm shadow-emerald-600/30 transition">
                Send reset link
            </button>
        </form>

        <p class="text-sm text-slate-500 dark:text-[#8FA398] text-center mt-7">
            <a href="{{ route('login') }}" class="font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">Back to sign in</a>
        </p>
    </div>
</div>
@endsection
