@extends('layouts.app')
@push('head')<meta name="robots" content="noindex, nofollow">@endpush

@section('content')
<div class="max-w-[460px] mx-auto px-4 py-12">
    <div class="card-elev p-7 sm:p-8">
        <div class="text-center mb-7">
            <div class="flex justify-center">@include('partials.logo', ['class' => 'h-8', 'textClass' => 'text-[22px]'])</div>
            <h1 class="font-extrabold text-2xl mt-3 text-slate-900 dark:text-white">Reset your password</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1.5">We'll email you a secure link to set a new password.</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 px-4 py-3 text-sm mb-4 rounded-xl">{{ $errors->first() }}</div>
        @endif
        @if(session('status'))
            <div class="bg-[#F0F7F3] dark:bg-[#2E7856]/10 border border-[#C7E0D4] dark:border-[#2E7856]/30 text-[#173A2A] dark:text-[#6FB393] px-4 py-3 text-sm mb-4 rounded-xl">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label class="label">Email</label>
                <input type="email" name="email" required value="{{ old('email') }}" autocomplete="email" autofocus
                    class="input !h-11"
                    placeholder="you@example.com">
            </div>
            <button type="submit"
                class="btn btn-primary w-full">
                Send reset link
            </button>
        </form>

        <p class="text-sm text-slate-600 dark:text-slate-400 text-center mt-6">
            <a href="{{ route('login') }}" class="font-semibold text-[#2E7856] dark:text-[#6FB393] hover:underline underline-offset-4">Back to sign in</a>
        </p>
    </div>
</div>
@endsection
