@extends('layouts.app')
@push('head')<meta name="robots" content="noindex, nofollow">@endpush

@section('content')
<div class="max-w-[460px] mx-auto px-4 py-12">
    <div class="card-elev p-7 sm:p-8">
        <div class="text-center mb-7">
            <div class="flex justify-center">@include('partials.logo', ['class' => 'h-8', 'textClass' => 'text-[22px]'])</div>
            <h1 class="font-extrabold text-2xl mt-3 text-slate-900 dark:text-white">Set a new password</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1.5">Choose a strong password you haven't used before.</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 px-4 py-3 text-sm mb-4 rounded-xl">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label class="label">Email</label>
                <input type="email" name="email" required value="{{ old('email', $email ?? '') }}" autocomplete="email" autofocus
                    class="input !h-11"
                    placeholder="you@example.com">
            </div>
            <div>
                <label class="label">New password</label>
                <input type="password" name="password" required autocomplete="new-password"
                    class="input !h-11"
                    placeholder="At least 8 characters">
            </div>
            <div>
                <label class="label">Confirm new password</label>
                <input type="password" name="password_confirmation" required autocomplete="new-password"
                    class="input !h-11">
            </div>
            <button type="submit"
                class="btn btn-primary w-full">
                Reset password
            </button>
        </form>
    </div>
</div>
@endsection
